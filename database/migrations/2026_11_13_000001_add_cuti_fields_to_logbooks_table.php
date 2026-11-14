<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->boolean('is_cuti')->default(false)->after('is_dinas_luar');
            $table->date('cuti_selesai')->nullable()->after('is_cuti');
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn(['is_cuti', 'cuti_selesai']);
        });
    }
};
