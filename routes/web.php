<?php

use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LogbookController as AdminLogbookController;
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

        // ACC logbook
        Route::get('/logbook', [AdminLogbookController::class, 'index'])
            ->name('logbook.index');

        Route::patch('/logbook/{logbook}/setujui', [AdminLogbookController::class, 'setujui'])
            ->name('logbook.setujui');

        Route::patch('/logbook/{logbook}/tolak', [AdminLogbookController::class, 'tolak'])
            ->name('logbook.tolak');
    });

// Riwayat logbook
Route::get('/logbook/riwayat', [LogbookController::class, 'riwayat'])->name('logbook.riwayat');