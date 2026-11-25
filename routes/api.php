<?php

use App\Http\Controllers\Api\CampusEventController;
use App\Http\Controllers\Api\VenueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::name('api.')->group(function () {
    Route::apiResource('campus-events', CampusEventController::class)
        ->parameters(['campus-events' => 'event'])
        ->only(['index', 'show']);

    Route::apiResource('venues', VenueController::class)->only(['index', 'show']);
});
