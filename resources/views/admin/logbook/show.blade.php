@extends('layouts.app')

@section('title', 'Review Logbook - ' . $user->name)

@section('content')
@include('admin.logbook._styles')

<div class="acc-page">

    <div class="acc-head">
        <div>
            <h1 class="acc-title">{{ $user->name }}</h1>
            <p class="acc-sub">
                @if ($user->employee_unit)
                    <i class="fa-solid fa-building"></i> {{ $user->employee_unit }} &middot;
                @endif
                Menampilkan entri: {{ $status === 'semua' ? 'semua status' : $status }}
            </p>
        </div>

        <a href="{{ route('admin.logbook.index', request()->except('page')) }}" class="acc-back">
            &larr; Kembali ke daftar pegawai
        </a>
    </div>

    {{-- Error validasi (mis. alasan penolakan kosong) --}}
    @if ($errors->any())
        <div class="acc-alert" role="alert">{{ $errors->first() }}</div>
    @endif

    {{-- Daftar logbook pegawai ini --}}
    @forelse ($logbooks as $lb)
        @php
            $jam = ($lb->jam_mulai && $lb->jam_selesai)
                ? substr($lb->jam_mulai, 0, 5) . ' - ' . substr($lb->jam_selesai, 0, 5)
                : null;

            $jenisLabel = ['harian' => 'Harian', 'lembur' => 'Lembur', 'oncall' => 'On Call'][$lb->jenis] ?? $lb->jenis;

            $kelasStatus = match ($lb->status) {
                \App\Models\Logbook::STATUS_DISETUJUI => 'st--ok',
                \App\Models\Logbook::STATUS_DITOLAK   => 'st--no',
                default                               => 'st--wait',
            };

            $detail = collect([$lb->shift?->nama ? 'Shift: ' . $lb->shift->nama : null, $jam])
                ->filter()->implode(' | ');
        @endphp

        <div class="lb-card acc-item">

            <div class="acc-top">
                <div class="acc-date">{{ $lb->tanggal->format('d/m/Y') }}</div>

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

            @if ($lb->keterangan_pegawai)
                <div class="acc-hc"><strong>Keterangan Banding Pegawai:</strong> {{ $lb->keterangan_pegawai }}</div>
            @endif

            <div class="acc-actions">
                @php($bandingFinal = in_array($lb->status_banding, [\App\Models\Logbook::BANDING_DISETUJUI, \App\Models\Logbook::BANDING_DITOLAK], true))
                @if (!$bandingFinal && $lb->status !== \App\Models\Logbook::STATUS_DISETUJUI)
                    <form method="POST" action="{{ route('admin.logbook.setujui', $lb) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="acc-btn acc-btn--ok">Setujui</button>
                    </form>
                @endif

                @if (!$bandingFinal && $lb->status !== \App\Models\Logbook::STATUS_DITOLAK)
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
                @if ($bandingFinal)
                    <span class="acc-chip">Banding {{ strtolower($lb->status_banding) }}</span>
                @endif
            </div>

        </div>
    @empty
        <div class="lb-card">
            <div class="acc-empty">
                Tidak ada entri untuk pegawai ini dengan filter yang dipilih.
                <br>
                <a href="{{ route('admin.logbook.index', request()->except('page')) }}" class="acc-back">
                    &larr; Kembali ke daftar pegawai
                </a>
            </div>
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
