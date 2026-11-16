<?php

namespace App\Console\Commands;

use App\Services\SinkronPegawaiMesin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPegawaiMesin extends Command
{
    protected $signature = 'pegawai:import-mesin
                            {file : Path file CSV hasil export mesin}
                            {--dry-run : Simulasi saja, tidak menyimpan apa pun}';

    protected $description = 'Impor pegawai dari CSV mesin sidik jari ke tabel users (cadangan; biasanya sudah otomatis lewat bridge)';

    public function handle(SinkronPegawaiMesin $sinkron): int
    {
        $path = $this->argument('file');

        if (! is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        if ($header === false) {
            $this->error('File CSV kosong.');

            return self::FAILURE;
        }

        // File dari export Python memakai utf-8-sig, jadi sel pertama diawali karakter BOM tak terlihat
        $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        $kolom = array_flip($header);

        if (! isset($kolom['user_id'], $kolom['nama'])) {
            $this->error('Kolom "user_id" dan "nama" tidak ditemukan di CSV.');

            return self::FAILURE;
        }

        $daftar = [];

        while (($baris = fgetcsv($handle)) !== false) {
            $daftar[] = [
                'fingerprint_id' => $baris[$kolom['user_id']] ?? '',
                'nama'           => $baris[$kolom['nama']] ?? '',
            ];
        }

        fclose($handle);

        // Transaksi supaya --dry-run bisa dibatalkan (rollback) setelah simulasi
        $dryRun = (bool) $this->option('dry-run');

        DB::beginTransaction();
        $hasil = $sinkron->sinkron($daftar);
        $dryRun ? DB::rollBack() : DB::commit();

        foreach ($hasil['nama_ganda'] as $pesan) {
            $this->warn("Nama sama: {$pesan}");
        }

        if ($dryRun) {
            $this->info('[DRY RUN] Tidak ada data yang disimpan.');
        }

        $this->newLine();
        $this->table(['Hasil', 'Jumlah'], [
            ['Pegawai baru'.($dryRun ? ' (simulasi)' : ''), $hasil['baru']],
            ['Dilewati (ID mesin sudah ada)', $hasil['dilewati']],
            ['Baris tidak valid', $hasil['tidak_valid']],
        ]);

        return self::SUCCESS;
    }
}