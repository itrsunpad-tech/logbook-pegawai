<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom jenis setelah nama (string biasa/nullable, bukan enum di DB)
        Schema::table('shifts', function (Blueprint $table) {
            $table->string('jenis', 50)->nullable()->after('nama');
        });

        // 2. Pastikan shift OH Reguler tersedia sebagai target remapping
        $ohRegulerId = DB::table('shifts')->where('nama', 'OH Reguler')->value('id');
        if (! $ohRegulerId) {
            $ohRegulerId = DB::table('shifts')->insertGetId([
                'nama' => 'OH Reguler',
                'jenis' => 'Office Hour',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '16:00:00',
                'is_kerja' => 1,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('shifts')->where('id', $ohRegulerId)->update([
                'jenis' => 'Office Hour',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '16:00:00',
                'is_kerja' => 1,
                'urutan' => 1,
                'updated_at' => now(),
            ]);
        }

        // Remap logbook lama yang menggunakan shift yang akan dihapus ke shift yang tetap dipakai
        $remap = [
            'OH IT,Gizi'  => 'OH Reguler',
            'OH Lab'      => 'OH Reguler',
            'Office Hour' => 'OH Reguler',
            'Pagi'        => 'Pagi Reguler',
            'Siang'       => 'Siang Reguler',
            'Malam'       => 'Malam Reguler',
        ];

        foreach ($remap as $oldName => $targetName) {
            $oldShift = DB::table('shifts')->where('nama', $oldName)->first();
            $targetShift = DB::table('shifts')->where('nama', $targetName)->first();

            if ($oldShift && $targetShift) {
                DB::table('logbooks')->where('shift_id', $oldShift->id)->update([
                    'shift_id' => $targetShift->id,
                ]);
            }
        }

        // Untuk shift LIBUR, lepaskan relasi di logbook menjadi null
        $liburShift = DB::table('shifts')->where('nama', 'LIBUR')->first();
        if ($liburShift) {
            DB::table('logbooks')->where('shift_id', $liburShift->id)->update([
                'shift_id' => null,
            ]);
        }

        // 3. Update atau insert shift-shift yang tetap dipakai & shift baru Middle
        $shiftsData = [
            [
                'nama' => 'OH Reguler',
                'jenis' => 'Office Hour',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '16:00:00',
                'is_kerja' => 1,
                'urutan' => 1,
            ],
            [
                'nama' => 'Pagi Reguler',
                'jenis' => 'Shift',
                'jam_mulai' => '07:00:00',
                'jam_selesai' => '14:00:00',
                'is_kerja' => 1,
                'urutan' => 2,
            ],
            [
                'nama' => 'Siang Reguler',
                'jenis' => 'Shift',
                'jam_mulai' => '14:00:00',
                'jam_selesai' => '21:00:00',
                'is_kerja' => 1,
                'urutan' => 3,
            ],
            [
                'nama' => 'Malam Reguler',
                'jenis' => 'Shift',
                'jam_mulai' => '21:00:00',
                'jam_selesai' => '07:00:00',
                'is_kerja' => 1,
                'urutan' => 4,
            ],
            [
                'nama' => 'Pagi Gizi & Linen',
                'jenis' => 'Shift',
                'jam_mulai' => '06:00:00',
                'jam_selesai' => '13:00:00',
                'is_kerja' => 1,
                'urutan' => 5,
            ],
            [
                'nama' => 'Siang Gizi & Linen',
                'jenis' => 'Shift',
                'jam_mulai' => '13:00:00',
                'jam_selesai' => '20:00:00',
                'is_kerja' => 1,
                'urutan' => 6,
            ],
            [
                'nama' => 'Pagi Hemodialisa',
                'jenis' => 'Shift',
                'jam_mulai' => '05:00:00',
                'jam_selesai' => '12:00:00',
                'is_kerja' => 1,
                'urutan' => 7,
            ],
            [
                'nama' => 'Siang Hemodialisa',
                'jenis' => 'Shift',
                'jam_mulai' => '12:00:00',
                'jam_selesai' => '19:00:00',
                'is_kerja' => 1,
                'urutan' => 8,
            ],
            [
                // Aturan Middle: Jam berapapun yang penting 7 jam, jam_mulai dan jam_selesai dikosongkan
                'nama' => 'Middle',
                'jenis' => 'Shift',
                'jam_mulai' => null,
                'jam_selesai' => null,
                'is_kerja' => 1,
                'urutan' => 9,
            ],
            [
                'nama' => 'Sakit',
                'jenis' => null,
                'jam_mulai' => null,
                'jam_selesai' => null,
                'is_kerja' => 0,
                'urutan' => 10,
            ],
            [
                'nama' => 'Izin',
                'jenis' => null,
                'jam_mulai' => null,
                'jam_selesai' => null,
                'is_kerja' => 0,
                'urutan' => 11,
            ],
        ];

        foreach ($shiftsData as $shift) {
            $existing = DB::table('shifts')->where('nama', $shift['nama'])->first();
            if ($existing) {
                DB::table('shifts')->where('id', $existing->id)->update([
                    'jenis' => $shift['jenis'],
                    'jam_mulai' => $shift['jam_mulai'],
                    'jam_selesai' => $shift['jam_selesai'],
                    'is_kerja' => $shift['is_kerja'],
                    'urutan' => $shift['urutan'],
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('shifts')->insert(array_merge($shift, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 4. Hapus shift yang diminta untuk dihapus
        $deletedShifts = [
            'OH IT,Gizi',
            'OH Lab',
            'Office Hour',
            'Pagi',
            'Siang',
            'Malam',
            'LIBUR',
            'Shift Ambulance',
            'Shift NA Pagi',
            'Shift NA Malam',
        ];

        DB::table('shifts')->whereIn('nama', $deletedShifts)->delete();
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};

