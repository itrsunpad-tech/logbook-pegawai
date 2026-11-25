<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Konversi users.work_hour_type dari nilai lama ('Office Hour' / 'Shift') ke jenis jadwal spesifik,
     * berdasarkan kolom "Absen" di KEBUTUHAN DATA KEPEGAWAIAN SISTEM LOGBOOK RS.xlsx.
     * Format: 'Jenis Jam Kerja' => [id user, ...]
     */
    private array $mapping = [
        'Office Hour' => [
            3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 19, 20, 21, 22, 23, 24,
            25, 26, 27, 28, 29, 31, 44, 48, 49, 50, 51, 52, 65, 66, 67, 70, 71, 72,
            73, 74, 84, 96, 124, 125, 126, 128, 129, 130, 131, 147, 159, 160, 230,
            231, 251, 261, 270, 271, 272, 276, 280, 299, 300, 306, 327, 328,
        ],
        'Shift Reguler' => [
            30, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 45, 46, 47, 53, 54, 55,
            56, 68, 69, 75, 76, 77, 79, 80, 81, 82, 83, 86, 87, 88, 89, 90, 91, 92,
            93, 94, 95, 97, 98, 99, 100, 101, 102, 103, 104, 106, 107, 108, 109,
            110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123,
            132, 133, 134, 135, 136, 137, 138, 139, 140, 142, 143, 144, 145, 146,
            148, 149, 150, 151, 152, 153, 154, 155, 156, 161, 162, 163, 164, 165,
            166, 167, 169, 170, 171, 172, 173, 174, 175, 176, 177, 178, 179, 180,
            181, 182, 184, 185, 186, 187, 188, 189, 190, 191, 192, 194, 195, 196,
            197, 198, 199, 200, 201, 202, 203, 205, 207, 208, 209, 210, 211, 212,
            213, 214, 215, 216, 217, 218, 219, 220, 221, 222, 223, 224, 225, 226,
            227, 228, 229, 233, 234, 235, 236, 237, 240, 241, 242, 245, 246, 247,
            248, 249, 250, 253, 254, 255, 256, 257, 258, 259, 260, 264, 265, 266,
            267, 268, 269, 274, 275, 277, 278, 279, 281, 282, 283, 284, 285, 286,
            287, 288, 289, 290, 292, 293, 294, 295, 296, 297, 298, 301, 302, 303,
            305, 308, 310, 311, 312, 313, 314, 315, 316, 317, 318, 319, 320, 321,
            322, 323, 324, 325, 326, 329, 331, 332,
        ],
        'Shift Rajal' => [
            57, 58, 59, 61, 62, 63, 64, 127, 158, 239, 243,
        ],
        'Shift Gizi & Linen' => [
            157, 204, 232, 273,
        ],
        'Shift Hemodialisa' => [
            60, 193, 238,
        ],
        'NA dan Ambulans' => [
            78, 85, 168, 206, 244, 252, 291, 309, 330,
        ],
    ];

    /** Cadangan untuk nilai lama 'Shift' yang tidak ada di Excel: ikuti instalasi pegawai. */
    private array $perInstalasi = [
        'Shift Gizi & Linen' => ['Instalasi Gizi'],
        'Shift Hemodialisa'  => ['Instalasi Hemodialisa'],
        'Shift Rajal'        => ['Instalasi Rawat Jalan'],
        'NA dan Ambulans'    => ['Ners Assistan', 'Driver Ambulance', 'Pemusalaran Jenazah', 'Penjaga Kantin'],
    ];

    public function up(): void
    {
        // 1. Jenis shift spesifik: boleh menggantikan nilai kosong atau 'Shift' (nilai lama yang belum spesifik).
        foreach (['Shift Reguler', 'Shift Rajal', 'Shift Gizi & Linen', 'Shift Hemodialisa', 'NA dan Ambulans'] as $jenis) {
            DB::table('users')
                ->whereIn('id', $this->mapping[$jenis])
                ->where(function ($q) {
                    $q->whereNull('work_hour_type')
                        ->orWhere('work_hour_type', '')
                        ->orWhere('work_hour_type', 'Shift');
                })
                ->update(['work_hour_type' => $jenis, 'updated_at' => now()]);
        }

        // 2. Office Hour: hanya mengisi yang masih kosong, pilihan HC tidak ditimpa.
        DB::table('users')
            ->whereIn('id', $this->mapping['Office Hour'])
            ->where(function ($q) {
                $q->whereNull('work_hour_type')->orWhere('work_hour_type', '');
            })
            ->update(['work_hour_type' => 'Office Hour', 'updated_at' => now()]);

        // 3. Sisa nilai lama 'Shift' (mis. diubah HC setelah isian awal): ikuti instalasi.
        foreach ($this->perInstalasi as $jenis => $instalasi) {
            DB::table('users')
                ->where('work_hour_type', 'Shift')
                ->whereIn('employee_instalasi', $instalasi)
                ->update(['work_hour_type' => $jenis, 'updated_at' => now()]);
        }

        DB::table('users')
            ->where('work_hour_type', 'Shift')
            ->update(['work_hour_type' => 'Shift Reguler', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Sengaja kosong: tidak bisa dibedakan mana yang diisi migration ini dan mana yang diatur HC.
    }
};