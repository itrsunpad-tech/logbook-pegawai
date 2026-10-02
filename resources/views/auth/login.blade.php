<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Logbook Pegawai</title>

    <style>
        :root {
            --accent: #2563eb;
            --accent-light: #3b82f6;
            --accent-dark: #1d4ed8;
            --accent-sky: #38bdf8;
            --accent-soft: #f1f6ff;

            --text-dark: #0f172a;
            --text: #475569;
            --muted: #94a3b8;
            --border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Latar: putih kebiruan + kotak halus + cahaya biru lembut */
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at 12% 18%, rgba(37, 99, 235, 0.12), transparent 38%),
                radial-gradient(circle at 88% 82%, rgba(56, 189, 248, 0.14), transparent 40%),
                linear-gradient(rgba(37, 99, 235, 0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37, 99, 235, 0.035) 1px, transparent 1px),
                #f6f9ff;
            background-size: auto, auto, 32px 32px, 32px 32px, auto;
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        .login-wrapper {
            width: 100%;
            max-width: 390px;
            padding: 20px;
        }

        /* Card */
        .login-card {
            position: relative;
            overflow: hidden;
            padding: 40px 34px 30px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow:
                0 1px 2px rgba(15, 23, 42, 0.04),
                0 18px 40px rgba(37, 99, 235, 0.10);
        }

        /* Garis aksen di atas card */
        .login-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-dark), var(--accent-light), var(--accent-sky));
        }

        /* Header */
        .card-header {
            text-align: center;
        }

        .brand-badge {
            width: 54px;
            height: 54px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            background: linear-gradient(145deg, var(--accent-light), var(--accent-dark));
            color: #ffffff;
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.30);
        }

        .brand-badge svg {
            width: 26px;
            height: 26px;
        }

        .card-header h1 {
            color: var(--text-dark);
            font-size: 21px;
            font-weight: 700;
            letter-spacing: 0.8px;
            line-height: 1.3;
        }

        .card-header h1 span {
            color: var(--accent);
        }

        .card-header p {
            margin-top: 10px;
            color: var(--text);
            font-size: 13px;
            line-height: 1.6;
        }

        /* Isi */
        .card-body {
            margin-top: 34px;
        }

        .card-body h2 {
            margin-bottom: 16px;
            color: var(--text-dark);
            font-size: 15px;
            font-weight: 700;
            text-align: center;
        }

        /* Tombol utama */
        .google-button {
            width: 100%;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            border-radius: 11px;
            background: linear-gradient(180deg, var(--accent-light), var(--accent));
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.28);
            transition: transform 0.15s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .google-button:hover {
            background: linear-gradient(180deg, var(--accent), var(--accent-dark));
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.36);
            transform: translateY(-1px);
        }

        .google-button:active {
            transform: translateY(0);
        }

        .google-button:focus-visible {
            outline: 3px solid rgba(37, 99, 235, 0.3);
            outline-offset: 3px;
        }

        .google-icon-wrap {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 50%;
            background: #ffffff;
        }

        .google-icon {
            width: 16px;
            height: 16px;
        }

        /* Info */
        .info-box {
            margin-top: 22px;
            padding: 14px 16px;
            display: flex;
            align-items: flex-start;
            gap: 11px;
            border-left: 3px solid var(--accent);
            border-radius: 4px 10px 10px 4px;
            background: var(--accent-soft);
            color: var(--text);
            font-size: 13px;
            line-height: 1.65;
        }

        .info-icon {
            width: 16px;
            height: 16px;
            margin-top: 3px;
            flex-shrink: 0;
            color: var(--accent);
        }

        /* Footer */
        .card-footer {
            margin-top: 28px;
            padding-top: 18px;
            border-top: 1px solid #f1f5f9;
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 480px) {
            .login-wrapper {
                padding: 14px;
            }

            .login-card {
                padding: 34px 24px 26px;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <div class="login-card">

            <!-- Header -->
            <div class="card-header">
                <div class="brand-badge">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path
                            fill="currentColor"
                            d="M6 2h11a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm1 4v2h8V6H7zm0 4v2h8v-2H7zm0 4v2h5v-2H7z"
                        />
                    </svg>
                </div>

                <h1>LOGBOOK <span>PEGAWAI</span></h1>
                <p>Catatan kegiatan harian dan absensi pegawai</p>
            </div>

            <!-- Isi -->
            <div class="card-body">

                <h2>Masuk ke aplikasi</h2>

                <a href="{{ route('google.login') }}" class="google-button">
                    <span class="google-icon-wrap">
                        <svg
                            class="google-icon"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                            aria-hidden="true"
                        >
                            <path
                                fill="#4285F4"
                                d="M21.35 12.27c0-.78-.07-1.53-.22-2.25H12v4.26h5.23a4.47 4.47 0 0 1-1.94 2.93v2.44h3.14c1.84-1.69 2.92-4.18 2.92-7.38z"
                            />
                            <path
                                fill="#34A853"
                                d="M12 21.9c2.63 0 4.83-.87 6.44-2.35l-3.14-2.44c-.87.58-1.98.93-3.3.93-2.54 0-4.69-1.72-5.46-4.03H3.3v2.52A9.73 9.73 0 0 0 12 21.9z"
                            />
                            <path
                                fill="#FBBC05"
                                d="M6.54 14.01a5.86 5.86 0 0 1 0-3.74V7.75H3.3a9.8 9.8 0 0 0 0 8.78l3.24-2.52z"
                            />
                            <path
                                fill="#EA4335"
                                d="M12 6.24c1.43 0 2.71.49 3.72 1.46l2.79-2.79C16.82 3.35 14.62 2.4 12 2.4a9.73 9.73 0 0 0-8.7 5.35l3.24 2.52C7.31 7.96 9.46 6.24 12 6.24z"
                            />
                        </svg>
                    </span>

                    <span>Login dengan Google</span>
                </a>

                <!-- Info -->
                <div class="info-box">
                    <svg class="info-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path
                            fill="currentColor"
                            d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1.1 15h-2.2v-6h2.2v6zM12 9.5a1.3 1.3 0 1 1 0-2.6 1.3 1.3 0 0 1 0 2.6z"
                        />
                    </svg>

                    <span>Gunakan akun Google yang sudah terdaftar untuk masuk.</span>
                </div>

                <div class="card-footer">&copy; {{ date('Y') }} Logbook Pegawai</div>

            </div>

        </div>
    </div>

</body>
</html>