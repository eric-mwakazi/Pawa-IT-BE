<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    /**
     * Get current weather details for a given city.
     */
    public function current(Request $request)
    {
        // Get the city and units from query parameters (default units: metric)
        $city = $request->query('city');
        $units = $request->query('units', 'metric'); // 'metric' or 'imperial'

        // Geocode city to get latitude and longitude
        $geoData = Http::get("http://api.openweathermap.org/geo/1.0/direct", [
            'q' => $city,
            'limit' => 1,
            'appid' => env('OPENWEATHER_API_KEY')
        ])->json();

        // Return error if city not found
        if (empty($geoData)) {
            return response()->json(['error' => 'City not found'], 404);
        }

        $lat = $geoData[0]['lat'];
        $lon = $geoData[0]['lon'];

        // Fetch current weather data using coordinates
        $weather = Http::get("https://api.openweathermap.org/data/2.5/weather", [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => env('OPENWEATHER_API_KEY'),
            'units' => $units
        ])->json();

        // Format and return the weather response
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

    /**
     * Get a 3-day weather forecast for a given city.
     */
    public function forecast(Request $request)
    {
        // Get the city and units from query parameters
        $city = $request->query('city');
        $units = $request->query('units', 'metric');

        // Geocode the city
        $geoData = Http::get("http://api.openweathermap.org/geo/1.0/direct", [
            'q' => $city,
            'limit' => 1,
            'appid' => env('OPENWEATHER_API_KEY')
        ])->json();

        // Return error if city not found
        if (empty($geoData)) {
            return response()->json(['error' => 'City not found'], 404);
        }

        $lat = $geoData[0]['lat'];
        $lon = $geoData[0]['lon'];

        // Fetch 5-day forecast data in 3-hour intervals
        $forecast = Http::get("https://api.openweathermap.org/data/2.5/forecast", [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => env('OPENWEATHER_API_KEY'),
            'units' => $units
        ])->json();

        // Take the first 24 intervals (3-hour steps = 3 days), group into 3 days
        $daily = collect($forecast['list'])->take(24)->map(function ($item) {
            return [
                'date' => $item['dt_txt'],
                'temp' => $item['main']['temp'],
                'description' => $item['weather'][0]['description'],
                'icon' => $item['weather'][0]['icon'],
            ];
        });

        // Return 3 daily chunks (8 intervals = 1 day)
        return response()->json($daily->chunk(8)->take(3));
    }

    /**
     * Geocode a city to get its latitude and longitude.
     */
    public function geocodeCity(Request $request)
    {
        // Get the city from the query parameters
        $city = $request->query('city');

        // Call OpenWeatherMap geocoding API
        $geoData = Http::get("http://api.openweathermap.org/geo/1.0/direct", [
            'q' => $city,
            'limit' => 1,
            'appid' => env('OPENWEATHER_API_KEY')
        ])->json();

        // Return error if city is not found
        if (empty($geoData)) {
            return response()->json(['error' => 'City not found'], 404);
        }

        // Return formatted geocoding information
        return response()->json([
            'city' => $geoData[0]['name'],
            'country' => $geoData[0]['country'],
            'lat' => $geoData[0]['lat'],
            'lon' => $geoData[0]['lon']
        ]);
    }
}

