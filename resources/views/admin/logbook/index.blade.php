@extends('layouts.app')

@section('title', 'ACC Logbook Pegawai')

@section('content')
@include('admin.logbook._styles')

<div class="acc-page">

    <div class="acc-head">
        <h1 class="acc-title">ACC Logbook Pegawai</h1>
        <a href="{{ route('admin.dashboard') }}" class="acc-back">&larr; Dashboard</a>
    </div>

    @if ($errors->any())
        <div class="acc-alert" role="alert">{{ $errors->first() }}</div>
    @endif

    {{-- Tab status --}}
    @php
        $tabs = [
            \App\Models\Logbook::STATUS_MENUNGGU  => $jumlah->get(\App\Models\Logbook::STATUS_MENUNGGU, 0),
            \App\Models\Logbook::STATUS_DISETUJUI => $jumlah->get(\App\Models\Logbook::STATUS_DISETUJUI, 0),
            \App\Models\Logbook::STATUS_DITOLAK   => $jumlah->get(\App\Models\Logbook::STATUS_DITOLAK, 0),
            'semua'                               => $jumlah->sum(),
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
    <form method="GET" action="{{ route('admin.logbook.index') }}" class="lb-card" id="acc-list-filter">
        <input type="hidden" name="status" value="{{ $status }}">

        <div class="acc-filter">
            <div>
                <label class="lb-label" for="q">Nama pegawai</label>
                <input type="text" id="q" name="q" value="{{ request('q') }}" class="lb-input" placeholder="Cari nama...">
            </div>

            <div>
                <label class="lb-label" for="instalasi">Instalasi</label>
                <select id="instalasi" name="instalasi" class="lb-select">
                    <option value="">Semua instalasi</option>
                    @foreach ($instalasiList as $namaInstalasi)
                        <option value="{{ $namaInstalasi }}" @selected($instalasi === $namaInstalasi)>{{ $namaInstalasi }}</option>
                    @endforeach
                </select>
            </div>

            <a href="{{ route('admin.logbook.index', ['status' => $status]) }}" class="acc-btn acc-btn--ghost acc-reset">Reset</a>
        </div>
    </form>

    {{-- Ringkasan --}}
    <div class="acc-total">
        {{ $totalEntri }} entri {{ $status === 'semua' ? 'dari semua status' : strtolower($status) }}
        &middot; Menampilkan {{ $pegawai->firstItem() ?? 0 }}–{{ $pegawai->lastItem() ?? 0 }} dari {{ $pegawai->total() }} pegawai
    </div>

    {{-- Daftar pegawai --}}
    @forelse ($pegawai as $p)
        <div class="lb-card acc-row">
            <div>
                <div class="acc-name">{{ $p->name }}</div>
                @if ($p->employee_unit)
                    <div class="acc-unit"><i class="fa-solid fa-building"></i> {{ $p->employee_unit }}</div>
                @endif
            </div>

            <div class="acc-row-right">
                <span class="acc-count">{{ $p->total_entri }} entri</span>

                {{-- Bawa status/jenis/tanggal ke halaman detail, tapi tanpa 'q' dan 'page' --}}
                <a href="{{ route('admin.logbook.pegawai', ['user' => $p->id, 'status' => $status]) }}"
                   class="acc-btn">
                    <i class="fa-solid fa-play"></i> Mulai Review
                </a>
            </div>
        </div>
    @empty
        <div class="lb-card">
            <div class="acc-empty">Tidak ada pegawai dengan logbook untuk filter ini.</div>
        </div>
    @endforelse

    {{-- Halaman sebelumnya / berikutnya --}}
    @if ($pegawai->hasPages())
        <div class="acc-pager">
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('acc-list-filter');
    if (!form) return;
    const unit = form.querySelector('[name="instalasi"]');
    unit?.addEventListener('change', () => form.requestSubmit());
    const name = form.querySelector('[name="q"]');
    let timer;
    name?.addEventListener('input', () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => form.requestSubmit(), 350);
    });
});
</script>
@endsection
