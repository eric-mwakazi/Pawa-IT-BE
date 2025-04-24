<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('weather')->controller(WeatherController::class)->group(function () {
    Route::get('/current', 'current');
    Route::get('/forecast', 'forecast');
    Route::get('/geocode', 'geocodeCity');
});


