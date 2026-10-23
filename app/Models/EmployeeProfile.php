<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProfile extends Model
{
    // Pilihan isian (ubah di sini kalau perlu menambah/mengurangi opsi)
    public const AGAMA = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

    public const JENIS_KELAMIN = ['Laki-laki', 'Perempuan'];

    public const PENDIDIKAN = ['SD', 'SMP', 'SMA/SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'];

    public const STATUS_NIKAH = ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'];

    public const STATUS_KEPEGAWAIAN = ['Tetap', 'Kontrak', 'Magang', 'Harian'];

    public const BANK = ['BJB', 'BRI', 'BNI', 'Mandiri', 'BCA', 'BSI', 'BTN', 'CIMB Niaga', 'Lainnya'];

    protected $fillable = [
        'phone',
        // data pribadi
        'nik', 'tempat_lahir', 'tanggal_lahir', 'agama', 'jenis_kelamin',
        'pendidikan_terakhir', 'status_nikah', 'jumlah_anak', 'nama_ibu_kandung',
        'alamat_domisili', 'alamat_ktp', 'npwp',
        // pekerjaan
        'tanggal_mulai_kerja', 'jabatan', 'status_kepegawaian',
        // perbankan
        'nama_bank', 'nomor_rekening', 'nama_pemegang_rekening',
    ];

    protected function casts(): array
    {
        return [
            // Dienkripsi otomatis saat disimpan, didekripsi otomatis saat dibaca
            'nik'            => 'encrypted',
            'npwp'           => 'encrypted',
            'nomor_rekening' => 'encrypted',

            'tanggal_lahir'       => 'date',
            'tanggal_mulai_kerja' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}