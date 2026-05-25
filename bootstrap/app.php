<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Paksa semua request /api/* untuk selalu expect JSON response.
        // Ini mencegah redirect ke halaman HTML saat tidak ada header Accept: application/json.
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        // Jangan redirect ke route "login" untuk request API yang tidak ter-autentikasi.
        // Kembalikan respons JSON 401 sebagai gantinya.
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Token tidak valid atau tidak disertakan.',
                ], 401));
            }

            return route('filament.admin.auth.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
