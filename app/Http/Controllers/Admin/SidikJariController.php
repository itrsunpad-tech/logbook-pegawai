<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FingerprintLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SidikJariController extends Controller
{
    /**
     * Halaman monitoring: 30 tap terakhir + status bridge. Selanjutnya diperbarui lewat terbaru().
     */
    public function index(): View
    {
        $awal = FingerprintLog::with('user')
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->reverse()   // urut lama -> baru, supaya yang terbaru tampil di paling atas
            ->values()
            ->map(fn (FingerprintLog $log) => $this->baris($log))
            ->all();

        return view('admin.sidik-jari.index', [
            'awal'    => $awal,
            'status'  => $this->status(),
            'hariIni' => $this->ringkasanHariIni(),
        ]);
    }

    /**
     * Dipanggil JavaScript tiap beberapa detik: kirim data yang lebih baru dari id ?after=...
     */
    public function terbaru(Request $request): JsonResponse
    {
        $after = max(0, (int) $request->query('after', 0));

        $baris = FingerprintLog::with('user')
            ->where('id', '>', $after)
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(fn (FingerprintLog $log) => $this->baris($log))
            ->all();

        return response()->json([
            'rows'     => $baris,
            'status'   => $this->status(),
            'hari_ini' => $this->ringkasanHariIni(),
        ]);
    }

    /**
     * Bentuk data satu tap untuk ditampilkan (waktu dikonversi ke WIB).
     */
    private function baris(FingerprintLog $log): array
    {
        $waktu = $log->punched_at->copy()->setTimezone(FingerprintLog::TZ_TAMPIL);

        return [
            'id'             => $log->id,
            'waktu'          => $waktu->format('H:i:s'),
            'tanggal'        => $waktu->format('d/m/Y'),
            'fingerprint_id' => $log->fingerprint_id,
            'nama'           => $log->user?->name,
            'unit'           => $log->user?->employee_unit,
            'verifikasi'     => $log->labelVerifikasi(),
            'absensi'        => $log->labelAbsensi(),
        ];
    }

    /**
     * Bridge dianggap online kalau sinyal terakhirnya belum lewat 45 detik
     * (bridge mengirim sinyal tiap ~15 detik).
     */
    private function status(): array
    {
        $terakhir = Cache::get('fingerprint.bridge_last_seen');
        $detik = $terakhir ? max(0, now()->timestamp - (int) $terakhir) : null;

        return [
            'online' => $detik !== null && $detik <= 45,
            'detik'  => $detik,
        ];
    }

    /**
     * Jumlah tap dan jumlah pegawai unik hari ini (batas hari mengikuti WIB, bukan UTC).
     */
    private function ringkasanHariIni(): array
    {
        $mulai   = now(FingerprintLog::TZ_TAMPIL)->startOfDay()->utc();
        $selesai = $mulai->copy()->addDay();

        $query = FingerprintLog::where('punched_at', '>=', $mulai)
            ->where('punched_at', '<', $selesai);

        return [
            'total' => (clone $query)->count(),
            'unik'  => (clone $query)->distinct()->count('fingerprint_id'),
        ];
    }
}