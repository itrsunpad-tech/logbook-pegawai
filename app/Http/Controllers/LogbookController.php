<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLogbookRequest;
use App\Models\Holiday;
use App\Models\Logbook;
use App\Models\Shift;
use App\Models\User;
use App\Support\WorkSchedule;
use App\Notifications\LogbookStatusChanged;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class LogbookController extends Controller
{
    /** Maksimal pengisian on call per hari. */
    public const MAKS_ONCALL = 1;

    /** Minimal durasi lembur yang dapat diajukan. */
    public const MIN_JAM_LEMBUR = 2;

    /** Minimal durasi on call yang dapat diajukan. */
    public const MIN_JAM_ONCALL = 4;

    public function index(): View
    {
        $hariIni = now()->format('Y-m-d');
        $batas = now()->endOfDay();
        $dibuka = now()->lte($batas);

        $maksOncall = self::MAKS_ONCALL;
        $minJam = self::MIN_JAM_LEMBUR;
        $minJamOncall = self::MIN_JAM_ONCALL;

        $logbooks = Logbook::query()
            ->where('user_id', Auth::id())
            ->latest('tanggal')
            ->latest('id')
            ->get();

        $unit = Auth::user()->employee_unit;
        $namaShiftDiizinkan = WorkSchedule::shiftNames($unit);
        $shifts = Shift::query()
            ->whereIn('nama', $namaShiftDiizinkan)
            ->get()
            ->sortBy(fn (Shift $shift) => array_search($shift->nama, $namaShiftDiizinkan, true))
            ->values();
        $grupShift = WorkSchedule::groups($unit);
        $opsiShift = [
            'Office hour' => $shifts->whereIn('nama', $grupShift['oh']),
            'Shift' => $shifts->whereIn('nama', $grupShift['shift']),
            'Status' => $shifts->whereIn('nama', config('work_schedules.common', [])),
        ];
        $namaDefaultShift = WorkSchedule::defaultShiftName($unit);
        $shiftDefaultId = $namaDefaultShift
            ? $shifts->firstWhere('nama', $namaDefaultShift)?->id
            : null;

        // Kalender: bulan lalu + bulan berjalan
        [$mulaiKalender, $akhirKalender] = $this->rentangKalender();

        $kalender = $this->dataKalender($mulaiKalender, $akhirKalender);
        $libur = $this->dataLibur($mulaiKalender, $akhirKalender);
        $notifikasiStatus = Auth::user()->unreadNotifications()
            ->where('type', LogbookStatusChanged::class)
            ->latest()
            ->get();

        $notifikasiStatus->each->markAsRead();

        return view('logbook.index', compact(
            'logbooks',
            'shifts',
            'opsiShift',
            'shiftDefaultId',
            'batas',
            'dibuka',
            'maksOncall',
            'minJam',
            'minJamOncall',
            'hariIni',
            'kalender',
            'libur',
            'notifikasiStatus'
        ));
    }

    public function store(StoreLogbookRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $userId = (int) $request->user()->id;

        $buktiPath = null;

        try {
            DB::transaction(function () use ($data, $userId, $request, &$buktiPath): void {
                // Serialkan submit dari akun yang sama sebelum memeriksa batas harian.
                User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();

                $entriTanggal = Logbook::query()
                    ->where('user_id', $userId)
                    ->whereDate('tanggal', $data['tanggal'])
                    ->lockForUpdate()
                    ->get(['jenis', 'status']);

                if ($entriTanggal->contains(fn ($item) => in_array(
                    $item->status,
                    [Logbook::STATUS_DISETUJUI, Logbook::STATUS_DITOLAK],
                    true
                ))) {
                    throw ValidationException::withMessages([
                        'tanggal' => 'Tanggal ini sudah final dan tidak dapat diisi ulang.',
                    ]);
                }

                if ($data['jenis'] === 'harian' && $entriTanggal->contains('jenis', 'harian')) {
                    throw ValidationException::withMessages([
                        'tanggal' => 'Logbook harian tanggal ini sudah diisi.',
                    ]);
                }

                if (in_array($data['jenis'], ['lembur', 'oncall'], true)) {
                    $jenisSudahAda = $entriTanggal->first(fn ($item) => in_array(
                        $item->jenis,
                        ['lembur', 'oncall'],
                        true
                    ))?->jenis;

                    if ($jenisSudahAda !== null) {
                        $namaJenis = $jenisSudahAda === 'lembur' ? 'Lembur' : 'On Call';
                        $pesan = $jenisSudahAda === $data['jenis']
                            ? "{$namaJenis} tanggal ini sudah diisi (maksimal 1 kali)."
                            : "Sudah ada {$namaJenis}. Pilih Lembur atau On Call, tidak bisa keduanya.";

                        throw ValidationException::withMessages(['tanggal' => $pesan]);
                    }
                }

                $harian = $data['jenis'] === 'harian';
                $shift = $harian ? Shift::find($data['shift_id']) : null;
                $tipeShift = WorkSchedule::tipe($shift);
                $dinasLuar = $tipeShift === 'kerja' && $request->boolean('is_dinas_luar');
                $perluBukti = $tipeShift === 'sakit' || $dinasLuar;

                if ($perluBukti && $request->hasFile('bukti')) {
                    $buktiPath = $request->file('bukti')->store("bukti/{$userId}", 'local');
                }

                Logbook::create([
                    'user_id'       => $userId,
                    'jenis'         => $data['jenis'],
                    'tanggal'       => $data['tanggal'],
                    'shift_id'      => $harian ? $data['shift_id'] : null,
                    'jam_mulai'     => $harian ? null : $data['jam_mulai'],
                    'jam_selesai'   => $harian ? null : $data['jam_selesai'],
                    'ringkasan'     => $tipeShift === 'libur' ? 'Libur' : ($data['ringkasan'] ?? ''),
                    'is_wfh'        => false,
                    'is_dinas_luar' => $dinasLuar,
                    'bukti_path'    => $buktiPath,
                    'harian_unik'   => $harian ? 1 : null,
                ]);
            });
        } catch (Throwable $exception) {
            if ($buktiPath) {
                Storage::disk('local')->delete($buktiPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('logbook.index')
            ->with('success', 'Logbook berhasil disimpan.');
    }

    public function bukti(Request $request, Logbook $logbook)
    {
        $user = $request->user();
        abort_unless((int) $user->id === (int) $logbook->user_id || $user->isAdmin(), 403);
        abort_unless(
            $logbook->bukti_path && Storage::disk('local')->exists($logbook->bukti_path),
            404
        );

        return Storage::disk('local')->response($logbook->bukti_path);
    }

    public function banding(Request $request, Logbook $logbook): RedirectResponse
    {
        abort_unless((int) $logbook->user_id === (int) $request->user()->id, 403);
        abort_unless($logbook->status === Logbook::STATUS_DITOLAK, 404);
        abort_unless($logbook->status_banding === null, 404);

        $data = $request->validate([
            'keterangan_pegawai' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'keterangan_pegawai.required' => 'Keterangan banding wajib diisi.',
            'keterangan_pegawai.min' => 'Keterangan banding minimal 10 karakter.',
            'keterangan_pegawai.max' => 'Keterangan banding maksimal 1000 karakter.',
        ]);

        $logbook->keterangan_pegawai = $data['keterangan_pegawai'];
        $logbook->status = Logbook::STATUS_MENUNGGU;
        $logbook->status_banding = Logbook::BANDING_MENUNGGU;
        $logbook->save();

        return redirect()
            ->route('logbook.index')
            ->with('success', 'Berhasil mengirimkan aju banding.');
    }


    /** Rentang data: awal bulan lalu sampai akhir bulan berjalan. */
    private function rentangKalender(): array
    {
        return [
            now()->startOfMonth()->subMonth()->startOfMonth(),
            now()->endOfMonth(),
        ];
    }

    /**
     * Logbook milik user yang sedang login, dikelompokkan per tanggal.
     */
    
    private function dataKalender(Carbon $mulai, Carbon $akhir): array
    {
        return Logbook::query()
            ->with('shift')
            ->where('user_id', Auth::id())
            ->whereBetween('tanggal', [$mulai, $akhir])
            ->orderBy('tanggal')
            ->get()
            ->groupBy(fn ($item) => $item->tanggal->format('Y-m-d'))
            ->map(fn ($items) => $items->map(fn ($item) => $this->formatItem($item))->values()->all())
            ->all();
    }

    /** Satu logbook dalam bentuk array untuk kalender, kartu riwayat, dan popup detail. */
    private function formatItem(Logbook $item): array
    {
        $shift = $item->shift ? 'Shift: ' . $item->shift->nama : null;

        $jam = ($item->jam_mulai && $item->jam_selesai)
            ? substr($item->jam_mulai, 0, 5) . ' - ' . substr($item->jam_selesai, 0, 5)
            : null;

        $data = [
            'id'        => $item->id,
            'jenis'     => $item->jenis,
            'detail'    => collect([$shift, $jam])->filter()->implode(' | ') ?: null,
            'jam_kerja' => $item->shift?->nama ?? $jam, // harian: nama shift, lembur/on call: rentang jam
            'kegiatan'  => $item->ringkasan,
            'is_wfh'    => (bool) $item->is_wfh,
            'is_dinas_luar' => (bool) $item->is_dinas_luar,
            'bukti_url' => $item->bukti_path ? route('logbook.bukti', $item, false) : null,
            'keterangan_pegawai' => $item->keterangan_pegawai,
            'status_banding' => $item->status_banding,
        ];

        // Opsional: hanya dikirim kalau kolomnya memang ada di tabel logbooks
        $atribut = $item->getAttributes();

        foreach (['status', 'keterangan_hc'] as $kolom) {
            if (array_key_exists($kolom, $atribut)) {
                $data[$kolom] = $atribut[$kolom];
            }
        }

        return $data;
    }

    /** Libur nasional: ['2026-08-17' => 'Hari Kemerdekaan RI'] */
    private function dataLibur(Carbon $mulai, Carbon $akhir): array
    {
        return Holiday::query()
            ->whereBetween('tanggal', [$mulai, $akhir])
            ->orderBy('tanggal')
            ->pluck('nama', 'tanggal')
            ->mapWithKeys(fn ($nama, $tanggal) => [Carbon::parse($tanggal)->format('Y-m-d') => $nama])
            ->all();
    }
}
