<?php

namespace App\Http\Controllers\Bridge;

use App\Http\Controllers\Controller;
use App\Services\SinkronPegawaiMesin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PegawaiMesinController extends Controller
{
    /**
     * Menerima daftar pegawai dari mesin (dikirim bridge Python secara berkala).
     * Hanya pegawai yang belum ada yang ditambahkan; yang sudah ada tidak diubah.
     */
    public function store(Request $request, SinkronPegawaiMesin $sinkron): JsonResponse
    {
        $data = $request->validate([
            'pegawai'                  => ['present', 'array', 'max:1000'],
            'pegawai.*.fingerprint_id' => ['required', 'string', 'max:30'],
            'pegawai.*.nama'           => ['nullable', 'string', 'max:255'],
        ]);

        $hasil = $sinkron->sinkron($data['pegawai']);

        // Permintaan ini juga membuktikan bridge masih hidup
        Cache::put('fingerprint.bridge_last_seen', now()->timestamp, now()->addDay());

        if ($hasil['baru'] > 0) {
            // Hanya jumlahnya yang dicatat, bukan nama pegawai
            Log::info('Sinkron pegawai dari mesin: pegawai baru ditambahkan.', ['baru' => $hasil['baru']]);
        }

        return response()->json(['ok' => true] + $hasil);
    }
}