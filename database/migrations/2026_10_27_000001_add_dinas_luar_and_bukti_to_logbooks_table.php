<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->boolean('is_dinas_luar')->default(false)->after('is_wfh');
            $table->string('bukti_path')->nullable()->after('is_dinas_luar');
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn(['is_dinas_luar', 'bukti_path']);
        });
    }
};
