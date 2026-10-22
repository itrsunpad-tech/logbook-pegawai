<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLogbookRequest;
use App\Models\Holiday;
use App\Models\Logbook;
use App\Models\Shift;
use App\Notifications\LogbookStatusChanged;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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

        $shifts = Shift::query()
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

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
        $harian = $data['jenis'] === 'harian';

        Logbook::create([
            'user_id'     => Auth::id(),
            'jenis'       => $data['jenis'],
            'tanggal'     => $data['tanggal'],
            'shift_id'    => $harian ? $data['shift_id'] : null,
            'jam_mulai'   => $harian ? null : $data['jam_mulai'],
            'jam_selesai' => $harian ? null : $data['jam_selesai'],
            'ringkasan'   => $data['ringkasan'],
            'is_wfh'      => false,
        ]);

        return redirect()
            ->route('logbook.index')
            ->with('success', 'Logbook berhasil disimpan.');
    }

    public function banding(Request $request, Logbook $logbook): RedirectResponse
    {
        abort_unless((int) $logbook->user_id === (int) $request->user()->id, 403);
        abort_unless($logbook->status === Logbook::STATUS_DITOLAK, 404);

        $data = $request->validate([
            'keterangan_pegawai' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'keterangan_pegawai.required' => 'Keterangan banding wajib diisi.',
            'keterangan_pegawai.min' => 'Keterangan banding minimal 10 karakter.',
            'keterangan_pegawai.max' => 'Keterangan banding maksimal 1000 karakter.',
        ]);

        $logbook->keterangan_pegawai = $data['keterangan_pegawai'];
        $logbook->status = Logbook::STATUS_MENUNGGU;
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
            'keterangan_pegawai' => $item->keterangan_pegawai,
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
