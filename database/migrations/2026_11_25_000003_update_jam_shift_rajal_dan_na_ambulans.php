<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rajal
        $this->simpan('Shift Rajal', 'Pagi Rajal', 'Shift 5', '07:00:00', '14:00:00', 12);
        $this->simpan(null, 'Siang Rajal', 'Shift 5', '13:00:00', '20:00:00', 13);

        // NA dan Ambulans
        $this->simpan('NA dan Ambulans', 'Pagi NA dan Ambulans', 'Shift 6', '07:00:00', '19:00:00', 14);
        $this->simpan(null, 'Malam NA dan Ambulans', 'Shift 6', '19:00:00', '07:00:00', 15);
    }

    public function down(): void
    {
        DB::table('shifts')->where('nama', 'Pagi Rajal')->update([
            'nama' => 'Shift Rajal', 'jam_mulai' => null, 'jam_selesai' => null, 'urutan' => 12,
        ]);
        DB::table('shifts')->where('nama', 'Pagi NA dan Ambulans')->update([
            'nama' => 'NA dan Ambulans', 'jam_mulai' => null, 'jam_selesai' => null, 'urutan' => 13,
        ]);
        DB::table('shifts')->whereIn('nama', ['Siang Rajal', 'Malam NA dan Ambulans'])->delete();
    }

    /**
     * Isi/ubah satu shift: pakai baris yang sudah bernama $nama, atau ganti nama baris lama
     * ($namaLama) agar id-nya tetap, atau buat baris baru.
     */
    private function simpan(?string $namaLama, string $nama, string $jenis, string $mulai, string $selesai, int $urutan): void
    {
        $data = [
            'nama'        => $nama,
            'jenis'       => $jenis,
            'jam_mulai'   => $mulai,
            'jam_selesai' => $selesai,
            'is_kerja'    => 1,
            'urutan'      => $urutan,
            'updated_at'  => now(),
        ];

        if (DB::table('shifts')->where('nama', $nama)->exists()) {
            DB::table('shifts')->where('nama', $nama)->update($data);

            return;
        }

        if ($namaLama !== null && DB::table('shifts')->where('nama', $namaLama)->exists()) {
            DB::table('shifts')->where('nama', $namaLama)->update($data);

            return;
        }

        DB::table('shifts')->insert($data + ['created_at' => now()]);
    }
};