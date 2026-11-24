<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom "Kategori" (employee_category) sudah tidak dipakai lagi.
     * Catatan: nilai yang tersimpan di kolom ini ikut terhapus.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'employee_category')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('employee_category');
            });
        }
    }

    /**
     * Rollback hanya mengembalikan strukturnya (kosong); isi lama tidak bisa dipulihkan.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'employee_category')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('employee_category', 100)->nullable()->after('role');
            });
        }
    }
};
