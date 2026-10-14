<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPegawaiMesin extends Command
{
    protected $signature = 'pegawai:import-mesin
                            {file : Path file CSV hasil export mesin}
                            {--dry-run : Simulasi saja, tidak menyimpan apa pun}';

    protected $description = 'Impor pegawai dari CSV mesin sidik jari ke tabel users';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (! is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        // File dari export Python memakai utf-8-sig, jadi sel pertama diawali karakter BOM tak terlihat
        $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        $kolom = array_flip($header);

        if (! isset($kolom['user_id'], $kolom['nama'])) {
            $this->error('Kolom "user_id" dan "nama" tidak ditemukan di CSV.');

            return self::FAILURE;
        }

        $dibuat = 0;
        $dilewati = 0;
        $tidakValid = 0;
        $namaTerlihat = []; // nama (huruf kecil) => id mesin pertama, untuk deteksi nama ganda

        DB::beginTransaction();

        while (($baris = fgetcsv($handle)) !== false) {
            $fingerprintId = trim($baris[$kolom['user_id']] ?? '');
            $nama = trim(preg_replace('/\s+/', ' ', $baris[$kolom['nama']] ?? ''));

            if ($fingerprintId === '' || $nama === '') {
                $tidakValid++;
                continue;
            }

            // Nama yang semuanya huruf kecil dirapikan jadi "Huruf Awal Besar"
            if ($nama === mb_strtolower($nama)) {
                $nama = mb_convert_case($nama, MB_CASE_TITLE, 'UTF-8');
            }

            // Peringatan nama ganda (tetap diimpor, HC yang memutuskan)
            $kunciNama = mb_strtolower($nama);
            if (isset($namaTerlihat[$kunciNama])) {
                $this->warn("Nama sama: \"{$nama}\" (ID mesin {$namaTerlihat[$kunciNama]} dan {$fingerprintId})");
            } else {
                $namaTerlihat[$kunciNama] = $fingerprintId;
            }

            // Sudah pernah diimpor / sudah ditautkan ke akun: jangan disentuh,
            // supaya nama yang sudah diedit HC tidak tertimpa kalau impor dijalankan ulang
            if (User::where('fingerprint_id', $fingerprintId)->exists()) {
                $dilewati++;
                continue;
            }

            // forceFill melewati $fillable, karena fingerprint_id sengaja tidak dibuat fillable
            (new User)->forceFill([
                'name'           => $nama,
                'fingerprint_id' => $fingerprintId,
            ])->save();

            $dibuat++;
        }

        fclose($handle);

        if ($this->option('dry-run')) {
            DB::rollBack();
            $this->info('[DRY RUN] Tidak ada data yang disimpan.');
        } else {
            DB::commit();
        }

        $this->newLine();
        $this->table(['Hasil', 'Jumlah'], [
            ['Pegawai baru'.($this->option('dry-run') ? ' (simulasi)' : ''), $dibuat],
            ['Dilewati (ID mesin sudah ada)', $dilewati],
            ['Baris tidak valid', $tidakValid],
        ]);

        return self::SUCCESS;
    }
}