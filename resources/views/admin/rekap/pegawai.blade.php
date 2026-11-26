@extends('layouts.app')

@section('title', 'Rekap Logbook Pegawai')

@section('content')
<style>
    .rk-report { max-width:960px; margin:0 auto; display:flex; flex-direction:column; gap:18px; color:#1d2b40; }
    .rk-report-head { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; flex-wrap:wrap; }
    .rk-report-title { color:#17263d; font-size:22px; font-weight:800; letter-spacing:-.02em; }
    .rk-report-sub { margin-top:5px; color:#60728b; font-size:13px; }
    .rk-report-tools { display:flex; align-items:center; gap:8px; }
    .rk-report-btn { display:inline-flex; align-items:center; justify-content:center; gap:5px; min-height:28px; padding:4px 8px; border:1px solid #cbd8eb; border-radius:7px; background:#fff; color:#145bd7; font-size:10px; font-weight:700; text-decoration:none; cursor:pointer; }
    .rk-report-btn--primary { border-color:#fecaca; background:#fff1f2; color:#c62828; }
    .rk-report-btn--primary:hover { border-color:#fca5a5; background:#ffe4e6; }
    .rk-report-btn--excel { border-color:#bbdfc8; background:#effaf2; color:#167444; }
    .rk-report-btn--excel:hover { border-color:#86c79d; background:#e3f5e8; }
    .rk-late-card { min-height:84px; padding:16px 18px; display:flex; align-items:center; justify-content:space-between; gap:16px; border:1px solid #d8e3f1; border-radius:15px; background:#fff; box-shadow:0 5px 16px rgba(29,55,90,.06); }
    .rk-late-title { color:#1d2b40; font-size:14px; font-weight:800; }
    .rk-late-sub { margin-top:4px; color:#718198; font-size:12px; }
    .rk-late-value { color:#1d2b40; font-size:28px; font-weight:800; white-space:nowrap; }
    .rk-late-value small { margin-left:4px; color:#718198; font-size:13px; font-weight:700; }
    .rk-export-tools { display:flex; align-items:center; justify-content:flex-end; gap:7px; margin-top:-8px; margin-bottom:-8px; }
    .rk-report-table-wrap { overflow-x:auto; border:1px solid #d8e3f1; border-radius:14px; background:#fff; box-shadow:0 5px 18px rgba(29,55,90,.06); }
    .rk-report-table { width:100%; border-collapse:collapse; font-size:13px; }
    .rk-report-table th,.rk-report-table td { padding:14px 16px; border-bottom:1px solid #e5ebf3; text-align:left; white-space:nowrap; }
    .rk-report-table th { background:#f3f7fc; color:#53657e; font-size:10px; text-transform:uppercase; letter-spacing:.045em; }
    .rk-report-table th:not(:first-child),.rk-report-table td:not(:first-child) { text-align:right; font-variant-numeric:tabular-nums; }
    .rk-report-table tr:last-child td { border-bottom:0; }
    .rk-report-table td:first-child { color:#1d2b40; font-weight:750; }
    .rk-report-table tbody tr:hover { background:#f8faff; }
    .rk-report-foot { color:#75869d; font-size:11px; }
    @media(max-width:520px) { .rk-export-tools { margin:0; } }
    @media print {
        @page { size:A4 portrait; margin:16mm; }
        body { background:#fff !important; }
        body * { visibility:hidden !important; }
        .rk-report,.rk-report * { visibility:visible !important; }
        .rk-report { position:absolute; inset:0; max-width:none; gap:12px; }
        .rk-report-tools,.rk-back { display:none !important; }
        .rk-late-card { box-shadow:none; }
        .rk-report-table-wrap { overflow:visible; border-color:#9aa9bb; }
        .rk-report-table th,.rk-report-table td { padding:9px; border-color:#cbd5e1; }
        .rk-report-table th { background:#edf2f7 !important; print-color-adjust:exact; }
    }
</style>
<main class="rk-report">
    <header class="rk-report-head">
        <div>
            <h1 class="rk-report-title">Rekap Logbook Pegawai</h1>
            <p class="rk-report-sub">{{ $user->name }} · {{ $user->employee_jabatan ?: ($user->employee_instalasi ?: 'Pegawai') }}</p>
        </div>
        <a class="rk-report-btn rk-back" href="{{ route('admin.rekap.index', ['instalasi' => $user->employee_instalasi]) }}"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </header>

    <div class="rk-export-tools rk-report-tools">
        <a class="rk-report-btn rk-report-btn--excel" href="{{ route('admin.rekap.pegawai.excel', $user) }}"><i class="fa-solid fa-file-excel"></i> Unduh Excel</a>
        <button class="rk-report-btn rk-report-btn--primary" type="button" onclick="window.print()"><i class="fa-solid fa-download"></i> Unduh PDF</button>
    </div>

    <section class="rk-late-card" aria-label="Total keterlambatan">
        <div><h2 class="rk-late-title">Total Keterlambatan</h2><p class="rk-late-sub">Ringkasan keterlambatan pegawai</p></div>
        <strong class="rk-late-value">{{ $totalTerlambat }}<small>hari</small></strong>
    </section>

    <div class="rk-report-table-wrap">
        <table class="rk-report-table">
            <thead>
                <tr><th>Ringkasan</th><th>Diisi</th><th>Approved</th><th>Total durasi</th><th>Durasi approved</th></tr>
            </thead>
            <tbody>
                @foreach ($ringkasan as $baris)
                    <tr>
                        <td>{{ $baris['nama'] }}</td>
                        <td>{{ $baris['diisi'] }} {{ $baris['satuan'] }}</td>
                        <td>{{ $baris['approved'] }} {{ $baris['satuan'] }}</td>
                        <td>{{ intdiv($baris['menit'], 60) }} jam {{ str_pad((string) ($baris['menit'] % 60), 2, '0', STR_PAD_LEFT) }} menit</td>
                        <td>{{ intdiv($baris['approved_menit'], 60) }} jam {{ str_pad((string) ($baris['approved_menit'] % 60), 2, '0', STR_PAD_LEFT) }} menit</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="rk-report-foot">Dibuat {{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB · Ringkasan mencakup seluruh data logbook dan fingerprint yang tersedia.</p>
</main>
@endsection
