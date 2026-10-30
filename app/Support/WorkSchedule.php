<?php

namespace App\Support;

use App\Models\Shift;

class WorkSchedule
{
    public static function unitKey(?string $unit): ?string
    {
        $unit = mb_strtolower(trim((string) $unit));

        foreach (array_keys(config('work_schedules.units', [])) as $key) {
            if (mb_strtolower($key) === $unit) {
                return $key;
            }
        }

        return null;
    }

    /** Jadwal dikelompokkan menurut Office hour dan shift untuk unit pegawai. */
    public static function groups(?string $unit): array
    {
        $key = self::unitKey($unit);
        $groups = $key ? config("work_schedules.units.{$key}", []) : [];

        return [
            'oh' => $groups['oh'] ?? [],
            'shift' => $groups['shift'] ?? [],
        ];
    }

    /** Semua pilihan yang boleh digunakan: Office hour, shift, lalu status umum. */
    public static function shiftNames(?string $unit): array
    {
        if (trim((string) $unit) === '') {
            return [];
        }

        $groups = self::groups($unit);
        $jadwal = self::unitKey($unit)
            ? array_merge($groups['oh'], $groups['shift'])
            : config('work_schedules.default', []);

        return array_values(array_unique(array_merge(
            $jadwal,
            config('work_schedules.common', [])
        )));
    }

    /** Satu jadwal kerja otomatis dipilih jika hanya ada satu opsi OH/shift. */
    public static function defaultShiftName(?string $unit): ?string
    {
        $groups = self::groups($unit);
        $jadwalKerja = array_merge($groups['oh'], $groups['shift']);

        return count($jadwalKerja) === 1 ? $jadwalKerja[0] : null;
    }

    /** Label jadwal untuk tampilan dropdown tanpa mengubah nama yang tersimpan. */
    public static function label(?Shift $shift): string
    {
        if (! $shift) {
            return '';
        }

        if ($shift->nama === 'LIBUR') {
            return 'Libur';
        }

        if ($shift->nama === 'OH IT,Gizi') {
            return 'OH (IT,Gizi)';
        }

        if ($shift->nama === 'Pagi Gizi & Linen') {
            return 'Shift Pagi (Gizi & Linen)';
        }

        if (preg_match('/^OH\s+(.+)$/u', $shift->nama, $matches)) {
            $unit = preg_replace('/\s*,\s*/u', ', ', $matches[1]);

            return 'Office Hour (' . $unit . ')';
        }

        return $shift->nama;
    }

    /** kerja | libur | sakit | izin | null */
    public static function tipe(?Shift $shift): ?string
    {
        if (! $shift) {
            return null;
        }

        return $shift->is_kerja ? 'kerja' : mb_strtolower($shift->nama);
    }
}
