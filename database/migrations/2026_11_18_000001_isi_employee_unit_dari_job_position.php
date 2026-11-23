<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Isi users.employee_unit yang masih kosong, diambil dari kolom
     * "Job Position" pada file KEBUTUHAN DATA HR (bukan kolom UNIT KERJA).
     *
     * Format: 'Unit' => [id user, ...]
     * Hasil pencocokan nama pegawai (DB) dengan nama di Excel.
     */
    private array $mapping = [
        'Kepala Casemix' => [280],
        'Kepala Instalasi Farmasi' => [66],
        'Kepala Instalasi Gizi' => [230],
        'Koordinator Bidan' => [37],
        'Koordinator Perawat Bedah Sentral' => [229],
        'Koordinator Perawat Perinatologi' => [265],
        'Koordinator Perawat Rawat Inap' => [150],
        'Manajer Human Capital' => [18],
        'Manajer Keperawatan' => [141],
        'Staff Administrasi' => [116, 151, 160, 162],
        'Staff Ambulans' => [168],
        'Staff Bidan' => [38, 91, 92, 137, 234, 248, 249],
        'Staff CSSD' => [133, 209],
        'Staff Casemix' => [22, 71, 125, 126],
        'Staff Coder' => [72],
        'Staff Farmasi' => [35, 65, 67, 68, 69, 77, 86, 87, 88, 156, 190, 235, 240, 254, 259],
        'Staff Gizi' => [157, 204, 231, 232],
        'Staff HSSE' => [131],
        'Staff Human Capital' => [5, 13, 16],
        'Staff IPSRS' => [30, 48, 83, 89, 163, 264],
        'Staff IT' => [7, 8, 10, 11],
        'Staff Keuangan' => [6, 14, 56, 70, 73, 74, 93, 94, 128, 129],
        'Staff Laboratorium' => [53, 54, 95, 149, 220, 255],
        'Staff Legal' => [124],
        'Staff Linen' => [132],
        'Staff Logistik' => [12, 19, 49],
        'Staff Marketing' => [31, 44, 51, 52, 96],
        'Staff NA' => [78, 85, 244, 252],
        'Staff Pendaftaran' => [32, 33, 45, 46, 47, 75, 76, 82, 106, 139, 140, 206, 246, 247],
        'Staff Perawat' => [
            34, 36, 57, 58, 59, 60, 61, 62, 63, 79, 90, 97, 98, 99, 100, 101, 102,
            103, 104, 105, 107, 108, 109, 110, 111, 112, 113, 114, 115, 117, 118,
            120, 121, 122, 123, 127, 130, 134, 135, 136, 138, 142, 143, 144, 145,
            146, 147, 152, 153, 154, 155, 158, 159, 161, 164, 165, 166, 167, 169,
            171, 172, 173, 174, 175, 176, 179, 180, 181, 182, 183, 184, 185, 186,
            187, 188, 189, 191, 192, 193, 194, 195, 196, 197, 198, 199, 200, 201,
            202, 203, 205, 207, 208, 210, 211, 212, 213, 214, 215, 216, 217, 218,
            219, 221, 222, 223, 224, 225, 226, 227, 228, 236, 237, 238, 239, 241,
            242, 243, 245, 253, 258, 260, 266, 267, 268, 269, 283,
        ],
        'Staff Radiologi' => [40, 41, 42, 43, 80, 81, 233],
        'Staff Rekam Medis' => [26, 27],
        'Staff Sekretaris' => [50],
    ];

    public function up(): void
    {
        // Catatan: Migrasi berbasis ID ini telah digantikan secara menyeluruh oleh migrasi:
        // 2026_11_23_000001_sync_all_users_unit_and_email.php
        // yang mencocokkan data langsung via Nama Pegawai (bukan auto-increment ID).
    }

    public function down(): void
    {
        // Sengaja kosong: tidak bisa dibedakan mana yang diisi migration ini
        // dan mana yang sudah diisi manual, jadi tidak aman di-rollback otomatis.
    }
};