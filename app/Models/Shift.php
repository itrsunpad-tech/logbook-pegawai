<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    public const JENIS_OFFICE_HOUR = 'Office Hour';

    public const JENIS_SHIFT = 'Shift';

    public const JENIS = [self::JENIS_OFFICE_HOUR, self::JENIS_SHIFT];

    protected $fillable = [
        'nama',
        'jenis',
        'jam_mulai',
        'jam_selesai',
        'is_kerja',
        'urutan',
    ];

    protected $casts = [
        'is_kerja' => 'boolean',
    ];

    public function getLabelAttribute(): string
    {
        $nama = $this->nama === 'LIBUR' ? 'Libur' : $this->nama;

        if (! $this->jam_mulai || ! $this->jam_selesai) {
            return $nama;
        }

        return sprintf(
            '%s (%s - %s)',
            $nama,
            substr($this->jam_mulai, 0, 5),
            substr($this->jam_selesai, 0, 5)
        );
    }
}
