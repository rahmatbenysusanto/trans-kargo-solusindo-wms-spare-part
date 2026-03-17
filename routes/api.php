<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiClientController;
use App\Http\Controllers\Api\ApiInboundController;
use App\Http\Controllers\Api\ApiInventoryController;
use App\Http\Controllers\Api\ApiOutboundController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Semua endpoint API WMS Spare Room.
| Endpoint auth tidak memerlukan token.
| Semua endpoint lain wajib menyertakan:
|   Authorization: Bearer <token>
*/

// ─── Auth (tidak butuh token) ───────────────────────────────────────────────
Route::prefix('auth')->controller(ApiAuthController::class)->group(function () {
    Route::post('/login', 'login');
});

// ─── Protected routes (wajib JWT) ───────────────────────────────────────────
Route::middleware('jwt')->group(function () {

    // Clients
    Route::prefix('clients')->controller(ApiClientController::class)->group(function () {
        Route::get('/', 'index');
    });

    // Inbound
    Route::prefix('inbound')->controller(ApiInboundController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
    });

    // Inventory
    Route::prefix('inventory')->controller(ApiInventoryController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/stock-statement', 'stockStatement');
        Route::get('/cycle-count', 'cycleCount');
    });

    // Outbound
    Route::prefix('outbound')->controller(ApiOutboundController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
    });
});
