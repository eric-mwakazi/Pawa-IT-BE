<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherApiController;

Route::get('/weather/current', [WeatherApiController::class, 'getCurrentWeather']);
Route::get('/weather/forecast', [WeatherApiController::class, 'getForecast']);
Route::get('/weather/geocode', [WeatherApiController::class, 'geocodeCity']);
