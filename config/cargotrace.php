<?php

return [
    'app_name' => env('CARGOTRACE_APP_NAME', 'CargoTrace'),

    /*
    |--------------------------------------------------------------------------
    | Delay Thresholds (in Hours)
    |--------------------------------------------------------------------------
    | Deterministic thresholds evaluated by EtaService:
    | - Delay <= 0: On-Time
    | - 0 < Delay <= low_max_hours: LOW
    | - low_max_hours < Delay <= medium_max_hours: MEDIUM
    | - Delay > medium_max_hours: HIGH
    */
    'delay_thresholds' => [
        'low_max_hours'    => (int) env('CARGOTRACE_DELAY_LOW_MAX', 12),
        'medium_max_hours' => (int) env('CARGOTRACE_DELAY_MEDIUM_MAX', 48),
        'group_threshold'  => (int) env('CARGOTRACE_GROUP_DELAY_THRESHOLD', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Unique Scenario: Global Trade Lane
    |--------------------------------------------------------------------------
    */
    'primary_route' => [
        'origin'      => env('CARGOTRACE_PRIMARY_ORIGIN', 'Tanjung Priok, Jakarta (IDTPP)'),
        'destination' => env('CARGOTRACE_PRIMARY_DESTINATION', 'Port of Rotterdam, Netherlands (NLRTM)'),
        'focus_corridor' => 'Intercontinental Maritime & Air Cargo',
    ],

    /*
    |--------------------------------------------------------------------------
    | Key Performance Indicators
    |--------------------------------------------------------------------------
    */
    'kpis' => [
        'target_on_time_rate' => 90.0,
        'target_max_avg_delay_hours' => 24.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | External API Services (Nominatim & Open-Meteo)
    |--------------------------------------------------------------------------
    */
    'services' => [
        'geocoding' => [
            'provider' => env('GEOCODING_PROVIDER', 'nominatim'),
            'nominatim_url' => 'https://nominatim.openstreetmap.org/search',
            'user_agent' => env('CARGOTRACE_USER_AGENT', 'CargoTrace/1.0 (admin@cargotrace.app)'),
            'timeout_seconds' => (int) env('GEOCODING_TIMEOUT', 5),
        ],
        'weather' => [
            'provider' => env('WEATHER_PROVIDER', 'open-meteo'),
            'open_meteo_url' => 'https://api.open-meteo.com/v1/forecast',
            'timeout_seconds' => (int) env('WEATHER_TIMEOUT', 5),
        ],
    ],
];
