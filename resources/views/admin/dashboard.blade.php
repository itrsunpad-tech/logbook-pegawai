@extends('layouts.app')

@section('title', 'Dashboard Admin HC')

@push('styles')
    <style>
        .adm-page {
            width: 100%;
            max-width: 960px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .adm-title {
            color: var(--text-dark);
            font-size: 18px;
            font-weight: 700;
        }

        .adm-sub {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
        }

        .adm-stats,
        .adm-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 12px;
        }

        .adm-stat-value {
            margin-top: 8px;
            color: var(--accent);
            font-size: 28px;
            font-weight: 700;
        }

        .adm-stat-label {
            margin-top: 2px;
            color: var(--muted);
            font-size: 11px;
        }

        .adm-menu-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 16px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 3px 10px rgba(15, 23, 42, 0.05);
        }

        a.adm-menu-item:hover {
            border-color: var(--accent-border);
            background: var(--accent-soft);
        }

        .adm-menu-item i {
            color: var(--accent);
            font-size: 18px;
        }

        .adm-menu-name {
            color: var(--text-dark);
            font-size: 13px;
            font-weight: 700;
        }

        .adm-menu-desc {
            color: var(--muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .adm-menu-item.is-disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .adm-badge {
            align-self: flex-start;
            padding: 2px 8px;
            border-radius: 999px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--muted);
            font-size: 9px;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
    <div class="adm-page">

        <div>
            <h1 class="adm-title">Dashboard Admin HC</h1>
            <p class="adm-sub">Kelola logbook pegawai: persetujuan, rekap, dan data kehadiran.</p>
        </div>

        {{-- Ringkasan angka --}}
        <div class="adm-stats">
            <div class="lb-card">
                <h2 class="lb-card-title"><i class="fa-solid fa-users"></i> Pegawai</h2>
                <div class="adm-stat-value">{{ $ringkasan['pegawai'] }}</div>
                <div class="adm-stat-label">Total akun pegawai</div>
            </div>

            <div class="lb-card">
                <h2 class="lb-card-title"><i class="fa-solid fa-calendar-day"></i> Hari ini</h2>
                <div class="adm-stat-value">{{ $ringkasan['logbook_hari_ini'] }}</div>
                <div class="adm-stat-label">Logbook tanggal hari ini</div>
            </div>

            <div class="lb-card">
                <h2 class="lb-card-title"><i class="fa-solid fa-calendar"></i> Bulan ini</h2>
                <div class="adm-stat-value">{{ $ringkasan['logbook_bulan_ini'] }}</div>
                <div class="adm-stat-label">Logbook bulan {{ now()->translatedFormat('F Y') }}</div>
            </div>
        </div>

        {{-- Menu --}}
        <div class="adm-menu">
            <a href="{{ route('admin.logbook.index') }}" class="adm-menu-item">
                <i class="fa-solid fa-circle-check"></i>
                <span class="adm-menu-name">ACC Logbook Pegawai</span>
                <span class="adm-menu-desc">Setujui atau tolak logbook yang dikirim pegawai.</span>
                @if ($ringkasan['menunggu'] > 0)
                    <span class="adm-badge">{{ $ringkasan['menunggu'] }} menunggu</span>
                @endif
            </a>

            <div class="adm-menu-item is-disabled">
                <i class="fa-solid fa-table-list"></i>
                <span class="adm-menu-name">Rekap Logbook Pegawai</span>
                <span class="adm-menu-desc">Rekap logbook per pegawai dan per periode.</span>
                <span class="adm-badge">Segera hadir</span>
            </div>

            <div class="adm-menu-item is-disabled">
                <i class="fa-solid fa-fingerprint"></i>
                <span class="adm-menu-name">Rekap Sidik Jari</span>
                <span class="adm-menu-desc">Data dari mesin sidik jari (eksperimental).</span>
                <span class="adm-badge">Tahap berikutnya</span>
            </div>
        </div>

    </div>
@endsection