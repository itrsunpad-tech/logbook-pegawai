<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Jam mulai/selesai sengaja dikosongkan dulu, menunggu konfirmasi HC.
        $shiftBaru = [
            ['nama' => 'Shift Rajal',      'jenis' => 'Shift 5', 'urutan' => 12],
            ['nama' => 'NA dan Ambulans',  'jenis' => 'Shift 6', 'urutan' => 13],
        ];

        foreach ($shiftBaru as $shift) {
            if (DB::table('shifts')->where('nama', $shift['nama'])->exists()) {
                continue;
            }

            DB::table('shifts')->insert([
                'nama'        => $shift['nama'],
                'jenis'       => $shift['jenis'],
                'jam_mulai'   => null,
                'jam_selesai' => null,
                'is_kerja'    => 1,
                'urutan'      => $shift['urutan'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('shifts')->whereIn('nama', ['Shift Rajal', 'NA dan Ambulans'])->delete();
    }
};