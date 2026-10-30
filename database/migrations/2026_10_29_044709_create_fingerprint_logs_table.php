<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fingerprint_logs', function (Blueprint $table) {
            $table->id();

            // ID pegawai di mesin (sama dengan users.fingerprint_id)
            $table->string('fingerprint_id', 30);

            // Waktu tap. Disimpan dalam UTC, sama seperti kolom timestamps lainnya.
            $table->dateTime('punched_at');

            $table->unsignedSmallInteger('verify_type')->nullable();  // metode: sidik jari, wajah, dst.
            $table->unsignedSmallInteger('punch_state')->nullable();  // masuk, pulang, dst.
            $table->unsignedBigInteger('machine_uid')->nullable();    // nomor urut log di mesin
            $table->string('device', 50)->nullable();                 // IP mesin

            $table->timestamps();

            // Mencegah data ganda kalau bridge mengirim ulang
            $table->unique(['fingerprint_id', 'punched_at']);
            $table->index('punched_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fingerprint_logs');
    }
};