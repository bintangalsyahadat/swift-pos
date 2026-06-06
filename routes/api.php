<?php

use App\Http\Controllers\Api\PosApiController;
use App\Http\Controllers\Api\XenditWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| POS API Routes
|--------------------------------------------------------------------------
|
| Semua route di sini menggunakan prefix /api (sudah di-set di bootstrap/app.php).
| Route dilindungi Sanctum kecuali endpoint login.
|
*/

// ── Public ──────────────────────────────────────────────────────────────────
Route::post('/login', [PosApiController::class, 'login']);

// ── Xendit Webhook (public, tidak perlu auth) ─────────────────────────────
// Daftarkan URL ini di Xendit Dashboard → Webhooks
// URL: {your-domain}/api/webhooks/xendit
Route::post('/webhooks/xendit', [XenditWebhookController::class, 'handle']);

// ── Protected (Sanctum) ─────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Data master — ditembak saat kasir menekan "Mulai Shift"
    Route::get('/init-data', [PosApiController::class, 'initData']);

    // Manajemen sesi / shift kasir
    Route::prefix('session')->group(function () {
        Route::post('/open',  [PosApiController::class, 'openSession']);
        Route::post('/close', [PosApiController::class, 'closeSession']);
    });

    // Sinkronisasi transaksi offline (bulk insert)
    Route::post('/orders/sync', [PosApiController::class, 'syncOrders']);
});
