@push('styles')
<style>
    .kp-page {
        width: 100%;
        max-width: 960px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .kp-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .kp-title { color: var(--text-dark); font-size: 18px; font-weight: 700; }
    .kp-sub   { margin-top: 3px; color: var(--muted); font-size: 13px; }
    .kp-back  { color: var(--accent); font-size: 13px; font-weight: 600; text-decoration: none; }

    /* Filter status (pil) */
    .kp-pills { display: flex; gap: 8px; flex-wrap: wrap; }
    .kp-pill {
        padding: 7px 12px;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: #fff;
        color: var(--muted);
        font-size: 13px; font-weight: 600;
        text-decoration: none;
    }
    .kp-pill.is-active { background: var(--accent-soft); border-color: var(--accent-border); color: var(--accent); }
    .kp-pill b { margin-left: 4px; }

    /* Pencarian */
    .kp-search { display: flex; gap: 8px; }
    .kp-search .lb-input { flex: 1; }

    .kp-btn {
        display: inline-block;
        padding: 9px 16px;
        border: 0; border-radius: 8px;
        background: var(--accent); color: #fff;
        font-size: 13px; font-weight: 700;
        cursor: pointer;
    }

    /* Baris pegawai */
    .kp-row {
        display: flex;
        align-items: center;
        gap: 12px;
        color: inherit;
        text-decoration: none;
    }
    a.kp-row:hover { border-color: var(--accent-border); background: var(--accent-soft); }
    .kp-row-main { flex: 1; min-width: 0; }
    .kp-name { color: var(--text-dark); font-size: 14px; font-weight: 700; }
    .kp-meta { margin-top: 2px; color: var(--muted); font-size: 12px; }

    .kp-avatar {
        width: 38px; height: 38px;
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        background: var(--accent-soft);
        color: var(--accent);
        font-size: 14px; font-weight: 700;
    }
    .kp-avatar--lg { width: 56px; height: 56px; font-size: 18px; }

    .kp-chips { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
    .kp-chip { padding: 3px 9px; border-radius: 999px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .kp-chip--ok    { background: #dcfce7; color: #15803d; }
    .kp-chip--wait  { background: #fef3c7; color: #b45309; }
    .kp-chip--admin { background: var(--accent-soft); color: var(--accent); }

    /* Header profil di halaman detail */
    .kp-profile { display: flex; align-items: center; gap: 14px; }

    /* Tab detail */
    .kp-dtabs { display: flex; gap: 4px; flex-wrap: wrap; border-bottom: 1px solid var(--border); }
    .kp-dtab {
        padding: 9px 12px;
        border-bottom: 2px solid transparent;
        color: var(--muted);
        font-size: 13px; font-weight: 600;
        text-decoration: none;
    }
    .kp-dtab.is-active { color: var(--accent); border-bottom-color: var(--accent); }
    .kp-dtab.is-off { opacity: .45; cursor: not-allowed; }

    /* Form */
    .kp-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px 20px;
    }
    @media (max-width: 640px) {
        .kp-form-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }
    }
    .kp-help  { margin-top: 4px; color: var(--muted); font-size: 12px; line-height: 1.5; }
    .kp-error { margin-top: 4px; color: var(--red); font-size: 12px; }
    .kp-actions { display: flex; justify-content: flex-end; margin-top: 16px; }

    /* Searchable Select (Dropdown dengan pencarian) */
    .searchable-select {
        position: relative;
        width: 100%;
    }
    .searchable-select-hidden {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }
    .searchable-select-trigger {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid var(--border-input);
        border-radius: 10px;
        background: #f7f9fc;
        color: var(--text-dark);
        font-size: 13px;
        font-family: inherit;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        cursor: pointer;
        text-align: left;
        transition: all 0.2s ease;
    }
    .searchable-select-trigger:hover {
        background: #fff;
        border-color: var(--accent-border);
    }
    .searchable-select.is-open .searchable-select-trigger,
    .searchable-select-trigger:focus-visible {
        background: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        outline: none;
    }
    .searchable-select-label {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .searchable-select-label.is-placeholder {
        color: var(--muted);
        font-weight: 500;
    }
    .searchable-select-arrow {
        font-size: 11px;
        color: var(--muted);
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }
    .searchable-select.is-open .searchable-select-arrow {
        transform: rotate(180deg);
        color: var(--accent);
    }

    /* Floating Dropdown Panel */
    .searchable-select-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1.5px solid #bfdbfe;
        border-radius: 12px;
        box-shadow: 0 12px 28px -4px rgba(15, 23, 42, 0.16), 0 4px 10px -2px rgba(15, 23, 42, 0.08);
        padding: 8px;
        z-index: 999;
        display: none;
        animation: ssFadeDown 0.15s ease-out;
    }
    @keyframes ssFadeDown {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .searchable-select.is-open .searchable-select-dropdown {
        display: block;
    }

    /* Search input inside dropdown */
    .searchable-select-search {
        position: relative;
        display: flex;
        align-items: center;
        margin-bottom: 6px;
    }
    .searchable-select-search-icon {
        position: absolute;
        left: 10px;
        font-size: 12px;
        color: var(--muted);
        pointer-events: none;
    }
    .searchable-select-input {
        width: 100%;
        padding: 8px 30px 8px 30px;
        border: 1.5px solid #93c5fd;
        border-radius: 8px;
        background: #f8fafc;
        color: var(--text-dark);
        font-size: 13px;
        font-family: inherit;
        font-weight: 500;
        outline: none;
        transition: all 0.2s;
    }
    .searchable-select-input:focus {
        background: #fff;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(11, 87, 208, 0.12);
    }
    .searchable-select-clear {
        position: absolute;
        right: 8px;
        background: none;
        border: none;
        color: var(--muted);
        cursor: pointer;
        padding: 4px 6px;
        font-size: 11px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .searchable-select-clear:hover {
        color: var(--text-dark);
        background: #e2e8f0;
    }

    /* Option Items List */
    .searchable-select-list {
        list-style: none;
        margin: 0;
        padding: 2px 0;
        max-height: 240px;
        overflow-y: auto;
        overscroll-behavior: contain;
    }
    .searchable-select-list::-webkit-scrollbar {
        width: 6px;
    }
    .searchable-select-list::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .searchable-select-list::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .searchable-select-list::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .searchable-select-item {
        padding: 8px 10px;
        border-radius: 8px;
        font-size: 13px;
        color: var(--text-dark);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        user-select: none;
        transition: background 0.12s, color 0.12s;
    }
    .searchable-select-item:hover,
    .searchable-select-item.is-highlighted {
        background: #f1f5f9;
        color: var(--accent);
    }
    .searchable-select-item.is-selected {
        background: var(--accent-soft);
        color: var(--accent);
        font-weight: 700;
    }
    .searchable-select-check {
        font-size: 11px;
        color: var(--accent);
        flex-shrink: 0;
    }
    .searchable-select-empty {
        padding: 20px 12px;
        text-align: center;
        color: var(--muted);
        font-size: 12px;
        font-weight: 500;
    }
    .searchable-select-empty i {
        margin-right: 4px;
        font-size: 13px;
    }

    .kp-info {
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px dashed var(--border);
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }
    .kp-info-label { color: var(--muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .kp-info-value { margin-top: 3px; color: var(--text-dark); font-size: 13px; font-weight: 600; }

    .kp-empty { padding: 28px 12px; text-align: center; color: var(--muted); font-size: 13px; }

    .kp-pager { display: flex; justify-content: space-between; gap: 10px; }
    .kp-pager a, .kp-pager span {
        padding: 8px 12px; border: 1px solid var(--border); border-radius: 8px;
        background: #fff; color: var(--accent); font-size: 13px; font-weight: 600; text-decoration: none;
    }
    .kp-pager .is-off { color: var(--muted); opacity: .5; }
</style>
@endpush