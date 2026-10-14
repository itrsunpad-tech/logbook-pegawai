<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ID pegawai di mesin sidik jari (user_id di mesin)
            $table->string('fingerprint_id', 30)->nullable()->unique()->after('email');

            // Pegawai hasil impor belum punya email, jadi email boleh kosong.
            // Index unique pada email tetap berlaku (MySQL mengizinkan banyak NULL).
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['fingerprint_id']);
            $table->dropColumn('fingerprint_id');

            // Gagal kalau sudah ada user tanpa email; isi/hapus dulu sebelum rollback
            $table->string('email')->nullable(false)->change();
        });
    }
};