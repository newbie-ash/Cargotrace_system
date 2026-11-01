<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarrierApiController;
use App\Http\Controllers\Api\LocationApiController;
use App\Http\Controllers\Api\ShipmentApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CargoTrace RESTful API Routes (Version 1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public / Authentication Endpoints
    Route::post('/auth/token', [AuthController::class, 'token'])->name('api.v1.auth.token');

    // Authenticated Endpoints (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {

        // Session & Identity
        Route::post('/auth/revoke', [AuthController::class, 'revoke'])->name('api.v1.auth.revoke');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');

        // Master Data Read APIs
        Route::get('/carriers', [CarrierApiController::class, 'index'])->name('api.v1.carriers.index');
        Route::get('/locations', [LocationApiController::class, 'index'])->name('api.v1.locations.index');

        // Shipments & Tracking APIs
        Route::get('/shipments', [ShipmentApiController::class, 'index'])->name('api.v1.shipments.index');
        Route::post('/shipments', [ShipmentApiController::class, 'store'])->name('api.v1.shipments.store');
        Route::get('/shipments/{shipment_number}', [ShipmentApiController::class, 'show'])->name('api.v1.shipments.show');
        Route::post('/shipments/{shipment_number}/events', [ShipmentApiController::class, 'appendEvent'])->name('api.v1.shipments.events.store');
    });
});
