<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'nama',
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
        if (! $this->jam_mulai || ! $this->jam_selesai) {
            return $this->nama;
        }

        return sprintf(
            '%s (%s - %s)',
            $this->nama,
            substr($this->jam_mulai, 0, 5),
            substr($this->jam_selesai, 0, 5)
        );
    }
}