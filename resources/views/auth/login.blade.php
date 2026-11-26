<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-unpad.png') }}?v=2">
    <title>Login - Logbook Pegawai</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --accent: #0b57d0;
            --accent-dark: #0842a0;
            --ink: #101f35;
            --text: #26364b;
            --muted: #566b84;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: radial-gradient(ellipse at 15% 0%, rgba(37, 99, 235, 0.1), transparent 38%), #f2f5fa;
            color: var(--text);
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
        }

        /* Kartu */
        .card {
            width: 100%;
            max-width: 780px;
            min-height: min(460px, calc(100vh - 48px));
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            background: #fff;
            border: 1px solid #d7e1ef;
            border-radius: 24px;
            box-shadow: 0 12px 32px rgba(24, 49, 83, 0.12), 0 2px 5px rgba(24, 49, 83, 0.05);
            animation: rise 0.6s ease-out both;
        }

        /* Bagian atas biru dengan gradasi berlapis */
        .card-top {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            min-height: 100%;
            padding: 24px 30px 28px;
            text-align: left;
            color: #fff;
            /* [BARU] potong pendar yang bergerak & jadikan konteks tumpukan sendiri */
            overflow: hidden;
            isolation: isolate;
            background:
                radial-gradient(circle at 100% 0%, rgba(255, 255, 255, 0.28), transparent 50%),
                radial-gradient(circle at 0% 100%, rgba(56, 189, 248, 0.45), transparent 60%),
                linear-gradient(160deg, #1e3a8a 0%, #1d4ed8 35%, #2563eb 65%, #38bdf8 100%);
        }

        /* [BARU] Pendar gradasi yang bergerak pelan di belakang konten */
        .card-top::before {
            content: "";
            position: absolute;
            inset: -30%;
            z-index: -1;
            pointer-events: none;
            background:
                radial-gradient(circle at 30% 30%, rgba(129, 140, 248, 0.45), transparent 40%),
                radial-gradient(circle at 70% 75%, rgba(34, 211, 238, 0.40), transparent 42%);
            animation: drift 22s ease-in-out infinite alternate;
        }

        /* [BARU] Tanda plus yang melayang pelan ke atas (dekoratif) */
        .plus {
            position: absolute;
            top: 100%;
            z-index: -1;
            color: rgba(255, 255, 255, 0.18);
            font-weight: 800;
            line-height: 1;
            pointer-events: none;
            animation: plusRise 16s linear infinite;
        }

        .plus.p1 { left: 14%; font-size: 18px; animation-delay: 0s; }
        .plus.p2 { left: 52%; font-size: 12px; animation-delay: -6s; animation-duration: 19s; }
        .plus.p3 { left: 80%; font-size: 22px; animation-delay: -11s; animation-duration: 17s; }

        /* Wadah logo + riak lembut di sekitarnya */
        .icon-wrap {
            position: relative;
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
        }

        .brand { display:flex; align-items:center; gap:10px; font-size:14px; font-weight:800; }
        .brand .icon-wrap { border-radius:50%; background:rgba(255,255,255,.22); }

        .ripple {
            position: absolute;
            inset: 0;
            border: 1.5px solid rgba(255, 255, 255, 0.55);
            border-radius: 50%;
            opacity: 0;
            animation: ripple 3.6s ease-out infinite;
        }

        .ripple.r2 { animation-delay: 1.8s; }

        /* Logo UNPAD sebagai siluet putih */
        .card-top .icon {
            width: 30px;
            height: 30px;
            object-fit: contain;
            position: relative;
            filter: brightness(0) invert(1);
            animation: float 4.5s ease-in-out infinite;
        }

        .card-top h1 {
            margin-top: 42px;
            padding-top: 0;
            font-size: clamp(25px, 2.4vw, 30px);
            font-weight: 800;
            line-height: 1.15;
        }

        .card-top p {
            margin-top: 10px;
            color: rgba(255, 255, 255, 0.88);
            font-size: 12.5px;
            line-height: 1.6;
        }

        /* [BARU] Teks panel kiri muncul berurutan */
        .card-top .brand,
        .card-top h1,
        .card-top p {
            animation: welcomeReveal 0.55s cubic-bezier(.2, .7, .25, 1) both;
        }

        .card-top .brand { animation-delay: 0.05s; }
        .card-top h1     { animation-delay: 0.15s; }
        .card-top p      { animation-delay: 0.27s; }

        /* Siluet gedung RS: utuh, berdiri di tepi bawah area biru */
        .sil {
            display: block;
            height: auto;
            flex: 1 1 auto;
            align-self: center;
            width: 94%;
            max-height: 250px;
            margin: 18px auto 0;
        }

        .sil * {
            fill: none;
            stroke: #fff;
            stroke-width: 4;
            stroke-linejoin: round;
            stroke-linecap: round;
        }

        .sil .s,
        .sil .ln {
            stroke-opacity: 0.6;
            stroke-dasharray: 1;
            stroke-dashoffset: 1;
            animation: draw 1.6s 0.3s ease-out forwards;
        }

        .sil .s { animation: draw 1.6s 0.3s ease-out forwards, fillin 0.8s 1.5s ease forwards; }

        .sil .win {
            stroke-width: 10;
            stroke-dasharray: 12 9;
            stroke-opacity: 0.12;
            animation: glow 5.5s ease-in-out infinite;
        }

        .sil .ground { stroke-opacity: 0.45; }

        /* Logo RS di fasad: muncul pelan setelah garis gedung selesai */
        .sil .late { opacity: 0; animation: lateIn 0.8s 1.4s ease forwards; }

        /* Bagian bawah putih */
        .card-body { position:relative; display:flex; flex-direction:column; justify-content:center; padding:36px 30px 48px; }
        .card-body > * { animation: welcomeReveal .55s cubic-bezier(.2,.7,.25,1) both; }
        .card-body > *:nth-child(1) { animation-delay:.12s; }
        .card-body > *:nth-child(2) { animation-delay:.22s; }
        .card-body > *:nth-child(3) { animation-delay:.32s; }
        .card-body > *:nth-child(4) { animation-delay:.42s; }

        .card-body h2 {
            color: var(--ink);
            font-size: clamp(20px, 2.2vw, 26px);
            font-weight: 800;
            text-align: center;
        }

        .welcome-subtitle { margin-top:8px; color:#8293aa; font-size:12px; line-height:1.5; text-align:center; }

        .alert {
            margin-top: 14px;
            padding: 10px 12px;
            border-radius: 8px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 13px;
            line-height: 1.55;
            /* [BARU] bergetar sebentar agar pesan error terlihat */
            animation: shake 0.4s ease-out both;
        }

        .google-button {
            width: 100%;
            height: 50px;
            margin-top: 22px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            background: #fff;
            color: var(--ink);
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            /* [BARU] memotong kilau yang menyapu tombol */
            overflow: hidden;
            transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.2s ease, transform 0.15s ease;
        }

        /* [BARU] Kilau satu kali setelah reveal selesai */
        .google-button::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(110deg, transparent 35%, rgba(37, 99, 235, 0.12) 50%, transparent 65%);
            transform: translateX(-120%);
            animation: shine 1.2s 1.1s ease-out 1 forwards;
        }

        .google-button:hover {
            background: #f8fafc;
            border-color: var(--accent);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.16);
            transform: translateY(-1px);
        }

        .google-button:active { transform: translateY(0) scale(0.99); }

        .google-button .label { white-space: nowrap; }

        .google-button .arrow {
            position: absolute;
            right: 16px;
            color: var(--accent);
            font-size: 13px;
            opacity: 0;
            transform: translateX(-6px);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        /* Panah bergeser halus saat hover (berhenti saat kursor keluar) */
        .google-button:hover .arrow {
            opacity: 1;
            transform: translateX(0);
            animation: nudge 1.2s ease-in-out infinite;
        }

        .google-button:hover .google-icon { transform: scale(1.08); }

        .google-button:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

        .google-icon { width: 18px; height: 18px; flex-shrink: 0; transition: transform 0.2s ease; }

        /* Spinner untuk status loading */
        .spinner {
            display: none;
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            border: 2px solid rgba(11, 87, 208, 0.25);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        /* Status loading: tombol tidak bisa diklik ulang */
        .google-button.is-loading {
            cursor: progress;
            pointer-events: none;
            background: #f8fafc;
        }

        .google-button.is-loading .google-icon,
        .google-button.is-loading .arrow { display: none; }

        .google-button.is-loading .spinner { display: inline-block; }

        /* [BARU] Panel kiri ikut merespons saat tombol sedang loading */
        .card.is-loading .ripple { animation-duration: 1.4s; }
        .card.is-loading .ripple.r2 { animation-delay: 0.7s; }
        .card.is-loading .sil .win { animation-duration: 1.6s; }

        .note {
            margin-top: 16px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 6px;
            color: var(--muted);
            font-size: 11.5px;
            line-height: 1.5;
        }

        .note i { margin-top: 3px; color: var(--accent); font-size: 13px; }

        /* Animasi halus */
        @keyframes rise {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes welcomeReveal {
            from { opacity:0; transform:translateY(8px); }
            to { opacity:1; transform:translateY(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-3px); }
        }

        @keyframes draw {
            0%   { stroke-dashoffset: 1; stroke-opacity: 0; }
            5%   { stroke-opacity: 0.6; }
            100% { stroke-dashoffset: 0; stroke-opacity: 0.6; }
        }

        @keyframes fillin {
            to { fill: rgba(255, 255, 255, 0.10); }
        }

        @keyframes ripple {
            0%   { transform: scale(0.7); opacity: 0.55; }
            100% { transform: scale(2.1); opacity: 0; }
        }

        @keyframes lateIn {
            to { opacity: 1; }
        }

        @keyframes glow {
            0%, 100% { stroke-opacity: 0.12; }
            40%, 60% { stroke-opacity: 0.6; }
        }

        @keyframes nudge {
            0%, 100% { transform: translateX(0); }
            50%      { transform: translateX(3px); }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* [BARU] Pendar gradasi bergerak pelan */
        @keyframes drift {
            from { transform: translate3d(-4%, -3%, 0) rotate(0deg) scale(1); }
            to   { transform: translate3d(5%, 4%, 0) rotate(12deg) scale(1.08); }
        }

        /* [BARU] Tanda plus naik perlahan lalu memudar */
        @keyframes plusRise {
            0%       { transform: translateY(0) rotate(0deg); opacity: 0; }
            10%, 85% { opacity: 1; }
            100%     { transform: translateY(-540px) rotate(90deg); opacity: 0; }
        }

        /* [BARU] Kilau menyapu tombol */
        @keyframes shine {
            to { transform: translateX(120%); }
        }

        /* [BARU] Pesan error bergetar */
        @keyframes shake {
            0%   { transform: translateX(0); }
            25%  { transform: translateX(-5px); }
            50%  { transform: translateX(4px); }
            75%  { transform: translateX(-2px); }
            100% { transform: translateX(0); }
        }

        @media (max-width: 680px) {
            body { padding:12px; }
            .card { max-width:440px; min-height:calc(100vh - 24px); grid-template-columns:1fr; grid-template-rows:minmax(260px, 42vh) 1fr; border-radius:22px; }
            .card-top { padding:22px 26px 18px; }
            .card-top h1 { margin-top:24px; padding-top:0; font-size:27px; }
            .card-top p { font-size:13px; }
            .sil { flex:1 1 auto; width:76%; max-height:38%; margin:12px auto 0; }
            .card-body { padding:28px 25px 58px; }
            .card-body h2 { font-size:22px; }
            .google-button { margin-top:22px; }
            /* [BARU] tanda plus disembunyikan di layar kecil */
            .plus { display: none; }
        }

        /* Hormati pengguna yang mematikan animasi */
        @media (prefers-reduced-motion: reduce) {
            .card, .card-top .icon, .card-body > * { animation: none; }
            .ripple { animation: none; display: none; }
            .sil .late { animation: none; opacity: 1; }
            .sil .s, .sil .ln { animation: none; stroke-dashoffset: 0; }
            .sil .s { fill: rgba(255, 255, 255, 0.10); }
            .sil .win { animation: none; stroke-opacity: 0.25; }
            .google-button:hover .arrow { animation: none; }
            .google-button, .google-button .arrow, .google-icon { transition: none; }
            .spinner { animation-duration: 1.6s; }

            /* [BARU] animasi tambahan ikut dimatikan */
            .card-top .brand, .card-top h1, .card-top p,
            .card-top::before, .alert { animation: none; }
            .plus, .google-button::after { display: none; }
        }
    </style>
</head>

<body>

    <main class="card">

        <header class="card-top">
            <!-- [BARU] Tanda plus melayang (dekoratif) -->
            <span class="plus p1" aria-hidden="true">+</span>
            <span class="plus p2" aria-hidden="true">+</span>
            <span class="plus p3" aria-hidden="true">+</span>

            <div class="brand">
                <div class="icon-wrap" aria-hidden="true">
                    <span class="ripple"></span>
                    <span class="ripple r2"></span>
                    <img src="{{ asset('images/logo-unpad.png') }}" alt="" class="icon">
                </div>
                <span>RS UNPAD</span>
            </div>
            <h1>Logbook Pegawai</h1>
            <p>Catat kegiatan harian dan absensi Anda</p>

            <!-- Siluet gedung rumah sakit -->
            <svg class="sil" viewBox="260 262 900 445" aria-hidden="true">
                <defs>
                    <filter id="logo-mono" color-interpolation-filters="sRGB">
                        <feColorMatrix type="matrix"
                            values="0 0 0 0 1
                                0 0 0 0 1
                                0 0 0 0 1
                                0 0 0 1 0"/>
                    </filter>
                </defs>

                <rect class="s" pathLength="1" x="445" y="270" width="430" height="45"/>
                <rect class="s" pathLength="1" x="440" y="315" width="540" height="100"/>
                <rect class="s" pathLength="1" x="875" y="280" width="100" height="135"/>
                <path class="ln" pathLength="1" d="M900 290 V408 M925 290 V408 M950 290 V408"/>
                <rect class="s" pathLength="1" x="390" y="415" width="600" height="58"/>
                <rect class="s" pathLength="1" x="277" y="473" width="645" height="27"/>
                <path class="ln" pathLength="1" d="M325 500 V595 M425 500 V595 M520 500 V595 M615 500 V595 M710 500 V595 M805 500 V595 M900 500 V595"/>
                <path class="s" pathLength="1" d="M905 485 H1012 L1148 568 V586 H905 Z"/>
                <path class="ln" pathLength="1" d="M953 586 V645 M1033 586 V645 M1113 586 V645"/>
                <path class="s" pathLength="1" d="M300 597 H700 L735 625 H260 Z"/>

                <!-- jendela menyala bergantian -->
                <g class="late">
                    <path class="win" d="M456 346 H558" style="animation-delay:0s"/>
                    <path class="win" d="M558 346 H660" style="animation-delay:-2.1s"/>
                    <path class="win" d="M660 346 H762" style="animation-delay:-3.4s"/>
                    <path class="win" d="M762 346 H864" style="animation-delay:-1.2s"/>
                    <path class="win" d="M456 390 H558" style="animation-delay:-4.2s"/>
                    <path class="win" d="M558 390 H660" style="animation-delay:-0.6s"/>
                    <path class="win" d="M660 390 H762" style="animation-delay:-2.8s"/>
                    <path class="win" d="M762 390 H864" style="animation-delay:-3.8s"/>
                    <path class="win" d="M575 444 H675" style="animation-delay:-1.7s"/>
                    <path class="win" d="M675 444 H775" style="animation-delay:-4.6s"/>
                    <path class="win" d="M775 444 H875" style="animation-delay:-2.4s"/>
                    <path class="win" d="M875 444 H975" style="animation-delay:-0.9s"/>
                </g>
                <g class="late">
                    <!-- logo RS UNPAD: warna bawaan + outline putih tipis -->
                    <image href="{{ asset('images/logo-rsup-unpad.png') }}" x="410" y="421" width="130" height="47" preserveAspectRatio="xMidYMid meet" filter="url(#logo-mono)" opacity="0.75"/>
                </g>
                <path class="ln ground" pathLength="1" d="M262 700 H1150"/>
            </svg>
        </header>

        <section class="card-body">
            <h2>Selamat Datang Kembali</h2>
            <!-- [BARU] Teks cadangan diganti tanggal hari ini lewat JavaScript di bawah -->
            <p class="welcome-subtitle" id="welcome-date">Catat kegiatan harian dan absensi Anda.</p>

            @if ($errors->any() || session('error'))
                <div class="alert" role="alert">
                    {{ session('error') ?? $errors->first() }}
                </div>
            @endif

            <a href="{{ route('google.login') }}" class="google-button" id="google-login">
                <svg class="google-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#4285F4" d="M21.35 12.27c0-.78-.07-1.53-.22-2.25H12v4.26h5.23a4.47 4.47 0 0 1-1.94 2.93v2.44h3.14c1.84-1.69 2.92-4.18 2.92-7.38z"/>
                    <path fill="#34A853" d="M12 21.9c2.63 0 4.83-.87 6.44-2.35l-3.14-2.44c-.87.58-1.98.93-3.3.93-2.54 0-4.69-1.72-5.46-4.03H3.3v2.52A9.73 9.73 0 0 0 12 21.9z"/>
                    <path fill="#FBBC05" d="M6.54 14.01a5.86 5.86 0 0 1 0-3.74V7.75H3.3a9.8 9.8 0 0 0 0 8.78l3.24-2.52z"/>
                    <path fill="#EA4335" d="M12 6.24c1.43 0 2.71.49 3.72 1.46l2.79-2.79C16.82 3.35 14.62 2.4 12 2.4a9.73 9.73 0 0 0-8.7 5.35l3.24 2.52C7.31 7.96 9.46 6.24 12 6.24z"/>
                </svg>
                <span class="spinner" aria-hidden="true"></span>
                <span class="label">Login dengan Google</span>
                <i class="fa-solid fa-arrow-right arrow" aria-hidden="true"></i>
            </a>

            <p class="note">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                <span>Gunakan akun Google yang sudah terdaftar.</span>
            </p>
        </section>

    </main>

    <script>
        (function () {
            // [BARU] Tampilkan tanggal hari ini (mengikuti jam perangkat pengguna)
            var dateEl = document.getElementById('welcome-date');
            if (dateEl) {
                try {
                    dateEl.textContent = new Intl.DateTimeFormat('id-ID', {
                        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
                    }).format(new Date());
                } catch (err) { /* biarkan teks cadangan */ }
            }

            var btn = document.getElementById('google-login');
            if (!btn) return;

            var card = document.querySelector('.card');
            var label = btn.querySelector('.label');
            var originalText = label.textContent;

            function setLoading(on) {
                btn.classList.toggle('is-loading', on);
                // [BARU] panel kiri ikut bereaksi saat loading
                if (card) card.classList.toggle('is-loading', on);
                btn.setAttribute('aria-busy', on ? 'true' : 'false');
                btn.setAttribute('aria-disabled', on ? 'true' : 'false');
                label.textContent = on ? 'Menghubungkan...' : originalText;
            }

            btn.addEventListener('click', function (e) {
                // Cegah klik ganda saat proses sedang berjalan
                if (btn.classList.contains('is-loading')) {
                    e.preventDefault();
                    return;
                }
                setLoading(true);
            });

            // Reset tombol jika halaman dikembalikan dari cache (misalnya tombol Back)
            window.addEventListener('pageshow', function (e) {
                if (e.persisted) setLoading(false);
            });
        })();
    </script>

</body>
</html>
