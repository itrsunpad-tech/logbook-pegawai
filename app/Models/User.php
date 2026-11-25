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

    /**
     * Unit kerja baku (kolom employee_unit), sesuai file
     * KEBUTUHAN DATA KEPEGAWAIAN SISTEM LOGBOOK RS.xlsx kolom "Unit Kerja".
     */
    public const UNIT_KERJA = [
        'Admin',
        'Afirmasi Masyarakat/POS/NA',
        'Bidan',
        'Direksi',
        'Dokter',
        'Farmasi',
        'Fisioterapi',
        'GA dan Aset',
        'Gizi',
        'HSSE',
        'Human Capital',
        'Keuangan',
        'Laboratorium',
        'Legal/SPI/Sekretaris',
        'Manajer',
        'Marketing/Humas/PKRS/Bussdev',
        'Perawat',
        'Radiologi',
        'SIMRS & IT',
        'Sub-Unit Instalasi Gawat Darurat',
    ];

    /**
     * Pilihan Instalasi (kolom employee_instalasi), dari kolom "Instalasi" pada file Excel kepegawaian.
     * Di dropdown, daftar ini digabung dengan nilai yang sudah tercatat di database.
     */
    public const INSTALASI = [
        'Administrasi Rawat Inap',
        'Administrasi Rawat Jalan',
        'Billing',
        'Billing Asuransi',
        'Business Development',
        'Casemix',
        'Diklitlat',
        'Diktiklat',
        'Direksi',
        'Driver Ambulance',
        'HSSE',
        'Instalasi Bedah Sentral',
        'Instalasi Farmasi',
        'Instalasi Gawat Darurat',
        'Instalasi Gizi',
        'Instalasi Hemodialisa',
        'Instalasi Laboratorium',
        'Instalasi Perawatan Intensive',
        'Instalasi Radiologi',
        'Instalasi Rawat Inap',
        'Instalasi Rawat Jalan',
        'K3RS',
        'Keuangan',
        'Linen',
        'Linen / Laundry',
        'Manajer',
        'Marketing',
        'Marketing Humas',
        'Marketing/Humas/PKRS/Bussdev',
        'Ners Assistan',
        'Pelayanan Medis',
        'Pemusalaran Jenazah',
        'Pendaftaran',
        'Pengadministrasi Human Development',
        'Pengadministrasi Rekam Nakes',
        'Penjaga Kantin',
        'PIPP',
        'Relations dan Payroll',
        'Sarana dan Prasarana',
        'Satuan Pengawas Internal (SPI)',
        'Sekretaris Direktur',
        'Staf Administrasi Dialisis',
        'Staf Administrasi Rawat Inap',
        'Staf CSSD',
        'Staff CSSD',
        'Staff IT',
        'Staff Legal',
        'Staff Manajemen Aset',
        'Staff SPI',
        'Teknisi Pelaksana ElektroNakes',
        'Teknisi Pelaksana Kesehatan Lingkungan',
        'Teknisi Pelaksana Logistik & Pengadaan',
        'Teknisi Pelaksana Marketing',
        'Tenaga Elektromedis',
        'Tenaga Teknis Sarana dan Prasarana',
    ];

    /**
     * Pilihan Jabatan (kolom employee_jabatan), dari kolom "Jabatan" pada file Excel kepegawaian.
     * Di dropdown, daftar ini digabung dengan nilai yang sudah tercatat di database.
     */
    public const JABATAN = [
        'Admin Farmasi',
        'Admin Kecantikan',
        'Admin OK',
        'Administrasi Farmasi',
        'Administrasi Radiologi',
        'Administrasi Rawat Inap',
        'Administrasi Rawat Jalan',
        'Ahli Gizi',
        'Analis Kesehatan',
        'Apoteker',
        'Bidan Pelaksana',
        'Billing',
        'Billing Asuransi',
        'Business Development',
        'Case Analyst Pasien Umum dan Asuransi',
        'Casemix',
        'Direktur Keuangan dan Administrasi',
        'Direktur Medis dan Keperawatan',
        'Direktur Umum dan Operasional',
        'Direktur Utama',
        'Driver Ambulance',
        'Fisioterapi',
        'HSSE',
        'K3RS',
        'Kepala Instalasi Rawat Jalan',
        'Koordinator Bidan',
        'Koordinator Diktiklat',
        'Koordinator Instalasi Farmasi',
        'Koordinator Instalasi Gizi',
        'Koordinator Perawat',
        'Linen',
        'Linen / Laundry',
        'Manajer Akuntansi & Keuangan',
        'Manajer General Affairs & Aset',
        'Manajer Health, Safety, Security, Environment (HSSE)',
        'Manajer Human Capital (HC)',
        'Manajer Keperawatan',
        'Manajer Marketing/Humas/PKRS/Bussdev',
        'Manajer Pelayanan Medis',
        'Manajer Penunjang Medis',
        'Manajer SIMRS (CIO) dan Informasi Teknologi',
        'Marketing',
        'Marketing Humas',
        'Marketing/Humas/PKRS/Bussdev',
        'Ners Assistan',
        'Pelaksana Laboratorium Medik',
        'Pemusalaran Jenazah',
        'Pendaftaran',
        'Pengadministrasi Rekam Nakes',
        'Penjaga Kantin',
        'Perawat Gigi',
        'Perawat Pelaksana',
        'Perawat Rawat Inap',
        'Perawat Rawat Inap Eks-Klinik',
        'PIPP',
        'PPI',
        'Pramu Gizi',
        'Radiografer',
        'Radiografer dan PPR',
        'Refraksionis',
        'Sarana dan Prasarana',
        'Satuan Pengawas Internal (SPI)',
        'Sekper/Legal',
        'Sekretaris Direktur',
        'Staf Administrasi Dialisis',
        'Staf Administrasi Rawat Inap',
        'Staf Billing',
        'Staf CSSD',
        'Staf Keuangan',
        'Staf Perawat Pelaksana',
        'Staff CSSD',
        'Staff Diklitlat',
        'Staff IT',
        'Staff Keuangan',
        'Staff Legal',
        'Staff Manajemen Aset',
        'Staff Pengadministrasi Human Development',
        'Staff Relations dan Payroll',
        'Staff SPI',
        'Teknisi Pelaksana ElektroNakes',
        'Teknisi Pelaksana Kesehatan Lingkungan',
        'Teknisi Pelaksana Laboratorium Medik/Analis Kesehatan',
        'Teknisi Pelaksana Logistik & Pengadaan',
        'Teknisi Pelaksana Marketing',
        'Teknisi Pelaksana Radiographer',
        'Tenaga Elektromedis',
        'Tenaga Teknik Kefarmasian',
        'Tenaga Teknis Kefarmasian',
        'Tenaga Teknis Sarana dan Prasarana',
        'Terapis',
    ];

        /** Pilihan jenis jam kerja pegawai (kolom work_hour_type). Kuncinya sama dengan config/work_schedules.php. */
    public const JENIS_JAM_KERJA = [
        'Office Hour',
        'Shift Reguler',
        'Shift Rajal',
        'Shift Gizi & Linen',
        'Shift Hemodialisa',
        'NA dan Ambulans',
        'Middle',
    ];

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
        'employee_unit',
        'employee_instalasi',
        'employee_jabatan',
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
