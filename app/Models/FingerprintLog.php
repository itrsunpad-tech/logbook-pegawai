<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FingerprintLog extends Model
{
    /** Zona waktu untuk tampilan. Data di database disimpan dalam UTC. */
    public const TZ_TAMPIL = 'Asia/Jakarta';

    public const VERIFIKASI = [
        0  => 'PIN',
        1  => 'Sidik jari',
        4  => 'Kartu',
        15 => 'Wajah',
        25 => 'Telapak tangan',
    ];

    public const ABSENSI = [
        0 => 'Masuk',
        1 => 'Pulang',
        2 => 'Mulai istirahat',
        3 => 'Selesai istirahat',
        4 => 'Lembur masuk',
        5 => 'Lembur pulang',
    ];

    protected $fillable = [
        'fingerprint_id',
        'punched_at',
        'verify_type',
        'punch_state',
        'machine_uid',
        'device',
    ];

    protected function casts(): array
    {
        return [
            'punched_at' => 'datetime',
        ];
    }

    /**
     * Pegawai pemilik log ini, dihubungkan lewat ID mesin (bukan user_id),
     * jadi log lama otomatis "ketemu" pemiliknya begitu pegawainya didaftarkan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fingerprint_id', 'fingerprint_id');
    }

    public function labelVerifikasi(): string
    {
        if ($this->verify_type === null) {
            return '-';
        }

        return self::VERIFIKASI[$this->verify_type] ?? 'Kode '.$this->verify_type;
    }

    public function labelAbsensi(): string
    {
        if ($this->punch_state === null) {
            return '-';
        }

        return self::ABSENSI[$this->punch_state] ?? 'Kode '.$this->punch_state;
    }
}