<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalIntegrationService
{
    protected string $nominatimSearchUrl;
    protected string $nominatimReverseUrl;
    protected string $openMeteoUrl;
    protected string $userAgent;
    protected int $timeout;

    public function __construct()
    {
        $searchUrl = config('cargotrace.services.geocoding.nominatim_url', 'https://nominatim.openstreetmap.org/search');
        $this->nominatimSearchUrl = $searchUrl;
        $this->nominatimReverseUrl = str_replace('/search', '/reverse', $searchUrl);
        $this->openMeteoUrl = config('cargotrace.services.weather.open_meteo_url', 'https://api.open-meteo.com/v1/forecast');
        $this->userAgent = config('cargotrace.services.geocoding.user_agent', 'CargoTrace/1.0 (admin@cargotrace.app)');
        $this->timeout = (int) config('cargotrace.services.geocoding.timeout_seconds', 4);
    }

    /**
     * Resolve forward coordinates from a textual query using Nominatim OpenStreetMap.
     */
    public function geocode(string $query): ?array
    {
        $cleanQuery = trim($query);
        if (empty($cleanQuery)) {
            return null;
        }

        $cacheKey = 'geocode:' . md5(strtolower($cleanQuery));

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($cleanQuery) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                ])->timeout($this->timeout)->get($this->nominatimSearchUrl, [
                    'q' => $cleanQuery,
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 1,
                ]);

                if ($response->successful()) {
                    $results = $response->json();
                    if (! empty($results) && isset($results[0]['lat'], $results[0]['lon'])) {
                        $top = $results[0];
                        return [
                            'latitude' => round((float) $top['lat'], 7),
                            'longitude' => round((float) $top['lon'], 7),
                            'display_name' => $top['display_name'] ?? $cleanQuery,
                            'type' => $top['type'] ?? 'location',
                        ];
                    }
                }
            } catch (Exception $e) {
                Log::warning("Nominatim geocoding failed for '{$cleanQuery}': " . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Resolve reverse address from coordinates.
     */
    public function reverseGeocode(float $latitude, float $longitude): ?array
    {
        $lat = round($latitude, 5);
        $lng = round($longitude, 5);
        $cacheKey = "reverse_geocode:{$lat}:{$lng}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($lat, $lng) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                ])->timeout($this->timeout)->get($this->nominatimReverseUrl, [
                    'lat' => $lat,
                    'lon' => $lng,
                    'format' => 'json',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'display_name' => $data['display_name'] ?? null,
                        'city' => $data['address']['city'] ?? $data['address']['town'] ?? $data['address']['county'] ?? null,
                        'country' => $data['address']['country'] ?? null,
                    ];
                }
            } catch (Exception $e) {
                Log::warning("Nominatim reverse geocoding failed for ({$lat}, {$lng}): " . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Fetch current weather conditions for specific coordinates from Open-Meteo.
     */
    public function getWeather(float $latitude, float $longitude): ?array
    {
        $lat = round($latitude, 4);
        $lng = round($longitude, 4);
        $cacheKey = "weather:{$lat}:{$lng}";

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($lat, $lng) {
            try {
                $response = Http::timeout($this->timeout)->get($this->openMeteoUrl, [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'current_weather' => 'true',
                ]);

                if ($response->successful()) {
                    $current = $response->json('current_weather');
                    if ($current) {
                        $code = (int) ($current['weathercode'] ?? 0);
                        return [
                            'temperature' => (float) ($current['temperature'] ?? 0),
                            'windspeed' => (float) ($current['windspeed'] ?? 0),
                            'weathercode' => $code,
                            'condition' => $this->interpretWmoWeatherCode($code),
                            'is_day' => (int) ($current['is_day'] ?? 1),
                            'recorded_at' => $current['time'] ?? now()->toIso8601String(),
                        ];
                    }
                }
            } catch (Exception $e) {
                Log::warning("Open-Meteo weather fetch failed for ({$lat}, {$lng}): " . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Map WMO Weather Interpretation Codes to human-readable maritime/logistics terms.
     */
    public function interpretWmoWeatherCode(int $code): string
    {
        return match ($code) {
            0 => 'Clear Sky',
            1 => 'Mainly Clear',
            2 => 'Partly Cloudy',
            3 => 'Overcast',
            45, 48 => 'Foggy Conditions',
            51, 53, 55 => 'Drizzle',
            61, 63, 65 => 'Rain',
            71, 73, 75 => 'Snow Fall',
            77 => 'Snow Grains',
            80, 81, 82 => 'Rain Showers',
            85, 86 => 'Snow Showers',
            95 => 'Thunderstorm',
            96, 99 => 'Severe Thunderstorm & Hail',
            default => 'Moderate Marine Weather',
        };
    }
}
