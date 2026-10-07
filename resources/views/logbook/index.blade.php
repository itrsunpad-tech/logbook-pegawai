@extends('layouts.app')

@section('title', 'Logbook System')

@section('content')

@push('styles')
<style>
    /* =====================================================
       RIWAYATKU: FILTER TANGGAL (tanpa Rekap Sheet)
    ====================================================== */
    /* Desktop: Dari | Sampai | [Lihat] [Reset] dalam satu baris */
    .rw-filter {
        margin-top: 12px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
        align-items: end;
        gap: 12px;
    }

    .rw-filter .lb-input {
        height: 38px;
    }

    .rw-actions {
        display: flex;
        gap: 8px;
    }

    .rw-actions .riwayat-view-button {
        flex: 0 0 auto;
    }

    /* Layar sempit: dua tanggal berdampingan, tombol di baris bawah */
    @media (max-width: 560px) {
        .rw-filter {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .rw-actions {
            grid-column: 1 / -1;
        }

        .rw-actions .riwayat-view-button {
            flex: 1;
        }
    }

    @media (max-width: 380px) {
        .rw-filter {
            grid-template-columns: 1fr;
        }
    }


    /* =====================================================
       RIWAYATKU: BARIS DETAIL + TOMBOL DETAIL
    ====================================================== */
    .rw-item-row {
        margin-top: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .rw-item-row .riwayat-item-detail {
        margin-top: 0;
        min-width: 0;
    }

    .rw-detail-btn {
        height: 30px;
        padding: 0 11px;
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid var(--accent-soft-border);
        border-radius: 8px;
        background: #fff;
        color: var(--accent);
        font-size: 11px;
        font-weight: 700;
        transition: background 0.15s ease, border-color 0.15s ease;
    }

    .rw-detail-btn:hover {
        background: var(--accent-soft);
        border-color: var(--accent-border);
    }


    /* =====================================================
       MODAL DETAIL LOGBOOK
    ====================================================== */
    .rw-modal {
        position: fixed;
        inset: 0;
        z-index: 250; /* di atas modal kalender (200), di bawah toast (300) */
        padding: 16px;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(2px);
    }

    .rw-modal.is-open {
        display: flex;
    }

    .rw-box {
        width: 100%;
        max-width: 540px;
        max-height: calc(100vh - 32px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);
        animation: rw-in 0.18s ease;
    }

    @keyframes rw-in {
        from { opacity: 0; transform: scale(0.97) translateY(6px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }

    .rw-head {
        padding: 16px 18px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid var(--border);
    }

    .rw-title {
        color: var(--text-dark);
        font-size: 14px;
        font-weight: 700;
    }

    .rw-sub {
        margin-top: 3px;
        color: var(--muted-light);
        font-size: 12px;
    }

    .rw-close {
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        border: 0;
        border-radius: 8px;
        background: var(--surface);
        color: var(--muted);
        font-size: 18px;
        line-height: 1;
    }

    .rw-close:hover {
        background: #f1f5f9;
    }

    .rw-body {
        padding: 16px 18px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        overflow-y: auto;
    }

    /* Ringkasan: jenis, jam kerja, status */
    .rw-meta {
        padding: 12px 14px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 12px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--surface);
    }

    .rw-meta-item {
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }

    .rw-meta-label,
    .rw-section-title {
        color: var(--muted-light);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.45px;
        text-transform: uppercase;
    }

    .rw-meta-value {
        color: var(--text-dark);
        font-size: 12.5px;
        font-weight: 600;
        line-height: 1.4;
    }

    .rw-status {
        padding: 4px 9px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .rw-status--ok   { background: #dcfce7; color: #15803d; }
    .rw-status--no   { background: #fee2e2; color: #b91c1c; }
    .rw-status--wait { background: #fef3c7; color: #b45309; }

    /* Wadah badge di kartu riwayat: status + jenis, sejajar di kanan */
    .riwayat-item-badges {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        flex-wrap: wrap;
    }

    /* Badge status di kartu dibuat sedikit lebih kecil dari yang di modal Detail */
    .riwayat-item-badges .rw-status {
        padding: 3px 9px;
        font-size: 10.5px;
    }

    /* Bagian isi */
    .rw-section {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .rw-section-title {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .rw-section-title i {
        color: var(--accent);
    }

    .rw-text {
        padding: 12px 14px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #fff;
        color: var(--text);
        font-size: 13px;
        line-height: 1.65;
        overflow-wrap: anywhere;
    }

    .rw-text p + p,
    .rw-text ul + p,
    .rw-text p + ul {
        margin-top: 8px;
    }

    .rw-text ul {
        padding-left: 18px;
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .rw-text ul li::marker {
        color: var(--accent);
    }

    .rw-text--muted {
        background: var(--surface);
        color: var(--muted-light);
    }

    .rw-foot {
        padding: 12px 18px;
        display: flex;
        justify-content: flex-end;
        border-top: 1px solid var(--border);
    }

    .rw-foot-btn {
        height: 36px;
        padding: 0 18px;
        border: 1px solid var(--border);
        border-radius: 9px;
        background: #fff;
        color: var(--text);
        font-size: 12px;
        font-weight: 700;
    }

    .rw-foot-btn:hover {
        background: var(--surface);
    }

    @media (max-width: 700px) {
        .rw-modal {
            padding: 10px;
            align-items: flex-end;
        }

        .rw-box {
            max-height: calc(100vh - 20px);
            border-radius: 16px 16px 12px 12px;
        }
    }
</style>
@endpush

@php
    // Jenis form yang terakhir dikirim (dipakai untuk membuka kembali form saat validasi gagal)
    $jenisLama = old('jenis');

    // Isi ulang hanya form yang tadi dikirim, form lain tetap kosong
    $o = fn ($jenis, $field, $default = '') => $jenisLama === $jenis ? old($field, $default) : $default;
@endphp


<div class="lb-page">

    {{-- Error validasi (notifikasi sukses memakai toast di layout) --}}
    @if ($errors->any())
        <div class="lb-alert lb-alert--error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===================== TAB ===================== --}}
    <div class="lb-tabs">
        <button type="button" data-tab="input" class="lb-tab is-active">
            <i class="fa-solid fa-pen"></i>
            Input Baru
        </button>

        <button type="button" data-tab="riwayat" class="lb-tab">
            <i class="fa-solid fa-clock-rotate-left"></i>
            Riwayatku
        </button>
    </div>


    {{-- ===================== TAB INPUT ===================== --}}
    <section data-tab-panel="input">

        {{-- Pilih jenis logbook --}}
        <div class="lb-card">
            <h2 class="lb-card-title">
                <i class="fa-solid fa-thumbtack"></i>
                Jenis Logbook
            </h2>

            <div class="lb-type-grid">
                <button type="button" data-jenis="harian" class="lb-type-button">
                    <i class="fa-solid fa-book"></i>
                    Logbook Harian
                </button>

                <button type="button" data-jenis="lembur" class="lb-type-button">
                    <i class="fa-solid fa-business-time"></i>
                    Lembur
                </button>

                <button type="button" data-jenis="oncall" class="lb-type-button lb-type-button--wide">
                    <i class="fa-solid fa-phone"></i>
                    On Call
                    <span class="lb-type-sub">Maks pengisian {{ $maksOncall }} kali per hari</span>
                </button>
            </div>
        </div>


        {{-- ===================== FORM HARIAN ===================== --}}
        <form
            method="POST"
            action="{{ route('logbook.store') }}"
            data-jenis-panel="harian"
            class="lb-card lb-form"
        >
            @csrf
            <input type="hidden" name="jenis" value="harian">

            <h2 class="lb-card-title">
                <i class="fa-solid fa-book"></i>
                Logbook Harian
            </h2>

            <div class="lb-field">
                <label class="lb-label">Tanggal *</label>
                <input
                    type="date"
                    name="tanggal"
                    value="{{ $o('harian', 'tanggal') }}"
                    required
                    class="lb-input js-tanggal"
                >
            </div>

            <div class="lb-field">
                <label class="lb-label">Shift / Jadwal *</label>
                <select name="shift_id" required class="lb-select">
                    <option value="">-- Pilih Shift --</option>

                    @foreach ($shifts as $shift)
                        <option
                            value="{{ $shift->id }}"
                            @selected($o('harian', 'shift_id') == $shift->id)
                        >
                            {{ $shift->label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lb-field">
                <label class="lb-label">Ringkasan Kegiatan *</label>
                <textarea
                    name="ringkasan"
                    rows="4"
                    required
                    class="lb-textarea"
                    placeholder="Jelaskan kegiatan yang dilakukan..."
                >{{ $o('harian', 'ringkasan') }}</textarea>
            </div>

            <div class="lb-field">
                <label class="lb-checkbox">
                    <input
                        type="checkbox"
                        name="is_wfh"
                        value="1"
                        disabled
                        class="js-wfh"
                        @checked($o('harian', 'is_wfh'))
                    >
                    <span>Saya hari ini WFH (Work From Home)</span>
                </label>

                <p class="lb-info js-wfh-info">
                    Pilih tanggal dulu untuk mengaktifkan opsi WFH.
                </p>
            </div>

            @include('logbook._pernyataan')
        </form>


        {{-- ===================== FORM LEMBUR ===================== --}}
        <form
            method="POST"
            action="{{ route('logbook.store') }}"
            data-jenis-panel="lembur"
            class="lb-card lb-form"
        >
            @csrf
            <input type="hidden" name="jenis" value="lembur">

            <h2 class="lb-card-title">
                <i class="fa-solid fa-business-time"></i>
                Lembur
            </h2>

            <div class="lb-field">
                <label class="lb-label">Tanggal *</label>
                <input
                    type="date"
                    name="tanggal"
                    value="{{ $o('lembur', 'tanggal') }}"
                    required
                    class="lb-input"
                >
            </div>

            <div class="lb-two-column">
                <div class="lb-field">
                    <label class="lb-label">Jam Mulai *</label>
                    <input
                        type="time"
                        name="jam_mulai"
                        value="{{ $o('lembur', 'jam_mulai') }}"
                        required
                        class="lb-input"
                    >
                </div>

                <div class="lb-field">
                    <label class="lb-label">Jam Selesai *</label>
                    <input
                        type="time"
                        name="jam_selesai"
                        value="{{ $o('lembur', 'jam_selesai') }}"
                        required
                        class="lb-input"
                    >
                </div>
            </div>

            <p class="lb-note">
                Minimal {{ $minJam }} jam biar diakui/disetujui HC.
            </p>

            <div class="lb-field">
                <label class="lb-label">Ringkasan Kegiatan *</label>
                <textarea
                    name="ringkasan"
                    rows="4"
                    required
                    class="lb-textarea"
                    placeholder="Jelaskan kegiatan yang dilakukan..."
                >{{ $o('lembur', 'ringkasan') }}</textarea>
            </div>

            @include('logbook._pernyataan')
        </form>


        {{-- ===================== FORM ON CALL ===================== --}}
        <form
            method="POST"
            action="{{ route('logbook.store') }}"
            data-jenis-panel="oncall"
            class="lb-card lb-form"
        >
            @csrf
            <input type="hidden" name="jenis" value="oncall">

            <h2 class="lb-card-title">
                <i class="fa-solid fa-phone"></i>
                On Call
            </h2>

            <div class="lb-field">
                <label class="lb-label">Tanggal *</label>
                <input
                    type="date"
                    name="tanggal"
                    value="{{ $o('oncall', 'tanggal') }}"
                    required
                    class="lb-input"
                >
            </div>

            <div class="lb-two-column">
                <div class="lb-field">
                    <label class="lb-label">Jam Mulai *</label>
                    <input
                        type="time"
                        name="jam_mulai"
                        value="{{ $o('oncall', 'jam_mulai') }}"
                        required
                        class="lb-input"
                    >
                </div>

                <div class="lb-field">
                    <label class="lb-label">Jam Selesai *</label>
                    <input
                        type="time"
                        name="jam_selesai"
                        value="{{ $o('oncall', 'jam_selesai') }}"
                        required
                        class="lb-input"
                    >
                </div>
            </div>

            <div class="lb-field">
                <label class="lb-label">Ringkasan Kegiatan *</label>
                <textarea
                    name="ringkasan"
                    rows="4"
                    required
                    class="lb-textarea"
                    placeholder="Jelaskan kegiatan yang dilakukan..."
                >{{ $o('oncall', 'ringkasan') }}</textarea>
            </div>

            @include('logbook._pernyataan')
        </form>

    </section>


    {{-- ===================== TAB RIWAYAT ===================== --}}
    <section data-tab-panel="riwayat" class="is-hidden">

        <div class="lb-card riwayat-card">

            <h2 class="lb-card-title">
                <i class="fa-solid fa-clock-rotate-left"></i>
                Riwayat Logbook
            </h2>

            {{-- FILTER TANGGAL --}}
            <div class="rw-filter">

                <div class="rw-field">
                    <label for="riwayat-dari" class="lb-label">Dari Tanggal</label>
                    <input type="date" id="riwayat-dari" class="lb-input">
                </div>

                <div class="rw-field">
                    <label for="riwayat-sampai" class="lb-label">Sampai Tanggal</label>
                    <input type="date" id="riwayat-sampai" class="lb-input">
                </div>

                <div class="rw-actions">
                    <button type="button" id="btn-lihat-riwayat" class="riwayat-view-button">
                        <i class="fa-solid fa-eye"></i>
                        Lihat
                    </button>

                    <button type="button" id="btn-reset-riwayat" class="riwayat-reset-button" title="Reset">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                </div>

            </div>

            {{-- HASIL --}}
            <div id="riwayat-result" class="riwayat-result">
                <div class="riwayat-empty">
                    <div class="riwayat-empty-icon">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>

                    <p>
                        Pilih rentang tanggal (dari–sampai) lalu klik "Lihat".
                        Data akan dimuat sekali untuk seluruh rentang.
                    </p>
                </div>
            </div>

        </div>

    </section>


    {{-- ===================== KALENDER ===================== --}}
    <div id="kalender-utama">
        <div class="lb-card">
            <h2 class="lb-card-title">
                <i class="fa-solid fa-calendar-days"></i>
                Kalender Pengisian
            </h2>

            <div class="lb-cal-nav">
                <button id="kal-prev" type="button" class="lb-cal-btn" aria-label="Bulan sebelumnya">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <span id="kal-judul" class="lb-cal-title"></span>

                <button id="kal-next" type="button" class="lb-cal-btn" aria-label="Bulan berikutnya">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

            <div class="lb-cal-head">
                <span>Min</span>
                <span>Sen</span>
                <span>Sel</span>
                <span>Rab</span>
                <span>Kam</span>
                <span>Jum</span>
                <span>Sab</span>
            </div>

            <div id="kal-grid" class="lb-cal-grid"></div>

            <div class="lb-legend">
                <span><i class="lb-dot lb-dot--harian"></i> Harian</span>
                <span><i class="lb-dot lb-dot--lembur"></i> Lembur</span>
                <span><i class="lb-dot lb-dot--oncall"></i> On Call</span>
                <span><i class="lb-dot lb-dot--libur"></i> Libur Nasional</span>
                <span><i class="lb-dot lb-dot--kosong"></i> Belum diisi</span>
                <span><i class="lb-dot lb-dot--dll"></i> DLL</span>
            </div>

            <p class="lb-hint">
                Hanya menampilkan 2 bulan terakhir. Ketuk tanggal buat lihat detail.
            </p>
        </div>
    </div>

</div>


{{-- ===================== MODAL DETAIL TANGGAL (KALENDER) ===================== --}}
<div id="kal-modal" class="lb-modal" role="dialog" aria-modal="true">
    <div class="lb-modal-box">

        <div class="lb-modal-head">
            <div>
                <h3 id="kal-modal-judul" class="lb-modal-title"></h3>
                <p id="kal-modal-sub" class="lb-modal-sub"></p>
            </div>

            <button id="kal-modal-tutup" type="button" class="lb-modal-close">&times;</button>
        </div>

        <ul id="kal-modal-list" class="lb-modal-list"></ul>

        <div id="kal-modal-type" class="lb-modal-type-grid">
            <button type="button" data-open-jenis="harian" class="lb-modal-type-button">
                <i class="fa-solid fa-book"></i>
                Logbook Harian
            </button>

            <button type="button" data-open-jenis="lembur" class="lb-modal-type-button">
                <i class="fa-solid fa-business-time"></i>
                Lembur
            </button>

            <button type="button" data-open-jenis="oncall" class="lb-modal-type-button">
                <i class="fa-solid fa-phone"></i>
                On Call
            </button>
        </div>

    </div>
</div>


{{-- ===================== MODAL DETAIL LOGBOOK (RIWAYATKU) ===================== --}}
<div
    id="rw-modal"
    class="rw-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="rw-modal-judul"
>
    <div class="rw-box">

        <div class="rw-head">
            <div>
                <h3 id="rw-modal-judul" class="rw-title">Detail Logbook</h3>
                <p id="rw-modal-sub" class="rw-sub"></p>
            </div>

            <button type="button" id="rw-modal-tutup" class="rw-close" aria-label="Tutup">&times;</button>
        </div>

        <div class="rw-body">

            <div class="rw-meta">
                <div class="rw-meta-item">
                    <span class="rw-meta-label">Jenis</span>
                    <span id="rw-meta-jenis"></span>
                </div>

                <div class="rw-meta-item">
                    <span class="rw-meta-label">Jam Kerja</span>
                    <span id="rw-meta-jam" class="rw-meta-value"></span>
                </div>

                <div id="rw-meta-status-wrap" class="rw-meta-item">
                    <span class="rw-meta-label">Status</span>
                    <span id="rw-meta-status"></span>
                </div>
            </div>

            <div class="rw-section">
                <h4 class="rw-section-title">
                    <i class="fa-solid fa-list-check"></i>
                    Kegiatan
                </h4>
                <div id="rw-kegiatan" class="rw-text"></div>
            </div>

            <div id="rw-keterangan-wrap" class="rw-section">
                <h4 class="rw-section-title">
                    <i class="fa-solid fa-comment-dots"></i>
                    Keterangan HC
                </h4>
                <div id="rw-keterangan" class="rw-text"></div>
            </div>

        </div>

        <div class="rw-foot">
            <button type="button" id="rw-modal-selesai" class="rw-foot-btn">Tutup</button>
        </div>

    </div>
</div>


{{-- Data dari server untuk JavaScript --}}
<div
    id="logbook-data"
    hidden
    data-kalender='@json($kalender ?? [])'
    data-libur='@json($libur ?? [])'
    data-hari-ini='@json($hariIni)'
    data-dibuka='@json($dibuka)'
    data-jenis-lama='@json($jenisLama)'
></div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const qs  = (selector, root = document) => root.querySelector(selector);
    const qsa = (selector, root = document) => root.querySelectorAll(selector);

    // Buat elemen dengan teks aman (tanpa innerHTML)
    const el = (tag, className = '', text = '') => {
        const node = document.createElement(tag);
        node.className = className;
        node.textContent = text;
        return node;
    };

    // Data dari server (dikirim lewat atribut data-* di #logbook-data)
    const serverData = qs('#logbook-data').dataset;
    const readData = (name) => JSON.parse(serverData[name]);

    const DATA = readData('kalender');
    const LIBUR = readData('libur');
    const HARI_INI = readData('hariIni');
    const DIBUKA = readData('dibuka');
    const JENIS_LAMA = readData('jenisLama');

    // Label & jenis yang dikenal (dipakai juga untuk kelas warna)
    const JENIS_LABEL = {
        harian: 'Logbook Harian',
        lembur: 'Lembur',
        oncall: 'On Call',
    };

    const safeJenis = (jenis) => (JENIS_LABEL[jenis] ? jenis : 'harian');


    /* =====================================================
       TAB
    ====================================================== */
    const tabs = qsa('.lb-tab');
    const tabPanels = qsa('[data-tab-panel]');

    const kalenderUtama = qs('#kalender-utama');

    function showTab(name) {
        tabs.forEach((tab) => {
            tab.classList.toggle('is-active', tab.dataset.tab === name);
        });

        tabPanels.forEach((panel) => {
            panel.classList.toggle('is-hidden', panel.dataset.tabPanel !== name);
        });

        // Kalender utama tetap tampil di Input Baru, tetapi disembunyikan
        // ketika Riwayatku baru dibuka dan belum ada hasil pencarian.
        kalenderUtama?.classList.toggle('is-hidden', name === 'riwayat');
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => showTab(tab.dataset.tab));
    });


    /* =====================================================
       RIWAYATKU
    ====================================================== */
    const riwayatDari = qs('#riwayat-dari');
    const riwayatSampai = qs('#riwayat-sampai');
    const btnLihatRiwayat = qs('#btn-lihat-riwayat');
    const btnResetRiwayat = qs('#btn-reset-riwayat');
    const riwayatResult = qs('#riwayat-result');

    const renderRiwayatEmpty = (message) => {
        riwayatResult.innerHTML = '';

        const empty = el('div', 'riwayat-empty');
        const icon = el('div', 'riwayat-empty-icon');
        const iconElement = document.createElement('i');
        iconElement.className = 'fa-solid fa-calendar-days';
        icon.append(iconElement);

        const text = el('p', '', message);
        empty.append(icon, text);
        riwayatResult.append(empty);
    };

    // Badge status berwarna untuk kartu daftar (Menunggu / Disetujui / Ditolak)
    function buildStatusBadge(status) {
        const text = String(status || 'Menunggu').trim();
        const lower = text.toLowerCase();

        let variant = 'rw-status--wait';
        let icon = 'fa-clock';

        if (/terima|setuju|approve|acc/.test(lower)) {
            variant = 'rw-status--ok';
            icon = 'fa-circle-check';
        } else if (/tolak|reject|revisi/.test(lower)) {
            variant = 'rw-status--no';
            icon = 'fa-circle-xmark';
        }

        const badge = el('span', `rw-status ${variant}`);
        const iconElement = document.createElement('i');
        iconElement.className = `fa-solid ${icon}`;
        badge.append(iconElement, document.createTextNode(' ' + text));

        return badge;
    }

    function renderRiwayat(tanggalDari, tanggalSampai) {
        const hasil = [];

        Object.entries(DATA).forEach(([tgl, items]) => {
            if (tgl < tanggalDari || tgl > tanggalSampai) return;

            (items || []).forEach((item) => {
                hasil.push({ tanggal: tgl, item });
            });
        });

        hasil.sort((a, b) => a.tanggal.localeCompare(b.tanggal));

        if (!hasil.length) {
            renderRiwayatEmpty('Tidak ada logbook pada rentang tanggal yang dipilih.');
            return;
        }

        const list = el('div', 'riwayat-list');

        hasil.forEach(({ tanggal: tgl, item }) => {
            const jenis = safeJenis(item.jenis);

            const card = el('div', `riwayat-item riwayat-item--${jenis}`);
            const head = el('div', 'riwayat-item-head');
            const date = el('span', 'riwayat-item-date', formatDate(tgl));
            const type = el(
                'span',
                `riwayat-item-type riwayat-item-type--${jenis}`,
                JENIS_LABEL[item.jenis] || item.jenis || 'Logbook'
            );

            // Status HC ditampilkan di sebelah label jenis
            const badges = el('div', 'riwayat-item-badges');
            badges.append(buildStatusBadge(item.status), type);

            head.append(date, badges);
            card.append(head);

            // Baris bawah: ringkasan singkat + tombol Detail
            const row = el('div', 'rw-item-row');
            row.append(el('div', 'riwayat-item-detail', item.detail || ''));

            const detailButton = el('button', 'rw-detail-btn');
            detailButton.type = 'button';

            const detailIcon = document.createElement('i');
            detailIcon.className = 'fa-solid fa-file-lines';
            detailButton.append(detailIcon, document.createTextNode(' Detail'));

            detailButton.addEventListener('click', () => openRiwayatModal(tgl, item));

            row.append(detailButton);
            card.append(row);

            list.append(card);
        });

        riwayatResult.innerHTML = '';
        riwayatResult.append(list);
    }

    btnLihatRiwayat?.addEventListener('click', () => {
        const tanggalDari = riwayatDari?.value || '';
        const tanggalSampai = riwayatSampai?.value || '';

        if (!tanggalDari || !tanggalSampai) {
            renderRiwayatEmpty('Pilih tanggal awal dan tanggal akhir terlebih dahulu.');
            return;
        }

        if (tanggalDari > tanggalSampai) {
            renderRiwayatEmpty('Tanggal awal tidak boleh lebih besar dari tanggal akhir.');
            return;
        }

        renderRiwayat(tanggalDari, tanggalSampai);
        // kalenderUtama?.classList.remove('is-hidden');
    });

    btnResetRiwayat?.addEventListener('click', () => {
        if (riwayatDari) riwayatDari.value = '';
        if (riwayatSampai) riwayatSampai.value = '';

        renderRiwayatEmpty(
            'Pilih rentang tanggal (dari–sampai) lalu klik "Lihat". Data akan dimuat sekali untuk seluruh rentang.'
        );

        // kalenderUtama?.classList.add('is-hidden');
    });


    /* =====================================================
       MODAL DETAIL LOGBOOK (tombol "Detail" di Riwayatku)
       Field yang dibaca dari tiap item:
       jenis, detail, jam_kerja, kegiatan, status, keterangan_hc
    ====================================================== */
    const rwModal = qs('#rw-modal');
    const rwSub = qs('#rw-modal-sub');
    const rwJenis = qs('#rw-meta-jenis');
    const rwJam = qs('#rw-meta-jam');
    const rwStatus = qs('#rw-meta-status');
    const rwKegiatan = qs('#rw-kegiatan');
    const rwKeterangan = qs('#rw-keterangan');

    // Badge status berwarna (Diterima / Ditolak / Menunggu)
    function renderStatus(status) {
        rwStatus.innerHTML = '';

        const text = String(status || '').trim();

        if (!text) {
            rwStatus.append(el('span', 'rw-meta-value', '-'));
            return;
        }

        const lower = text.toLowerCase();
        let variant = '';
        let icon = '';

        if (/terima|setuju|approve|acc/.test(lower)) {
            variant = 'rw-status--ok';
            icon = 'fa-circle-check';
        } else if (/tolak|reject|revisi/.test(lower)) {
            variant = 'rw-status--no';
            icon = 'fa-circle-xmark';
        } else if (/tunggu|pending|proses|review/.test(lower)) {
            variant = 'rw-status--wait';
            icon = 'fa-clock';
        }

        if (!variant) {
            rwStatus.append(el('span', 'rw-meta-value', text));
            return;
        }

        const badge = el('span', `rw-status ${variant}`);
        const iconElement = document.createElement('i');
        iconElement.className = `fa-solid ${icon}`;
        badge.append(iconElement, document.createTextNode(' ' + text));
        rwStatus.append(badge);
    }

    // Uraian kegiatan: baris berawalan "-" menjadi poin, baris lain menjadi paragraf
    function renderKegiatan(container, text, emptyMessage) {
        container.innerHTML = '';
        container.classList.remove('rw-text--muted');

        const lines = String(text || '')
            .split(/\r?\n/)
            .map((line) => line.trim())
            .filter(Boolean);

        if (!lines.length) {
            container.classList.add('rw-text--muted');
            container.textContent = emptyMessage;
            return;
        }

        let bullets = null;

        lines.forEach((line) => {
            const match = line.match(/^[-•*]\s+(.*)$/);

            if (match) {
                if (!bullets) {
                    bullets = el('ul');
                    container.append(bullets);
                }

                bullets.append(el('li', '', match[1]));
            } else {
                bullets = null;
                container.append(el('p', '', line));
            }
        });
    }

    function openRiwayatModal(tgl, item) {
        const jenis = safeJenis(item.jenis);

        rwSub.textContent = formatDate(tgl);

        rwJenis.innerHTML = '';
        rwJenis.append(
            el(
                'span',
                `riwayat-item-type riwayat-item-type--${jenis}`,
                JENIS_LABEL[item.jenis] || item.jenis || 'Logbook'
            )
        );

        const jamKerja = item.jam_kerja
            || (item.jam_mulai && item.jam_selesai ? `${item.jam_mulai} – ${item.jam_selesai}` : '')
            || item.shift
            || '-';

        rwJam.textContent = jamKerja + (item.is_wfh ? ' · WFH' : '');

        // Status & Keterangan HC selalu tampil. Selama dashboard admin belum
        // mengisi, status dianggap "Menunggu" dan keterangan memakai teks kosong.
        renderStatus(item.status || 'Menunggu');

        renderKegiatan(
            rwKegiatan,
            item.kegiatan || item.ringkasan || '',
            'Tidak ada uraian kegiatan.'
        );

        renderKegiatan(
            rwKeterangan,
            item.keterangan_hc || '',
            'Belum ada keterangan dari HC.'
        );

        rwModal.classList.add('is-open');
        rwModal.querySelector('.rw-body').scrollTop = 0;
    }

    const closeRiwayatModal = () => rwModal.classList.remove('is-open');

    qs('#rw-modal-tutup').addEventListener('click', closeRiwayatModal);
    qs('#rw-modal-selesai').addEventListener('click', closeRiwayatModal);

    rwModal.addEventListener('click', (event) => {
        if (event.target === rwModal) closeRiwayatModal();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeRiwayatModal();
    });


    /* =====================================================
       JENIS LOGBOOK (form muncul saat tombol diklik)
    ====================================================== */
    const typeButtons = qsa('[data-jenis]');
    const forms = qsa('[data-jenis-panel]');

    function showJenis(jenis) {
        forms.forEach((form) => form.classList.toggle('is-active', form.dataset.jenisPanel === jenis));
        typeButtons.forEach((button) => button.classList.toggle('is-active', button.dataset.jenis === jenis));

        qs(`[data-jenis-panel="${jenis}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    typeButtons.forEach((button) => button.addEventListener('click', () => showJenis(button.dataset.jenis)));

    // Validasi gagal: buka kembali form yang tadi dikirim
    if (JENIS_LAMA) showJenis(JENIS_LAMA);


    /* =====================================================
       WFH (aktif setelah tanggal diisi)
    ====================================================== */
    const tanggal = qs('.js-tanggal');
    const wfh = qs('.js-wfh');
    const wfhInfo = qs('.js-wfh-info');

    function syncWfh() {
        const hasDate = Boolean(tanggal.value);

        wfh.disabled = !hasDate;
        wfhInfo.classList.toggle('is-hidden', hasDate);

        if (!hasDate) {
            wfh.checked = false;
        }
    }

    tanggal.addEventListener('change', syncWfh);
    syncWfh();


    /* =====================================================
       TOMBOL SUBMIT (aktif setelah pernyataan dicentang)
    ====================================================== */
    forms.forEach((form) => {
        const checkbox = qs('input[name="pernyataan"]', form);
        const submit = qs('button[type="submit"]', form);

        if (!checkbox || !submit) return;

        const sync = () => { submit.disabled = !checkbox.checked; };

        checkbox.addEventListener('change', sync);
        sync();
    });


    /* =====================================================
       KALENDER
    ====================================================== */

    const pad = (value) => String(value).padStart(2, '0');
    const dateKeyOf = (year, month, day) => `${year}-${pad(month)}-${pad(day)}`;

    const grid = qs('#kal-grid');
    const title = qs('#kal-judul');
    const prev = qs('#kal-prev');
    const next = qs('#kal-next');

    // Kalender hanya menampilkan bulan lalu dan bulan ini
    const [thisYear, thisMonth] = HARI_INI.split('-').map(Number);

    const months = [
        thisMonth === 1
            ? { y: thisYear - 1, m: 12 }
            : { y: thisYear, m: thisMonth - 1 },
        { y: thisYear, m: thisMonth },
    ];

    let monthIndex = 1;

    function renderCalendar() {
        const { y, m } = months[monthIndex];

        title.textContent = new Date(y, m - 1, 1).toLocaleDateString('id-ID', {
            month: 'long',
            year: 'numeric',
        });

        prev.disabled = monthIndex === 0;
        next.disabled = monthIndex === 1;

        grid.innerHTML = '';

        // Kotak kosong sebelum tanggal 1
        const firstDay = new Date(y, m - 1, 1).getDay();
        for (let i = 0; i < firstDay; i++) {
            grid.append(el('div', 'lb-cal-empty'));
        }

        const totalDays = new Date(y, m, 0).getDate();

        for (let day = 1; day <= totalDays; day++) {
            const dateKey = dateKeyOf(y, m, day);
            const items = DATA[dateKey] || [];

            // Hari kerja (Sen-Jum) yang sudah lewat, bukan libur nasional, dan belum ada logbook
            const weekday = new Date(y, m - 1, day).getDay();
            const isWeekday = weekday !== 0 && weekday !== 6;
            const isMissing = isWeekday
                && dateKey < HARI_INI
                && !LIBUR[dateKey]
                && items.length === 0;

            const button = el(
                'button',
                'lb-cal-day'
                    + (dateKey === HARI_INI ? ' is-today' : '')
                    + (isMissing ? ' is-missing' : '')
            );
            button.type = 'button';
            button.disabled = dateKey > HARI_INI;
            if (isMissing) button.title = 'Belum diisi';
            button.append(el('span', '', day));

            const types = new Set(items.map((item) => item.jenis));
            if (LIBUR[dateKey]) types.add('libur');
            if (isMissing) types.add('kosong');

            const dots = el('span', 'lb-dots');
            types.forEach((type) => dots.append(el('i', `lb-dot lb-dot--${type}`)));
            button.append(dots);

            button.addEventListener('click', () => openDateModal(dateKey));

            grid.append(button);
        }
    }

    prev.addEventListener('click', () => { monthIndex = 0; renderCalendar(); });
    next.addEventListener('click', () => { monthIndex = 1; renderCalendar(); });


    /* =====================================================
       MODAL DETAIL TANGGAL
    ====================================================== */
    const modal = qs('#kal-modal');
    const modalTitle = qs('#kal-modal-judul');
    const modalSub = qs('#kal-modal-sub');
    const modalList = qs('#kal-modal-list');
    const modalClose = qs('#kal-modal-tutup');
    const modalTypes = qs('#kal-modal-type');

    let selectedDate = null;

    function formatDate(date) {
        const [year, month, day] = date.split('-').map(Number);

        return new Date(year, month - 1, day).toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    }

    function modalItem(dotType, label, detail = '') {
        const item = el('li', 'lb-modal-item');
        const text = el('span', '', label);

        if (detail) text.append(el('small', '', detail));

        item.append(el('i', `lb-dot lb-dot--${dotType}`), text);

        return item;
    }

    function openDateModal(date) {
        selectedDate = date;

        const items = DATA[date] || [];

        modalTitle.textContent = formatDate(date);

        // Jumlah logbook yang sudah terisi pada tanggal ini
        modalSub.textContent = items.length
            ? `${items.length} jenis logbook terisi`
            : 'Belum ada logbook terisi';

        modalList.innerHTML = '';

        // Informasi libur
        if (LIBUR[date]) {
            modalList.append(modalItem('libur', `Libur: ${LIBUR[date]}`));
        }

        // Logbook yang sudah ada
        items.forEach((item) => {
            modalList.append(
                modalItem(
                    item.jenis,
                    JENIS_LABEL[item.jenis] || item.jenis,
                    item.detail
                )
            );
        });

        // Belum ada logbook dan bukan tanggal libur
        if (!items.length && !LIBUR[date]) {
            modalList.append(
                el('li', 'lb-modal-item lb-modal-empty', 'Belum ada aktivitas pada tanggal ini.')
            );
        }

        const bolehTambah =
            items.length === 0 &&
            !LIBUR[date] &&
            date <= HARI_INI &&
            DIBUKA;

        modalTypes.classList.toggle('is-hidden', !bolehTambah);

        modal.classList.add('is-open');
    }

    const closeModal = () => modal.classList.remove('is-open');

    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeModal(); });

    // Pilih jenis logbook dari modal -> buka form dan isi tanggalnya
    qsa('[data-open-jenis]').forEach((button) => {
        button.addEventListener('click', () => {
            const jenis = button.dataset.openJenis;

            closeModal();
            showJenis(jenis);

            const input = qs(`[data-jenis-panel="${jenis}"] input[name="tanggal"]`);

            if (input) {
                input.value = selectedDate;
                input.dispatchEvent(new Event('change'));
            }
        });
    });


    renderCalendar();

});
</script>
@endpush

@endsection