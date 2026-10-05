<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLogbookRequest;
use App\Models\Holiday;
use App\Models\Logbook;
use App\Models\Shift;
use Carbon\Carbon;
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

        // Kalender: bulan lalu + bulan berjalan
        $mulaiKalender = now()->startOfMonth()->subMonth()->startOfMonth();
        $akhirKalender = now()->endOfMonth();

        $logbookKalender = Logbook::query()
            ->with('shift')
            ->where('user_id', Auth::id())
            ->whereBetween('tanggal', [$mulaiKalender, $akhirKalender])
            ->orderBy('tanggal')
            ->get();

        // Format: ['2026-10-02' => [['jenis' => 'harian', 'detail' => 'Shift: Pagi'], ...]]
        $kalender = $logbookKalender
            ->groupBy(fn ($item) => $item->tanggal->format('Y-m-d'))
            ->map(fn ($items) => $items->map(function ($item) {
                $shift = $item->shift ? 'Shift: ' . $item->shift->nama : null;

                $jam = ($item->jam_mulai && $item->jam_selesai)
                    ? substr($item->jam_mulai, 0, 5) . ' - ' . substr($item->jam_selesai, 0, 5)
                    : null;

                return [
                    'jenis' => $item->jenis,
                    'detail' => collect([$shift, $jam])->filter()->implode(' | ') ?: null,
                ];
            })->values()->all())
            ->all();

        // Libur nasional: ['2026-08-17' => 'Hari Kemerdekaan RI']
        $libur = Holiday::query()
            ->whereBetween('tanggal', [$mulaiKalender, $akhirKalender])
            ->orderBy('tanggal')
            ->pluck('nama', 'tanggal')
            ->mapWithKeys(fn ($nama, $tanggal) => [Carbon::parse($tanggal)->format('Y-m-d') => $nama])
            ->all();

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
} 