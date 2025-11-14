<?php

use App\Modules\CarRentals\Controllers\RentalController;
use App\Modules\CarRentals\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Car Rentals API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider and are assigned
| to the "api" middleware group.
|
*/

Route::prefix('v1/car-rentals')->middleware(['auth:sanctum'])->group(function () {

    // Vehicle routes
    Route::prefix('vehicles')->group(function () {
        Route::get('/', [VehicleController::class, 'index']);
        Route::get('/{vehicle}', [VehicleController::class, 'show']);
        Route::get('/{vehicle}/availability', [VehicleController::class, 'availability']);

        // Manager and Admin only
        Route::middleware(['role:manager|admin'])->group(function () {
            Route::post('/', [VehicleController::class, 'store']);
            Route::put('/{vehicle}', [VehicleController::class, 'update']);
        });

        // Admin only
        Route::middleware(['role:admin'])->group(function () {
            Route::delete('/{vehicle}', [VehicleController::class, 'destroy']);
        });
    });

    // Rental routes
    Route::prefix('rentals')->group(function () {
        Route::get('/', [RentalController::class, 'index']);
        Route::post('/', [RentalController::class, 'store']);
        Route::get('/{rental}', [RentalController::class, 'show']);
        Route::put('/{rental}', [RentalController::class, 'update']);
        Route::post('/{rental}/cancel', [RentalController::class, 'cancel']);

        // Manager and Admin only
        Route::middleware(['role:manager|admin'])->group(function () {
            Route::post('/{rental}/verify', [RentalController::class, 'verify']);
            Route::post('/{rental}/checkin', [RentalController::class, 'checkin']);
            Route::post('/{rental}/checkout', [RentalController::class, 'checkout']);
        });
    });
});
