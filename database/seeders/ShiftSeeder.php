<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $sistemKerja = [
            ['OH Reguler', '08:00', '16:00'],
            ['OH IT,Gizi', '08:00', '16:00'],
            ['OH Lab', '08:00', '16:00'],
            ['Pagi Reguler', '07:00', '14:00'],
            ['Siang Reguler', '14:00', '21:00'],
            ['Malam Reguler', '21:00', '07:00'],
            ['Pagi Gizi & Linen', '06:00', '13:00'],
            ['Siang Gizi & Linen', '13:00', '20:00'],
            ['Pagi Hemodialisa', '05:00', '12:00'],
            ['Siang Hemodialisa', '12:00', '19:00'],
        ];

        foreach ($sistemKerja as $urutan => [$nama, $mulai, $selesai]) {
            Shift::updateOrCreate(
                ['nama' => $nama],
                [
                    'jam_mulai' => $mulai,
                    'jam_selesai' => $selesai,
                    'is_kerja' => true,
                    'urutan' => $urutan + 1,
                ]
            );
        }

        $shifts = [
            [
                'nama' => 'Office Hour',
                'jam_mulai' => '08:00',
                'jam_selesai' => '16:00',
                'is_kerja' => true,
                'urutan' => 1,
            ],
            [
                'nama' => 'Pagi',
                'jam_mulai' => '07:00',
                'jam_selesai' => '14:00',
                'is_kerja' => true,
                'urutan' => 2,
            ],
            [
                'nama' => 'Siang',
                'jam_mulai' => '14:00',
                'jam_selesai' => '21:00',
                'is_kerja' => true,
                'urutan' => 3,
            ],
            [
                'nama' => 'Malam',
                'jam_mulai' => '21:00',
                'jam_selesai' => '07:00',
                'is_kerja' => true,
                'urutan' => 4,
            ],
            [
                'nama' => 'Shift Ambulance',
                'jam_mulai' => '08:00',
                'jam_selesai' => '20:00',
                'is_kerja' => true,
                'urutan' => 6,
            ],
            [
                'nama' => 'Shift NA Pagi',
                'jam_mulai' => '07:00',
                'jam_selesai' => '14:00',
                'is_kerja' => true,
                'urutan' => 7,
            ],
            [
                'nama' => 'Shift NA Malam',
                'jam_mulai' => '21:00',
                'jam_selesai' => '07:00',
                'is_kerja' => true,
                'urutan' => 8,
            ],
            [
                'nama' => 'LIBUR',
                'jam_mulai' => null,
                'jam_selesai' => null,
                'is_kerja' => false,
                'urutan' => 9,
            ],
            [
                'nama' => 'Sakit',
                'jam_mulai' => null,
                'jam_selesai' => null,
                'is_kerja' => false,
                'urutan' => 10,
            ],
            [
                'nama' => 'Izin',
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

        $middle = Shift::query()->where('nama', 'Middle')->first();
        if ($middle && ! DB::table('logbooks')->where('shift_id', $middle->id)->exists()) {
            $middle->delete();
        }
    }
}
