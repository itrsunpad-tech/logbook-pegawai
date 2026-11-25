<?php

namespace App\Support;

use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WorkSchedule
{
    /** Jenis Jam Kerja pegawai yang valid; null jika belum diatur HC. */
    public static function jenis(?User $user): ?string
    {
        $jenis = trim((string) $user?->work_hour_type);

        return array_key_exists($jenis, config('work_schedules.jenis_jam_kerja', [])) ? $jenis : null;
    }

    /**
     * Jadwal pegawai, dikelompokkan untuk tampilan dropdown.
     * 'Office Hour' => oh, 'Middle' => middle, jenis shift lainnya => shift.
     */
    public static function groups(?User $user): array
    {
        $jenis = self::jenis($user);

        if ($jenis === null) {
            return ['oh' => [], 'shift' => [], 'middle' => []];
        }

        $jadwal = config('work_schedules.jenis_jam_kerja', [])[$jenis];

        return match ($jenis) {
            'Office Hour' => ['oh' => $jadwal, 'shift' => [], 'middle' => []],
            'Middle' => ['oh' => [], 'shift' => [], 'middle' => $jadwal],
            default => ['oh' => [], 'shift' => $jadwal, 'middle' => []],
        };
    }

    /** Semua pilihan yang boleh digunakan: jadwal kerja, lalu status umum. Kosong jika Jenis Jam Kerja belum diatur. */
    public static function shiftNames(?User $user): array
    {
        if (self::jenis($user) === null) {
            return [];
        }

        $groups = self::groups($user);

        return array_values(array_unique(array_merge(
            $groups['oh'],
            $groups['shift'],
            $groups['middle'],
            config('work_schedules.common', [])
        )));
    }

    /** Satu jadwal kerja otomatis dipilih jika hanya ada satu opsi (Office Hour atau Middle). */
    public static function defaultShiftName(?User $user): ?string
    {
        $groups = self::groups($user);
        $jadwalKerja = array_merge($groups['oh'], $groups['shift'], $groups['middle']);

        return count($jadwalKerja) === 1 ? $jadwalKerja[0] : null;
    }

    /**
     * Jadwal berjam tetap (OH + shift) pegawai, lengkap dengan jamnya.
     * Middle sengaja tidak termasuk: tidak dicocokkan dari jam tap.
     */
    public static function jadwalKerja(?User $user): Collection
    {
        $groups = self::groups($user);
        $nama = array_values(array_unique(array_merge($groups['oh'], $groups['shift'])));

        if ($nama === []) {
            return collect();
        }

        return Shift::query()
            ->whereIn('nama', $nama)
            ->where('is_kerja', true)
            ->get()
            ->sortBy(fn (Shift $shift) => array_search($shift->nama, $nama, true))
            ->values();
    }

    /**
     * Apakah jadwal pegawai ini diisi otomatis dari fingerprint?
     * Syarat: punya ID mesin, dan semua jadwal kerjanya sudah punya jam mulai dan selesai.
     * Pegawai Middle atau yang jadwalnya belum punya jam memilih jadwal manual lewat dropdown.
     */
    public static function autoFingerprint(?User $user): bool
    {
        if (! $user || ! $user->fingerprint_id) {
            return false;
        }

        $jadwal = self::jadwalKerja($user);

        return $jadwal->isNotEmpty()
            && $jadwal->every(fn (Shift $shift) => $shift->jam_mulai && $shift->jam_selesai);
    }

    /**
     * Cocokkan jam tap masuk dengan rentang jam shift pegawai.
     * Tap dianggap masuk sebuah shift jika jatuh dalam [jam mulai - toleransi, jam selesai).
     * Jika masuk ke beberapa shift, dipilih yang jam mulainya paling dekat;
     * jika tidak masuk ke shift mana pun, dipilih shift dengan jam mulai terdekat.
     */
    public static function cocokkanDariTap(?User $user, Carbon $waktuTap): ?Shift
    {
        $jadwal = self::jadwalKerja($user)
            ->filter(fn (Shift $shift) => $shift->jam_mulai && $shift->jam_selesai);

        if ($jadwal->isEmpty()) {
            return null;
        }

        $toleransi = (int) config('work_schedules.toleransi_menit', 60);
        $menitTap = $waktuTap->hour * 60 + $waktuTap->minute;

        $terdekatDalamRentang = null;
        $jarakDalamRentang = PHP_INT_MAX;
        $terdekat = null;
        $jarakTerdekat = PHP_INT_MAX;

        foreach ($jadwal as $shift) {
            $mulai = self::menit($shift->jam_mulai);
            $selesai = self::menit($shift->jam_selesai);

            if ($selesai <= $mulai) {
                $selesai += 1440; // shift malam melewati tengah malam
            }

            $jarak = abs($menitTap - $mulai);
            $jarak = min($jarak, 1440 - $jarak);

            $dalamRentang = ($menitTap >= $mulai - $toleransi && $menitTap < $selesai)
                || ($menitTap + 1440 >= $mulai - $toleransi && $menitTap + 1440 < $selesai);

            if ($dalamRentang && $jarak < $jarakDalamRentang) {
                $jarakDalamRentang = $jarak;
                $terdekatDalamRentang = $shift;
            }

            if ($jarak < $jarakTerdekat) {
                $jarakTerdekat = $jarak;
                $terdekat = $shift;
            }
        }

        return $terdekatDalamRentang ?? $terdekat;
    }

    /** Ubah 'HH:MM[:SS]' menjadi menit sejak 00:00. */
    private static function menit(string $jam): int
    {
        [$h, $m] = array_map('intval', explode(':', $jam));

        return $h * 60 + $m;
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