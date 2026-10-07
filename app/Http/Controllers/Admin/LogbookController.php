<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LogbookController extends Controller
{
    /**
     * Daftar logbook + filter. Default: yang masih menunggu.
     */
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'status' => ['nullable', Rule::in(array_merge(Logbook::STATUS, ['semua']))],
            'jenis' => ['nullable', Rule::in(Logbook::JENIS)],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $status = $filter['status'] ?? Logbook::STATUS_MENUNGGU;

        $logbooks = Logbook::with(['user', 'shift'])
            ->when($status !== 'semua', fn($q) => $q->where('status', $status))
            ->when($filter['jenis'] ?? null, fn($q, $jenis) => $q->where('jenis', $jenis))
            ->when($filter['dari'] ?? null, fn($q, $dari) => $q->whereDate('tanggal', '>=', $dari))
            ->when($filter['sampai'] ?? null, fn($q, $sampai) => $q->whereDate('tanggal', '<=', $sampai))
            ->when($filter['q'] ?? null, fn($q, $kata) => $q->whereHas(
                'user',
                fn($u) => $u->where('name', 'like', "%{$kata}%")
            ))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->simplePaginate(15)
            ->withQueryString();

        // Jumlah per status untuk angka di tab: ['Menunggu' => 5, 'Disetujui' => 20, ...]
        $jumlah = Logbook::selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.logbook.index', compact('logbooks', 'status', 'jumlah'));
    }

    public function setujui(Logbook $logbook): RedirectResponse
    {
        $logbook->status = Logbook::STATUS_DISETUJUI;
        $logbook->keterangan_hc = null; // bersihkan alasan lama kalau sebelumnya pernah ditolak
        $logbook->save();

        return back()->with(
            'success',
            "Logbook {$logbook->user->name} tanggal {$logbook->tanggal->format('d/m/Y')} disetujui."
        );
    }

    public function tolak(Request $request, Logbook $logbook): RedirectResponse
    {
        $data = $request->validate([
            'keterangan_hc' => ['required', 'string', 'max:500'],
        ], [
            'keterangan_hc.required' => 'Alasan penolakan wajib diisi.',
            'keterangan_hc.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        $logbook->status = Logbook::STATUS_DITOLAK;
        $logbook->keterangan_hc = $data['keterangan_hc'];
        $logbook->save();

        return back()->with(
            'success',
            "Logbook {$logbook->user->name} tanggal {$logbook->tanggal->format('d/m/Y')} ditolak."
        );
    }
}