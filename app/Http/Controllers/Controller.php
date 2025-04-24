<?php

// namespace App\Http\Controllers;

// abstract class Controller
// {
//     Route::get('/weather/current', [WeatherApiController::class, 'getCurrentWeather']);
//     Route::get('/weather/forecast', [WeatherApiController::class, 'getForecast']);
//     Route::get('/weather/geocode', [WeatherApiController::class, 'geocodeCity']);
// }
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class WeatherApiController extends Controller
{
    private $apiKey = 'YOUR_API_KEY'; // Replace with your actual API key
    private $openWeatherMapBaseUrl = 'https://api.openweathermap.org/data/2.5/';
    private $openWeatherMapGeoUrl = 'http://api.openweathermap.org/geo/1.0/';

    public function getCurrentWeather(Request $request)
    {
        $city = $request->query('city');
        if (!$city) {
            return response()->json(['error' => 'City parameter is required'], 400);
        }

        $client = new Client();
        $url = $this->openWeatherMapBaseUrl . "weather?q={$city}&appid={$this->apiKey}&units=metric"; // You can change units here

        try {
            $response = $client->get($url);
            $data = json_decode($response->getBody(), true);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch current weather data'], 500);
        }
    }

    public function getForecast(Request $request)
    {
         $city = $request->query('city');
        if (!$city) {
            return response()->json(['error' => 'City parameter is required'], 400);
        }

        $client = new Client();
        $url = $this->openWeatherMapBaseUrl . "forecast?q={$city}&appid={$this->apiKey}&units=metric&cnt=3"; //  3-day forecast

        try {
            $response = $client->get($url);
            $data = json_decode($response->getBody(), true);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch forecast data'], 500);
        }
    }

    public function geocodeCity(Request $request)
    {
        $city = $request->query('city');
        if (!$city) {
            return response()->json(['error' => 'City parameter is required'], 400);
        }

        $client = new Client();
        $url = $this->openWeatherMapGeoUrl . "direct?q={$city}&limit=1&appid={$this->apiKey}";

        try {
            $response = $client->get($url);
            $data = json_decode($response->getBody(), true);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to geocode city'], 500);
        }
    }
}
