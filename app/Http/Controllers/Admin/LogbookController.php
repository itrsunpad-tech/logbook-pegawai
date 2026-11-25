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
        $instalasi = $filter['instalasi'] ?? null;

        // Jenis dan tanggal dipilih saat review dimulai; daftar hanya disaring menurut status, nama, instalasi.
        $filterDaftar = ['jenis' => null, 'dari' => null, 'sampai' => null];
        $batasi = fn ($query) => $this->terapkanFilter($query, $filterDaftar, $status);

        $pegawai = User::query()
            ->whereHas('logbooks', $batasi)
            ->withCount(['logbooks as total_entri' => $batasi])
            ->when($kata, fn ($q) => $q->where('name', 'like', "%{$kata}%"))
            ->when($instalasi, fn ($q) => $q->where('employee_instalasi', $instalasi))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        // Total entri untuk teks ringkasan di atas daftar
        $totalEntri = $this->terapkanFilter(Logbook::query(), $filterDaftar, $status)
            ->when($kata, fn ($q) => $q->whereHas(
                'user',
                fn ($u) => $u->where('name', 'like', "%{$kata}%")
            ))
            ->whereHas('user', fn ($u) => $u->when($instalasi, fn ($uq) => $uq->where('employee_instalasi', $instalasi)))
            ->count();

        $instalasiList = collect(User::INSTALASI)
            ->merge(User::query()->whereNotNull('employee_instalasi')->distinct()->pluck('employee_instalasi'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Jumlah per status untuk angka di tab: ['Menunggu' => 5, 'Disetujui' => 20, ...]
        $jumlah = Logbook::selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.logbook.index', compact('pegawai', 'status', 'jumlah', 'totalEntri', 'instalasiList', 'instalasi'));
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
            ->paginate(10)
            ->withQueryString();

        $riwayatStatus = Logbook::query()
            ->where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.logbook.show', compact('user', 'logbooks', 'status', 'riwayatStatus'));
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

    public function setujuiTerpilih(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['logbook_ids' => ['required', 'array', 'min:1'], 'logbook_ids.*' => ['integer', 'distinct', 'exists:logbooks,id']]);
        $items = Logbook::query()->where('user_id', $user->id)->whereIn('id', $data['logbook_ids'])
            ->where('status', Logbook::STATUS_MENUNGGU)->get();

        foreach ($items as $item) {
            if (in_array($item->status_banding, [Logbook::BANDING_DISETUJUI, Logbook::BANDING_DITOLAK], true)) continue;
            $item->status = Logbook::STATUS_DISETUJUI;
            if ($item->status_banding === Logbook::BANDING_MENUNGGU) $item->status_banding = Logbook::BANDING_DISETUJUI;
            $item->keterangan_hc = null;
            $item->save();
            $user->notify(new LogbookStatusChanged($item, Logbook::STATUS_DISETUJUI));
        }

        return back()->with('success', $items->count() . ' logbook terpilih disetujui.');
    }

    public function tolakTerpilih(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'logbook_ids' => ['required', 'array', 'min:1'],
            'logbook_ids.*' => ['integer', 'distinct', 'exists:logbooks,id'],
        ]);
        $items = Logbook::query()->where('user_id', $user->id)->whereIn('id', $request->input('logbook_ids'))
            ->where('status', Logbook::STATUS_MENUNGGU)->get();
        $adaNonCuti = $items->contains(fn (Logbook $item) => !$item->is_cuti);
        $data = $request->validate([
            'keterangan_hc' => [$adaNonCuti ? 'required' : 'nullable', 'string', 'max:500'],
        ], [
            'keterangan_hc.required' => 'Alasan penolakan wajib diisi untuk logbook selain cuti.',
        ]);
        foreach ($items as $item) {
            if (in_array($item->status_banding, [Logbook::BANDING_DISETUJUI, Logbook::BANDING_DITOLAK], true)) continue;
            $item->status = Logbook::STATUS_DITOLAK;
            $item->keterangan_hc = $data['keterangan_hc'] ?? null;
            if ($item->is_cuti) $item->harian_unik = null;
            if ($item->status_banding === Logbook::BANDING_MENUNGGU) $item->status_banding = Logbook::BANDING_DITOLAK;
            $item->save();
            $user->notify(new LogbookStatusChanged($item, Logbook::STATUS_DITOLAK));
        }
        return back()->with('success', $items->count() . ' logbook terpilih ditolak.');
    }

    public function tolak(Request $request, Logbook $logbook): RedirectResponse
    {
        abort_if(in_array($logbook->status_banding, [Logbook::BANDING_DISETUJUI, Logbook::BANDING_DITOLAK], true), 403);

        $data = $request->validate([
            'keterangan_hc' => [$logbook->is_cuti ? 'nullable' : 'required', 'string', 'max:500'],
        ], [
            'keterangan_hc.required' => 'Alasan penolakan wajib diisi.',
            'keterangan_hc.max'      => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        $statusSebelumnya = $logbook->status;
        $logbook->status = Logbook::STATUS_DITOLAK;
        if ($logbook->is_cuti) {
            // Lepaskan batas unik harian agar pegawai bisa mengisi tanggal cuti yang ditolak.
            $logbook->harian_unik = null;
        }
        if ($logbook->status_banding === Logbook::BANDING_MENUNGGU) {
            $logbook->status_banding = Logbook::BANDING_DITOLAK;
        }
        $logbook->keterangan_hc = $data['keterangan_hc'] ?? null;
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
            'instalasi' => ['nullable', 'string', 'max:150'],
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
            ->when($filter['dari'] ?? null, fn ($q, $dari) => $q->where(function ($range) use ($dari) {
                $range->whereDate('tanggal', '>=', $dari)
                    ->orWhere(fn ($cuti) => $cuti->where('is_cuti', true)->whereDate('cuti_selesai', '>=', $dari));
            }))
            ->when($filter['sampai'] ?? null, fn ($q, $sampai) => $q->where(function ($range) use ($sampai) {
                $range->whereDate('tanggal', '<=', $sampai)
                    ->orWhere(fn ($cuti) => $cuti->where('is_cuti', true)->whereDate('tanggal', '<=', $sampai));
            }));
    }
}
