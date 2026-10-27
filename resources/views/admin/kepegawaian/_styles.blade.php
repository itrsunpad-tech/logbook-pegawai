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
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 14px;
    }
    .kp-help  { margin-top: 4px; color: var(--muted); font-size: 12px; line-height: 1.5; }
    .kp-error { margin-top: 4px; color: var(--red); font-size: 12px; }
    .kp-actions { display: flex; justify-content: flex-end; margin-top: 16px; }

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