@extends('layouts.app')

@section('title', 'Rekap Pegawai')

@section('content')
<style>
    .rk-page { max-width:960px; margin:0 auto; display:flex; flex-direction:column; gap:14px; }
    .rk-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .rk-title { color:#16243a; font-size:20px; font-weight:800; }
    .rk-sub { margin-top:4px; color:#60728b; font-size:13px; }
    .rk-back { color:#145bd7; font-size:13px; font-weight:700; text-decoration:none; }
    .rk-list { display:grid; gap:12px; }
    .rk-search { display:flex; align-items:center; gap:9px; padding:10px 12px; border:1px solid #d5e1f0; border-radius:12px; background:#fff; box-shadow:0 3px 10px rgba(29,55,90,.04); }
    .rk-search i { color:#75869d; }
    .rk-search input { width:100%; min-width:0; border:0; outline:0; background:transparent; color:#1d2b40; font:inherit; font-size:13px; }
    .rk-search input::placeholder { color:#91a0b5; }
    .rk-row { min-height:70px; padding:12px 16px; display:flex; align-items:center; gap:12px; border:1px solid #d5e1f0; border-radius:16px; background:#fff; box-shadow:0 4px 14px rgba(29,55,90,.07); color:inherit; text-decoration:none; transition:transform .15s,border-color .15s,box-shadow .15s; }
    a.rk-row:hover { transform:translateY(-1px); border-color:#9bbcf2; box-shadow:0 7px 18px rgba(29,78,160,.11); }
    .rk-avatar { width:40px; height:40px; flex:0 0 40px; display:grid; place-items:center; border-radius:50%; background:#e9f1ff; color:#145bd7; font-size:13px; font-weight:800; }
    .rk-main { flex:1; min-width:0; }
    .rk-name { color:#1d2b40; font-size:14px; font-weight:750; }
    .rk-meta { margin-top:3px; color:#52647e; font-size:12px; }
    .rk-count { padding:5px 10px; border-radius:999px; background:#edf4ff; color:#145bd7; font-size:11px; font-weight:700; white-space:nowrap; }
    .rk-login { padding:4px 10px; border-radius:999px; background:#dcfce7; color:#15803d; font-size:10px; font-weight:800; white-space:nowrap; }
    .rk-empty { padding:32px 18px; border:1px solid #d5e1f0; border-radius:16px; background:#fff; color:#60728b; text-align:center; }
    .rk-pager { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:2px 1px; }
    .rk-pager-summary { color:#60728b; font-size:12px; }
    .rk-pager-controls { display:flex; align-items:center; gap:5px; }
    .rk-page-link { min-width:34px; height:34px; padding:0 9px; display:inline-flex; align-items:center; justify-content:center; border:1px solid #d5e1f0; border-radius:9px; background:#fff; color:#40536e; font-size:13px; font-weight:700; text-decoration:none; }
    .rk-page-link:hover { border-color:#8db3f5; color:#145bd7; }
    .rk-page-link.is-current { border-color:#145bd7; background:#145bd7; color:#fff; }
    .rk-page-link.is-disabled { color:#a2afc0; background:#f8fafd; cursor:not-allowed; }
    @media(max-width:520px) { .rk-row { padding:11px 12px; } .rk-login { display:none; } }
</style>
<main class="rk-page">
    <header class="rk-head">
        <div>
            <h1 class="rk-title">Rekap Logbook Pegawai</h1>
            <p class="rk-sub">Pilih instalasi untuk melihat daftar pegawai dan ringkasan logbook.</p>
        </div>
        @if ($instalasi)
            <a class="rk-back" href="{{ route('admin.rekap.index') }}">← Semua instalasi</a>
        @else
            <a class="rk-back" href="{{ route('admin.dashboard') }}">← Dashboard</a>
        @endif
    </header>

    @if ($instalasi && $pegawai)
        <h2 class="rk-title" style="font-size:16px">{{ $instalasi }} <span class="rk-count">{{ $pegawai->total() }} pegawai</span></h2>
        <form method="GET" class="rk-search" id="rekap-search-form">
            <input type="hidden" name="instalasi" value="{{ $instalasi }}">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="search" name="q" value="{{ $kata }}" placeholder="Cari nama, jabatan, atau email pegawai..." aria-label="Cari pegawai">
        </form>
        @if ($pegawai->isEmpty())
            <div class="rk-empty">{{ $kata ? 'Pegawai tidak ditemukan dengan kata kunci tersebut.' : 'Belum ada pegawai terdaftar pada instalasi ini.' }}</div>
        @else
            <div class="rk-list">
                @foreach ($pegawai as $p)
                    @php($inisial = collect(explode(' ', trim($p->name)))->filter()->take(2)->map(fn ($kata) => mb_substr($kata, 0, 1))->implode(''))
                    <a class="rk-row" href="{{ route('admin.rekap.pegawai', $p) }}">
                        <span class="rk-avatar">{{ mb_strtoupper($inisial) }}</span>
                        <span class="rk-main"><span class="rk-name">{{ $p->name }}</span><span class="rk-meta" style="display:block">{{ $p->employee_jabatan ?: $p->employee_unit ?: 'Pegawai' }}</span></span>
                        @if ($p->email)<span class="rk-login">Bisa login</span>@endif
                    </a>
                @endforeach
            </div>
            @if ($pegawai->hasPages())
                <nav class="rk-pager" aria-label="Navigasi halaman pegawai">
                    <span class="rk-pager-summary">Menampilkan {{ $pegawai->firstItem() }}–{{ $pegawai->lastItem() }} dari {{ $pegawai->total() }} pegawai</span>
                    <div class="rk-pager-controls">
                        @if ($pegawai->onFirstPage())
                            <span class="rk-page-link is-disabled" aria-disabled="true" aria-label="Halaman sebelumnya">‹</span>
                        @else
                            <a class="rk-page-link" href="{{ $pegawai->previousPageUrl() }}" aria-label="Halaman sebelumnya">‹</a>
                        @endif
                        @foreach ($pegawai->getUrlRange(1, $pegawai->lastPage()) as $page => $url)
                            <a class="rk-page-link {{ $pegawai->currentPage() === $page ? 'is-current' : '' }}" href="{{ $url }}" @if ($pegawai->currentPage() === $page) aria-current="page" @endif>{{ $page }}</a>
                        @endforeach
                        @if ($pegawai->hasMorePages())
                            <a class="rk-page-link" href="{{ $pegawai->nextPageUrl() }}" aria-label="Halaman berikutnya">›</a>
                        @else
                            <span class="rk-page-link is-disabled" aria-disabled="true" aria-label="Halaman berikutnya">›</span>
                        @endif
                    </div>
                </nav>
            @endif
        @endif
    @else
        <form method="GET" class="rk-search" id="rekap-search-form">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="search" name="q" value="{{ $kata }}" placeholder="Cari instalasi..." aria-label="Cari instalasi">
        </form>
        @if ($daftarInstalasi->isEmpty())
            <div class="rk-empty">{{ $kata ? 'Instalasi tidak ditemukan dengan kata kunci tersebut.' : 'Belum ada instalasi yang terdaftar pada data pegawai.' }}</div>
        @else
            <div class="rk-list">
                @foreach ($daftarInstalasi as $item)
                    @php($inisial = collect(explode(' ', trim($item->employee_instalasi)))->filter()->take(2)->map(fn ($kata) => mb_substr($kata, 0, 1))->implode(''))
                    <a class="rk-row" href="{{ route('admin.rekap.index', ['instalasi' => $item->employee_instalasi]) }}">
                        <span class="rk-avatar"><i class="fa-solid fa-building"></i></span>
                        <span class="rk-main"><span class="rk-name">{{ $item->employee_instalasi }}</span><span class="rk-meta" style="display:block">Lihat rekap pegawai pada instalasi ini</span></span>
                        <span class="rk-count">{{ $item->total_pegawai }} pegawai</span>
                    </a>
                @endforeach
            </div>
        @endif
    @endif
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('rekap-search-form');
    const input = form?.querySelector('input[name="q"]');
    if (!form || !input) return;
    let timer;
    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => form.requestSubmit(), 350);
    });
});
</script>
@endsection
