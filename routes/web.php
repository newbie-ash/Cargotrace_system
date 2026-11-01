<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\TrackingEventController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public & Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/auth/{provider}', [AuthController::class, 'socialLogin'])->name('auth.social');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Application Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shipments: Common visibility (index & show) with controller-level customer isolation
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show')->whereNumber('shipment');

    // Shipments: Operations for Operator & Admin
    Route::middleware('role:admin,operator')->group(function () {
        Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
        Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
        Route::get('/shipments/{shipment}/edit', [ShipmentController::class, 'edit'])->name('shipments.edit')->whereNumber('shipment');
        Route::put('/shipments/{shipment}', [ShipmentController::class, 'update'])->name('shipments.update')->whereNumber('shipment');
        Route::post('/shipments/{shipment}/transition', [ShipmentController::class, 'transition'])->name('shipments.transition')->whereNumber('shipment');
        Route::post('/shipments/{shipment}/tracking', [TrackingEventController::class, 'store'])->name('shipments.tracking.store')->whereNumber('shipment');
    });

    // Shipments: Deletion exclusively for Admin
    Route::middleware('role:admin')->group(function () {
        Route::delete('/shipments/{shipment}', [ShipmentController::class, 'destroy'])->name('shipments.destroy')->whereNumber('shipment');
    });

    // Master Data: View-only for Operator & Admin
    Route::middleware('role:admin,operator')->group(function () {
        Route::get('/carriers', [CarrierController::class, 'index'])->name('carriers.index');
        Route::get('/carriers/{carrier}', [CarrierController::class, 'show'])->name('carriers.show')->whereNumber('carrier');

        Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
        Route::get('/locations/{location}', [LocationController::class, 'show'])->name('locations.show')->whereNumber('location');
    });

    // Master Data: Mutations restricted exclusively to Admin
    Route::middleware('role:admin')->group(function () {
        Route::resource('carriers', CarrierController::class)->except(['index', 'show']);
        Route::resource('locations', LocationController::class)->except(['index', 'show']);
        Route::resource('users', UserController::class);
    });
});
