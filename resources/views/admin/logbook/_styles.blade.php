@push('styles')
<style>
    .acc-page {
        width: 100%;
        max-width: 960px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .acc-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .acc-title { color: var(--text-dark); font-size: 18px; font-weight: 700; }
    .acc-sub   { margin-top: 3px; color: var(--muted); font-size: 13px; }
    .acc-back  { color: var(--accent); font-size: 13px; font-weight: 600; text-decoration: none; }

    /* Tab status */
    .acc-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .acc-tab {
        padding: 7px 12px;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: #fff;
        color: var(--muted);
        font-size: 13px; font-weight: 600;
        text-decoration: none;
    }
    .acc-tab.is-active { background: var(--accent-soft); border-color: var(--accent-border); color: var(--accent); }
    .acc-tab b { margin-left: 4px; }

    /* Filter */
    .acc-filter {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        align-items: end;
    }
    .acc-btn {
        display: inline-block;
        padding: 9px 14px;
        border: 0; border-radius: 8px;
        background: var(--accent); color: #fff;
        font-size: 13px; font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }
    .acc-btn--ghost { background: var(--surface); color: var(--muted); border: 1px solid var(--border); text-align: center; }
    .acc-btn--ok { background: #16a34a; }
    .acc-btn--no { background: #fff; color: var(--red); border: 1px solid #fecaca; }

    /* Baris pegawai (halaman daftar) */
    .acc-total { color: var(--muted); font-size: 13px; }
    .acc-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .acc-name { color: var(--text-dark); font-size: 14px; font-weight: 700; }
    .acc-unit { margin-top: 3px; color: var(--muted); font-size: 12px; }
    .acc-row-right { display: flex; align-items: center; gap: 10px; }
    .acc-count {
        padding: 4px 10px; border-radius: 999px;
        background: var(--accent-soft); color: var(--accent);
        font-size: 12px; font-weight: 700;
    }

    /* Kartu logbook (halaman detail) */
    .acc-item { display: flex; flex-direction: column; gap: 10px; }
    .acc-top { display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .acc-date { color: var(--text-dark); font-size: 14px; font-weight: 700; }
    .acc-badges { display: flex; gap: 6px; flex-wrap: wrap; align-items: flex-start; }
    .acc-chip {
        padding: 3px 9px; border-radius: 999px;
        background: var(--surface); border: 1px solid var(--border);
        color: var(--muted); font-size: 11px; font-weight: 700;
    }
    .st { padding: 3px 9px; border-radius: 999px; font-size: 11px; font-weight: 700; }
    .st--ok   { background: #dcfce7; color: #15803d; }
    .st--no   { background: #fee2e2; color: #b91c1c; }
    .st--wait { background: #fef3c7; color: #b45309; }

    .acc-detail  { color: var(--muted); font-size: 12px; }
    .acc-summary { color: var(--text); font-size: 13px; line-height: 1.6; white-space: pre-line; }
    .acc-submitted { display: inline-flex; align-items: center; gap: 6px; color: var(--muted-light); font-size: 11px; font-weight: 600; }
    .acc-submitted i { color: var(--accent); }
    .acc-evidence-row { display: flex; }
    .acc-evidence {
        min-height: 38px;
        padding: 0 13px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #b8d1fb;
        border-radius: 10px;
        background: linear-gradient(135deg, #f1f6ff, #e8f1ff);
        color: #164eaa;
        font-size: 12px;
        font-weight: 700;
        box-shadow: 0 2px 7px rgba(24, 82, 166, .08);
        transition: background .15s ease, border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }
    .acc-evidence:hover { border-color: #78a9f8; background: #e4efff; box-shadow: 0 5px 12px rgba(24, 82, 166, .14); transform: translateY(-1px); }
    .acc-evidence > i:first-child { font-size: 14px; }
    .acc-evidence-open { margin-left: 2px; color: #5376a8; font-size: 10px; }
    .acc-hc {
        padding: 8px 10px; border-left: 3px solid var(--red);
        background: #fff7f7; color: #b91c1c; font-size: 12px; line-height: 1.5;
    }

    .acc-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-start; }
    .acc-tolak summary { list-style: none; cursor: pointer; }
    .acc-tolak summary::-webkit-details-marker { display: none; }
    .acc-tolak form { margin-top: 8px; display: flex; flex-direction: column; gap: 8px; min-width: 260px; }

    .acc-alert { padding: 10px 12px; border-radius: 8px; background: #fef2f2; color: #b91c1c; font-size: 13px; }
    .acc-empty { padding: 28px 12px; text-align: center; color: var(--muted); font-size: 13px; }

    .acc-pager { display: flex; justify-content: space-between; gap: 10px; }
    .acc-pager a, .acc-pager span {
        padding: 8px 12px; border: 1px solid var(--border); border-radius: 8px;
        background: #fff; color: var(--accent); font-size: 13px; font-weight: 600; text-decoration: none;
    }
    .acc-pager .is-off { color: var(--muted); opacity: .5; }
</style>
@endpush
