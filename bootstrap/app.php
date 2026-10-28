<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $trusted = env('TRUSTED_PROXIES', '*');
        $middleware->trustProxies(at: $trusted === '*' ? '*' : explode(',', (string) $trusted));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi keamanan halaman telah diperbarui. Silakan muat ulang halaman.'], 419);
            }

            $request->session()->regenerateToken();

            return redirect()
                ->back()
                ->withInput($request->except('password', '_token'))
                ->withErrors(['login_id' => 'Sesi halaman sempat kedaluwarsa karena lama tidak aktif. Silakan tekan tombol Masuk sekali lagi.'])
                ->with('status', 'Sesi keamanan telah diperbarui otomatis. Silakan ulangi tindakan Anda.');
        });
    })->create();
