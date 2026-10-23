<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\LogbookStatusChanged;
use App\Models\Logbook;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LogbookController extends Controller
{
    /**
     * Daftar pegawai + jumlah entri sesuai status/filter.
     * Default: yang masih menunggu.
     */
    public function index(Request $request): View
    {
        $filter = $this->validasiFilter($request);
        $status = $filter['status'] ?? Logbook::STATUS_MENUNGGU;
        $kata   = $filter['q'] ?? null;

        // Syarat logbook yang dihitung (status, jenis, tanggal)
        $batasi = fn ($query) => $this->terapkanFilter($query, $filter, $status);

        $pegawai = User::query()
            ->whereHas('logbooks', $batasi)
            ->withCount(['logbooks as total_entri' => $batasi])
            ->when($kata, fn ($q) => $q->where('name', 'like', "%{$kata}%"))
            ->orderBy('name')
            ->simplePaginate(20)
            ->withQueryString();

        // Total entri untuk teks ringkasan di atas daftar
        $totalEntri = $this->terapkanFilter(Logbook::query(), $filter, $status)
            ->when($kata, fn ($q) => $q->whereHas(
                'user',
                fn ($u) => $u->where('name', 'like', "%{$kata}%")
            ))
            ->count();

        // Jumlah per status untuk angka di tab: ['Menunggu' => 5, 'Disetujui' => 20, ...]
        $jumlah = Logbook::selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.logbook.index', compact('pegawai', 'status', 'jumlah', 'totalEntri'));
    }

    /**
     * Detail logbook satu pegawai (halaman "Mulai Review").
     */
    public function show(Request $request, User $user): View
    {
        $filter = $this->validasiFilter($request);
        $status = $filter['status'] ?? Logbook::STATUS_MENUNGGU;

        $logbooks = $this->terapkanFilter(
            Logbook::where('user_id', $user->id)->with('shift'),
            $filter,
            $status
        )
            ->orderBy('tanggal')   // dari yang paling lama, biar review urut
            ->orderBy('id')
            ->simplePaginate(30)
            ->withQueryString();

        return view('admin.logbook.show', compact('user', 'logbooks', 'status'));
    }

    public function setujui(Logbook $logbook): RedirectResponse
    {
        abort_if(in_array($logbook->status_banding, [Logbook::BANDING_DISETUJUI, Logbook::BANDING_DITOLAK], true), 403);

        $statusSebelumnya = $logbook->status;
        $logbook->status = Logbook::STATUS_DISETUJUI;
        if ($logbook->status_banding === Logbook::BANDING_MENUNGGU) {
            $logbook->status_banding = Logbook::BANDING_DISETUJUI;
        }
        $logbook->keterangan_hc = null; // bersihkan alasan lama kalau sebelumnya pernah ditolak
        $logbook->save();

        if ($statusSebelumnya !== Logbook::STATUS_DISETUJUI) {
            $logbook->user->notify(new LogbookStatusChanged($logbook, Logbook::STATUS_DISETUJUI));
        }

        return back()->with(
            'success',
            "Logbook {$logbook->user->name} tanggal {$logbook->tanggal->format('d/m/Y')} disetujui."
        );
    }

    public function tolak(Request $request, Logbook $logbook): RedirectResponse
    {
        abort_if(in_array($logbook->status_banding, [Logbook::BANDING_DISETUJUI, Logbook::BANDING_DITOLAK], true), 403);

        $data = $request->validate([
            'keterangan_hc' => ['required', 'string', 'max:500'],
        ], [
            'keterangan_hc.required' => 'Alasan penolakan wajib diisi.',
            'keterangan_hc.max'      => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        $statusSebelumnya = $logbook->status;
        $logbook->status = Logbook::STATUS_DITOLAK;
        if ($logbook->status_banding === Logbook::BANDING_MENUNGGU) {
            $logbook->status_banding = Logbook::BANDING_DITOLAK;
        }
        $logbook->keterangan_hc = $data['keterangan_hc'];
        $logbook->save();

        if ($statusSebelumnya !== Logbook::STATUS_DITOLAK) {
            $logbook->user->notify(new LogbookStatusChanged($logbook, Logbook::STATUS_DITOLAK));
        }

        return back()->with(
            'success',
            "Logbook {$logbook->user->name} tanggal {$logbook->tanggal->format('d/m/Y')} ditolak."
        );
    }

    /**
     * Aturan filter yang dipakai bersama oleh daftar & detail.
     */
    private function validasiFilter(Request $request): array
    {
        return $request->validate([
            'status' => ['nullable', Rule::in(array_merge(Logbook::STATUS, ['semua']))],
            'jenis'  => ['nullable', Rule::in(Logbook::JENIS)],
            'dari'   => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'q'      => ['nullable', 'string', 'max:100'],
        ]);
    }

    /**
     * Tempelkan filter status/jenis/tanggal ke query logbook.
     */
    private function terapkanFilter(Builder $query, array $filter, string $status): Builder
    {
        return $query
            ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
            ->when($filter['jenis'] ?? null, fn ($q, $jenis) => $q->where('jenis', $jenis))
            ->when($filter['dari'] ?? null, fn ($q, $dari) => $q->whereDate('tanggal', '>=', $dari))
            ->when($filter['sampai'] ?? null, fn ($q, $sampai) => $q->whereDate('tanggal', '<=', $sampai));
    }
}
