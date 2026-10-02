<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbooks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('jenis', 10); // harian | lembur | oncall

            $table->date('tanggal');

            $table->foreignId('shift_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();

            $table->text('ringkasan');

            $table->boolean('is_wfh')->default(false);

            // Untuk memastikan 1 logbook harian
            // per user per tanggal.
            $table->unsignedTinyInteger('harian_unik')->nullable();

            $table->timestamps();

            $table->unique([
                'user_id',
                'tanggal',
                'harian_unik',
            ]);

            $table->index([
                'user_id',
                'tanggal',
                'jenis',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbooks');
    }
};