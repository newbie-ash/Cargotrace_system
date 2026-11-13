<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\TrackingEventController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public & Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/track-public', [LandingController::class, 'trackPublic'])->middleware('throttle:30,1')->name('public.track');
Route::get('/estimate-tariff', [LandingController::class, 'estimateTariff'])->middleware('throttle:30,1')->name('public.estimate');

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

    // In-App Notifications
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Shipments: Common visibility (index & show) with controller-level customer isolation
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show')->whereNumber('shipment');

    // Centralized e-Bill of Lading Documents Hub
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');

    // Dedicated Tariff & Carbon Footprint Simulator
    Route::get('/calculator', [CalculatorController::class, 'index'])->name('calculator.index');
    Route::post('/calculator/estimate', [CalculatorController::class, 'estimate'])->name('calculator.estimate');

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
        Route::get('/locations/lookup-coordinates', [LocationController::class, 'lookupCoordinates'])->name('locations.lookup');
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
