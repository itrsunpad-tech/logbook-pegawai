<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Logbook extends Model
{
    public const HARIAN = 'harian';

    public const LEMBUR = 'lembur';

    public const ONCALL = 'oncall';

    public const JENIS = [
        self::HARIAN,
        self::LEMBUR,
        self::ONCALL,
    ];

    protected $fillable = [
        'user_id',
        'jenis',
        'tanggal',
        'shift_id',
        'jam_mulai',
        'jam_selesai',
        'ringkasan',
        'is_wfh',
        'harian_unik',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_wfh' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public static function batasPengisian(): Carbon
    {
        return Carbon::parse(
            config('logbook.batas_pengisian'),
            config('app.timezone')
        );
    }

    public static function pengisianDibuka(): bool
    {
        return now()->lte(static::batasPengisian());
    }
}