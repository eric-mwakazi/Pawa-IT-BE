# 🌦️ Laravel Weather API

A Laravel API backend that integrates with the OpenWeatherMap API to provide current weather, forecast, and geocoding information for any city in the world.

## 📦 Features

- Get **current weather** by city name
- Get a **3-day forecast** with temperature and weather descriptions
- **Geocode a city** to get its latitude and longitude
- Supports both **metric** and **imperial** units

---

## 🚀 Getting Started

### ✅ Prerequisites

- PHP 8.0+
- Composer
- Laravel 10+
- OpenWeatherMap API Key
- MySQL/PostgreSQL (if database needed later)

### 📥 Installation

1. Clone the repo:

```bash
git clone https://github.com/your-username/weather-api.git
cd weather-api
```

2. Install dependencies:

```bash
composer install
```

3. Copy the `.env` file and set up your environment:

```bash
cp .env.example .env
```

4. Generate application key:

```bash
php artisan key:generate
```

5. Set your OpenWeatherMap API key in `.env`:

```
OPENWEATHER_API_KEY=your_api_key_here
```

6. Run the Laravel development server:

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

---

## 🌐 API Endpoints

All endpoints are prefixed with `/api`.

### 1. 📍 Geocode a City

**GET** `/api/weather/geocode?city={city_name}`

**Query Params:**
- `city` (required): Name of the city (e.g., `Nairobi`)

**Response:**
```json
{
  "city": "Nairobi",
  "country": "KE",
  "lat": -1.2921,
  "lon": 36.8219
}
```

---

### 2. ☁️ Current Weather

**GET** `/api/weather/current?city={city_name}&units={units}`

**Query Params:**
- `city` (required): Name of the city
- `units` (optional): `metric` (default) or `imperial`

**Response:**
```json
{
  "location": "Nairobi",
  "country": "KE",
  "temp": 26.7,
  "description": "scattered clouds",
  "icon": "03d",
  "humidity": 50,
  "wind_speed": 3.5,
  "date": "2025-04-24"
}
```

---

### 3. 📅 3-Day Forecast

**GET** `/api/weather/forecast?city={city_name}&units={units}`

**Query Params:**
- `city` (required): Name of the city
- `units` (optional): `metric` (default) or `imperial`

**Response:**
```json
[
  [
    {
      "date": "2025-04-24 09:00:00",
      "temp": 25.1,
      "description": "light rain",
      "icon": "10d"
    },
    ...
  ],
  ...
]
```

---

## 🛠 Built With

- [Laravel](https://laravel.com/)
- [OpenWeatherMap API](https://openweathermap.org/api)
- PHP 8.0+

---

## 📄 License

MIT License. See `LICENSE` for more information.

---

## 🙋🏽‍♂️ Author

**Eric Mwakazi**  
[LinkedIn](https://linkedin.com/in/eric-mwakazi) • [GitHub](https://github.com/eric-mwakazi)

---

