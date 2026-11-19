<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ubah penamaan unit dari 'Staff IPSRS' menjadi 'Staff Sarana dan Prasarana (Sarpras)'.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('employee_unit', 'Staff IPSRS')
            ->update([
                'employee_unit' => 'Staff Sarana dan Prasarana (Sarpras)',
            ]);
    }

    /**
     * Kembalikan perubahan jika rollback.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('employee_unit', 'Staff Sarana dan Prasarana (Sarpras)')
            ->update([
                'employee_unit' => 'Staff IPSRS',
            ]);
    }
};
