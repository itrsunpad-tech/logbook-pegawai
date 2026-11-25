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
    .acc-btn.acc-reset { width: auto; min-width: 0; padding: 7px 11px; justify-self: start; font-size: 12px; line-height: 1.2; }
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
    .acc-bulk-select { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 14px; border:1px solid var(--border); border-radius:12px; background:#fff; color:var(--text); font-size:13px; }
    .acc-bulk-select[hidden] { display:none; }
    .acc-bulk-select label { display:flex; align-items:center; gap:9px; color:var(--accent); font-weight:700; cursor:pointer; }
    .acc-bulk-select input[type="checkbox"], .acc-select-item input[type="checkbox"], .acc-bulk-bar input[type="checkbox"] { width:16px; height:16px; accent-color:var(--accent); }
    .acc-bulk-select button { border:0; background:transparent; color:var(--accent); font-size:12px; font-weight:700; cursor:pointer; }
    .acc-bulk { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:10px 12px; border:1px solid var(--border); border-radius:10px; background:#fff; color:var(--text); font-size:13px; }
    .acc-bulk label,.acc-select-item { display:flex; align-items:center; gap:8px; color:var(--muted); font-size:12px; font-weight:600; }
    .acc-bulk input[type="checkbox"],.acc-select-item input[type="checkbox"] { width:16px; height:16px; accent-color:var(--accent); }
    .acc-select-item { align-self:flex-start; }
    .acc-item.is-selected { border-color:#6b9cff; background:#f5f8ff; box-shadow:0 0 0 1px rgba(37,99,235,.12); }
    .acc-item.is-selected .acc-select-item { color:var(--accent); }
    .acc-actions.is-muted { opacity:.38; filter:saturate(.55); pointer-events:none; }
    .acc-bulk-hint { color:var(--accent); font-size:11px; font-weight:600; }
    .acc-bulk-hint[hidden] { display:none; }
    .acc-bulk-bar { position:fixed; left:50%; bottom:18px; z-index:1000; transform:translateX(-50%); display:flex; align-items:center; justify-content:center; flex-wrap:wrap; gap:10px 12px; max-width:calc(100vw - 24px); padding:10px 14px; border:1px solid rgba(190,207,232,.9); border-radius:18px; background:rgba(255,255,255,.88); color:var(--text-dark); box-shadow:0 8px 28px rgba(31,55,89,.16); backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px); font-size:12px; white-space:nowrap; }
    .acc-bulk-bar[hidden] { display:none; }
    .acc-bulk-bar strong { color:var(--text-dark); font-size:12px; }
    .acc-bulk-bar label { display:flex; align-items:center; gap:6px; color:var(--muted); cursor:pointer; }
    .acc-bulk-bar .acc-btn { min-height:34px; padding:7px 12px; border-radius:9px; font-size:12px; }
    .acc-bulk-bar .acc-btn--ghost { background:rgba(255,255,255,.75); }
    .acc-bulk-bar .acc-btn--ok { background:#16a34a; color:#fff; border:1px solid #16a34a; }
    .acc-bulk-bar .acc-btn--no { background:#fff; color:var(--red); border:1px solid #fecaca; }
    .acc-bulk-dialog { position:fixed; inset:50% auto auto 50%; transform:translate(-50%,-50%); width:min(460px,calc(100% - 28px)); max-height:calc(100dvh - 32px); overflow-y:auto; margin:0; padding:22px; border:0; border-radius:16px; color:var(--text); box-shadow:0 24px 70px rgba(15,23,42,.3); }
    .acc-bulk-dialog::backdrop { background:rgba(15,23,42,.48); }
    .acc-bulk-dialog h2 { margin:0 0 8px; color:var(--text-dark); font-size:18px; }
    .acc-bulk-dialog p { margin:0 0 16px; color:var(--muted); font-size:13px; }
    .acc-bulk-dialog .acc-actions { justify-content:flex-end; margin-top:16px; }
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
    .acc-no-evidence { color: var(--muted-light); font-size: 11px; font-style: italic; }
    .acc-evidence {
        min-height: 32px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #b8d1fb;
        border-radius: 10px;
        background: linear-gradient(135deg, #f1f6ff, #e8f1ff);
        color: #164eaa;
        font-size: 11px;
        font-weight: 700;
        box-shadow: 0 2px 7px rgba(24, 82, 166, .08);
        transition: background .15s ease, border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }
    .acc-evidence:hover { border-color: #78a9f8; background: #e4efff; box-shadow: 0 5px 12px rgba(24, 82, 166, .14); transform: translateY(-1px); }
    .acc-evidence > i:first-child { font-size: 12px; }
    .acc-evidence-open { margin-left: 1px; color: #5376a8; font-size: 9px; }
    .acc-hc {
        padding: 8px 10px; border-left: 3px solid var(--red);
        background: #fff7f7; color: #b91c1c; font-size: 12px; line-height: 1.5;
    }

    .acc-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-start; }
    .acc-tolak summary { list-style: none; cursor: pointer; }
    .acc-tolak summary::-webkit-details-marker { display: none; }
    .acc-tolak form { margin-top: 8px; display: flex; flex-direction: column; gap: 8px; min-width: 260px; }

    .acc-alert { padding: 10px 12px; border-radius: 8px; background: #fef2f2; color: #b91c1c; font-size: 13px; }
    .acc-success { display:flex; align-items:center; gap:8px; padding:10px 12px; border:1px solid #bfdbfe; border-radius:10px; background:#eff6ff; color:#1d4ed8; font-size:13px; font-weight:600; }
    .acc-empty { display:flex; flex-direction:column; align-items:center; gap:8px; padding:30px 12px; text-align:center; color:var(--muted); font-size:13px; }
    .acc-empty strong { color:var(--text-dark); font-size:15px; }
    .acc-empty-icon { display:grid; place-items:center; width:40px; height:40px; border-radius:50%; background:#eff6ff; color:var(--accent); font-size:18px; }

    .acc-pager { display: flex; justify-content: space-between; gap: 10px; }
    .acc-pager a, .acc-pager span {
        padding: 8px 12px; border: 1px solid var(--border); border-radius: 8px;
        background: #fff; color: var(--accent); font-size: 13px; font-weight: 600; text-decoration: none;
    }
    .acc-pager .is-off { color: var(--muted); opacity: .5; }
</style>
@endpush
