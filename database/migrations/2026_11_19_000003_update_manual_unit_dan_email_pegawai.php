<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pembaruan data email manual untuk pegawai.
     * Format: [id => [email, nama_pegawai]]
     */
    private array $emails = [
        1   => ['test@example.com', 'Test User'],
        2   => ['itrsunpad@gmail.com', 'IT RS UNPAD'],
        3   => ['dghaz.xrpl5@gmail.com', 'Ilmi Fathurrahman Ghazali, S.Kom'],
        4   => ['smix2572@gmail.com', 'Red MiX'],
        15  => ['aliphyasuryadi.putri13@gmail.com', 'Nur Aliphya Suryadi Putr'],
        20  => ['lidyaapuspaa2002@gmail.com', 'Lidya Puspa Sari'],
        21  => ['sandimrahman02@gmail.com', 'Sandi Muhamad Rahman'],
        24  => ['shofirah933@gmail.com', 'Shofirah'],
        28  => ['juanalfonsoensi@gmail.com', 'Juan Alfonso'],
        29  => ['nailahrifdahthea@gmail.com', 'Nailah Rifdah Ramadhani'],
        64  => ['syahranisyafitri247@gmail.com', 'Syahrani Syafitri'],
        78  => ['sportajang@gmail.com', 'Ajang Muhaemin'],
        83  => ['widiwahyudin98@gmail.com', 'Widi Wahyudin'],
        84  => ['tohirinilyas@upi.edu', 'Ilyas Tohirin'],
        119 => ['dentydewi12@gmail.com', 'Denty Dustira Saridewi'],
        121 => ['alawiyah1501@gmail.com', 'Melin Alawiyah'],
        170 => ['aldimridwan07@gmail.com', 'Aldi Muhammad Ridwan'],
        177 => ['hyldasr@gmail.com', 'Hilda Sri Bungawaty'],
        178 => ['nadhiraauliarrahman@gmail.com', 'Nadhira Aulia Rahman'],
        251 => ['daffasyidadwahyudi@gmail.com', 'Muhammad Daffa S W'],
        276 => ['farsyaghassanisaffah01@gmail.com', 'Farsya Ghassani'],
        313 => ['tiaraamndasyif01@gmail.com', 'Tiara Amanda Syifa Sahr'],
        327 => ['dr.putrisenna@gmail.com', 'dr Putri Senna Rahayu'],
    ];

    /**
     * Pembaruan data unit kerja manual untuk pegawai.
     * Format: [id => [unit, nama_pegawai]]
     */
    private array $units = [
        3   => ['Gizi', 'Ilmi Fathurrahman Ghazali, S.Kom'],
        9   => ['Staff IT', 'Helmy Yanuar'],
        15  => ['Staff Keuangan', 'Nur Aliphya Suryadi Putr'],
        20  => ['Staff Human Capital', 'Lidya Puspa Sari'],
        21  => ['Staff Legal', 'Sandi Muhamad Rahman'],
        28  => ['Staff Rekam Medis', 'Juan Alfonso'],
        29  => ['Staff Rekam Medis', 'Nailah Rifdah Ramadhani'],
        84  => ['Staff Sarana dan Prasarana (Sarpras)', 'Ilyas Tohirin'],
        170 => ['Staff Perawat', 'Aldi Muhammad Ridwan'],
        251 => ['Staff Marketing', 'Muhammad Daffa S W'],
        276 => ['Staff HSSE', 'Farsya Ghassani'],
        328 => ['Satuan Pengawas Internal (SPI)', 'Gwendy Dale Henry'],
    ];

    public function up(): void
    {
        // 1. Update data email
        foreach ($this->emails as $id => [$email, $name]) {
            DB::table('users')
                ->where('id', $id)
                ->update([
                    'email'      => strtolower(trim($email)),
                    'updated_at' => now(),
                ]);
        }

        // 2. Update data unit kerja
        foreach ($this->units as $id => [$unit, $name]) {
            DB::table('users')
                ->where('id', $id)
                ->update([
                    'employee_unit' => trim($unit),
                    'updated_at'    => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Sengaja kosong: data manual yang diisi tidak perlu di-null kan secara otomatis
    }
};
