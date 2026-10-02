@extends('layouts.app')

@section('title', 'Logbook System')

@section('content')

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
        <div class="lb-card">
            <h2 class="lb-card-title">
                <i class="fa-solid fa-clock-rotate-left"></i>
                Riwayatku
            </h2>

            <p class="lb-empty">
                Riwayat logbook akan dibuat di langkah berikutnya.
            </p>
        </div>
    </section>


    {{-- ===================== KALENDER ===================== --}}
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
            <span><i class="lb-dot lb-dot--dll"></i> DLL</span>
        </div>

        <p class="lb-hint">
            Hanya menampilkan 2 bulan terakhir. Ketuk tanggal buat lihat detail.
        </p>
    </div>

</div>


{{-- ===================== MODAL DETAIL TANGGAL ===================== --}}
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


    /* =====================================================
       TAB
    ====================================================== */
    const tabs = qsa('.lb-tab');
    const tabPanels = qsa('[data-tab-panel]');

    function showTab(name) {
        tabs.forEach((tab) => tab.classList.toggle('is-active', tab.dataset.tab === name));
        tabPanels.forEach((panel) => panel.classList.toggle('is-hidden', panel.dataset.tabPanel !== name));
    }

    tabs.forEach((tab) => tab.addEventListener('click', () => showTab(tab.dataset.tab)));


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
    const JENIS_LABEL = {
        harian: 'Logbook Harian',
        lembur: 'Lembur',
        oncall: 'On Call',
    };

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

            const button = el('button', 'lb-cal-day' + (dateKey === HARI_INI ? ' is-today' : ''));
            button.type = 'button';
            button.disabled = dateKey > HARI_INI;
            button.append(el('span', '', day));

            const types = new Set((DATA[dateKey] || []).map((item) => item.jenis));
            if (LIBUR[dateKey]) types.add('libur');

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
        modalSub.textContent = items.length
            ? `${items.length} logbook terisi`
            : 'Belum ada logbook terisi';

        modalList.innerHTML = '';

        if (LIBUR[date]) {
            modalList.append(modalItem('libur', `Libur: ${LIBUR[date]}`));
        }

        items.forEach((item) => {
            modalList.append(modalItem(item.jenis, JENIS_LABEL[item.jenis] || item.jenis, item.detail));
        });

        if (!items.length && !LIBUR[date]) {
            modalList.append(el('li', 'lb-modal-item lb-modal-empty', 'Belum ada aktivitas pada tanggal ini.'));
        }

        // Tombol tambah logbook hanya muncul untuk tanggal yang boleh diisi
        modalTypes.classList.toggle('is-hidden', !(date <= HARI_INI && DIBUKA));

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