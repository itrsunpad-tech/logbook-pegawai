<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update klasifikasi kolom 'jenis' pada tabel shifts:
     * - Shift 1: Pagi Reguler, Siang Reguler, Malam Reguler
     * - Shift 2: Pagi Gizi & Linen, Siang Gizi & Linen
     * - Shift 3: Pagi Hemodialisa, Siang Hemodialisa
     * - Shift 4: Middle
     */
    public function up(): void
    {
        // Shift 1
        DB::table('shifts')
            ->whereIn('nama', ['Pagi Reguler', 'Siang Reguler', 'Malam Reguler'])
            ->update(['jenis' => 'Shift 1']);

        // Shift 2
        DB::table('shifts')
            ->whereIn('nama', ['Pagi Gizi & Linen', 'Siang Gizi & Linen', 'Pagi & Gizi Linen', 'Siang & Gizi Linen'])
            ->update(['jenis' => 'Shift 2']);

        // Shift 3
        DB::table('shifts')
            ->whereIn('nama', ['Pagi Hemodialisa', 'Siang Hemodialisa'])
            ->update(['jenis' => 'Shift 3']);

        // Shift 4
        DB::table('shifts')
            ->whereIn('nama', ['Middle', 'middle'])
            ->update(['jenis' => 'Shift 4']);
    }

    /**
     * Rollback: kembalikan jenis menjadi 'Shift'.
     */
    public function down(): void
    {
        DB::table('shifts')
            ->whereIn('jenis', ['Shift 1', 'Shift 2', 'Shift 3', 'Shift 4'])
            ->update(['jenis' => 'Shift']);
    }
};
