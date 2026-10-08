<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLogbookRequest;
use App\Models\Holiday;
use App\Models\Logbook;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LogbookController extends Controller
{
    /** Maksimal pengisian on call per hari. */
    public const MAKS_ONCALL = 1;

    /** Minimal jam lembur agar diakui HC (hanya sebagai informasi di form). */
    public const MIN_JAM_LEMBUR = 1;

    public function index(): View
    {
        $hariIni = now()->format('Y-m-d');
        $batas = now()->endOfDay();
        $dibuka = now()->lte($batas);

        $maksOncall = self::MAKS_ONCALL;
        $minJam = self::MIN_JAM_LEMBUR;

        $logbooks = Logbook::query()
            ->where('user_id', Auth::id())
            ->latest('tanggal')
            ->latest('id')
            ->get();

        $shifts = Shift::query()
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        // Kalender & Riwayatku: bulan lalu + bulan berjalan
        [$mulaiKalender, $akhirKalender] = $this->rentangKalender();

        $kalender = $this->dataKalender($mulaiKalender, $akhirKalender);
        $libur = $this->dataLibur($mulaiKalender, $akhirKalender);

        return view('logbook.index', compact(
            'logbooks',
            'shifts',
            'batas',
            'dibuka',
            'maksOncall',
            'minJam',
            'hariIni',
            'kalender',
            'libur'
        ));
    }

    /**
     * Tombol Refresh untuk memuat ulang data (JSON).
     */

    public function riwayat(): JsonResponse
    {
        [$mulai, $akhir] = $this->rentangKalender();

        return response()->json([
            'kalender' => $this->dataKalender($mulai, $akhir),
            'libur'    => $this->dataLibur($mulai, $akhir),
        ]);
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
            'is_wfh'      => $harian && $request->boolean('is_wfh'),
        ]);

        return redirect()
            ->route('logbook.index')
            ->with('success', 'Logbook berhasil disimpan.');
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
            'jenis'     => $item->jenis,
            'detail'    => collect([$shift, $jam])->filter()->implode(' | ') ?: null,
            'jam_kerja' => $item->shift?->nama ?? $jam, // harian: nama shift, lembur/on call: rentang jam
            'kegiatan'  => $item->ringkasan,
            'is_wfh'    => (bool) $item->is_wfh,
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