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
            font-size: 13px;
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
            font-size: 12px;
        }

        .adm-menu-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 16px;
            background: linear-gradient(160deg, #fff 0%, #fafdff 100%);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(24, 49, 83, 0.075), 0 1px 3px rgba(24, 49, 83, 0.045);
            transition: transform 0.16s ease, border-color 0.16s ease, box-shadow 0.16s ease;
        }

        a.adm-menu-item:hover {
            border-color: var(--accent-border);
            background: linear-gradient(160deg, #fff 0%, var(--accent-soft) 100%);
            box-shadow: 0 12px 28px rgba(24, 82, 166, 0.13);
            transform: translateY(-2px);
        }

        .adm-menu-item i {
            color: var(--accent);
            font-size: 18px;
        }

        .adm-menu-name {
            color: var(--text-dark);
            font-size: 14px;
            font-weight: 700;
        }

        .adm-menu-desc {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .adm-menu-item.is-disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .adm-menu-item:focus-visible {
            outline: 3px solid rgba(40, 120, 240, 0.3);
            outline-offset: 3px;
        }

        .adm-badge {
            align-self: flex-start;
            padding: 2px 8px;
            border-radius: 999px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--muted);
            font-size: 10px;
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

            <a href="{{ route('admin.sidik-jari.index') }}" class="adm-menu-item">
                <i class="fa-solid fa-fingerprint"></i>
                <span class="adm-menu-name">Rekap Sidik Jari</span>
                <span class="adm-menu-desc">Monitoring absensi langsung dari mesin sidik jari.</span>
                <span class="adm-badge">Live</span>
            </a>

            <a href="{{ route('admin.kepegawaian.index') }}" class="adm-menu-item">
                <i class="fa-solid fa-users-gear"></i>
                <span class="adm-menu-name">Kepegawaian</span>
                <span class="adm-menu-desc">Data pegawai, kontak, dan pendaftaran email login.</span>
            </a>
        </div>

    </div>
@endsection
