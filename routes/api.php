<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\TripController;
use App\Http\Controllers\Api\V1\TripPointController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API do app de campo (doc 05)
|--------------------------------------------------------------------------
|
| Cada token vale para uma empresa. O middleware `tenant.token` define a
| empresa da requisição a partir dele.
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/token', [AuthTokenController::class, 'store'])
        ->middleware('throttle:api-login')
        ->name('auth.token.store');

    Route::middleware(['auth:sanctum', 'tenant.token', 'throttle:api'])->group(function () {
        Route::delete('auth/token', [AuthTokenController::class, 'destroy'])->name('auth.token.destroy');
        Route::get('me', MeController::class)->name('me');

        Route::get('trips', [TripController::class, 'index'])->name('trips.index');
        Route::put('trips/{tripId}', [TripController::class, 'update'])->whereUuid('tripId')->name('trips.update');
        Route::post('trips/{trip}/points', [TripPointController::class, 'store'])
            ->whereUuid('trip')
            ->can('update', 'trip')
            ->name('trips.points.store');
    });
});
