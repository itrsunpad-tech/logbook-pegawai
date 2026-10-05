<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Logbook Pegawai</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --accent: #2563eb;
            --accent-dark: #1d4ed8;
            --ink: #0f172a;
            --text: #475569;
            --muted: #64748b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #f1f5f9;
            color: var(--text);
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
        }

        /* ===== Kartu ===== */
        .card {
            width: 100%;
            max-width: 320px;
            overflow: hidden;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 14px 34px rgba(29, 78, 216, 0.14);
            animation: rise 0.6s ease-out both;
        }

        /* Bagian atas biru */
        .card-top {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 32px 24px 0;
            text-align: center;
            color: #fff;
            background:
                radial-gradient(circle at 85% 0%, rgba(255, 255, 255, 0.22), transparent 55%),
                linear-gradient(165deg, #1d4ed8 0%, #2563eb 50%, #38bdf8 100%);
        }

        /* Riak lembut di sekitar ikon */
        .icon-wrap {
            position: relative;
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
        }

        .ripple {
            position: absolute;
            inset: 0;
            border: 1.5px solid rgba(255, 255, 255, 0.55);
            border-radius: 50%;
            opacity: 0;
            animation: ripple 3.6s ease-out infinite;
        }

        .ripple.r2 { animation-delay: 1.8s; }

        .card-top .icon {
            font-size: 32px;
            line-height: 1;
            animation: float 4.5s ease-in-out infinite;
        }

        .card-top h1 {
            margin-top: 14px;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.3;
        }

        .card-top p {
            margin-top: 6px;
            color: rgba(255, 255, 255, 0.88);
            font-size: 12.5px;
            line-height: 1.6;
        }

        /* Siluet gedung RS: utuh, berdiri di tepi bawah area biru */
        .sil {
            display: block;
            width: 76%;
            height: auto;
            margin-top: 20px;
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

        /* Garis denyut (ECG): garis dasar redup + denyut terang yang berjalan pelan */
        .sil .ecg-base { stroke-width: 4; stroke-opacity: 0.28; }

        .sil .ecg-pulse {
            stroke-width: 5;
            stroke-opacity: 0.95;
            stroke-dasharray: 0.16 0.84;
            stroke-dashoffset: 0.16;
            animation: pulse 3.6s linear infinite;
        }


        /* Bagian bawah putih */
        .card-body { padding: 22px 26px 24px; }

        .card-body h2 {
            color: var(--ink);
            font-size: 14px;
            font-weight: 600;
            text-align: center;
        }

        .alert {
            margin-top: 14px;
            padding: 10px 12px;
            border-radius: 8px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 12px;
            line-height: 1.55;
        }

        .google-button {
            width: 100%;
            height: 44px;
            margin-top: 16px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            color: var(--ink);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.2s ease, transform 0.15s ease;
        }

        .google-button:hover {
            background: #f8fafc;
            border-color: var(--accent);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.16);
            transform: translateY(-1px);
        }

        .google-button:active { transform: translateY(0) scale(0.99); }

        .google-button .arrow {
            position: absolute;
            right: 16px;
            color: var(--accent);
            font-size: 12px;
            opacity: 0;
            transform: translateX(-6px);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .google-button:hover .arrow { opacity: 1; transform: translateX(0); }
        .google-button:hover .google-icon { transform: scale(1.08); }

        .google-button:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

        .google-icon { width: 18px; height: 18px; flex-shrink: 0; transition: transform 0.2s ease; }

        .note {
            margin-top: 14px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 7px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.6;
        }

        .note i { margin-top: 3px; color: var(--accent); font-size: 12px; }

        /* ===== Animasi halus ===== */
        @keyframes rise {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
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

        @keyframes pulse {
            from { stroke-dashoffset: 0.16; }
            to   { stroke-dashoffset: -0.84; }
        }

        @keyframes glow {
            0%, 100% { stroke-opacity: 0.12; }
            40%, 60% { stroke-opacity: 0.6; }
        }

        @media (max-width: 420px) {
            .card-top { padding: 28px 22px 0; }
            .card-body { padding: 20px 22px 22px; }
        }

        /* Hormati pengguna yang mematikan animasi */
        @media (prefers-reduced-motion: reduce) {
            .card, .card-top .icon { animation: none; }
            .ripple { animation: none; display: none; }
            .sil .late { animation: none; opacity: 1; }
            .sil .ecg-pulse { animation: none; stroke-dasharray: none; stroke-opacity: 0.6; }
            .sil .s, .sil .ln { animation: none; stroke-dashoffset: 0; }
            .sil .s { fill: rgba(255, 255, 255, 0.10); }
            .sil .win { animation: none; stroke-opacity: 0.25; }
            .google-button, .google-button .arrow, .google-icon { transition: none; }
        }
    </style>
</head>

<body>

    <main class="card">

        <header class="card-top">
            <div class="icon-wrap" aria-hidden="true">
                <span class="ripple"></span>
                <span class="ripple r2"></span>
                <i class="fa-solid fa-book icon"></i>
            </div>
            <h1>Logbook Pegawai</h1>
            <p>Catat kegiatan harian dan absensi Anda.</p>

            <!-- Siluet gedung rumah sakit -->
            <svg class="sil" viewBox="260 262 900 520" aria-hidden="true">
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
                    <!-- logo RS UNPAD: warna bawaan + outline putih tipis agar terbaca di atas biru -->
                    <image href="{{ asset('images/logo-rsup-unpad.png') }}" x="410" y="421" width="130" height="47" preserveAspectRatio="xMidYMid meet" filter="url(#logo-mono)" opacity="0.75"/>
                </g>
                <path class="ln ground" pathLength="1" d="M262 700 H1150"/>
                <path class="ecg-base" d="M262 738 H480 L505 738 L520 720 L536 768 L556 702 L576 774 L592 738 L615 738 H800 L825 738 L840 720 L856 768 L876 702 L896 774 L912 738 L935 738 H1150"/>
                <path class="ecg-pulse" pathLength="1" d="M262 738 H480 L505 738 L520 720 L536 768 L556 702 L576 774 L592 738 L615 738 H800 L825 738 L840 720 L856 768 L876 702 L896 774 L912 738 L935 738 H1150"/>
            </svg>
        </header>

        <section class="card-body">
            <h2>Masuk ke aplikasi</h2>

            @if ($errors->any() || session('error'))
                <div class="alert" role="alert">
                    {{ session('error') ?? $errors->first() }}
                </div>
            @endif

            <a href="{{ route('google.login') }}" class="google-button">
                <svg class="google-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#4285F4" d="M21.35 12.27c0-.78-.07-1.53-.22-2.25H12v4.26h5.23a4.47 4.47 0 0 1-1.94 2.93v2.44h3.14c1.84-1.69 2.92-4.18 2.92-7.38z"/>
                    <path fill="#34A853" d="M12 21.9c2.63 0 4.83-.87 6.44-2.35l-3.14-2.44c-.87.58-1.98.93-3.3.93-2.54 0-4.69-1.72-5.46-4.03H3.3v2.52A9.73 9.73 0 0 0 12 21.9z"/>
                    <path fill="#FBBC05" d="M6.54 14.01a5.86 5.86 0 0 1 0-3.74V7.75H3.3a9.8 9.8 0 0 0 0 8.78l3.24-2.52z"/>
                    <path fill="#EA4335" d="M12 6.24c1.43 0 2.71.49 3.72 1.46l2.79-2.79C16.82 3.35 14.62 2.4 12 2.4a9.73 9.73 0 0 0-8.7 5.35l3.24 2.52C7.31 7.96 9.46 6.24 12 6.24z"/>
                </svg>
                <span>Login dengan Google</span>
                <i class="fa-solid fa-arrow-right arrow" aria-hidden="true"></i>
            </a>

            <p class="note">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                <span>Gunakan akun Google yang sudah terdaftar.</span>
            </p>
        </section>

    </main>

</body>
</html>