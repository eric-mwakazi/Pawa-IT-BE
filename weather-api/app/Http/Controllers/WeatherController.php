<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function current(Request $request)
    {
        $city = $request->query('city');
        $units = $request->query('units', 'metric'); // 'metric' or 'imperial'

        $geoData = Http::get("http://api.openweathermap.org/geo/1.0/direct", [
            'q' => $city,
            'limit' => 1,
            'appid' => env('OPENWEATHER_API_KEY')
        ])->json();

        if (empty($geoData)) {
            return response()->json(['error' => 'City not found'], 404);
        }

        $lat = $geoData[0]['lat'];
        $lon = $geoData[0]['lon'];

        $weather = Http::get("https://api.openweathermap.org/data/2.5/weather", [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => env('OPENWEATHER_API_KEY'),
            'units' => $units
        ])->json();

        return response()->json([
            'location' => $geoData[0]['name'],
            'country' => $geoData[0]['country'],
            'temp' => $weather['main']['temp'],
            'description' => $weather['weather'][0]['description'],
            'icon' => $weather['weather'][0]['icon'],
            'humidity' => $weather['main']['humidity'],
            'wind_speed' => $weather['wind']['speed'],
            'date' => now()->toDateString()
        ]);
    }

    public function forecast(Request $request)
    {
        $city = $request->query('city');
        $units = $request->query('units', 'metric');

        $geoData = Http::get("http://api.openweathermap.org/geo/1.0/direct", [
            'q' => $city,
            'limit' => 1,
            'appid' => env('OPENWEATHER_API_KEY')
        ])->json();

        if (empty($geoData)) {
            return response()->json(['error' => 'City not found'], 404);
        }

        $lat = $geoData[0]['lat'];
        $lon = $geoData[0]['lon'];

        $forecast = Http::get("https://api.openweathermap.org/data/2.5/forecast", [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => env('OPENWEATHER_API_KEY'),
            'units' => $units
        ])->json();

        // Group data into next 3 days (simple logic)
        $daily = collect($forecast['list'])->take(24)->map(function ($item) {
            return [
                'date' => $item['dt_txt'],
                'temp' => $item['main']['temp'],
                'description' => $item['weather'][0]['description'],
                'icon' => $item['weather'][0]['icon'],
            ];
        });

        return response()->json($daily->chunk(8)->take(3));
    }

}

