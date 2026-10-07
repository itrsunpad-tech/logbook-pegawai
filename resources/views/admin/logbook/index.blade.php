@extends('layouts.app')

@section('title', 'ACC Logbook Pegawai')

@push('styles')
    <style>
        .acc-page {
            width: 100%;
            max-width: 960px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .acc-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .acc-title {
            color: var(--text-dark);
            font-size: 18px;
            font-weight: 700;
        }

        .acc-back {
            color: var(--accent);
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        /* Tab status */
        .acc-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .acc-tab {
            padding: 7px 12px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: #fff;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .acc-tab.is-active {
            background: var(--accent-soft);
            border-color: var(--accent-border);
            color: var(--accent);
        }

        .acc-tab b {
            margin-left: 4px;
        }

        /* Filter */
        .acc-filter {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            align-items: end;
        }

        .acc-btn {
            padding: 9px 14px;
            border: 0;
            border-radius: 8px;
            background: var(--accent);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .acc-btn--ghost {
            background: var(--surface);
            color: var(--muted);
            border: 1px solid var(--border);
            text-decoration: none;
            text-align: center;
        }

        .acc-btn--ok {
            background: #16a34a;
        }

        .acc-btn--no {
            background: #fff;
            color: var(--red);
            border: 1px solid #fecaca;
        }

        /* Kartu logbook */
        .acc-item {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .acc-top {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .acc-name {
            color: var(--text-dark);
            font-size: 13px;
            font-weight: 700;
        }

        .acc-date {
            color: var(--muted);
            font-size: 11px;
            margin-top: 2px;
        }

        .acc-badges {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            align-items: flex-start;
        }

        .acc-chip {
            padding: 3px 9px;
            border-radius: 999px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
        }

        .st {
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
        }

        .st--ok {
            background: #dcfce7;
            color: #15803d;
        }

        .st--no {
            background: #fee2e2;
            color: #b91c1c;
        }

        .st--wait {
            background: #fef3c7;
            color: #b45309;
        }

        .acc-detail {
            color: var(--muted);
            font-size: 11px;
        }

        .acc-summary {
            color: var(--text);
            font-size: 12px;
            line-height: 1.6;
            white-space: pre-line;
        }

        .acc-hc {
            padding: 8px 10px;
            border-left: 3px solid var(--red);
            background: #fff7f7;
            color: #b91c1c;
            font-size: 11px;
            line-height: 1.5;
        }

        .acc-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: flex-start;
        }

        .acc-tolak summary {
            list-style: none;
            cursor: pointer;
        }

        .acc-tolak summary::-webkit-details-marker {
            display: none;
        }

        .acc-tolak form {
            margin-top: 8px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 260px;
        }

        .acc-alert {
            padding: 10px 12px;
            border-radius: 8px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 12px;
        }

        .acc-empty {
            padding: 28px 12px;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
        }

        .acc-pager {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .acc-pager a,
        .acc-pager span {
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            color: var(--accent);
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .acc-pager .is-off {
            color: var(--muted);
            opacity: .5;
        }
    </style>
@endpush

@section('content')
    <div class="acc-page">

        <div class="acc-head">
            <h1 class="acc-title">ACC Logbook Pegawai</h1>
            <a href="{{ route('admin.dashboard') }}" class="acc-back">&larr; Dashboard</a>
        </div>

        {{-- Error validasi (mis. alasan penolakan kosong) --}}
        @if ($errors->any())
            <div class="acc-alert" role="alert">{{ $errors->first() }}</div>
        @endif

        {{-- Tab status --}}
        @php
            $tabs = [
                \App\Models\Logbook::STATUS_MENUNGGU => $jumlah->get(\App\Models\Logbook::STATUS_MENUNGGU, 0),
                \App\Models\Logbook::STATUS_DISETUJUI => $jumlah->get(\App\Models\Logbook::STATUS_DISETUJUI, 0),
                \App\Models\Logbook::STATUS_DITOLAK => $jumlah->get(\App\Models\Logbook::STATUS_DITOLAK, 0),
                'semua' => $jumlah->sum(),
            ];
        @endphp

        <div class="acc-tabs">
            @foreach ($tabs as $nama => $total)
                <a href="{{ route('admin.logbook.index', array_merge(request()->except('page'), ['status' => $nama])) }}"
                    class="acc-tab {{ $status === $nama ? 'is-active' : '' }}">
                    {{ $nama === 'semua' ? 'Semua' : $nama }} <b>{{ $total }}</b>
                </a>
            @endforeach
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('admin.logbook.index') }}" class="lb-card">
            <input type="hidden" name="status" value="{{ $status }}">

            <div class="acc-filter">
                <div>
                    <label class="lb-label" for="q">Nama pegawai</label>
                    <input type="text" id="q" name="q" value="{{ request('q') }}" class="lb-input"
                        placeholder="Cari nama...">
                </div>

                <div>
                    <label class="lb-label" for="jenis">Jenis</label>
                    <select id="jenis" name="jenis" class="lb-select">
                        <option value="">Semua</option>
                        @foreach (['harian' => 'Harian', 'lembur' => 'Lembur', 'oncall' => 'On Call'] as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(request('jenis') === $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="lb-label" for="dari">Dari</label>
                    <input type="date" id="dari" name="dari" value="{{ request('dari') }}" class="lb-input">
                </div>

                <div>
                    <label class="lb-label" for="sampai">Sampai</label>
                    <input type="date" id="sampai" name="sampai" value="{{ request('sampai') }}" class="lb-input">
                </div>

                <button type="submit" class="acc-btn">Terapkan</button>
                <a href="{{ route('admin.logbook.index', ['status' => $status]) }}" class="acc-btn acc-btn--ghost">Reset</a>
            </div>
        </form>

        {{-- Daftar --}}
        @forelse ($logbooks as $lb)
            @php
                $jam = ($lb->jam_mulai && $lb->jam_selesai)
                    ? substr($lb->jam_mulai, 0, 5) . ' - ' . substr($lb->jam_selesai, 0, 5)
                    : null;

                $jenisLabel = ['harian' => 'Harian', 'lembur' => 'Lembur', 'oncall' => 'On Call'][$lb->jenis] ?? $lb->jenis;

                $kelasStatus = match ($lb->status) {
                    \App\Models\Logbook::STATUS_DISETUJUI => 'st--ok',
                    \App\Models\Logbook::STATUS_DITOLAK => 'st--no',
                    default => 'st--wait',
                };

                $detail = collect([$lb->shift?->nama ? 'Shift: ' . $lb->shift->nama : null, $jam])
                    ->filter()->implode(' | ');
            @endphp

            <div class="lb-card acc-item">

                <div class="acc-top">
                    <div>
                        <div class="acc-name">{{ $lb->user->name ?? '-' }}</div>
                        <div class="acc-date">{{ $lb->tanggal->format('d/m/Y') }}</div>
                    </div>

                    <div class="acc-badges">
                        <span class="acc-chip">{{ $jenisLabel }}</span>
                        @if ($lb->is_wfh)
                            <span class="acc-chip">WFH</span>
                        @endif
                        <span class="st {{ $kelasStatus }}">{{ $lb->status }}</span>
                    </div>
                </div>

                @if ($detail !== '')
                    <div class="acc-detail">{{ $detail }}</div>
                @endif

                <div class="acc-summary">{{ $lb->ringkasan }}</div>

                @if ($lb->keterangan_hc)
                    <div class="acc-hc"><strong>Keterangan HC:</strong> {{ $lb->keterangan_hc }}</div>
                @endif

                <div class="acc-actions">
                    @if ($lb->status !== \App\Models\Logbook::STATUS_DISETUJUI)
                        <form method="POST" action="{{ route('admin.logbook.setujui', $lb) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="acc-btn acc-btn--ok">Setujui</button>
                        </form>
                    @endif

                    @if ($lb->status !== \App\Models\Logbook::STATUS_DITOLAK)
                        <details class="acc-tolak">
                            <summary class="acc-btn acc-btn--no">Tolak</summary>

                            <form method="POST" action="{{ route('admin.logbook.tolak', $lb) }}">
                                @csrf
                                @method('PATCH')
                                <textarea name="keterangan_hc" class="lb-textarea" required maxlength="500"
                                    placeholder="Alasan penolakan (wajib)..."></textarea>
                                <button type="submit" class="acc-btn acc-btn--no">Kirim penolakan</button>
                            </form>
                        </details>
                    @endif
                </div>

            </div>
        @empty
            <div class="lb-card">
                <div class="acc-empty">Tidak ada logbook untuk filter ini.</div>
            </div>
        @endforelse

        {{-- Halaman sebelumnya / berikutnya --}}
        @if ($logbooks->hasPages())
            <div class="acc-pager">
                @if ($logbooks->onFirstPage())
                    <span class="is-off">&laquo; Sebelumnya</span>
                @else
                    <a href="{{ $logbooks->previousPageUrl() }}">&laquo; Sebelumnya</a>
                @endif

                @if ($logbooks->hasMorePages())
                    <a href="{{ $logbooks->nextPageUrl() }}">Berikutnya &raquo;</a>
                @else
                    <span class="is-off">Berikutnya &raquo;</span>
                @endif
            </div>
        @endif

    </div>
@endsection