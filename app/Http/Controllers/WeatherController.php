<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    /**
     * Get current weather details for a given city OR coordinates.
     */
    public function current(Request $request)
    {
        $lat = $request->query('lat');
        $lon = $request->query('lon');
        $city = $request->query('city');
        $units = $request->query('units', 'metric'); // 'metric' or 'imperial'

        $weatherData = null;
        $locationData = null;

        if ($city) {
            // Geocode city to get latitude and longitude
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
            $locationData = $geoData[0];
        } elseif ($lat && $lon) {
            // Get location details from coordinates (reverse geocoding)
            $reverseGeoData = Http::get("http://api.openweathermap.org/geo/1.0/reverse", [
                'lat' => $lat,
                'lon' => $lon,
                'limit' => 1,
                'appid' => env('OPENWEATHER_API_KEY')
            ])->json();

            if (!empty($reverseGeoData)) {
                $locationData = $reverseGeoData[0];
            }
        } else {
            return response()->json(['error' => 'Please provide either a city name or latitude and longitude'], 400);
        }

        if ($lat && $lon) {
            // Fetch current weather data using coordinates
            $weather = Http::get("https://api.openweathermap.org/data/2.5/weather", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => env('OPENWEATHER_API_KEY'),
                'units' => $units
            ])->json();

            if (!empty($weather)) {
                $weatherData = $weather;
            } else {
                return response()->json(['error' => 'Could not fetch weather data for the provided coordinates'], 500);
            }
        }

        if ($weatherData && $locationData) {
            return response()->json([
                'location' => $locationData['name'] ?? null,
                'country' => $locationData['country'] ?? null,
                'temp' => $weatherData['main']['temp'],
                'description' => $weatherData['weather'][0]['description'],
                'icon' => $weatherData['weather'][0]['icon'],
                'humidity' => $weatherData['main']['humidity'],
                'wind_speed' => $weatherData['wind']['speed'],
                'date' => now()->toDateString()
            ]);
        } else {
            return response()->json(['error' => 'Could not retrieve weather information'], 500);
        }
    }

    /**
     * Get a 3-day weather forecast for a given city OR coordinates.
     */
    public function forecast(Request $request)
    {
        $lat = $request->query('lat');
        $lon = $request->query('lon');
        $city = $request->query('city');
        $units = $request->query('units', 'metric');

        $forecastData = null;

        if ($city) {
            // Geocode the city
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
        } elseif (!$lat || !$lon) {
            return response()->json(['error' => 'Please provide either a city name or latitude and longitude'], 400);
        }

        if ($lat && $lon) {
            // Fetch 5-day forecast data in 3-hour intervals
            $forecast = Http::get("https://api.openweathermap.org/data/2.5/forecast", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => env('OPENWEATHER_API_KEY'),
                'units' => $units
            ])->json();

            if (!empty($forecast) && isset($forecast['list'])) {
                $daily = collect($forecast['list'])->take(24)->map(function ($item) {
                    return [
                        'date' => $item['dt_txt'],
                        'temp' => $item['main']['temp'],
                        'description' => $item['weather'][0]['description'],
                        'icon' => $item['weather'][0]['icon'],
                    ];
                });
                $forecastData = $daily->chunk(8)->take(3);
            } else {
                return response()->json(['error' => 'Could not fetch forecast data for the provided coordinates'], 500);
            }
        }

        if ($forecastData) {
            return response()->json($forecastData);
        } else {
            return response()->json(['error' => 'Could not retrieve forecast information'], 500);
        }
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
