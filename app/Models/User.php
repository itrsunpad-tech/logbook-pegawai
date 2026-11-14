<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    public const ROLE_PEGAWAI = 'pegawai';

    public const ROLE_ADMIN = 'admin';

    /** Pilihan kategori pegawai (kolom employee_category). */
    public const KATEGORI = ['Nakes', 'Non-Nakes'];

    /** Unit / Posisi yang tersedia di Overview Kepegawaian (dari Job Position). */
    public const UNIT_KERJA = [
        'Direktur Keuangan dan Administrasi',
        'Direktur Umum dan Operasional',
        'Direktur Utama',
        'Kepala Casemix',
        'Kepala Instalasi Bedah Sentral',
        'Kepala Instalasi Farmasi',
        'Kepala Instalasi Gizi',
        'Kepala Instalasi IGD',
        'Kepala Instalasi Kamar Jenazah',
        'Kepala Instalasi Laboratorium',
        'Kepala Instalasi Perawatan Intensive',
        'Kepala Instalasi Radiologi',
        'Kepala Instalasi Rawat Inap',
        'Kepala Instalasi Rawat Jalan',
        'Ketua Komite',
        'Koordinator Bidan',
        'Koordinator IT',
        'Koordinator Perawat',
        'Koordinator Perawat Bedah Sentral',
        'Koordinator Perawat Perinatologi',
        'Koordinator Perawat Rawat Inap',
        'Koordinator Perawat Rawat Jalan',
        'Manajer General Affair dan Aset',
        'Manajer HSSE',
        'Manajer Human Capital',
        'Manajer Keperawatan',
        'Manajer Keuangan',
        'Manajer Pelayanan Medik',
        'Manajer Penunjang Medis',
        'Manajer SIMRS',
        'Satuan Pengawas Internal (SPI)',
        'Sekper',
        'Staff',
        'Staff Administrasi',
        'Staff Ambulans',
        'Staff Bidan',
        'Staff Casemix',
        'Staff Coder',
        'Staff CSSD',
        'Staff Farmasi',
        'Staff General Affair dan Aset',
        'Staff Gizi',
        'Staff HSSE',
        'Staff Human Capital',
        'Staff IPSRS',
        'Staff IT',
        'Staff Keuangan',
        'Staff Laboratorium',
        'Staff Legal',
        'Staff Linen',
        'Staff Logistik',
        'Staff Marketing',
        'Staff NA',
        'Staff Pendaftaran',
        'Staff Perawat',
        'Staff Radiologi',
        'Staff Rekam Medis',
        'Staff Sekretaris',
    ];

    /** Pilihan jenis jam kerja pegawai (kolom work_hour_type). */
    public const JENIS_JAM_KERJA = ['Office Hour', 'Shift'];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'employee_category',
        'employee_unit',
        'work_hour_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Apakah user ini admin (HC)?
     * 'role' sengaja TIDAK masuk $fillable supaya tidak bisa
     * diubah lewat mass assignment.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Semua logbook milik user ini.
     */
    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class);
    }

    /**
     * Profil detail pegawai (satu-ke-satu).
     */
    public function profile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    /**
     * Inisial untuk avatar, mis. "Ilmi Ghazali" => "IG".
     */
    public function inisial(): string
    {
        return Str::of((string) $this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
            ->implode('');
    }
}
