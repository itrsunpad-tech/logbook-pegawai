<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
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
                'nama' => 'Middle',
                'jam_mulai' => '10:00',
                'jam_selesai' => '17:00',
                'is_kerja' => true,
                'urutan' => 5,
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
    }
}