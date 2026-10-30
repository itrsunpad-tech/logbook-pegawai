<?php

namespace App\Http\Controllers\Bridge;

use App\Http\Controllers\Controller;
use App\Models\FingerprintLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class FingerprintEventController extends Controller
{
    /**
     * Menerima kumpulan data tap dari skrip bridge Python.
     * Kalau "events" kosong, permintaan ini dianggap sinyal "bridge masih hidup" (heartbeat).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device'                  => ['nullable', 'string', 'max:50'],
            'events'                  => ['present', 'array', 'max:500'],
            'events.*.fingerprint_id' => ['required', 'string', 'max:30'],
            'events.*.timestamp'      => ['required', 'date_format:Y-m-d\TH:i:sP'],
            'events.*.verify_type'    => ['nullable', 'integer', 'between:0,65535'],
            'events.*.punch_state'    => ['nullable', 'integer', 'between:0,65535'],
            'events.*.machine_uid'    => ['nullable', 'integer', 'min:0'],
        ]);

        $sekarang = now();

        $baris = collect($data['events'])->map(fn (array $e) => [
            'fingerprint_id' => $e['fingerprint_id'],
            // Terima waktu berzona (mis. +07:00), simpan dalam UTC
            'punched_at'     => Carbon::parse($e['timestamp'])->utc()->format('Y-m-d H:i:s'),
            'verify_type'    => $e['verify_type'] ?? null,
            'punch_state'    => $e['punch_state'] ?? null,
            'machine_uid'    => $e['machine_uid'] ?? null,
            'device'         => $data['device'] ?? null,
            'created_at'     => $sekarang,
            'updated_at'     => $sekarang,
        ])->all();

        // insertOrIgnore: data yang sudah ada (pegawai + detik yang sama) dilewati, bukan error
        $baru = $baris !== [] ? FingerprintLog::query()->insertOrIgnore($baris) : 0;

        // Catat "terakhir terlihat" supaya halaman admin tahu bridge masih hidup
        Cache::put('fingerprint.bridge_last_seen', $sekarang->timestamp, now()->addDay());

        return response()->json([
            'ok'       => true,
            'diterima' => count($baris),
            'baru'     => $baru,
        ]);
    }
}