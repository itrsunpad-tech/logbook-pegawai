<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom instalasi dan jabatan pada tabel users,
     * sejajar dengan kolom employee_unit (unit kerja).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'employee_instalasi')) {
                $table->string('employee_instalasi', 100)->nullable()->after('employee_unit');
            }

            if (! Schema::hasColumn('users', 'employee_jabatan')) {
                $table->string('employee_jabatan', 150)->nullable()->after('employee_instalasi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['employee_instalasi', 'employee_jabatan']);
        });
    }
};
