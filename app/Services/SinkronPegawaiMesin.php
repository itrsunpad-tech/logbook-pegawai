<?php

namespace App\Services;

use App\Models\User;

/**
 * Menambahkan pegawai dari mesin sidik jari ke tabel users.
 *
 * Dipakai bersama oleh:
 *  - penerima data dari bridge Python (sinkron otomatis), dan
 *  - perintah "php artisan pegawai:import-mesin" (cadangan lewat file CSV).
 *
 * Aturan:
 *  - Pegawai dikenali lewat fingerprint_id (ID di mesin).
 *  - Yang SUDAH ada tidak pernah diubah, jadi nama yang sudah dirapikan HC aman.
 *  - Tidak pernah menghapus apa pun.
 */
class SinkronPegawaiMesin
{
    private const POTONGAN = 500;

    /**
     * @param  iterable<array{fingerprint_id: mixed, nama: mixed}>  $daftar
     * @return array{baru: int, dilewati: int, tidak_valid: int, nama_ganda: list<string>}
     */
    public function sinkron(iterable $daftar): array
    {
        $tidakValid = 0;
        $kandidat = [];       // fingerprint_id => nama (kemunculan pertama yang dipakai)
        $namaTerlihat = [];   // nama (huruf kecil) => id mesin pertama, untuk deteksi nama ganda
        $namaGanda = [];

        foreach ($daftar as $item) {
            $id = trim((string) ($item['fingerprint_id'] ?? ''));
            $nama = self::rapikanNama($item['nama'] ?? '');

            if ($id === '' || $nama === '') {
                $tidakValid++;

                continue;
            }

            if (array_key_exists($id, $kandidat)) {
                continue; // ID yang sama muncul dua kali di daftar ini
            }

            $kandidat[$id] = $nama;

            // Nama ganda tetap dimasukkan (bisa dua orang berbeda), tapi dilaporkan supaya HC bisa memeriksa
            $kunci = mb_strtolower($nama);
            if (isset($namaTerlihat[$kunci])) {
                $namaGanda[] = "\"{$nama}\" (ID mesin {$namaTerlihat[$kunci]} dan {$id})";
            } else {
                $namaTerlihat[$kunci] = $id;
            }
        }

        $baru = 0;
        $sekarang = now();

        // preserve_keys = true: kunci array (ID mesin) tetap ikut
        foreach (array_chunk($kandidat, self::POTONGAN, true) as $potongan) {
            // PHP mengubah kunci angka menjadi int, jadi dipaksa kembali ke string
            // supaya MySQL membandingkan sebagai teks (bukan angka)
            $ids = array_map('strval', array_keys($potongan));

            $sudahAda = array_flip(
                User::whereIn('fingerprint_id', $ids)->pluck('fingerprint_id')->all()
            );

            $baris = [];
            foreach ($potongan as $id => $nama) {
                $id = (string) $id;

                if (isset($sudahAda[$id])) {
                    continue;
                }

                $baris[] = [
                    'name'           => $nama,
                    'fingerprint_id' => $id,
                    'role'           => User::ROLE_PEGAWAI,
                    'created_at'     => $sekarang,
                    'updated_at'     => $sekarang,
                ];
            }

            // insertOrIgnore: kalau ada proses lain yang menyisipkan ID yang sama di saat bersamaan,
            // barisnya dilewati, bukan error
            if ($baris !== []) {
                $baru += User::query()->insertOrIgnore($baris);
            }
        }

        return [
            'baru'        => $baru,
            'dilewati'    => count($kandidat) - $baru,
            'tidak_valid' => $tidakValid,
            'nama_ganda'  => $namaGanda,
        ];
    }

    /**
     * Merapikan nama dari mesin: spasi ganda dibuang, dan nama yang seluruhnya huruf kecil
     * dijadikan "Huruf Awal Besar". Nama yang sudah campur huruf besar/kecil tidak diubah.
     */
    public static function rapikanNama(mixed $nama): string
    {
        $nama = trim((string) preg_replace('/\s+/u', ' ', (string) $nama));

        if ($nama !== '' && $nama === mb_strtolower($nama)) {
            $nama = mb_convert_case($nama, MB_CASE_TITLE, 'UTF-8');
        }

        return mb_substr($nama, 0, 255);
    }
}