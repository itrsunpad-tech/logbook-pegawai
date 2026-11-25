<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLogbookRequest;
use App\Models\Holiday;
use App\Models\FingerprintLog;
use App\Models\Logbook;
use App\Models\Shift;
use App\Models\User;
use App\Support\WorkSchedule;
use App\Notifications\LogbookStatusChanged;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
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

        $pegawai = Auth::user();
        $autoFingerprint = WorkSchedule::autoFingerprint($pegawai);
        $namaShiftDiizinkan = WorkSchedule::shiftNames($pegawai);
        $shifts = Shift::query()
            ->whereIn('nama', $namaShiftDiizinkan)
            ->get()
            ->sortBy(fn (Shift $shift) => array_search($shift->nama, $namaShiftDiizinkan, true))
            ->values();
        $grupShift = WorkSchedule::groups($pegawai);
        $opsiShift = [
            'Office hour' => $shifts->whereIn('nama', $grupShift['oh']),
            'Shift' => $shifts->whereIn('nama', $grupShift['shift']),
            'Middle' => $shifts->whereIn('nama', $grupShift['middle']),
            'Status' => $shifts->whereIn('nama', config('work_schedules.common', [])),
        ];
        $namaDefaultShift = WorkSchedule::defaultShiftName($pegawai);
        $shiftDefaultId = $namaDefaultShift
            ? $shifts->firstWhere('nama', $namaDefaultShift)?->id
            : null;

        // Kalender: bulan lalu + bulan berjalan
        [$mulaiKalender, $akhirKalender] = $this->rentangKalender();

        $kalender = $this->dataKalender($mulaiKalender, $akhirKalender);
        $libur = $this->dataLibur($mulaiKalender, $akhirKalender);
        $monitoringAbsensi = collect();
        if (Auth::user()->fingerprint_id) {
            $awalUtc = $mulaiKalender->copy()->startOfDay()->utc();
            $akhirUtc = $akhirKalender->copy()->endOfDay()->utc();
            $logs = FingerprintLog::query()
                ->where('fingerprint_id', Auth::user()->fingerprint_id)
                ->whereIn('punch_state', [0, 1, 4, 5])
                ->where('punched_at', '>=', $awalUtc)
                ->where('punched_at', '<=', $akhirUtc)
                ->orderBy('punched_at')
                ->get();
            $monitoringAbsensi = $logs->groupBy(fn (FingerprintLog $log) => $log->punched_at->copy()->setTimezone(FingerprintLog::TZ_TAMPIL)->toDateString())
                ->map(function ($dailyLogs, $tanggal) {
                    $checkIn = $dailyLogs->first(fn (FingerprintLog $log) => (int) $log->punch_state === 0);
                    $checkOut = $dailyLogs->filter(fn (FingerprintLog $log) => (int) $log->punch_state === 1)->last();
                    $overtimeIn = $dailyLogs->first(fn (FingerprintLog $log) => (int) $log->punch_state === 4);
                    $overtimeOut = $dailyLogs->filter(fn (FingerprintLog $log) => (int) $log->punch_state === 5)->last();
                    $toJakarta = fn (?FingerprintLog $log) => $log?->punched_at->copy()->setTimezone(FingerprintLog::TZ_TAMPIL);
                    $kerjaMenit = $checkIn && $checkOut && $checkOut->punched_at->gt($checkIn->punched_at)
                        ? (int) $checkIn->punched_at->diffInMinutes($checkOut->punched_at)
                        : 0;
                    $lemburMenit = $overtimeIn && $overtimeOut && $overtimeOut->punched_at->gt($overtimeIn->punched_at)
                        ? (int) $overtimeIn->punched_at->diffInMinutes($overtimeOut->punched_at)
                        : 0;

                    return [
                        'tanggal' => $tanggal,
                        'check_in' => $checkIn?->punched_at->copy()->setTimezone(FingerprintLog::TZ_TAMPIL)->format('H:i'),
                        'check_out' => $checkOut?->punched_at->copy()->setTimezone(FingerprintLog::TZ_TAMPIL)->format('H:i'),
                        'metode' => $checkIn?->labelVerifikasi() ?? '—',
                        'kerja_menit' => $kerjaMenit,
                        'lembur_menit' => $lemburMenit,
                        'status' => $checkIn ? ($checkIn->punched_at->copy()->setTimezone(FingerprintLog::TZ_TAMPIL)->format('H:i:s') > '08:00:00' ? 'Terlambat' : 'Tepat waktu') : 'Tidak tercatat',
                    ];
                })->sortKeysDesc();
        }
        $monitoringInput = Logbook::query()
            ->where('user_id', Auth::id())
            ->whereIn('jenis', [Logbook::HARIAN, Logbook::LEMBUR, Logbook::ONCALL])
            ->whereDate('tanggal', '>=', $mulaiKalender->toDateString())
            ->whereDate('tanggal', '<=', $akhirKalender->toDateString())
            ->get(['tanggal', 'jenis', 'status', 'jam_mulai', 'jam_selesai'])
            ->map(function (Logbook $item) {
                $mulai = $item->jam_mulai ? Carbon::parse($item->jam_mulai) : null;
                $selesai = $item->jam_selesai ? Carbon::parse($item->jam_selesai) : null;

                return [
                    'tanggal' => $item->tanggal->toDateString(),
                    'jenis' => $item->jenis,
                    'status' => $item->status,
                    'menit' => $mulai && $selesai && $selesai->gt($mulai) ? (int) $mulai->diffInMinutes($selesai) : 0,
                ];
            });
        $notifikasiStatus = Auth::user()->unreadNotifications()
            ->where('type', LogbookStatusChanged::class)
            ->latest()
            ->get();

        $notifikasiStatus->each->markAsRead();

        return view('logbook.index', compact(
            'logbooks',
            'shifts',
            'opsiShift',
            'autoFingerprint',
            'shiftDefaultId',
            'batas',
            'dibuka',
            'maksOncall',
            'minJam',
            'minJamOncall',
            'hariIni',
            'kalender',
            'monitoringAbsensi',
            'monitoringInput',
            'mulaiKalender',
            'akhirKalender',
            'libur',
            'notifikasiStatus'
        ));
    }

    /**
     * Cocokkan jadwal pegawai dari tap masuk pertama pada tanggal yang dipilih.
     * Hanya membaca log milik akun yang sedang login.
     */
    public function jadwalFingerprint(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $user = $request->user();

        if (! $user->fingerprint_id) {
            return response()->json([
                'status' => 'fingerprint_not_registered',
                'message' => 'ID sidik jari akun ini belum didaftarkan oleh admin.',
            ]);
        }

        if (! WorkSchedule::autoFingerprint($user)) {
            return response()->json([
                'status' => 'unit_not_supported',
                'message' => 'Jadwal otomatis dari fingerprint belum tersedia untuk jadwal Anda. Pilih jadwal secara manual.',
            ]);
        }

        $zona = FingerprintLog::TZ_TAMPIL;
        $tanggal = $data['tanggal'] ?? Carbon::now($zona)->toDateString();
        $hariIni = Carbon::now($zona)->toDateString();

        if ($tanggal > $hariIni) {
            return response()->json([
                'status' => 'future_date',
                'message' => 'Fingerprint untuk tanggal mendatang belum tersedia.',
            ]);
        }

        // Satu hari penuh, supaya shift pagi (mis. 05.00) dan malam (21.00) ikut terbaca.
        $mulaiHari = Carbon::createFromFormat('Y-m-d', $tanggal, $zona)->startOfDay();
        $awalRentang = $mulaiHari->copy()->utc();
        $akhirRentang = $mulaiHari->copy()->addDay()->utc();

        $fingerprint = FingerprintLog::query()
            ->where('fingerprint_id', $user->fingerprint_id)
            ->where('punch_state', 0)
            ->where('punched_at', '>=', $awalRentang)
            ->where('punched_at', '<', $akhirRentang)
            ->orderBy('punched_at')
            ->orderBy('id')
            ->first();

        if (! $fingerprint) {
            $tanggalLabel = $mulaiHari->copy()->locale('id')->translatedFormat('d F Y');
            $fingerprintKeluar = FingerprintLog::query()
                ->where('fingerprint_id', $user->fingerprint_id)
                ->where('punch_state', 1)
                ->where('punched_at', '>=', $awalRentang)
                ->where('punched_at', '<', $akhirRentang)
                ->orderByDesc('punched_at')
                ->orderByDesc('id')
                ->first();

            if ($fingerprintKeluar) {
                $waktuKeluar = $fingerprintKeluar->punched_at->copy()->setTimezone($zona);
                $pesanCheckOut = 'Tap Check Out pukul ' . $waktuKeluar->format('H:i')
                    . ' WIB tercatat, tetapi jadwal hanya dicocokkan dari Check In.';

                if ($tanggal === $hariIni) {
                    return response()->json([
                        'status' => 'waiting',
                        'check_out_found' => true,
                        'waktu_fingerprint' => $waktuKeluar->format('H:i'),
                        'message' => $pesanCheckOut . ' Menunggu data Check In untuk tanggal ' . $tanggalLabel . '.',
                    ]);
                }

                return response()->json([
                    'status' => 'check_out_only',
                    'waktu_fingerprint' => $waktuKeluar->format('H:i'),
                    'message' => $pesanCheckOut . ' Check In tidak ditemukan pada tanggal ' . $tanggalLabel . '.',
                ]);
            }

            if ($tanggal < $hariIni) {
                return response()->json([
                    'status' => 'no_fingerprint',
                    'message' => 'Tidak ditemukan fingerprint masuk untuk tanggal ' . $tanggalLabel . '.',
                ]);
            }

            return response()->json([
                'status' => 'waiting',
                'message' => 'Menunggu fingerprint tanggal ' . $tanggalLabel . '.',
            ]);
        }

        $waktuTap = $fingerprint->punched_at->copy()->setTimezone($zona);
        $shift = WorkSchedule::cocokkanDariTap($user, $waktuTap);

        if (! $shift) {
            return response()->json([
                'status' => 'schedule_unavailable',
                'message' => 'Jadwal untuk instalasi Anda belum tersedia di database. Hubungi admin.',
                'waktu_fingerprint' => $waktuTap->format('H:i'),
            ]);
        }

        return response()->json([
            'status' => 'matched',
            'message' => 'Jadwal otomatis dicocokkan dari fingerprint.',
            'shift' => [
                'id' => $shift->id,
                'nama' => $shift->nama,
                'label' => WorkSchedule::label($shift),
                'tipe' => WorkSchedule::tipe($shift),
            ],
            'tanggal' => $waktuTap->toDateString(),
            'waktu_fingerprint' => $waktuTap->format('H:i'),
        ]);
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

                $harian = $data['jenis'] === 'harian';
                $isCuti = $harian && $request->boolean('is_cuti');
                $tanggalMulai = $isCuti ? $data['cuti_mulai'] : $data['tanggal'];
                $tanggalSelesai = $isCuti ? $data['cuti_selesai'] : $data['tanggal'];

                $entriRentang = Logbook::query()
                    ->where('user_id', $userId)
                    ->where(function ($query) use ($tanggalMulai, $tanggalSelesai) {
                        $query->where(function ($padaTanggal) use ($tanggalMulai, $tanggalSelesai) {
                            $padaTanggal->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                                ->where(function ($regularAtauCutiAktif) {
                                    $regularAtauCutiAktif->whereNull('is_cuti')
                                        ->orWhere('is_cuti', false)
                                        ->orWhere(function ($cutiAktif) {
                                            $cutiAktif->where('is_cuti', true)
                                                ->whereIn('status', [Logbook::STATUS_MENUNGGU, Logbook::STATUS_DISETUJUI]);
                                        });
                                });
                        })->orWhere(function ($cuti) use ($tanggalMulai, $tanggalSelesai) {
                            $cuti->where('is_cuti', true)
                                ->whereIn('status', [Logbook::STATUS_MENUNGGU, Logbook::STATUS_DISETUJUI])
                                ->where('tanggal', '<=', $tanggalSelesai)
                                ->where('cuti_selesai', '>=', $tanggalMulai);
                        });
                    })
                    ->lockForUpdate()
                    ->get(['id', 'jenis', 'status', 'is_cuti', 'is_izin_sakit', 'bukti_path', 'ringkasan', 'tanggal']);

                if ($entriRentang->contains(fn ($item) => ! $item->is_cuti && in_array(
                    $item->status,
                    [Logbook::STATUS_DISETUJUI, Logbook::STATUS_DITOLAK],
                    true
                ))) {
                    throw ValidationException::withMessages([
                        'tanggal' => 'Tanggal ini sudah final dan tidak dapat diisi ulang.',
                    ]);
                }

                if ($isCuti && $entriRentang->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'cuti_mulai' => 'Rentang cuti bertabrakan dengan logbook yang sudah ada.',
                    ]);
                }

                if ($harian && ! $isCuti && $entriRentang->contains(fn ($item) => $item->is_cuti
                    && in_array($item->status, [Logbook::STATUS_MENUNGGU, Logbook::STATUS_DISETUJUI], true))) {
                    throw ValidationException::withMessages([
                        'tanggal' => 'Tanggal ini berada dalam rentang cuti yang sudah diajukan.',
                    ]);
                }

                $entriTanggal = $entriRentang->filter(fn ($item) => $item->tanggal->toDateString() === $data['tanggal']);

                $isIzinSakit = $harian && $request->boolean('is_izin_sakit');
                $entriIzinSakitUntukBukti = $isIzinSakit && $request->hasFile('bukti')
                    ? $entriTanggal->first(fn ($item) => $item->jenis === 'harian'
                        && $item->is_izin_sakit
                        && $item->bukti_path === null
                        && ! in_array($item->status, [Logbook::STATUS_DISETUJUI, Logbook::STATUS_DITOLAK], true))
                    : null;

                if ($harian && $entriTanggal->contains('jenis', 'harian') && ! $entriIzinSakitUntukBukti) {
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

                $shift = $harian && ! $isCuti && ! $isIzinSakit
                    ? Shift::find($data['shift_id'] ?? null)
                    : null;
                $tipeShift = WorkSchedule::tipe($shift);
                $dinasLuarTanpaFingerprint = $harian
                    && WorkSchedule::autoFingerprint($request->user())
                    && $request->boolean('is_dinas_luar')
                    && empty($data['shift_id']);
                $dinasLuar = $request->boolean('is_dinas_luar')
                    && ($tipeShift === 'kerja' || $dinasLuarTanpaFingerprint);
                $bolehUnggahBukti = $dinasLuar || $isCuti || $isIzinSakit || in_array($tipeShift, ['sakit', 'izin'], true);

                if ($bolehUnggahBukti && $request->hasFile('bukti')) {
                    $buktiPath = $request->file('bukti')->store("bukti/{$userId}", 'local');
                }

                if ($entriIzinSakitUntukBukti && $buktiPath) {
                    $entriIzinSakitUntukBukti->update(['bukti_path' => $buktiPath]);

                    return;
                }

                Logbook::create([
                    'user_id'       => $userId,
                    'jenis'         => $data['jenis'],
                    'tanggal'       => $tanggalMulai,
                    'shift_id'      => $harian && ! $isCuti && ! $isIzinSakit ? ($data['shift_id'] ?? null) : null,
                    'jam_mulai'     => $harian ? null : $data['jam_mulai'],
                    'jam_selesai'   => $harian ? null : $data['jam_selesai'],
                    'ringkasan'     => $tipeShift === 'libur' ? 'Libur' : ($data['ringkasan'] ?? ''),
                    'is_wfh'        => false,
                    'is_dinas_luar' => $dinasLuar,
                    'is_cuti'       => $isCuti,
                    'is_izin_sakit' => $isIzinSakit,
                    'cuti_selesai'  => $isCuti ? $tanggalSelesai : null,
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

        $namaFile = basename($logbook->bukti_path);

        return Storage::disk('local')->response($logbook->bukti_path, $namaFile, [
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
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
        $logbooks = Logbook::query()
            ->with('shift')
            ->where('user_id', Auth::id())
            ->where(function ($query) use ($mulai, $akhir) {
                $query->whereBetween('tanggal', [$mulai, $akhir])
                    ->orWhere(function ($cuti) use ($mulai, $akhir) {
                        $cuti->where('is_cuti', true)
                            ->where('tanggal', '<=', $akhir)
                            ->where('cuti_selesai', '>=', $mulai);
                    });
            })
            ->orderBy('tanggal')
            ->get();

        $kalender = [];
        foreach ($logbooks as $item) {
            // Cuti yang ditolak tidak lagi menandai tanggal pada kalender.
            if ($item->is_cuti && $item->status === Logbook::STATUS_DITOLAK) {
                continue;
            }

            $data = $this->formatItem($item);

            if ($item->is_cuti && $item->cuti_selesai) {
                $tanggal = $item->tanggal->copy()->max($mulai)->startOfDay();
                $batasCuti = $item->cuti_selesai->copy()->min($akhir)->startOfDay();
                while ($tanggal->lte($batasCuti)) {
                    $kalender[$tanggal->format('Y-m-d')][] = $data;
                    $tanggal->addDay();
                }
                continue;
            }

            $kalender[$item->tanggal->format('Y-m-d')][] = $data;
        }

        ksort($kalender);

        return $kalender;
    }

    /** Satu logbook dalam bentuk array untuk kalender, kartu riwayat, dan popup detail. */
    private function formatItem(Logbook $item): array
    {
        $isCuti = (bool) $item->is_cuti;
        $isIzinSakit = (bool) $item->is_izin_sakit;
        $cutiRange = null;
        if ($isCuti && $item->cuti_selesai) {
            $mulaiCuti = $item->tanggal->copy()->locale('id');
            $akhirCuti = $item->cuti_selesai->copy()->locale('id');
            $formatMulai = $mulaiCuti->isSameMonth($akhirCuti)
                ? $mulaiCuti->translatedFormat('j')
                : $mulaiCuti->translatedFormat('j M');
            $cutiRange = $formatMulai . '–' . $akhirCuti->translatedFormat('j M Y');
        }
        $shift = $item->shift ? 'Shift: ' . $item->shift->nama : null;

        $jam = ($item->jam_mulai && $item->jam_selesai)
            ? substr($item->jam_mulai, 0, 5) . ' - ' . substr($item->jam_selesai, 0, 5)
            : null;

        $data = [
            'id'        => $item->id,
            'jenis'     => $item->jenis,
            'detail'    => $isCuti ? 'Cuti · ' . $cutiRange : ($isIzinSakit ? 'Izin/Sakit' : (collect([$shift, $jam])->filter()->implode(' | ') ?: null)),
            'jam_kerja' => $isCuti ? 'Cuti' : ($isIzinSakit ? 'Izin/Sakit' : ($item->shift?->nama ?? $jam)), // harian: nama shift, lembur/on call: rentang jam
            'kegiatan'  => $item->ringkasan,
            'is_wfh'    => (bool) $item->is_wfh,
            'is_dinas_luar' => (bool) $item->is_dinas_luar,
            'is_cuti'   => $isCuti,
            'is_izin_sakit' => $isIzinSakit,
            'cuti_range' => $cutiRange,
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
