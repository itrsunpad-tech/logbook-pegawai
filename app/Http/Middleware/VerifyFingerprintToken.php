<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyFingerprintToken
{
    /**
     * Hanya skrip bridge yang membawa token rahasia yang boleh mengirim data.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) config('services.fingerprint.token');

        // Kalau token belum diisi di .env, tolak semua permintaan (jangan dibiarkan terbuka)
        if ($token === '' || ! hash_equals($token, (string) $request->bearerToken())) {
            abort(401, 'Token bridge tidak valid.');
        }

        return $next($request);
    }
}