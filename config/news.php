<?php

return [

    /*
    |--------------------------------------------------------------------------
    | News API (Go backend)
    |--------------------------------------------------------------------------
    |
    | Base URL of the existing Go (Echo) news API. Local dev points at the
    | host machine; in production the FE container reaches the backend on
    | the same VPS.
    |
    */

    'api_url' => env('NEWS_API_URL', 'http://localhost:8082'),

    // HTTP client timeout in seconds.
    'timeout' => (int) env('NEWS_API_TIMEOUT', 5),

    // How long /news responses are cached, in seconds.
    'cache_ttl' => (int) env('NEWS_CACHE_TTL', 300),

    // Timeout for POST /news/refresh — the backend calls NewsAPI upstream
    // (up to 15s), so this must be longer than the regular timeout.
    'refresh_timeout' => (int) env('NEWS_REFRESH_TIMEOUT', 20),

    // Shared secret sent as X-Refresh-Token; must match the backend's
    // REFRESH_TOKEN env. Leave empty if the backend has none set.
    'refresh_token' => env('NEWS_REFRESH_TOKEN', ''),

    // Items per page on the index page (API returns the full list).
    'per_page' => (int) env('NEWS_PER_PAGE', 12),

];
