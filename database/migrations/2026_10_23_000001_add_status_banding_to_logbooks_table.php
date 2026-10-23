<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->string('status_banding', 20)->nullable()->after('keterangan_pegawai');
        });

        DB::table('logbooks')
            ->whereNotNull('keterangan_pegawai')
            ->where('status', 'Menunggu')
            ->update(['status_banding' => 'Menunggu']);

        DB::table('logbooks')
            ->whereNotNull('keterangan_pegawai')
            ->where('status', 'Disetujui')
            ->update(['status_banding' => 'Disetujui']);

        DB::table('logbooks')
            ->whereNotNull('keterangan_pegawai')
            ->where('status', 'Ditolak')
            ->update(['status_banding' => 'Ditolak']);
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn('status_banding');
        });
    }
};
