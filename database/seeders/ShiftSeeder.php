<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            // Office Hour
            [
                'nama' => 'OH Reguler',
                'jenis' => 'Office Hour',
                'jam_mulai' => '08:00',
                'jam_selesai' => '16:00',
                'is_kerja' => true,
                'urutan' => 1,
            ],
            // Shift
            [
                'nama' => 'Pagi Reguler',
                'jenis' => 'Shift 1',
                'jam_mulai' => '07:00',
                'jam_selesai' => '14:00',
                'is_kerja' => true,
                'urutan' => 2,
            ],
            [
                'nama' => 'Siang Reguler',
                'jenis' => 'Shift 1',
                'jam_mulai' => '14:00',
                'jam_selesai' => '21:00',
                'is_kerja' => true,
                'urutan' => 3,
            ],
            [
                'nama' => 'Malam Reguler',
                'jenis' => 'Shift 1',
                'jam_mulai' => '21:00',
                'jam_selesai' => '07:00',
                'is_kerja' => true,
                'urutan' => 4,
            ],
            [
                'nama' => 'Pagi Gizi & Linen',
                'jenis' => 'Shift 2',
                'jam_mulai' => '06:00',
                'jam_selesai' => '13:00',
                'is_kerja' => true,
                'urutan' => 5,
            ],
            [
                'nama' => 'Siang Gizi & Linen',
                'jenis' => 'Shift 2',
                'jam_mulai' => '13:00',
                'jam_selesai' => '20:00',
                'is_kerja' => true,
                'urutan' => 6,
            ],
            [
                'nama' => 'Pagi Hemodialisa',
                'jenis' => 'Shift 3',
                'jam_mulai' => '05:00',
                'jam_selesai' => '12:00',
                'is_kerja' => true,
                'urutan' => 7,
            ],
            [
                'nama' => 'Siang Hemodialisa',
                'jenis' => 'Shift 3',
                'jam_mulai' => '12:00',
                'jam_selesai' => '19:00',
                'is_kerja' => true,
                'urutan' => 8,
            ],
            [
                // Aturan Middle: Jam berapapun yang penting 7 jam, jam_mulai dan jam_selesai dikosongkan
                'nama' => 'Middle',
                'jenis' => 'Shift 4',
                'jam_mulai' => null,
                'jam_selesai' => null,
                'is_kerja' => true,
                'urutan' => 9,
            ],
            // Status Umum
            [
                'nama' => 'Sakit',
                'jenis' => null,
                'jam_mulai' => null,
                'jam_selesai' => null,
                'is_kerja' => false,
                'urutan' => 10,
            ],
            [
                'nama' => 'Izin',
                'jenis' => null,
                'jam_mulai' => null,
                'jam_selesai' => null,
                'is_kerja' => false,
                'urutan' => 11,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(
                ['nama' => $shift['nama']],
                $shift
            );
        }

        // Remap logbook jika masih ada yang merujuk shift lama
        $remap = [
            'OH IT,Gizi'  => 'OH Reguler',
            'OH Lab'      => 'OH Reguler',
            'Office Hour' => 'OH Reguler',
            'Pagi'        => 'Pagi Reguler',
            'Siang'       => 'Siang Reguler',
            'Malam'       => 'Malam Reguler',
        ];

        foreach ($remap as $oldName => $targetName) {
            $oldShift = Shift::where('nama', $oldName)->first();
            $targetShift = Shift::where('nama', $targetName)->first();

            if ($oldShift && $targetShift) {
                DB::table('logbooks')->where('shift_id', $oldShift->id)->update([
                    'shift_id' => $targetShift->id,
                ]);
            }
        }

        $liburShift = Shift::where('nama', 'LIBUR')->first();
        if ($liburShift) {
            DB::table('logbooks')->where('shift_id', $liburShift->id)->update([
                'shift_id' => null,
            ]);
        }

        // Hapus shift yang diinstruksikan untuk dihapus
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

        Shift::whereIn('nama', $deletedShifts)->delete();
    }
}
