<?php

use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LogbookController as AdminLogbookController;
use App\Http\Controllers\Admin\KepegawaianController;
use App\Http\Controllers\Admin\SidikJariController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Google OAuth
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])
    ->name('google.login');

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
    ->name('google.callback');

// Logout
Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

// Logbook
Route::middleware('auth')
    ->prefix('logbook')
    ->name('logbook.')
    ->group(function () {
        Route::get('/', [LogbookController::class, 'index'])
            ->name('index');

        Route::post('/', [LogbookController::class, 'store'])
            ->name('store');

        Route::get('/{logbook}/bukti', [LogbookController::class, 'bukti'])
            ->name('bukti');

        Route::patch('/{logbook}/banding', [LogbookController::class, 'banding'])
            ->name('banding');
    });

// Ping connection
Route::get('/ping', function () {
    return response()->json([
        'ok' => true,
        'time' => now()->timestamp,
    ]);
})->middleware('auth')->name('ping');

// Admin / HC
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // Kepegawaian
        Route::get('/kepegawaian', [KepegawaianController::class, 'index'])
            ->name('kepegawaian.index');

        Route::get('/kepegawaian/{user}', [KepegawaianController::class, 'show'])
            ->name('kepegawaian.show');

        Route::patch('/kepegawaian/{user}/overview', [KepegawaianController::class, 'updateOverview'])
            ->name('kepegawaian.update-overview');

        Route::patch('/kepegawaian/{user}/kontak', [KepegawaianController::class, 'updateKontak'])
            ->name('kepegawaian.update-kontak');

        // Sidik jari (live)
        Route::get('/sidik-jari', [SidikJariController::class, 'index'])
            ->name('sidik-jari.index');

        Route::get('/sidik-jari/terbaru', [SidikJariController::class, 'terbaru'])
            ->name('sidik-jari.terbaru');

        // ACC logbook
        Route::get('/logbook', [AdminLogbookController::class, 'index'])
            ->name('logbook.index');

        Route::get('/logbook/pegawai/{user}', [AdminLogbookController::class, 'show'])
            ->name('logbook.pegawai');

        Route::patch('/logbook/{logbook}/setujui', [AdminLogbookController::class, 'setujui'])
            ->name('logbook.setujui');

        Route::patch('/logbook/{logbook}/tolak', [AdminLogbookController::class, 'tolak'])
            ->name('logbook.tolak');

        Route::patch('/kepegawaian/{user}/data-pribadi', [KepegawaianController::class, 'updateDataPribadi'])
            ->name('kepegawaian.update-data-pribadi');

        Route::patch('/kepegawaian/{user}/pekerjaan', [KepegawaianController::class, 'updatePekerjaan'])
            ->name('kepegawaian.update-pekerjaan');

        Route::patch('/kepegawaian/{user}/perbankan', [KepegawaianController::class, 'updatePerbankan'])
            ->name('kepegawaian.update-perbankan');
    });

