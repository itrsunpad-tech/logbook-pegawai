@extends('layouts.app')

@section('title', 'Kepegawaian')

@section('content')
@include('admin.kepegawaian._styles')

<div class="kp-page">

    <div class="kp-head">
        <div>
            <h1 class="kp-title">Kepegawaian</h1>
            <p class="kp-sub">{{ $jumlah['semua'] }} orang terdaftar di sistem</p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="kp-back">&larr; Dashboard</a>
    </div>

    {{-- Filter: punya email atau belum --}}
    @php
        $tabs = [
            'semua'     => 'Semua',
            'terdaftar' => 'Sudah punya email',
            'belum'     => 'Belum punya email',
        ];
    @endphp

    <div class="kp-pills">
        @foreach ($tabs as $kode => $label)
            <a href="{{ route('admin.kepegawaian.index', array_merge(request()->except('page'), ['status' => $kode])) }}"
               class="kp-pill {{ $status === $kode ? 'is-active' : '' }}">
                {{ $label }} <b>{{ $jumlah[$kode] }}</b>
            </a>
        @endforeach
    </div>

    {{-- Pencarian --}}
    <form method="GET" action="{{ route('admin.kepegawaian.index') }}" class="kp-search">
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="text" name="q" value="{{ request('q') }}" class="lb-input"
               placeholder="Cari nama, email, atau ID mesin...">
        <button type="submit" class="kp-btn">Cari</button>
    </form>

    {{-- Daftar pegawai --}}
    @forelse ($pegawai as $p)
        <a href="{{ route('admin.kepegawaian.show', $p) }}" class="lb-card kp-row">
            <div class="kp-avatar">{{ $p->inisial() }}</div>

            <div class="kp-row-main">
                <div class="kp-name">{{ $p->name }}</div>
                <div class="kp-meta">
                    {{ $p->employee_unit ?: 'Unit belum diisi' }}
                    @if ($p->fingerprint_id)
                        &middot; ID mesin {{ $p->fingerprint_id }}
                    @endif
                </div>
            </div>

            @if ($p->isAdmin())
                <span class="kp-chip kp-chip--admin">Admin</span>
            @endif

            @if ($p->email)
                <span class="kp-chip kp-chip--ok">Bisa login</span>
            @else
                <span class="kp-chip kp-chip--wait">Belum ada email</span>
            @endif
        </a>
    @empty
        <div class="lb-card">
            <div class="kp-empty">Tidak ada pegawai yang cocok.</div>
        </div>
    @endforelse

    {{-- Halaman sebelumnya / berikutnya --}}
    @if ($pegawai->hasPages())
        <div class="kp-pager">
            @if ($pegawai->onFirstPage())
                <span class="is-off">&laquo; Sebelumnya</span>
            @else
                <a href="{{ $pegawai->previousPageUrl() }}">&laquo; Sebelumnya</a>
            @endif

            @if ($pegawai->hasMorePages())
                <a href="{{ $pegawai->nextPageUrl() }}">Berikutnya &raquo;</a>
            @else
                <span class="is-off">Berikutnya &raquo;</span>
            @endif
        </div>
    @endif

</div>
@endsection