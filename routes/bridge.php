<?php

use App\Http\Controllers\Bridge\FingerprintEventController;
use Illuminate\Support\Facades\Route;

// Dimuat dari bootstrap/app.php dengan prefix "bridge", tanpa sesi dan tanpa CSRF.
// Throttle ditaruh di depan token supaya percobaan token salah juga ikut dibatasi.
Route::post('/fingerprint', [FingerprintEventController::class, 'store'])
    ->middleware(['throttle:240,1', 'fingerprint.token'])
    ->name('fingerprint');