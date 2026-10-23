<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            // --- Data pribadi ---
            $table->text('nik')->nullable();                 // terenkripsi (lihat cast di model)
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 30)->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->string('pendidikan_terakhir', 30)->nullable();
            $table->string('status_nikah', 30)->nullable();
            $table->unsignedTinyInteger('jumlah_anak')->nullable();
            $table->string('nama_ibu_kandung', 150)->nullable();
            $table->text('alamat_domisili')->nullable();
            $table->text('alamat_ktp')->nullable();
            $table->text('npwp')->nullable();                // terenkripsi

            // --- Pekerjaan ---
            $table->date('tanggal_mulai_kerja')->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->string('status_kepegawaian', 30)->nullable();

            // --- Perbankan ---
            $table->string('nama_bank', 50)->nullable();
            $table->text('nomor_rekening')->nullable();      // terenkripsi
            $table->string('nama_pemegang_rekening', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'nik', 'tempat_lahir', 'tanggal_lahir', 'agama', 'jenis_kelamin',
                'pendidikan_terakhir', 'status_nikah', 'jumlah_anak', 'nama_ibu_kandung',
                'alamat_domisili', 'alamat_ktp', 'npwp',
                'tanggal_mulai_kerja', 'jabatan', 'status_kepegawaian',
                'nama_bank', 'nomor_rekening', 'nama_pemegang_rekening',
            ]);
        });
    }
};