@extends('layouts.app')

@section('title', 'Logbook System')

@section('content')

@push('styles')
<style>
    .lb-dinas-toggle { display: flex; align-items: center; gap: 9px; color: var(--text); font-size: 13px; font-weight: 600; }
    .lb-dinas-toggle input { width: 16px; height: 16px; accent-color: var(--accent); }
    .lb-dinas-fields { margin-top: 10px; padding: 12px; border: 1px solid var(--accent-soft-border); border-radius: 10px; background: #f8fbff; }

    .lb-file-picker {
        position: relative;
        min-height: 96px;
        padding: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 7px;
        overflow: hidden;
        border: 1px dashed #b8c7dc;
        border-radius: 11px;
        background: linear-gradient(135deg, #f8fbff, #f1f6fd);
        color: var(--text);
        cursor: pointer;
        transition: border-color .16s ease, background .16s ease, box-shadow .16s ease;
    }
    .lb-file-picker:hover { border-color: var(--accent); background: #eff6ff; }
    .lb-file-picker:focus-within { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
    .lb-file-picker.is-dragging { border-color: var(--accent); background: #e8f1ff; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
    .lb-file-picker.is-selected { border: 1px solid #22a06b; background: linear-gradient(135deg, #f0fdf4, #ecfdf5); box-shadow: 0 0 0 3px rgba(34, 160, 107, .1); }
    .lb-file-picker.is-selected .lb-file-picker-icon { background: #dcfce7; color: #15803d; }
    .lb-file-picker.is-selected .lb-file-picker-copy strong { color: #15803d; }
    .lb-file-picker-name[hidden] { display: none; }
    .lb-file-picker-icon { width: 36px; height: 36px; flex: 0 0 36px; display: grid; place-items: center; border-radius: 10px; background: #e0ecff; color: var(--accent); font-size: 15px; }
    .lb-file-picker-copy { min-width: 0; display: flex; flex-direction: column; align-items: center; gap: 3px; }
    .lb-file-picker-copy strong { font-size: 13px; font-weight: 700; }
    .lb-file-picker-copy small { color: var(--muted); font-size: 12px; font-weight: 600; }
    .lb-file-picker-name { max-width: 100%; overflow: hidden; color: var(--accent); font-size: 12px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
    .lb-file-picker-error { display: block; margin-top: 5px; color: #b91c1c; font-size: 12px; font-weight: 600; }
    .lb-file-picker-error[hidden] { display: none; }
    .lb-required { color: #dc2626; font-weight: 700; }
    .lb-file-picker input[type="file"] { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .lb-file-picker input[type="file"]:disabled { cursor: not-allowed; }
    .lb-file-picker:has(input:disabled) { opacity: .58; cursor: not-allowed; }

    .lb-date-control { position: relative; }
    .lb-date-control.is-open { z-index: 90; }
    .lb-date-range-hint { margin-top: 5px; color: var(--muted-light); font-size: 11px; font-weight: 600; line-height: 1.4; }
    .lb-select--auto-hidden { position: absolute; width: 1px; height: 1px; padding: 0; opacity: 0; pointer-events: none; }
    .lb-date-control.is-enhanced .lb-date-native { display: none; }
    .lb-date-display,
    .lb-date-trigger { display: none; }
    .lb-date-popover {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        z-index: 80;
        width: min(292px, calc(100vw - 48px));
        padding: 11px;
        display: none;
        border: 1px solid #d8e2ef;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 18px 42px rgba(24, 49, 83, .18), 0 3px 8px rgba(24, 49, 83, .06);
    }
    .lb-date-popover.is-open { display: block; }
    .lb-date-head { display: grid; grid-template-columns: 32px 1fr 32px; align-items: center; gap: 7px; }
    .lb-date-month { color: var(--text-dark); font-size: 14px; font-weight: 700; text-align: center; text-transform: capitalize; }
    .lb-date-nav {
        width: 32px; height: 32px; display: grid; place-items: center;
        border: 1px solid var(--border); border-radius: 9px; background: #f7f9fc;
        color: var(--muted); transition: .15s ease;
    }
    .lb-date-nav:hover { border-color: var(--accent-soft-border); background: var(--accent-soft); color: var(--accent); }
    .lb-date-nav:disabled { opacity: .4; cursor: not-allowed; }
    .lb-date-weekdays,
    .lb-date-days { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 4px; }
    .lb-date-weekdays { margin-top: 10px; }
    .lb-date-weekday { padding: 3px 0; color: var(--muted-light); font-size: 10px; font-weight: 700; text-align: center; }
    .lb-date-days { margin-top: 4px; }
    .lb-date-day {
        aspect-ratio: 1; display: grid; place-items: center;
        border: 1px solid transparent; border-radius: 8px; background: transparent;
        color: var(--text); font-size: 11px; font-weight: 600; transition: .15s ease;
    }
    .lb-date-day:hover:not(:disabled) { border-color: var(--accent-soft-border); background: var(--accent-soft); color: var(--accent); }
    .lb-date-day.is-today { color: var(--accent); box-shadow: inset 0 0 0 1px #a9c8ff; }
    .lb-date-day.is-selected { border-color: var(--accent); background: var(--accent); color: #fff; box-shadow: 0 3px 8px rgba(11, 87, 208, .22); }
    .lb-date-day:disabled { color: #c1cad6; cursor: not-allowed; }
    .lb-date-foot { margin-top: 8px; padding-top: 8px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #edf1f6; }
    .lb-date-clear { padding: 6px 9px; border: 0; border-radius: 8px; background: transparent; color: #66758a; font-size: 11px; font-weight: 700; }
    .lb-date-clear:hover { background: #f1f4f8; color: #334155; }
    .lb-date-today { padding: 6px 9px; border: 0; border-radius: 8px; background: var(--accent-soft); color: var(--accent); font-size: 11px; font-weight: 700; }
    .lb-date-today:hover { background: #dceaff; }

    .lb-date-control.is-enhanced .lb-date-display {
        width: 100%; min-height: 40px; padding: 9px 42px 9px 12px;
        display: block; border: 1px solid var(--border-input); border-radius: 10px;
        background: #f7f9fc; color: var(--text-dark); font-size: 13px; font-weight: 600;
        cursor: pointer; outline: none; transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }
    .lb-date-control.is-enhanced .lb-date-display:focus { border-color: var(--accent-border); background: #fff; box-shadow: 0 0 0 3px rgba(11, 87, 208, .14); }
    .lb-date-control.is-enhanced .lb-date-trigger {
        position: absolute; top: 1px; right: 1px; width: 40px; height: calc(100% - 2px);
        display: grid; place-items: center; border: 0; border-radius: 0 9px 9px 0;
        background: transparent; color: var(--muted); cursor: pointer;
    }
    .lb-date-control.is-enhanced .lb-date-trigger:hover { color: var(--accent); background: var(--accent-soft); }
    @media (max-width: 700px) {
        .lb-date-control.is-enhanced .lb-date-display { min-height: 44px; padding: 10px 42px 10px 12px; font-size: 16px; }
        .lb-date-control.is-enhanced .lb-date-trigger { width: 42px; }
        .lb-date-popover { width: min(292px, calc(100vw - 32px)); padding: 10px; }
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
        font-size: 12px;
        font-weight: 700;
        transition: background 0.15s ease, border-color 0.15s ease;
    }

    .rw-detail-btn:hover {
        background: var(--accent-soft);
        border-color: var(--accent-border);
    }

    .kal-logbook-item {
        align-items: flex-start;
    }

    .kal-logbook-item > .lb-dot {
        margin-top: 5px;
    }

    .kal-logbook-content {
        min-width: 0;
        flex: 1;
    }

    .kal-logbook-head,
    .kal-logbook-detail {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .kal-logbook-detail {
        margin-top: 7px;
    }

    .kal-logbook-detail .riwayat-item-detail {
        margin: 0;
        min-width: 0;
    }

    .kal-logbook-head .riwayat-item-badges {
        justify-content: flex-start;
    }

    .rw-banding-error {
        color: #b91c1c;
        font-size: 12px;
    }

    .lb-shift-help {
        margin-top: 4px;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.35;
    }


    /* Modal detail logbook */
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
        font-size: 15px;
        font-weight: 700;
    }

    .rw-sub {
        margin-top: 3px;
        color: var(--muted-light);
        font-size: 13px;
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
        font-size: 11px;
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

    .rw-dinas-badge {
        margin-left: 5px;
        padding: 3px 8px;
        display: inline-flex;
        align-items: center;
        border: 1px solid #d9e0e9;
        border-radius: 999px;
        background: #f3f5f8;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        vertical-align: middle;
    }

    .rw-proof-link {
        min-height: 32px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid var(--accent-soft-border);
        border-radius: 9px;
        background: var(--accent-soft);
        color: var(--accent);
        font-size: 11px;
        font-weight: 700;
    }
    .rw-proof-link:hover { border-color: var(--accent-border); background: #dceaff; }

    .rw-status {
        min-height: 22px;
        padding: 0 9px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        box-sizing: border-box;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
    }

    .rw-status > i {
        width: 11px;
        flex: 0 0 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        line-height: 1;
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
        min-height: 20px;
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
        font-size: 14px;
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
        font-size: 13px;
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

    {{-- Error pengajuan banding (error logbook tampil di bawah checkbox pernyataan) --}}
    @if ($errors->has('keterangan_pegawai'))
        <div class="lb-alert lb-alert--error" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            {{ $errors->first('keterangan_pegawai') }}
        </div>
    @endif

    <section>

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
                    <span class="lb-type-sub">Maks. 1x/hari · tidak digabung On Call</span>
                </button>

                <button type="button" data-jenis="oncall" class="lb-type-button lb-type-button--wide">
                    <i class="fa-solid fa-phone"></i>
                    On Call
                    <span class="lb-type-sub">Maks. {{ $maksOncall }}x/hari · tidak digabung Lembur</span>
                </button>
            </div>
        </div>


        {{-- Form logbook harian --}}
        <form
            method="POST"
            action="{{ route('logbook.store') }}"
            enctype="multipart/form-data"
            data-jenis-panel="harian"
            id="harian-form"
            class="lb-card lb-form"
        >
            @csrf
            <input type="hidden" name="jenis" value="harian">

            <h2 class="lb-card-title">
                <i class="fa-solid fa-book"></i>
                Logbook Harian
            </h2>

            <div class="lb-field">
                    <label class="lb-label">Tanggal <span class="lb-required">*</span></label>
                <input
                    type="date"
                    name="tanggal"
                    id="harian-tanggal"
                    value="{{ $o('harian', 'tanggal') }}"
                    required
                    class="lb-input js-tanggal"
                >
            </div>

            <div class="lb-field">
                <label class="lb-label" for="{{ $unitKey === 'Gizi' && $shifts->isNotEmpty() ? 'harian-shift-display' : 'harian-shift-select' }}">Shift / Jadwal <span class="lb-required">*</span></label>
                @if ($unitKey === 'Gizi' && $shifts->isNotEmpty())
                    <input type="text" id="harian-shift-display" class="lb-select" readonly
                           placeholder="Menunggu fingerprint" aria-describedby="harian-shift-help" aria-required="true">
                @endif
                <select name="shift_id" @required($unitKey !== 'Gizi') class="lb-select {{ $unitKey === 'Gizi' && $shifts->isNotEmpty() ? 'lb-select--auto-hidden' : '' }}"
                        id="harian-shift-select" data-auto-fingerprint="{{ $unitKey === 'Gizi' ? 'true' : 'false' }}"
                        @if ($unitKey === 'Gizi' && $shifts->isNotEmpty()) aria-hidden="true" tabindex="-1" @endif
                        @disabled($shifts->isEmpty())>
                    @if (! $shiftDefaultId)
                        <option value="">-- Pilih Shift --</option>
                    @endif

                    @foreach ($opsiShift as $label => $items)
                        @continue($items->isEmpty())
                        <optgroup label="{{ $label }}">
                            @foreach ($items as $shift)
                                <option
                                    value="{{ $shift->id }}"
                                    data-tipe="{{ \App\Support\WorkSchedule::tipe($shift) }}"
                                    @selected($o('harian', 'shift_id', $shiftDefaultId) == $shift->id)
                                >
                                    {{ \App\Support\WorkSchedule::label($shift) }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('shift_id')
                    <div class="lb-alert lb-alert--error" role="alert">{{ $message }}</div>
                @enderror
                @if ($shifts->isEmpty())
                    <div class="lb-alert" role="status">
                        {{ $unitKey === 'Gizi' ? 'Jadwal untuk unit Gizi belum tersedia di database. Hubungi admin.' : 'Unit kerja belum diatur atau belum memiliki jadwal. Hubungi admin.' }}
                    </div>
                @elseif ($unitKey === 'Gizi')
                    <div class="lb-shift-help" id="harian-shift-help" role="status">Tanggal bisa dipilih manual atau terisi otomatis saat fingerprint terdeteksi.</div>
                @else
                    <div class="lb-shift-help" id="harian-shift-help">Pilihan jadwal disesuaikan dengan unit kerja.</div>
                @endif
            </div>

            <div class="lb-field" id="harian-dl-box" style="display:none">
                <label class="lb-dinas-toggle">
                    <input type="checkbox" name="is_dinas_luar" value="1"
                           @checked($o('harian', 'is_dinas_luar'))>
                    <span>Dinas Luar</span>
                </label>
                @error('is_dinas_luar')
                    <div class="lb-alert lb-alert--error" role="alert">{{ $message }}</div>
                @enderror
            </div>

            <div class="lb-dinas-fields" id="harian-dinas-fields" style="display:none">
                <div class="lb-field" style="margin-top:0">
                    <label class="lb-label" for="harian-dinas-ringkasan">Deskripsi Pekerjaan Dinas Luar <span class="lb-required">*</span></label>
                    <textarea id="harian-dinas-ringkasan" name="ringkasan" rows="4"
                              class="lb-textarea" placeholder="Jelaskan pekerjaan dinas luar..."
                              disabled>{{ $o('harian', 'is_dinas_luar') ? $o('harian', 'ringkasan') : '' }}</textarea>
                    @if ($o('harian', 'is_dinas_luar'))
                        @error('ringkasan')
                            <div class="lb-alert lb-alert--error" role="alert">{{ $message }}</div>
                        @enderror
                    @endif
                </div>
                <div class="lb-field">
                    <label class="lb-label" for="harian-dinas-bukti">SURAT TUGAS <span class="lb-required">*</span></label>
                    <label class="lb-file-picker">
                        <span class="lb-file-picker-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                        <span class="lb-file-picker-copy">
                            <strong class="lb-file-picker-prompt" data-empty-label="Pilih surat tugas">Pilih surat tugas</strong>
                            <small class="lb-file-picker-hint">PNG, JPG, atau PDF - maksimal 5 MB · Klik atau tarik file</small>
                            <small class="lb-file-picker-name" hidden></small>
                        </span>
                        <input type="file" id="harian-dinas-bukti" name="bukti" disabled
                               accept=".png,.jpg,.jpeg,.pdf,image/png,image/jpeg,application/pdf">
                    </label>
                    <small class="lb-file-picker-error" data-file-error-for="harian-dinas-bukti" hidden></small>
                    @if ($o('harian', 'is_dinas_luar'))
                        @error('bukti')
                            <div class="lb-alert lb-alert--error" role="alert">{{ $message }}</div>
                        @enderror
                    @endif
                </div>
            </div>

            <div class="lb-field" id="harian-ringkasan-box">
                <label class="lb-label" id="harian-ringkasan-label">Ringkasan Kegiatan <span class="lb-required">*</span></label>
                <textarea
                    id="harian-ringkasan-input"
                    name="ringkasan"
                    rows="4"
                    class="lb-textarea"
                    placeholder="Jelaskan kegiatan yang dilakukan..."
                >{{ $o('harian', 'is_dinas_luar') ? '' : $o('harian', 'ringkasan') }}</textarea>
                @if (! $o('harian', 'is_dinas_luar'))
                    @error('ringkasan')
                        <div class="lb-alert lb-alert--error" role="alert">{{ $message }}</div>
                    @enderror
                @endif
            </div>

            <div class="lb-field" id="harian-bukti-box" style="display:none">
                <label class="lb-label">Upload Bukti <span class="lb-required">*</span></label>
                <label class="lb-file-picker">
                    <span class="lb-file-picker-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                    <span class="lb-file-picker-copy">
                        <strong class="lb-file-picker-prompt" data-empty-label="Pilih bukti">Pilih bukti</strong>
                        <small class="lb-file-picker-hint">PNG, JPG, atau PDF - maksimal 5 MB · Klik atau tarik file</small>
                        <small class="lb-file-picker-name" hidden></small>
                    </span>
                    <input type="file" name="bukti" id="harian-sakit-bukti" disabled
                           accept=".png,.jpg,.jpeg,.pdf,image/png,image/jpeg,application/pdf">
                </label>
                <small class="lb-file-picker-error" data-file-error-for="harian-sakit-bukti" hidden></small>
                @error('bukti')
                    @if (! $o('harian', 'is_dinas_luar'))
                        <div class="lb-alert lb-alert--error" role="alert">{{ $message }}</div>
                    @endif
                @enderror
            </div>

            @include('logbook._pernyataan', ['jenisForm' => 'harian'])
        </form>


        {{-- Form lembur --}}
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
                <label class="lb-label">Tanggal <span class="lb-required">*</span></label>
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
                    <label class="lb-label">Jam Mulai <span class="lb-required">*</span></label>
                    <input
                        type="time"
                        name="jam_mulai"
                        value="{{ $o('lembur', 'jam_mulai') }}"
                        required
                        class="lb-input"
                    >
                </div>

                <div class="lb-field">
                    <label class="lb-label">Jam Selesai <span class="lb-required">*</span></label>
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
                Durasi minimal lembur {{ $minJam }} jam.
            </p>

            <div class="lb-field">
                <label class="lb-label">Ringkasan Kegiatan <span class="lb-required">*</span></label>
                <textarea
                    name="ringkasan"
                    rows="4"
                    required
                    class="lb-textarea"
                    placeholder="Jelaskan kegiatan yang dilakukan..."
                >{{ $o('lembur', 'ringkasan') }}</textarea>
            </div>

            @include('logbook._pernyataan', ['jenisForm' => 'lembur'])
        </form>


        {{-- Form on call --}}
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
                <label class="lb-label">Tanggal <span class="lb-required">*</span></label>
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
                    <label class="lb-label">Jam Mulai <span class="lb-required">*</span></label>
                    <input
                        type="time"
                        name="jam_mulai"
                        value="{{ $o('oncall', 'jam_mulai') }}"
                        required
                        class="lb-input"
                    >
                </div>

                <div class="lb-field">
                    <label class="lb-label">Jam Selesai <span class="lb-required">*</span></label>
                    <input
                        type="time"
                        name="jam_selesai"
                        value="{{ $o('oncall', 'jam_selesai') }}"
                        required
                        class="lb-input"
                    >
                </div>
            </div>

            <p class="lb-note">
                Durasi minimal on call {{ $minJamOncall }} jam.
            </p>

            <div class="lb-field">
                <label class="lb-label">Ringkasan Kegiatan <span class="lb-required">*</span></label>
                <textarea
                    name="ringkasan"
                    rows="4"
                    required
                    class="lb-textarea"
                    placeholder="Jelaskan kegiatan yang dilakukan..."
                >{{ $o('oncall', 'ringkasan') }}</textarea>
            </div>

            @include('logbook._pernyataan', ['jenisForm' => 'oncall'])
        </form>

    </section>


    {{-- Kalender pengisian --}}
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
                {{-- Libur nasional disembunyikan sementara.
                <span><i class="lb-dot lb-dot--libur"></i> Libur Nasional</span>
                --}}
                <span><i class="lb-dot lb-dot--kosong"></i> Belum diisi</span>
                <span><i class="lb-calendar-key lb-calendar-key--waiting"></i> Menunggu</span>
                <span><i class="lb-calendar-key lb-calendar-key--approved"></i> Disetujui</span>
                <span><i class="lb-calendar-key lb-calendar-key--rejected"></i> Ditolak</span>
            </div>

            <p class="lb-hint">
                Hanya menampilkan 2 bulan terakhir. Ketuk tanggal buat lihat detail.
            </p>
        </div>
    </div>

</div>


{{-- Modal detail tanggal kalender --}}
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


{{-- Modal detail logbook --}}
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

            <div id="rw-bukti-wrap" class="rw-section is-hidden">
                <h4 class="rw-section-title">
                    <i class="fa-solid fa-paperclip"></i>
                    Bukti Pendukung
                </h4>
                <a id="rw-bukti-link" class="rw-proof-link" target="_blank" rel="noopener">
                    <i class="fa-solid fa-file-arrow-up" aria-hidden="true"></i>
                    <span id="rw-bukti-label">Lihat bukti pendukung</span>
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                </a>
            </div>

            <div id="rw-keterangan-wrap" class="rw-section">
                <h4 class="rw-section-title">
                    <i class="fa-solid fa-comment-dots"></i>
                    <span id="rw-keterangan-label">Keterangan HC</span>
                </h4>
                <div id="rw-keterangan" class="rw-text"></div>
            </div>

            <div id="rw-banding-history-wrap" class="rw-section is-hidden">
                <h4 class="rw-section-title">
                    <i class="fa-solid fa-scale-balanced"></i>
                    Keterangan Banding
                </h4>
                <div id="rw-banding-history" class="rw-text"></div>
            </div>

            <form id="rw-banding-form" class="rw-section is-hidden" method="POST">
                @csrf
                @method('PATCH')
                <label for="rw-banding-textarea" class="rw-section-title">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Ajukan Banding
                </label>
                <textarea
                    id="rw-banding-textarea"
                    name="keterangan_pegawai"
                    rows="4"
                    minlength="10"
                    maxlength="1000"
                    required
                    class="lb-textarea"
                    placeholder="Jelaskan alasan banding (minimal 10 karakter)..."
                >{{ old('keterangan_pegawai') }}</textarea>
                @error('keterangan_pegawai')
                    <small class="rw-banding-error">{{ $message }}</small>
                @enderror
                <input type="hidden" name="logbook_id" value="{{ old('logbook_id') }}">
                <button type="submit" class="lb-submit-button">Kirim Banding</button>
            </form>

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
    data-banding-url="{{ route('logbook.banding', ['logbook' => '__ID__'], false) }}"
    data-old-banding='@json(old("keterangan_pegawai"))'
    data-old-banding-id='@json(old("logbook_id"))'
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

    // Data kalender berisi logbook dan hari libur untuk bulan lalu serta bulan ini.
    let DATA = readData('kalender');
    let LIBUR = readData('libur');
    const HARI_INI = readData('hariIni');
    const DIBUKA = readData('dibuka');
    const HARI_INI_KEY = HARI_INI;
    const JENIS_LAMA = readData('jenisLama');
    const BANDING_URL = serverData.bandingUrl;
    const OLD_BANDING = readData('oldBanding');
    const OLD_BANDING_ID = readData('oldBandingId');

    // Kalender kustom yang sama dipakai di desktop dan ponsel.
    const parseDateKey = (key) => {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(key || '')) return null;
        const [year, month, day] = key.split('-').map(Number);
        return new Date(year, month - 1, day);
    };
    const dateToKey = (date) => [
        date.getFullYear(),
        String(date.getMonth() + 1).padStart(2, '0'),
        String(date.getDate()).padStart(2, '0'),
    ].join('-');
    const dateToDisplay = (key) => {
        const date = parseDateKey(key);
        return date ? new Intl.DateTimeFormat('id-ID', {
            day: 'numeric', month: 'long', year: 'numeric',
        }).format(date) : '';
    };
    const todayDate = parseDateKey(HARI_INI) || new Date();
    const earliestDate = new Date(todayDate.getFullYear(), todayDate.getMonth() - 1, 1);
    const earliestDateKey = dateToKey(earliestDate);
    const formatMonthYear = (date) => new Intl.DateTimeFormat('id-ID', {
        month: 'long', year: 'numeric',
    }).format(date);
    const rangeHintText = `Pilihan tanggal mengikuti kalender pengisian: ${formatMonthYear(earliestDate)}–${formatMonthYear(todayDate)}.`;
    const firstAllowedMonth = earliestDate.getFullYear() * 12 + earliestDate.getMonth();
    const currentAllowedMonth = todayDate.getFullYear() * 12 + todayDate.getMonth();
    const withinCalendarRange = (date) => {
        const month = date.getFullYear() * 12 + date.getMonth();
        return month >= firstAllowedMonth && month <= currentAllowedMonth;
    };
    const initialCalendarDate = (key) => {
        const date = parseDateKey(key);
        return date && withinCalendarRange(date) && dateToKey(date) <= HARI_INI ? date : todayDate;
    };

    function setupDatePickers() {
        let datePickerIndex = 0;
        qsa('input[type="date"][name="tanggal"]').forEach((nativeInput) => {
            const control = document.createElement('div');
            control.className = 'lb-date-control is-enhanced';
            if (!nativeInput.id) nativeInput.id = `logbook-date-${++datePickerIndex}`;
            const fieldLabel = nativeInput.closest('.lb-field')?.querySelector('.lb-label');
            nativeInput.classList.add('lb-date-native');
            nativeInput.required = false;
            nativeInput.parentNode.insertBefore(control, nativeInput);
            control.append(nativeInput);

            const rangeHint = el('div', 'lb-date-range-hint', rangeHintText);
            rangeHint.setAttribute('aria-live', 'polite');
            control.after(rangeHint);

            const display = document.createElement('input');
            display.type = 'text';
            display.className = 'lb-date-display';
            display.id = `${nativeInput.id}-display`;
            display.readOnly = true;
            display.required = true;
            display.autocomplete = 'off';
            display.placeholder = 'dd/mm/yyyy';
            display.setAttribute('aria-label', 'Pilih tanggal');
            display.setAttribute('aria-haspopup', 'dialog');
            if (fieldLabel) {
                fieldLabel.htmlFor = display.id;
                fieldLabel.id ||= `${nativeInput.id}-label`;
                display.setAttribute('aria-labelledby', fieldLabel.id);
                nativeInput.setAttribute('aria-labelledby', fieldLabel.id);
            }

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'lb-date-trigger';
            trigger.setAttribute('aria-label', 'Buka kalender');
            trigger.innerHTML = '<i class="fa-regular fa-calendar" aria-hidden="true"></i>';

            const popover = document.createElement('div');
            popover.className = 'lb-date-popover';
            popover.setAttribute('role', 'dialog');
            popover.setAttribute('aria-label', 'Pilih tanggal');

            control.append(display, trigger, popover);

            let visibleMonth = initialCalendarDate(nativeInput.value);
            visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), 1);

            const close = () => {
                popover.classList.remove('is-open');
                control.classList.remove('is-open');
                display.setAttribute('aria-expanded', 'false');
            };

            const selectDate = (key) => {
                nativeInput.value = key;
                display.value = dateToDisplay(key);
                nativeInput.dispatchEvent(new Event('input', { bubbles: true }));
                nativeInput.dispatchEvent(new Event('change', { bubbles: true }));
                nativeInput.dispatchEvent(new Event('logbook:date-selected', { bubbles: true }));
                visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), 1);
                close();
            };

            const render = () => {
                popover.replaceChildren();

                const head = el('div', 'lb-date-head');
                const previous = el('button', 'lb-date-nav');
                previous.type = 'button';
                previous.setAttribute('aria-label', 'Bulan sebelumnya');
                previous.innerHTML = '<i class="fa-solid fa-chevron-left" aria-hidden="true"></i>';
                const monthName = el('div', 'lb-date-month', new Intl.DateTimeFormat('id-ID', {
                    month: 'long', year: 'numeric',
                }).format(visibleMonth));
                const next = el('button', 'lb-date-nav');
                next.type = 'button';
                next.setAttribute('aria-label', 'Bulan berikutnya');
                next.innerHTML = '<i class="fa-solid fa-chevron-right" aria-hidden="true"></i>';
                const visibleMonthIndex = visibleMonth.getFullYear() * 12 + visibleMonth.getMonth();
                previous.disabled = visibleMonthIndex <= firstAllowedMonth;
                next.disabled = visibleMonthIndex >= currentAllowedMonth;
                head.append(previous, monthName, next);

                previous.addEventListener('click', (event) => {
                    event.stopPropagation();
                    if (previous.disabled) return;
                    visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() - 1, 1);
                    render();
                });
                next.addEventListener('click', (event) => {
                    event.stopPropagation();
                    if (next.disabled) return;
                    visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 1);
                    render();
                });
                popover.append(head);

                const weekdayRow = el('div', 'lb-date-weekdays');
                ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'].forEach((name) => {
                    weekdayRow.append(el('span', 'lb-date-weekday', name));
                });
                popover.append(weekdayRow);

                const dayGrid = el('div', 'lb-date-days');
                const firstDay = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), 1).getDay();
                const daysInMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 0).getDate();
                const todayKey = HARI_INI_KEY;
                const selectedKey = nativeInput.value;

                for (let cell = 0; cell < firstDay; cell++) dayGrid.append(el('span'));
                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), day);
                    const key = dateToKey(date);
                    const dayButton = el('button', 'lb-date-day', String(day));
                    dayButton.type = 'button';
                    dayButton.disabled = key < earliestDateKey || key > HARI_INI;
                    dayButton.setAttribute('aria-label', new Intl.DateTimeFormat('id-ID', {
                        dateStyle: 'full',
                    }).format(date));
                    if (key === todayKey) dayButton.classList.add('is-today');
                    if (key === selectedKey) {
                        dayButton.classList.add('is-selected');
                        dayButton.setAttribute('aria-pressed', 'true');
                    }
                    dayButton.addEventListener('click', () => selectDate(key));
                    dayGrid.append(dayButton);
                }
                popover.append(dayGrid);

                const foot = el('div', 'lb-date-foot');
                const clear = el('button', 'lb-date-clear', 'Hapus tanggal');
                clear.type = 'button';
                clear.addEventListener('click', () => {
                    nativeInput.value = '';
                    display.value = '';
                    nativeInput.dispatchEvent(new Event('input', { bubbles: true }));
                    nativeInput.dispatchEvent(new Event('change', { bubbles: true }));
                    nativeInput.dispatchEvent(new Event('logbook:date-selected', { bubbles: true }));
                    close();
                });
                const today = el('button', 'lb-date-today', 'Hari ini');
                today.type = 'button';
                today.addEventListener('click', () => selectDate(todayKey));
                foot.append(clear, today);
                popover.append(foot);
            };

            const open = () => {
                visibleMonth = initialCalendarDate(nativeInput.value);
                visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), 1);
                render();
                popover.classList.add('is-open');
                control.classList.add('is-open');
                display.setAttribute('aria-expanded', 'true');
            };

            display.addEventListener('click', () => popover.classList.contains('is-open') ? close() : open());
            display.addEventListener('keydown', (event) => {
                if (['Enter', ' ', 'ArrowDown'].includes(event.key)) {
                    event.preventDefault();
                    open();
                }
            });
            trigger.addEventListener('click', () => popover.classList.contains('is-open') ? close() : open());
            nativeInput.addEventListener('change', () => { display.value = dateToDisplay(nativeInput.value); });
            display.value = dateToDisplay(nativeInput.value);

        });

        document.addEventListener('click', (event) => {
            qsa('.lb-date-control.is-open').forEach((control) => {
                if (!control.contains(event.target)) {
                    control.querySelector('.lb-date-popover')?.classList.remove('is-open');
                    control.classList.remove('is-open');
                    control.querySelector('.lb-date-display')?.setAttribute('aria-expanded', 'false');
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                qsa('.lb-date-control.is-open').forEach((control) => {
                    control.querySelector('.lb-date-popover')?.classList.remove('is-open');
                    control.classList.remove('is-open');
                    control.querySelector('.lb-date-display')?.setAttribute('aria-expanded', 'false');
                });
            }
        });
    }

    setupDatePickers();

    // Label & jenis yang dikenal (dipakai juga untuk kelas warna)
    const JENIS_LABEL = {
        harian: 'Logbook Harian',
        lembur: 'Lembur',
        oncall: 'On Call',
    };

    const safeJenis = (jenis) => (JENIS_LABEL[jenis] ? jenis : 'harian');

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


    // Detail logbook yang dibuka dari kalender.
    const rwModal = qs('#rw-modal');
    const rwSub = qs('#rw-modal-sub');
    const rwJenis = qs('#rw-meta-jenis');
    const rwJam = qs('#rw-meta-jam');
    const rwStatus = qs('#rw-meta-status');
    const rwKegiatan = qs('#rw-kegiatan');
    const rwKeterangan = qs('#rw-keterangan');
    const rwKeteranganLabel = qs('#rw-keterangan-label');
    const rwBuktiWrap = qs('#rw-bukti-wrap');
    const rwBuktiLink = qs('#rw-bukti-link');
    const rwBuktiLabel = qs('#rw-bukti-label');
    const rwBandingForm = qs('#rw-banding-form');
    const rwBandingTextarea = qs('#rw-banding-textarea');
    const rwBandingHistoryWrap = qs('#rw-banding-history-wrap');
    const rwBandingHistory = qs('#rw-banding-history');

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
        if (item.is_dinas_luar) {
            rwJenis.append(el('span', 'rw-dinas-badge', 'Dinas Luar'));
        }

        rwBuktiWrap.classList.toggle('is-hidden', !item.bukti_url);
        if (item.bukti_url) {
            rwBuktiLink.href = item.bukti_url;
            if (rwBuktiLabel) rwBuktiLabel.textContent = item.is_dinas_luar ? 'Lihat bukti surat tugas' : 'Lihat bukti pendukung';
        }

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
        rwKeteranganLabel.textContent = item.status_banding === 'Ditolak'
            ? 'Alasan Banding Ditolak'
            : 'Keterangan HC';

        rwBandingHistory.textContent = item.keterangan_pegawai || '';
        rwBandingHistoryWrap.classList.toggle('is-hidden', !item.keterangan_pegawai);
        rwBandingForm.action = BANDING_URL.replace('__ID__', String(item.id));
        rwBandingTextarea.value = String(item.id) === String(OLD_BANDING_ID)
            ? (OLD_BANDING || '')
            : '';
        qs('input[name="logbook_id"]', rwBandingForm).value = String(item.id);
        rwBandingForm.classList.toggle('is-hidden', item.status !== 'Ditolak' || Boolean(item.status_banding));

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


    // Tampilkan form sesuai jenis logbook yang dipilih.
    const typeButtons = qsa('[data-jenis]');
    const forms = qsa('[data-jenis-panel]');
    const shiftSelect = qs('#harian-shift-select');
    const shiftDisplay = qs('#harian-shift-display');
    const shiftHelp = qs('#harian-shift-help');
    const dateInput = qs('#harian-tanggal');
    const autoFingerprintSchedule = shiftSelect?.dataset.autoFingerprint === 'true';
    const dinasBox = qs('#harian-dl-box');
    const dinasCheckbox = qs('#harian-dl-box input');
    const ringkasanBox = qs('#harian-ringkasan-box');
    const ringkasanInput = qs('#harian-ringkasan-input');
    const ringkasanLabel = qs('#harian-ringkasan-label');
    const buktiBox = qs('#harian-bukti-box');
    const sakitBuktiInput = qs('#harian-sakit-bukti');
    const dinasFields = qs('#harian-dinas-fields');
    const dinasRingkasanInput = qs('#harian-dinas-ringkasan');
    const dinasBuktiInput = qs('#harian-dinas-bukti');

    const syncFileName = (input) => {
        const picker = input.closest('.lb-file-picker');
        const name = qs('.lb-file-picker-name', picker);
        const prompt = qs('.lb-file-picker-prompt', picker);
        const icon = qs('.lb-file-picker-icon i', picker);
        const error = qs(`[data-file-error-for="${input.id}"]`);
        const file = input.files?.[0];
        picker?.classList.toggle('is-selected', Boolean(file));
        if (error) { error.textContent = ''; error.hidden = true; }
        if (name) {
            name.textContent = file?.name || '';
            name.hidden = !file;
        }
        if (prompt) prompt.textContent = file ? 'File siap diunggah' : (prompt.dataset.emptyLabel || 'Pilih file');
        if (icon) icon.className = file ? 'fa-solid fa-circle-check' : 'fa-solid fa-cloud-arrow-up';
    };

    const validateFileSize = (input) => {
        const file = input.files?.[0];
        if (file && file.size > 5 * 1024 * 1024) {
            const error = qs(`[data-file-error-for="${input.id}"]`);
            input.value = '';
            syncFileName(input);
            if (error) {
                error.textContent = 'Ukuran file maksimal 5 MB. Silakan pilih file yang lebih kecil.';
                error.hidden = false;
            }
            return false;
        }
        return true;
    };

    const setupFilePicker = (input) => {
        const picker = input.closest('.lb-file-picker');
        if (!picker) return;
        input.addEventListener('change', () => {
            if (validateFileSize(input)) syncFileName(input);
        });
        ['dragenter', 'dragover'].forEach((eventName) => picker.addEventListener(eventName, (event) => {
            event.preventDefault();
            if (!input.disabled) picker.classList.add('is-dragging');
        }));
        ['dragleave', 'drop'].forEach((eventName) => picker.addEventListener(eventName, (event) => {
            event.preventDefault();
            picker.classList.remove('is-dragging');
        }));
        picker.addEventListener('drop', (event) => {
            if (input.disabled) return;
            const file = event.dataTransfer?.files?.[0];
            if (!file) return;
            const transfer = new DataTransfer();
            transfer.items.add(file);
            input.files = transfer.files;
            if (validateFileSize(input)) syncFileName(input);
        });
    };
    [sakitBuktiInput, dinasBuktiInput].forEach((input) => input && setupFilePicker(input));

    if (autoFingerprintSchedule && shiftSelect && shiftHelp) {
        const scheduleUrl = @json(route('logbook.fingerprint-schedule'));
        let requestSequence = 0;
        let schedulePollingTimer = null;

        const clearSchedule = () => {
            shiftSelect.value = '';
            if (shiftDisplay) shiftDisplay.value = '';
            shiftSelect.dispatchEvent(new Event('change', { bubbles: true }));
        };

        const refreshFingerprintSchedule = async () => {
            const sequence = ++requestSequence;
            clearSchedule();
            shiftHelp.textContent = 'Memeriksa fingerprint terbaru...';

            try {
                const params = new URLSearchParams();
                if (dateInput?.value) params.set('tanggal', dateInput.value);
                const url = params.size ? `${scheduleUrl}?${params.toString()}` : scheduleUrl;
                const response = await fetch(url, {
                    headers: { Accept: 'application/json' },
                    cache: 'no-store',
                });
                const result = await response.json();
                if (sequence !== requestSequence) return;

                if (result.status === 'matched' && result.shift) {
                    shiftSelect.value = String(result.shift.id);
                    if (shiftDisplay) shiftDisplay.value = result.shift.label;
                    if (dateInput && result.tanggal) {
                        dateInput.value = result.tanggal;
                        dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    shiftHelp.textContent = `Fingerprint pukul ${result.waktu_fingerprint} WIB terdeteksi. Jadwal terisi otomatis.`;
                    stopSchedulePolling();
                } else {
                    shiftHelp.textContent = result.message || 'Jadwal belum dapat dicocokkan dari fingerprint.';
                    if (result.status !== 'waiting') stopSchedulePolling();
                }

                shiftSelect.dispatchEvent(new Event('change', { bubbles: true }));
            } catch (error) {
                if (sequence !== requestSequence) return;
                shiftHelp.textContent = 'Data fingerprint belum dapat diperiksa. Coba lagi sebentar.';
                clearSchedule();
            }
        };

        const stopSchedulePolling = () => {
            requestSequence++;
            if (schedulePollingTimer) {
                window.clearInterval(schedulePollingTimer);
                schedulePollingTimer = null;
            }
        };

        const startSchedulePolling = () => {
            if (!schedulePollingTimer) {
                schedulePollingTimer = window.setInterval(refreshFingerprintSchedule, 10000);
            }
            refreshFingerprintSchedule();
        };

        dateInput?.addEventListener('logbook:date-selected', () => {
            clearSchedule();
            if (dinasCheckbox?.checked) {
                stopSchedulePolling();
                shiftHelp.textContent = 'Dinas luar tidak memerlukan fingerprint. Isi deskripsi dan unggah surat tugas.';
                return;
            }
            stopSchedulePolling();
            startSchedulePolling();
        });

        dinasCheckbox?.addEventListener('change', () => {
            if (dinasCheckbox.checked && !shiftSelect.value) {
                stopSchedulePolling();
                shiftHelp.textContent = 'Dinas luar tidak memerlukan fingerprint. Isi deskripsi dan unggah surat tugas.';
            } else if (!dinasCheckbox.checked && !shiftSelect.value) {
                startSchedulePolling();
            }
        });

        if (dinasCheckbox?.checked && !shiftSelect.value) {
            shiftHelp.textContent = 'Dinas luar tidak memerlukan fingerprint. Isi deskripsi dan unggah surat tugas.';
        } else {
            startSchedulePolling();
        }
    }

    const applyShiftFields = () => {
        if (!shiftSelect || !dinasCheckbox || !ringkasanInput || !sakitBuktiInput) return;

        const tipe = shiftSelect.selectedOptions[0]?.dataset.tipe || '';
        const isKerja = tipe === 'kerja';
        const bolehDinasLuarTanpaShift = autoFingerprintSchedule && !shiftSelect.value;
        if (!isKerja && !bolehDinasLuarTanpaShift && dinasCheckbox.checked) {
            dinasCheckbox.checked = false;
            dinasRingkasanInput.value = '';
            dinasBuktiInput.value = '';
            syncFileName(dinasBuktiInput);
        }

        const dinasLuar = dinasCheckbox.checked && (isKerja || bolehDinasLuarTanpaShift);
        const sakit = tipe === 'sakit';
        const ringkasanTampil = tipe !== 'libur' && !dinasLuar;

        dinasBox.style.display = isKerja || autoFingerprintSchedule ? '' : 'none';
        dinasCheckbox.disabled = !isKerja && !autoFingerprintSchedule;
        dinasFields.style.display = dinasLuar ? '' : 'none';
        if (shiftDisplay && !shiftSelect.value) {
            shiftDisplay.value = dinasCheckbox.checked
                ? 'Tidak perlu fingerprint untuk Dinas Luar'
                : '';
        }

        ringkasanBox.style.display = ringkasanTampil ? '' : 'none';
        ringkasanInput.disabled = !ringkasanTampil;
        ringkasanInput.required = ringkasanTampil;

        dinasRingkasanInput.disabled = !dinasLuar;
        dinasRingkasanInput.required = dinasLuar;
        dinasRingkasanInput.minLength = 10;
        dinasRingkasanInput.maxLength = 2000;
        dinasBuktiInput.disabled = !dinasLuar;
        dinasBuktiInput.required = dinasLuar;

        buktiBox.style.display = sakit ? '' : 'none';
        sakitBuktiInput.disabled = !sakit;
        sakitBuktiInput.required = sakit;
        if (!sakit) {
            sakitBuktiInput.value = '';
            syncFileName(sakitBuktiInput);
        }

        const labelKeterangan = sakit || tipe === 'izin';
        ringkasanLabel.innerHTML = dinasLuar
            ? 'Deskripsi Pekerjaan Dinas Luar <span class="lb-required">*</span>'
            : `${labelKeterangan ? 'Keterangan' : 'Ringkasan Kegiatan'} <span class="lb-required">*</span>`;
        ringkasanInput.placeholder = labelKeterangan
            ? 'Jelaskan keterangan...'
            : 'Jelaskan kegiatan yang dilakukan...';
    };

    shiftSelect?.addEventListener('change', applyShiftFields);
    dinasCheckbox?.addEventListener('change', () => {
        if (!dinasCheckbox.checked) {
            dinasRingkasanInput.value = '';
            dinasBuktiInput.value = '';
            syncFileName(dinasBuktiInput);
        }
        applyShiftFields();
    });
    applyShiftFields();

    function showJenis(jenis, clearDurationErrors = true) {
        if (clearDurationErrors) {
            qsa('.js-form-warning').forEach((message) => message.remove());
            qsa('.js-time-order-warning').forEach((message) => message.remove());
        }

        forms.forEach((form) => form.classList.toggle('is-active', form.dataset.jenisPanel === jenis));
        typeButtons.forEach((button) => button.classList.toggle('is-active', button.dataset.jenis === jenis));

        qs(`[data-jenis-panel="${jenis}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    typeButtons.forEach((button) => button.addEventListener('click', () => showJenis(button.dataset.jenis)));

    // Validasi gagal: buka kembali form yang tadi dikirim
    if (JENIS_LAMA) showJenis(JENIS_LAMA, false);


    // Aktifkan tombol kirim setelah pernyataan dicentang.
    forms.forEach((form) => {
        const checkbox = qs('input[name="pernyataan"]', form);
        const submit = qs('button[type="submit"]', form);

        if (!checkbox || !submit) return;

        const needsAutoSchedule = form.id === 'harian-form' && autoFingerprintSchedule;
        const sync = () => {
            const tanpaJadwalUntukDinasLuar = needsAutoSchedule && dinasCheckbox?.checked;
            submit.disabled = !checkbox.checked || (needsAutoSchedule && !shiftSelect?.value && !tanpaJadwalUntukDinasLuar);
        };

        checkbox.addEventListener('change', sync);
        if (needsAutoSchedule) shiftSelect?.addEventListener('change', sync);
        if (needsAutoSchedule) dinasCheckbox?.addEventListener('change', sync);
        sync();

        form.addEventListener('submit', (event) => {
            const mulai = qs('input[name="jam_mulai"]', form)?.value;
            const selesai = qs('input[name="jam_selesai"]', form)?.value;

            if (!mulai || !selesai || selesai > mulai) return;

            event.preventDefault();
            qs('.js-time-order-warning', form)?.remove();

            const warning = document.createElement('div');
            warning.className = 'lb-alert lb-alert--error js-time-order-warning';
            warning.setAttribute('role', 'alert');
            warning.setAttribute('aria-live', 'assertive');

            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-circle-exclamation';
            icon.setAttribute('aria-hidden', 'true');

            const text = document.createElement('span');
            text.textContent = 'Jam mulai dan jam selesai tidak valid.';
            warning.append(icon, text);

            const declaration = qs('.lb-declaration', form);
            const submitWrap = qs('.lb-submit-wrap', declaration);
            declaration.insertBefore(warning, submitWrap);
        });
    });


    // Render kalender dua bulan terakhir.

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

            // Semua tanggal sampai hari ini yang bukan libur nasional dan belum ada logbook
            const isMissing = dateKey <= HARI_INI
                && !LIBUR[dateKey]
                && items.length === 0;
            const adaPenolakan = items.some((item) => item.status === 'Ditolak');
            const adaMenunggu = items.some((item) => item.status === 'Menunggu');
            const semuaDisetujui = items.length > 0
                && items.every((item) => item.status === 'Disetujui');

            const button = el(
                'button',
                'lb-cal-day'
                    + (dateKey === HARI_INI ? ' is-today' : '')
                    + (adaPenolakan ? ' has-rejected' : '')
                    + (!adaPenolakan && adaMenunggu ? ' has-waiting' : '')
                    + (!adaPenolakan && !adaMenunggu && semuaDisetujui ? ' has-approved' : '')
            );
            button.type = 'button';
            button.disabled = dateKey > HARI_INI;
            if (adaPenolakan) button.title = 'Ada logbook ditolak';
            else if (adaMenunggu) button.title = 'Menunggu persetujuan';
            else if (semuaDisetujui) button.title = 'Semua logbook disetujui';
            else if (isMissing) button.title = 'Belum diisi';
            button.append(el('span', '', day));

            const dots = el('span', 'lb-dots');
            if (isMissing) {
                dots.append(el('i', 'lb-dot lb-dot--kosong'));
            } else {
                const jenisTerisi = [...new Set(items.map((item) => item.jenis))];
                // Indikator libur nasional disembunyikan sementara.
                // if (LIBUR[dateKey]) jenisTerisi.push('libur');
                jenisTerisi.forEach((jenis) => {
                    dots.append(el('i', `lb-dot lb-dot--${jenis}`));
                });
            }
            button.append(dots);

            button.addEventListener('click', () => openDateModal(dateKey));

            grid.append(button);
        }
    }

    prev.addEventListener('click', () => { monthIndex = 0; renderCalendar(); });
    next.addEventListener('click', () => { monthIndex = 1; renderCalendar(); });


    // Tampilkan detail logbook untuk tanggal yang dipilih.
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

        // Informasi libur nasional disembunyikan sementara.
        // if (LIBUR[date]) modalList.append(modalItem('libur', `Libur: ${LIBUR[date]}`));

        // Logbook yang sudah ada
        items.forEach((item) => {
            const jenis = safeJenis(item.jenis);
            const row = el('li', `lb-modal-item kal-logbook-item kal-logbook-item--${jenis}`);
            const content = el('div', 'kal-logbook-content');
            const head = el('div', 'kal-logbook-head');
            const badges = el('div', 'riwayat-item-badges');
            const type = el(
                'span',
                `riwayat-item-type riwayat-item-type--${jenis}`,
                JENIS_LABEL[item.jenis] || item.jenis || 'Logbook'
            );
            const detailRow = el('div', 'kal-logbook-detail');
            const detail = el('span', 'riwayat-item-detail', item.detail || '');
            const detailButton = el('button', 'rw-detail-btn');

            detailButton.type = 'button';
            detailButton.append(
                el('i', 'fa-solid fa-file-lines'),
                document.createTextNode(' Detail')
            );
            detailButton.addEventListener('click', () => openRiwayatModal(date, item));

            badges.append(buildStatusBadge(item.status), type);
            head.append(badges);
            detailRow.append(detail, detailButton);
            content.append(head, detailRow);
            row.append(el('i', `lb-dot lb-dot--${jenis}`), content);
            modalList.append(row);
        });

        // Tampilkan keadaan kosong jika tanggal bukan hari libur.
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
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                input.dispatchEvent(new Event('logbook:date-selected', { bubbles: true }));
            }
        });
    });


    renderCalendar();



});
</script>
@endpush

@endsection
