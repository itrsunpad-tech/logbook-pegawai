@extends('layouts.app')

@section('title', 'Rekap Sidik Jari')

@push('styles')
    <style>
        .sj-page {
            width: 100%;
            max-width: 960px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .sj-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .sj-title {
            color: var(--text-dark);
            font-size: 18px;
            font-weight: 700;
        }

        .sj-sub {
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
        }

        .sj-back {
            color: var(--accent);
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        /* Status bridge */
        .sj-status {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .sj-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #94a3b8;
            flex-shrink: 0;
        }

        .sj-dot.is-on {
            background: #16a34a;
            box-shadow: 0 0 0 4px rgba(22, 163, 74, .18);
        }

        .sj-dot.is-off {
            background: #dc2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, .15);
        }

        /* Angka ringkasan */
        .sj-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .sj-stat-value {
            margin-top: 8px;
            color: var(--accent);
            font-size: 28px;
            font-weight: 700;
        }

        .sj-stat-label {
            margin-top: 2px;
            color: var(--muted);
            font-size: 11px;
        }

        /* Daftar tap */
        .sj-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .sj-row {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .sj-row.is-new {
            animation: sj-flash 3.5s ease-out;
        }

        @keyframes sj-flash {
            from {
                background: var(--accent-soft);
                border-color: var(--accent-border);
            }

            to {
                background: #fff;
            }
        }

        .sj-time {
            min-width: 78px;
        }

        .sj-time-jam {
            color: var(--text-dark);
            font-size: 16px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .sj-time-tgl {
            color: var(--muted);
            font-size: 10px;
        }

        .sj-info {
            flex: 1;
            min-width: 160px;
        }

        .sj-name {
            color: var(--text-dark);
            font-size: 13px;
            font-weight: 700;
        }

        .sj-meta {
            margin-top: 2px;
            color: var(--muted);
            font-size: 11px;
        }

        .sj-chips {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .sj-chip {
            padding: 3px 9px;
            border-radius: 999px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .sj-chip--punch {
            background: var(--accent-soft);
            border-color: var(--accent-border);
            color: var(--accent);
        }

        .sj-empty {
            padding: 28px 12px;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
        }

        .is-hidden {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="sj-page" id="sj-app" data-url="{{ route('admin.sidik-jari.terbaru') }}" data-awal="{{ json_encode($awal) }}"
        data-status="{{ json_encode($status) }}">

        <div class="sj-head">
            <div>
                <h1 class="sj-title">Rekap Sidik Jari</h1>
                <p class="sj-sub">Absensi dari mesin sidik jari, diperbarui otomatis tiap beberapa detik.</p>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="sj-back">&larr; Dashboard</a>
        </div>

        {{-- Status koneksi bridge --}}
        <div class="lb-card sj-status">
            <span class="sj-dot" id="sj-dot"></span>
            <span id="sj-status-text">Memeriksa koneksi...</span>
        </div>

        {{-- Ringkasan hari ini --}}
        <div class="sj-stats">
            <div class="lb-card">
                <h2 class="lb-card-title"><i class="fa-solid fa-hand-pointer"></i> Tap hari ini</h2>
                <div class="sj-stat-value" id="sj-total">{{ $hariIni['total'] }}</div>
                <div class="sj-stat-label">Jumlah semua tap sejak 00:00 WIB</div>
            </div>

            <div class="lb-card">
                <h2 class="lb-card-title"><i class="fa-solid fa-users"></i> Pegawai hari ini</h2>
                <div class="sj-stat-value" id="sj-unik">{{ $hariIni['unik'] }}</div>
                <div class="sj-stat-label">Pegawai berbeda yang sudah tap</div>
            </div>
        </div>

        {{-- Daftar tap (diisi JavaScript) --}}
        <div class="sj-list" id="sj-list"></div>

        <div class="lb-card sj-empty" id="sj-empty">
            Belum ada data. Tempelkan jari di mesin untuk mencoba.
        </div>

    </div>

    <script>
        (function () {
            const app = document.getElementById('sj-app');
            const URL_TERBARU = app.dataset.url;
            const JEDA_MS = 3000;      // seberapa sering memeriksa data baru
            const MAKS_BARIS = 100;    // jumlah baris maksimal di layar

            const list = document.getElementById('sj-list');
            const empty = document.getElementById('sj-empty');
            const dot = document.getElementById('sj-dot');
            const statusText = document.getElementById('sj-status-text');
            const totalEl = document.getElementById('sj-total');
            const unikEl = document.getElementById('sj-unik');

            let lastId = 0;
            let timer = null;

            // Membuat elemen dengan textContent (aman dari HTML injection)
            function el(tag, className, text) {
                const node = document.createElement(tag);
                if (className) node.className = className;
                if (text !== undefined && text !== null) node.textContent = text;
                return node;
            }

            function buatBaris(row, baru) {
                const card = el('div', 'lb-card sj-row' + (baru ? ' is-new' : ''));

                const waktu = el('div', 'sj-time');
                waktu.append(el('div', 'sj-time-jam', row.waktu), el('div', 'sj-time-tgl', row.tanggal));

                const info = el('div', 'sj-info');
                info.append(el('div', 'sj-name', row.nama || ('ID mesin ' + row.fingerprint_id)));

                const meta = row.nama
                    ? (row.unit || 'Unit belum diisi') + ' · ID mesin ' + row.fingerprint_id
                    : 'Belum terdaftar di data pegawai';
                info.append(el('div', 'sj-meta', meta));

                const chips = el('div', 'sj-chips');
                chips.append(
                    el('span', 'sj-chip', row.verifikasi),
                    el('span', 'sj-chip sj-chip--punch', row.absensi)
                );

                card.append(waktu, info, chips);

                if (baru) {
                    setTimeout(function () { card.classList.remove('is-new'); }, 4000);
                }

                return card;
            }

            // rows harus berurutan dari lama ke baru; yang terbaru ditaruh paling atas
            function tambah(rows, baru) {
                rows.forEach(function (row) {
                    list.prepend(buatBaris(row, baru));
                    if (row.id > lastId) lastId = row.id;
                });

                while (list.children.length > MAKS_BARIS) {
                    list.removeChild(list.lastElementChild);
                }

                empty.classList.toggle('is-hidden', list.children.length > 0);
            }

            function formatDurasi(detik) {
                if (detik < 60) return detik + ' detik';
                if (detik < 3600) return Math.floor(detik / 60) + ' menit';
                return Math.floor(detik / 3600) + ' jam';
            }

            function ubahStatus(status) {
                dot.className = 'sj-dot ' + (status.online ? 'is-on' : 'is-off');

                if (status.online) {
                    statusText.textContent = 'Bridge terhubung (sinyal terakhir ' + formatDurasi(status.detik) + ' lalu)';
                } else if (status.detik === null) {
                    statusText.textContent = 'Bridge belum pernah terhubung. Jalankan skrip Python di komputer yang satu jaringan dengan mesin.';
                } else {
                    statusText.textContent = 'Bridge tidak mengirim sinyal sejak ' + formatDurasi(status.detik) + ' lalu.';
                }
            }

            function ubahRingkasan(hariIni) {
                totalEl.textContent = hariIni.total;
                unikEl.textContent = hariIni.unik;
            }

            function jadwalkan() {
                clearTimeout(timer);
                timer = setTimeout(periksa, JEDA_MS);
            }

            async function periksa() {
                // Tab tidak terlihat: lewati, hemat beban server
                if (document.hidden) {
                    jadwalkan();
                    return;
                }

                try {
                    const res = await fetch(URL_TERBARU + '?after=' + lastId, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                        cache: 'no-store'
                    });

                    if (res.status === 401 || res.status === 419) {
                        dot.className = 'sj-dot is-off';
                        statusText.textContent = 'Sesi login berakhir. Muat ulang halaman ini.';
                        return; // berhenti memeriksa
                    }

                    if (!res.ok) throw new Error('HTTP ' + res.status);

                    const data = await res.json();
                    tambah(data.rows, true);
                    ubahStatus(data.status);
                    ubahRingkasan(data.hari_ini);
                } catch (e) {
                    dot.className = 'sj-dot is-off';
                    statusText.textContent = 'Gagal memuat data dari server. Mencoba lagi...';
                }

                jadwalkan();
            }

            // Tampilan awal dari server, lalu mulai memeriksa data baru
            tambah(JSON.parse(app.dataset.awal), false);
            ubahStatus(JSON.parse(app.dataset.status));
            jadwalkan();
        })();
    </script>
@endsection