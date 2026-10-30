<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Rute untuk skrip bridge mesin sidik jari: tanpa sesi dan tanpa CSRF
            Route::middleware('api')
                ->prefix('bridge')
                ->name('bridge.')
                ->group(base_path('routes/bridge.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'fingerprint.token' => \App\Http\Middleware\VerifyFingerprintToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $exception) {
            return redirect()
                ->route('logbook.index')
                ->with('error', 'Sesi formulir berakhir. Silakan buka kembali logbook lalu kirim aju banding lagi.');
        });
    })->create();
