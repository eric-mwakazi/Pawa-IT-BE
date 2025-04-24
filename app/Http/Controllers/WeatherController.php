<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    /**
     * Retrieves current weather details based on either city name or coordinates.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function current(Request $request)
    {
        $lat = $request->query('lat');
        $lon = $request->query('lon');
        $city = $request->query('city');
        $units = $request->query('units', 'metric'); // Default to metric

        $weatherData = null;
        $locationData = null;

        if ($city) {
            // Geocode the city to obtain latitude and longitude
            $geoResponse = Http::get("http://api.openweathermap.org/geo/1.0/direct", [
                'q' => $city,
                'limit' => 1,
                'appid' => env('OPENWEATHER_API_KEY')
            ]);

            if ($geoResponse->failed()) {
                return response()->json(['error' => 'Failed to geocode city'], $geoResponse->status());
            }

            $geoData = $geoResponse->json();

            if (empty($geoData)) {
                return response()->json(['error' => 'City not found'], 404);
            }

            $lat = $geoData[0]['lat'];
            $lon = $geoData[0]['lon'];
            $locationData = $geoData[0];
        } elseif (is_numeric($lat) && is_numeric($lon)) {
            // Reverse geocode to get location details from coordinates
            $reverseGeoResponse = Http::get("http://api.openweathermap.org/geo/1.0/reverse", [
                'lat' => $lat,
                'lon' => $lon,
                'limit' => 1,
                'appid' => env('OPENWEATHER_API_KEY')
            ]);

            if ($reverseGeoResponse->failed()) {
                return response()->json(['error' => 'Failed to perform reverse geocoding'], $reverseGeoResponse->status());
            }

            $reverseGeoData = $reverseGeoResponse->json();

            if (!empty($reverseGeoData)) {
                $locationData = $reverseGeoData[0];
            }
        } else {
            return response()->json(['error' => 'Please provide either a valid city name or valid latitude and longitude coordinates'], 400);
        }

        // Fetch weather data if latitude and longitude are available
        if ($lat && $lon) {
            $weatherResponse = Http::get("https://api.openweathermap.org/data/2.5/weather", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => env('OPENWEATHER_API_KEY'),
                'units' => $units
            ]);

            if ($weatherResponse->failed()) {
                return response()->json(['error' => 'Failed to fetch weather data'], $weatherResponse->status());
            }

            $weatherData = $weatherResponse->json();
        }

        // Return weather information if both weather and location data are available
        if ($weatherData && $locationData) {
            return response()->json([
                'location' => $locationData['name'] ?? null,
                'country' => $locationData['country'] ?? null,
                'temperature' => $weatherData['main']['temp'] ?? null,
                'description' => $weatherData['weather'][0]['description'] ?? null,
                'icon' => $weatherData['weather'][0]['icon'] ?? null,
                'humidity' => $weatherData['main']['humidity'] ?? null,
                'wind_speed' => $weatherData['wind']['speed'] ?? null,
                'date' => now()->toDateString()
            ]);
        } else {
            return response()->json(['error' => 'Could not retrieve complete weather information'], 500);
        }
    }

    /**
     * Retrieves a 3-day weather forecast based on either city name or coordinates.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forecast(Request $request)
    {
        $lat = $request->query('lat');
        $lon = $request->query('lon');
        $city = $request->query('city');
        $units = $request->query('units', 'metric'); // Default to metric

        $forecastData = null;

        if ($city) {
            // Geocode the city
            $geoResponse = Http::get("http://api.openweathermap.org/geo/1.0/direct", [
                'q' => $city,
                'limit' => 1,
                'appid' => env('OPENWEATHER_API_KEY')
            ]);

            if ($geoResponse->failed()) {
                return response()->json(['error' => 'Failed to geocode city'], $geoResponse->status());
            }

            $geoData = $geoResponse->json();

            if (empty($geoData)) {
                return response()->json(['error' => 'City not found'], 404);
            }

            $lat = $geoData[0]['lat'];
            $lon = $geoData[0]['lon'];
        } elseif (!is_numeric($lat) || !is_numeric($lon)) {
            return response()->json(['error' => 'Please provide either a valid city name or valid latitude and longitude coordinates'], 400);
        }

        if ($lat && $lon) {
            // Fetch 5-day forecast data in 3-hour intervals
            $forecastResponse = Http::get("https://api.openweathermap.org/data/2.5/forecast", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => env('OPENWEATHER_API_KEY'),
                'units' => $units
            ]);

            if ($forecastResponse->failed()) {
                return response()->json(['error' => 'Failed to fetch forecast data'], $forecastResponse->status());
            }

            $forecast = $forecastResponse->json();

            if (!empty($forecast) && isset($forecast['list'])) {
                $daily = collect($forecast['list'])->take(24)->map(function ($item) {
                    return [
                        'datetime' => $item['dt_txt'],
                        'temperature' => $item['main']['temp'] ?? null,
                        'description' => $item['weather'][0]['description'] ?? null,
                        'icon' => $item['weather'][0]['icon'] ?? null,
                    ];
                });
                $forecastData = $daily->chunk(8)->take(3);
            } else {
                return response()->json(['error' => 'Could not retrieve forecast data for the provided coordinates'], 500);
            }
        }

        if ($forecastData) {
            return response()->json($forecastData);
        } else {
            return response()->json(['error' => 'Could not retrieve forecast information'], 500);
        }
    }

    /**
     * Geocodes a city name to retrieve its latitude and longitude.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function geocodeCity(Request $request)
    {
        $city = $request->query('city');

        $geoResponse = Http::get("http://api.openweathermap.org/geo/1.0/direct", [
            'q' => $city,
            'limit' => 1,
            'appid' => env('OPENWEATHER_API_KEY')
        ]);

        if ($geoResponse->failed()) {
            return response()->json(['error' => 'Failed to geocode city'], $geoResponse->status());
        }

        $geoData = $geoResponse->json();

        if (empty($geoData)) {
            return response()->json(['error' => 'City not found'], 404);
        }

        return response()->json([
            'city' => $geoData[0]['name'] ?? null,
            'country' => $geoData[0]['country'] ?? null,
            'latitude' => $geoData[0]['lat'] ?? null,
            'longitude' => $geoData[0]['lon'] ?? null
        ]);
    }
}
