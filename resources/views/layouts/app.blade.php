<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

    <title>@yield('title', 'Logbook System')</title>

    {{-- Font Awesome --}}
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <style>
        /* =====================================================
           RESET & VARIABEL
        ====================================================== */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --accent: #2563eb;
            --accent-border: #60a5fa;
            --accent-soft: #eff6ff;
            --accent-soft-border: #bfdbfe;

            --text: #334155;
            --text-dark: #1e293b;
            --muted: #475569;
            --muted-light: #64748b;

            --surface: #f8fafc;
            --border: #e2e8f0;
            --border-input: #dbe3ec;

            --blue: #3b82f6;
            --green: #22c55e;
            --orange: #f59e0b;
            --purple: #8b5cf6;
            --gray: #9ca3af;
            --red: #dc2626;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            background:
                linear-gradient(rgba(37, 99, 235, 0.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37, 99, 235, 0.045) 1px, transparent 1px),
                #eff6ff;
            background-size: 32px 32px;
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        textarea,
        select {
            font-family: inherit;
        }

        button {
            cursor: pointer;
        }

        button:disabled {
            cursor: not-allowed;
        }

        .is-hidden {
            display: none !important;
        }


        /* =====================================================
           LAYOUT
        ====================================================== */
        .app-layout {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .app-content {
            width: 100%;
            /* padding bawah lebih besar supaya konten tidak tertutup pill batas */
            padding: 22px 16px 90px;
        }


        /* =====================================================
           HEADER
        ====================================================== */
        .app-header {
            position: sticky;
            top: 0;
            z-index: 100;
            height: 68px;
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid rgba(226, 232, 240, 0.95);
            backdrop-filter: blur(10px);
        }

        .header-inner {
            width: 100%;
            height: 100%;
            padding: 0 22px;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
        }

        /* Brand */
        .brand {
            justify-self: start;
            display: flex;
            align-items: center;
            gap: 18px;
            min-width: 0;
        }

        .brand-logo {
            width: 80px;
            height: 44px;
            flex: 0 0 80px;
            object-fit: contain;
            object-position: center;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .brand-name {
            color: var(--accent);
            font-size: 15px;
            font-weight: 700;
            line-height: 1.1;
            white-space: nowrap;
        }

        .brand-status {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--muted-light);
            font-size: 10px;
        }

        .brand-status-dot {
            width: 7px;
            height: 7px;
            flex: 0 0 7px;
            border-radius: 50%;
            background: var(--green);
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }

        .brand-status-dot.ping-good {
            background: #22c55e;
            box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.12);
        }

        .brand-status-dot.ping-medium {
            background: #f59e0b;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.12);
        }

        .brand-status-dot.ping-bad {
            background: #ef4444;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.12);
        }

        .brand-status-dot.ping-offline {
            background: #94a3b8;
            box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.12);
        }

        /* Tanggal & jam */
        .header-status {
            justify-self: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header-date {
            color: var(--muted);
            font-size: 11px;
            font-weight: 600;
        }

        .header-time {
            margin-top: 1px;
            color: var(--text-dark);
            font-family: monospace;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.1;
        }

        .header-ampm {
            margin-left: 4px;
            color: var(--muted);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            font-weight: 700;
        }

        /* Profil */
        .profile-wrapper {
            position: relative;
            justify-self: end;
        }

        .profile-button {
            min-height: 46px;
            padding: 5px 12px 5px 6px;
            display: flex;
            align-items: center;
            gap: 9px;
            border: 1px solid rgba(148, 163, 184, 0.38);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.28);
            color: var(--text);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .profile-button:hover {
            background: rgba(248, 250, 252, 0.76);
            border-color: rgba(100, 116, 139, 0.45);
            box-shadow: 0 2px 7px rgba(15, 23, 42, 0.06);
        }

        .profile-avatar {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            border: 2px solid #fff;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.12);
        }

        .profile-initial {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--surface);
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .profile-info {
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 1px;
        }

        .profile-name,
        .profile-email {
            max-width: 190px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .profile-name {
            color: var(--text);
            font-size: 12px;
            font-weight: 700;
            line-height: 1.15;
        }

        .profile-email {
            color: var(--muted);
            font-size: 10px;
            line-height: 1.1;
        }

        .profile-arrow {
            margin-left: 2px;
            color: var(--muted);
            font-size: 9px;
            transition: transform 0.2s ease;
        }

        .profile-button.is-open .profile-arrow {
            transform: rotate(180deg);
        }

        .profile-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 240px;
            padding: 8px;
            display: none;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid var(--border);
            border-radius: 11px;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.12);
        }

        .profile-menu.is-open {
            display: block;
        }

        .profile-menu-info {
            padding: 10px 11px 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .profile-menu-name {
            color: var(--text);
            font-size: 11px;
            font-weight: 700;
        }

        .profile-menu-email {
            margin-top: 3px;
            color: var(--muted-light);
            font-size: 11px;
            word-break: break-word;
        }

        .profile-logout {
            width: 100%;
            margin-top: 5px;
            padding: 10px 11px;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 0;
            border-radius: 7px;
            background: transparent;
            color: var(--red);
            font-size: 10px;
            font-weight: 600;
            text-align: left;
        }

        .profile-logout:hover {
            background: #fef2f2;
        }


        /* =====================================================
           BATAS PENGISIAN (pill melayang, selalu merah)
        ====================================================== */
        .deadline-float {
            position: fixed;
            left: 50%;
            bottom: calc(16px + env(safe-area-inset-bottom, 0px));
            transform: translateX(-50%);
            z-index: 150; /* di bawah modal (200), toast (300), konfirmasi (400) */
            max-width: calc(100% - 24px);
            padding: 9px 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #fecaca;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.96);
            color: var(--red);
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.14);
            backdrop-filter: blur(8px);
        }

        .deadline-float i {
            font-size: 11px;
        }


        /* =====================================================
           LOGBOOK: PAGE, TAB, CARD
        ====================================================== */
        .lb-page {
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Jarak antar card di dalam panel tab */
        [data-tab-panel] {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .lb-tabs {
            width: 100%;
            padding: 4px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        }

        .lb-tab {
            padding: 8px 10px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
        }

        .lb-tab.is-active {
            background: var(--surface);
            color: var(--accent);
        }

        .lb-card {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 3px 10px rgba(15, 23, 42, 0.05);
        }

        .lb-card-title {
            display: flex;
            align-items: center;
            gap: 7px;
            color: var(--muted-light);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.45px;
            text-transform: uppercase;
        }

        .lb-card-title::after {
            content: "";
            height: 1px;
            flex: 1;
            background: var(--border);
        }

        .lb-card-title i {
            color: var(--accent);
        }

        /* Teks catatan kecil (pengganti inline style di index.blade.php) */
        .lb-note {
            margin-top: 6px;
            color: var(--muted-light);
            font-size: 11px;
            font-style: italic;
        }

        .lb-modal-empty {
            justify-content: center;
            color: var(--muted-light);
        }

        .lb-alert {
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 12px;
            line-height: 1.6;
        }

        .lb-alert ul {
            padding-left: 16px;
        }

        .lb-alert--error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .lb-empty {
            margin-top: 12px;
            color: var(--muted-light);
            font-size: 11px;
            text-align: center;
        }


        /* =====================================================
           LOGBOOK: PILIH JENIS
        ====================================================== */
        .lb-type-grid {
            margin-top: 12px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .lb-type-button {
            padding: 10px 8px;
            border: 1px solid var(--border-input);
            border-radius: 9px;
            background: var(--surface);
            color: #475569;
            font-size: 12px;
            font-weight: 600;
        }

        .lb-type-button:hover {
            background: var(--accent-soft);
            border-color: var(--accent-soft-border);
        }

        .lb-type-button.is-active {
            background: var(--accent-soft);
            border-color: var(--accent-border);
            color: var(--accent);
        }

        .lb-type-button--wide {
            grid-column: 1 / -1;
        }

        .lb-type-sub {
            display: block;
            margin-top: 4px;
            color: var(--muted-light);
            font-size: 11px;
            font-weight: 400;
        }


        /* =====================================================
           LOGBOOK: FORM
        ====================================================== */
        .lb-form {
            display: none;
            scroll-margin-top: 90px; /* supaya tidak tertutup header sticky */
        }

        .lb-form.is-active {
            display: block;
        }

        .lb-field {
            margin-top: 12px;
        }

        .lb-label {
            display: block;
            margin-bottom: 6px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .lb-input,
        .lb-select,
        .lb-textarea {
            width: 100%;
            padding: 9px 10px;
            border: 1px solid var(--border-input);
            border-radius: 8px;
            background: var(--surface);
            color: var(--text);
            font-size: 12px;
            outline: none;
        }

        .lb-input:focus,
        .lb-select:focus,
        .lb-textarea:focus {
            background: #fff;
            border-color: var(--accent-border);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }

        .lb-textarea {
            min-height: 90px;
            resize: vertical;
        }

        .lb-two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .lb-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: #475569;
            font-size: 12px;
            line-height: 1.45;
        }

        .lb-checkbox input {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
        }

        .lb-info {
            margin-top: 6px;
            padding: 9px 12px;
            border-radius: 8px;
            background: #eff6ff;
            color: var(--blue);
            font-size: 11px;
        }

        /* Dipakai oleh partial logbook/_pernyataan */
        .lb-declaration {
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid var(--border);
        }

        .lb-declaration-check {
            font-size: 12px;
        }

        .lb-submit-wrap {
            margin-top: 14px;
            display: flex;
            justify-content: flex-end;
        }

        .lb-submit-button {
            padding: 10px 20px;
            border: 0;
            border-radius: 9px;
            background: var(--accent);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
        }

        .lb-submit-button:disabled {
            opacity: 0.45;
        }


        /* =====================================================
           LOGBOOK: KALENDER
        ====================================================== */
        .lb-cal-nav {
            margin-top: 12px;
            display: grid;
            grid-template-columns: 30px 1fr 30px;
            align-items: center;
            gap: 8px;
        }

        .lb-cal-title {
            color: var(--text-dark);
            font-size: 13px;
            font-weight: 700;
            text-align: center;
        }

        .lb-cal-btn {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
            color: var(--muted);
            font-size: 10px;
        }

        .lb-cal-btn:hover:not(:disabled) {
            background: var(--accent-soft);
            color: var(--accent);
        }

        .lb-cal-btn:disabled {
            opacity: 0.25;
        }

        .lb-cal-head,
        .lb-cal-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 5px;
        }

        .lb-cal-head {
            margin: 10px 0 6px;
        }

        .lb-cal-head span {
            color: var(--muted-light);
            font-size: 11px;
            font-weight: 600;
            text-align: center;
        }

        .lb-cal-empty,
        .lb-cal-day {
            min-height: 58px;
        }

        .lb-cal-day {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            border: 1px solid transparent;
            border-radius: 9px;
            background: #f1f5f9;
            color: var(--muted);
            font-size: 11px;
            font-weight: 500;
        }

        .lb-cal-day:hover:not(:disabled) {
            border-color: var(--accent-soft-border);
            background: var(--accent-soft);
        }

        .lb-cal-day.is-today {
            border: 2px solid #3b82f6;
            background: #f5f9ff;
            color: var(--text);
            font-weight: 700;
        }

        .lb-dots {
            min-height: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .lb-dot {
            width: 6px;
            height: 6px;
            flex: 0 0 6px;
            display: inline-block;
            border-radius: 50%;
            background: var(--gray);
        }

        .lb-dot--harian { background: var(--blue); }
        .lb-dot--lembur { background: var(--orange); }
        .lb-dot--oncall { background: var(--purple); }
        .lb-dot--libur  { background: var(--green); }
        .lb-dot--dll    { background: var(--gray); }

        .lb-legend {
            margin-top: 12px;
            padding-top: 10px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px 14px;
            border-top: 1px solid #f1f5f9;
            color: var(--muted-light);
            font-size: 11px;
        }

        .lb-legend span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .lb-hint {
            margin-top: 8px;
            color: var(--muted-light);
            font-size: 11px;
            line-height: 1.4;
        }


        /* =====================================================
           LOGBOOK: MODAL
        ====================================================== */
        .lb-modal {
            position: fixed;
            inset: 0;
            z-index: 200;
            padding: 15px;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.45);
        }

        .lb-modal.is-open {
            display: flex;
        }

        .lb-modal-box {
            width: 100%;
            max-width: 420px;
            overflow: hidden;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 15px 40px rgba(15, 23, 42, 0.2);
        }

        .lb-modal-head {
            padding: 13px 15px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
        }

        .lb-modal-title {
            font-size: 13px;
            font-weight: 700;
        }

        .lb-modal-sub {
            margin-top: 2px;
            color: var(--muted-light);
            font-size: 11px;
        }

        .lb-modal-close {
            width: 30px;
            height: 30px;
            border: 0;
            border-radius: 7px;
            background: var(--surface);
            color: var(--muted);
            font-size: 18px;
        }

        .lb-modal-list {
            padding: 10px 15px;
            list-style: none;
        }

        .lb-modal-item {
            padding: 10px 0;
            display: flex;
            align-items: flex-start;
            gap: 9px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
        }

        .lb-modal-item small {
            display: block;
            margin-top: 2px;
            color: var(--muted-light);
            font-size: 11px;
        }

        .lb-modal-type-grid {
            padding: 0 15px 13px;
            display: grid;
            gap: 8px;
        }

        .lb-modal-type-button {
            width: 100%;
            padding: 9px 10px;
            border: 1px solid var(--border-input);
            border-radius: 8px;
            background: var(--surface);
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            text-align: left;
        }

        .lb-modal-type-button:hover {
            background: var(--accent-soft);
            border-color: var(--accent-soft-border);
            color: var(--accent);
        }


        /* =====================================================
           MODAL KONFIRMASI (logout)
        ====================================================== */
        .confirm-modal {
            position: fixed;
            inset: 0;
            z-index: 400;
            padding: 16px;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(2px);
        }

        .confirm-modal.is-open {
            display: flex;
        }

        .confirm-box {
            width: 100%;
            max-width: 320px;
            padding: 24px 22px 20px;
            border-radius: 16px;
            background: #ffffff;
            text-align: center;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);
            animation: confirm-in 0.18s ease;
        }

        .confirm-icon {
            width: 44px;
            height: 44px;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #fef2f2;
            color: var(--red);
            font-size: 17px;
        }

        .confirm-title {
            color: var(--text-dark);
            font-size: 15px;
            font-weight: 700;
        }

        .confirm-text {
            margin-top: 6px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .confirm-actions {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .confirm-btn {
            height: 38px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 700;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .confirm-btn--cancel {
            border: 1px solid var(--border);
            background: #ffffff;
            color: var(--text);
        }

        .confirm-btn--cancel:hover {
            background: var(--surface);
        }

        .confirm-btn--danger {
            border: 1px solid var(--red);
            background: var(--red);
            color: #ffffff;
        }

        .confirm-btn--danger:hover {
            background: #b91c1c;
            border-color: #b91c1c;
        }

        .confirm-btn:focus-visible {
            outline: 3px solid rgba(37, 99, 235, 0.3);
            outline-offset: 2px;
        }

        @keyframes confirm-in {
            from { opacity: 0; transform: scale(0.96) translateY(6px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }


        /* =====================================================
           TOAST (notifikasi pojok kanan atas)
        ====================================================== */
        .toast-container {
            position: fixed;
            top: 80px; /* tepat di bawah header / profil */
            right: 22px;
            z-index: 300;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
            pointer-events: none;
        }

        .toast {
            --toast-color: var(--green);

            position: relative;
            overflow: hidden;
            min-width: 210px;
            max-width: 300px;
            padding: 9px 10px 9px 12px;
            display: flex;
            align-items: center;
            gap: 9px;
            border: 1px solid var(--border);
            border-left: 3px solid var(--toast-color);
            border-radius: 10px;
            background: #ffffff;
            color: var(--text-dark);
            font-size: 12px;
            line-height: 1.4;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.12);
            pointer-events: auto;
            animation: toast-in 0.25s ease;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .toast--error {
            --toast-color: var(--red);
        }

        .toast > i {
            color: var(--toast-color);
            font-size: 14px;
        }

        .toast span {
            flex: 1;
        }

        .toast-close {
            width: 20px;
            height: 20px;
            border: 0;
            border-radius: 5px;
            background: transparent;
            color: var(--muted-light);
            font-size: 15px;
            line-height: 1;
        }

        .toast-close:hover {
            background: var(--surface);
            color: var(--muted);
        }

        /* Garis tipis penanda sisa waktu */
        .toast::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            height: 2px;
            width: 100%;
            background: var(--toast-color);
            opacity: 0.55;
            transform-origin: left;
            animation: toast-timer 4s linear forwards;
        }

        .toast.is-leaving {
            opacity: 0;
            transform: translateX(16px);
        }

        @keyframes toast-in {
            from { opacity: 0; transform: translateX(16px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        @keyframes toast-timer {
            from { transform: scaleX(1); }
            to   { transform: scaleX(0); }
        }


        /* =====================================================
           MOBILE
        ====================================================== */
        @media (max-width: 700px) {
            .app-header {
                height: 64px;
            }

            .toast-container {
                top: 72px;
                right: 10px;
            }

            .header-inner {
                padding: 0 10px;
            }

            .brand-logo {
                width: 38px;
                height: 38px;
                flex-basis: 38px;
            }

            .brand {
                gap: 10px;
            }

            .brand-name {
                font-size: 13px;
            }

            .header-date {
                font-size: 9px;
            }

            .header-time {
                font-size: 15px;
            }

            .header-ampm {
                font-size: 8px;
            }

            .profile-info {
                display: none;
            }

            .profile-button {
                min-height: 40px;
            }

            .profile-avatar {
                width: 32px;
                height: 32px;
                flex-basis: 32px;
            }

            .app-content {
                padding: 12px 8px 84px;
            }

            .deadline-float {
                padding: 8px 14px;
                font-size: 10px;
            }

            .lb-page {
                max-width: 100%;
            }

            .lb-card {
                padding: 14px;
            }

            .lb-cal-grid {
                gap: 4px;
            }

            .lb-cal-empty,
            .lb-cal-day {
                min-height: 52px;
                font-size: 12px;
            }
        }


        /* =====================================================
           RIWAYATKU
        ====================================================== */
        .riwayat-card {
            padding: 15px;
        }

        /* Mode Riwayat */
        .riwayat-mode {
            margin-top: 12px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .riwayat-mode-button {
            min-height: 62px;
            padding: 9px 11px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid var(--border-input);
            border-radius: 9px;
            background: var(--surface);
            color: var(--muted);
            text-align: left;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .riwayat-mode-button:hover {
            background: var(--accent-soft);
            border-color: var(--accent-soft-border);
        }

        .riwayat-mode-button.is-active {
            border: 2px solid var(--accent-border);
            background: var(--accent-soft);
            color: var(--accent);
        }

        .riwayat-mode-icon {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.75);
            color: var(--accent);
            font-size: 13px;
        }

        .riwayat-mode-content {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .riwayat-mode-content strong {
            color: inherit;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
        }

        .riwayat-mode-content small {
            color: var(--muted-light);
            font-size: 11px;
            line-height: 1.35;
        }

        /* Filter Tanggal */
        .riwayat-filter {
            margin-top: 12px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr) auto 38px;
            align-items: center;
            gap: 7px;
        }

        .riwayat-filter .lb-input {
            width: 100%;
            height: 38px;
            padding: 9px 10px;
            font-size: 12px;
        }

        .riwayat-sampai {
            color: var(--muted);
            font-size: 11px;
            text-align: center;
        }

        .riwayat-view-button {
            height: 38px;
            padding: 0 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 0;
            border-radius: 9px;
            background: var(--accent);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
        }

        .riwayat-view-button:hover {
            background: #1d4ed8;
        }

        .riwayat-reset-button {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-input);
            border-radius: 9px;
            background: var(--surface);
            color: var(--muted);
            font-size: 11px;
        }

        .riwayat-reset-button:hover {
            background: var(--accent-soft);
            color: var(--accent);
        }

        /* Hasil */
        .riwayat-result {
            min-height: 165px;
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .riwayat-empty {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: var(--muted-light);
            text-align: center;
        }

        .riwayat-empty-icon {
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 20px;
        }

        .riwayat-empty p {
            max-width: 420px;
            color: var(--muted-light);
            font-size: 12px;
            line-height: 1.5;
        }

        /* Hasil Logbook */
        .riwayat-list {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .riwayat-item {
            padding: 11px 12px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: var(--surface);
        }

        .riwayat-item-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .riwayat-item-date {
            color: var(--text);
            font-size: 11px;
            font-weight: 700;
        }

        .riwayat-item-type {
            padding: 4px 7px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 10px;
            font-weight: 700;
        }

        .riwayat-item-detail {
            margin-top: 7px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .riwayat-item-shift {
            margin-top: 5px;
            color: var(--muted-light);
            font-size: 11px;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .riwayat-mode {
                grid-template-columns: 1fr;
            }

            .riwayat-filter {
                grid-template-columns: 1fr auto 1fr;
            }

            .riwayat-view-button {
                grid-column: 1 / 3;
            }

            .riwayat-reset-button {
                grid-column: 3;
                grid-row: 2;
            }
        }

        /* =====================================================
           WARNA PER JENIS LOGBOOK (selaras dengan titik kalender)
        ====================================================== */
        .lb-type-button[data-jenis="harian"] i,
        .lb-modal-type-button[data-open-jenis="harian"] i { color: var(--blue); }

        .lb-type-button[data-jenis="lembur"] i,
        .lb-modal-type-button[data-open-jenis="lembur"] i { color: var(--orange); }

        .lb-type-button[data-jenis="oncall"] i,
        .lb-modal-type-button[data-open-jenis="oncall"] i { color: var(--purple); }

        .lb-type-button[data-jenis="lembur"]:hover,
        .lb-type-button[data-jenis="lembur"].is-active {
            background: #fffbeb;
            border-color: #fcd34d;
        }

        .lb-type-button[data-jenis="lembur"].is-active { color: #b45309; }

        .lb-type-button[data-jenis="oncall"]:hover,
        .lb-type-button[data-jenis="oncall"].is-active {
            background: #f5f3ff;
            border-color: #c4b5fd;
        }

        .lb-type-button[data-jenis="oncall"].is-active { color: #6d28d9; }

        .lb-form[data-jenis-panel="harian"] { border-top: 3px solid var(--blue); }
        .lb-form[data-jenis-panel="lembur"] { border-top: 3px solid var(--orange); }
        .lb-form[data-jenis-panel="oncall"] { border-top: 3px solid var(--purple); }

        .lb-form[data-jenis-panel="lembur"] .lb-card-title i { color: var(--orange); }
        .lb-form[data-jenis-panel="oncall"] .lb-card-title i { color: var(--purple); }

        .lb-form[data-jenis-panel="lembur"] .lb-submit-button { background: #b45309; }
        .lb-form[data-jenis-panel="oncall"] .lb-submit-button { background: #7c3aed; }

        .riwayat-item--harian { border-left: 3px solid var(--blue); }
        .riwayat-item--lembur { border-left: 3px solid var(--orange); }
        .riwayat-item--oncall { border-left: 3px solid var(--purple); }

        .riwayat-item-type--harian { background: #dbeafe; color: #1d4ed8; }
        .riwayat-item-type--lembur { background: #fef3c7; color: #b45309; }
        .riwayat-item-type--oncall { background: #ede9fe; color: #6d28d9; }


        /* =====================================================
           KALENDER: HARI KERJA YANG BELUM DIISI
        ====================================================== */
        .lb-cal-day.is-missing {
            border: 1px dashed #fca5a5;
            background: #fff7f7;
        }

        .lb-cal-day.is-missing:hover:not(:disabled) {
            border-color: #f87171;
            background: #fee2e2;
        }

        .lb-dot--kosong { background: #f87171; }


        /* =====================================================
           MOBILE: input 16px supaya iPhone tidak zoom otomatis
        ====================================================== */
        @media (max-width: 700px) {
            .lb-input,
            .lb-select,
            .lb-textarea,
            .riwayat-filter .lb-input {
                font-size: 16px;
            }
        }
    </style>

    @stack('styles')
</head>


<body>

@php
    $user = auth()->user();

    $inisial = collect(explode(' ', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
        ->implode('');
@endphp


<div class="app-layout">

    {{-- ===================== HEADER ===================== --}}
    <header class="app-header">
        <div class="header-inner">

            {{-- Logo + nama aplikasi --}}
            <a href="{{ route('logbook.index') }}" class="brand">
                <img
                    src="{{ asset('images/logo-rsup-unpad.png') }}"
                    alt="Rumah Sakit UNPAD"
                    class="brand-logo"
                >

                <span class="brand-text">
                    <span class="brand-name">Logbook System</span>

                    <span class="brand-status">
                        <span
                            id="network-status-dot"
                            class="brand-status-dot ping-good"
                        ></span>

                        <span id="network-ping">
                            --ms
                        </span>
                    </span>
                </span>
            </a>

            {{-- Tanggal & jam --}}
            <div class="header-status">
                <div id="header-date" class="header-date"></div>
                <div id="header-time" class="header-time"></div>
            </div>

            {{-- Profil --}}
            <div class="profile-wrapper">
                <button
                    type="button"
                    id="profile-button"
                    class="profile-button"
                    aria-expanded="false"
                >
                    @if ($user->avatar)
                        <img
                            src="{{ $user->avatar }}"
                            alt="{{ $user->name }}"
                            class="profile-avatar"
                            referrerpolicy="no-referrer"
                        >
                    @else
                        <span class="profile-avatar profile-initial">{{ $inisial }}</span>
                    @endif

                    <span class="profile-info">
                        <span class="profile-name">{{ $user->name }}</span>
                        <span class="profile-email">{{ $user->email }}</span>
                    </span>

                    <span class="profile-arrow">
                        <i class="fa-solid fa-chevron-down"></i>
                    </span>
                </button>

                <div id="profile-menu" class="profile-menu">
                    <div class="profile-menu-info">
                        <div class="profile-menu-name">{{ $user->name }}</div>
                        <div class="profile-menu-email">{{ $user->email }}</div>
                    </div>

                    <form id="logout-form" method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="button" id="logout-button" class="profile-logout">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </header>


    {{-- ===================== MODAL KONFIRMASI LOGOUT ===================== --}}
    <div
        id="confirm-modal"
        class="confirm-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-title"
    >
        <div class="confirm-box">
            <div class="confirm-icon">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>

            <h3 id="confirm-title" class="confirm-title">Logout</h3>
            <p class="confirm-text">Apakah Anda yakin ingin logout?</p>

            <div class="confirm-actions">
                <button type="button" id="confirm-cancel" class="confirm-btn confirm-btn--cancel">Batal</button>
                <button type="button" id="confirm-ok" class="confirm-btn confirm-btn--danger">Ya, Logout</button>
            </div>
        </div>
    </div>


    {{-- ===================== TOAST ===================== --}}
    @if (session('success') || session('error'))
        <div class="toast-container">
            @if (session('success'))
                <div class="toast toast--success" role="status">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="toast-close" aria-label="Tutup">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="toast toast--error" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="toast-close" aria-label="Tutup">&times;</button>
                </div>
            @endif
        </div>
    @endif


    {{-- ===================== CONTENT ===================== --}}
    <main class="app-content">
        @yield('content')
    </main>


    {{-- ===================== BATAS PENGISIAN (MELAYANG) ===================== --}}
    <div class="deadline-float" role="status">
        <i class="fa-solid fa-lock"></i>
        <span>Batas: {{ $batas->format('d/m/Y \p\u\k\u\l H:i') }} WIB</span>
    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ---------------------------------
       Dropdown profil
    ---------------------------------- */
    const profileButton = document.getElementById('profile-button');
    const profileMenu = document.getElementById('profile-menu');

    if (profileButton && profileMenu) {
        const setMenu = (open) => {
            profileMenu.classList.toggle('is-open', open);
            profileButton.classList.toggle('is-open', open);
            profileButton.setAttribute('aria-expanded', String(open));
        };

        profileButton.addEventListener('click', (event) => {
            event.stopPropagation();
            setMenu(!profileMenu.classList.contains('is-open'));
        });

        profileMenu.addEventListener('click', (event) => event.stopPropagation());

        document.addEventListener('click', () => setMenu(false));


        /* ---------------------------------
           Konfirmasi logout (modal sendiri)
        ---------------------------------- */
        const logoutForm = document.getElementById('logout-form');
        const logoutButton = document.getElementById('logout-button');
        const confirmModal = document.getElementById('confirm-modal');
        const confirmCancel = document.getElementById('confirm-cancel');
        const confirmOk = document.getElementById('confirm-ok');

        const openConfirm = () => {
            setMenu(false);
            confirmModal.classList.add('is-open');
            confirmCancel.focus();
        };

        const closeConfirm = () => confirmModal.classList.remove('is-open');

        logoutButton.addEventListener('click', openConfirm);
        confirmCancel.addEventListener('click', closeConfirm);
        confirmOk.addEventListener('click', () => logoutForm.submit());

        confirmModal.addEventListener('click', (event) => {
            if (event.target === confirmModal) closeConfirm();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeConfirm();
        });
    }


    /* ---------------------------------
       Jam & tanggal header (WIB)
    ---------------------------------- */
    const dateElement = document.getElementById('header-date');
    const timeElement = document.getElementById('header-time');

    const dateFormat = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });

    const timeFormat = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
    });

    function updateClock() {
        const now = new Date();

        if (dateElement) dateElement.textContent = dateFormat.format(now);
        if (timeElement) {
            const parts = timeFormat.formatToParts(now);
            const get = (type) => parts.find((part) => part.type === type)?.value ?? '';

            const period = document.createElement('small');
            period.className = 'header-ampm';
            period.textContent = get('dayPeriod');

            timeElement.textContent = `${get('hour')}.${get('minute')}.${get('second')}`;
            timeElement.append(period);
        }
    }

    updateClock();
    setInterval(updateClock, 1000);


    /* ---------------------------------
       Ping jaringan ke server Laravel
    ---------------------------------- */
    const pingElement = document.getElementById('network-ping');
    const pingDot = document.getElementById('network-status-dot');

    async function updateNetworkPing() {

        if (!pingElement || !pingDot) {
            return;
        }

        const start = performance.now();

        try {

            const response = await fetch(
                '{{ route('ping') }}?_=' + Date.now(),
                {
                    method: 'GET',
                    cache: 'no-store',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            if (!response.ok) {
                throw new Error('Ping request failed.');
            }

            await response.json();

            const ping = Math.max(0, Math.round(performance.now() - start));

            pingElement.textContent = `${ping}ms`;

            pingDot.classList.remove(
                'ping-good',
                'ping-medium',
                'ping-bad',
                'ping-offline'
            );

            if (ping <= 80) {
                pingDot.classList.add('ping-good');
            } else if (ping <= 180) {
                pingDot.classList.add('ping-medium');
            } else {
                pingDot.classList.add('ping-bad');
            }

        } catch (error) {

            pingElement.textContent = 'Offline';

            pingDot.classList.remove(
                'ping-good',
                'ping-medium',
                'ping-bad'
            );

            pingDot.classList.add('ping-offline');
        }
    }

    updateNetworkPing();
    setInterval(updateNetworkPing, 5000);


    /* ---------------------------------
       Toast: hilang otomatis setelah 4 detik
    ---------------------------------- */
    document.querySelectorAll('.toast').forEach((toast) => {
        const dismiss = () => {
            toast.classList.add('is-leaving');
            setTimeout(() => toast.remove(), 300);
        };

        toast.querySelector('.toast-close').addEventListener('click', dismiss);
        setTimeout(dismiss, 4000);
    });

});
</script>

@stack('scripts')

</body>
</html>