<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración General de API
    |--------------------------------------------------------------------------
    */

    'rate_limit' => [
        'enabled' => env('API_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('API_RATE_LIMIT_ATTEMPTS', 60),
        'decay_minutes' => env('API_RATE_LIMIT_DECAY', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Logging de APIs
    |--------------------------------------------------------------------------
    */

    'logging' => [
        'enabled' => env('API_LOGGING_ENABLED', true),
        'log_requests' => env('API_LOG_REQUESTS', true),
        'log_responses' => env('API_LOG_RESPONSES', true),
        'log_headers' => env('API_LOG_HEADERS', false),
        'sensitive_fields' => [
            'password',
            'token',
            'api_key',
            'secret',
            'authorization',
            'credit_card',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Cache de APIs
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'enabled' => env('API_CACHE_ENABLED', true),
        'ttl' => env('API_CACHE_TTL', 3600), // 1 hora en segundos
        'prefix' => 'api_cache_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Retry (Reintentos)
    |--------------------------------------------------------------------------
    */

    'retry' => [
        'enabled' => env('API_RETRY_ENABLED', true),
        'max_attempts' => env('API_RETRY_MAX_ATTEMPTS', 3),
        'delay' => env('API_RETRY_DELAY', 1000), // milisegundos
        'multiplier' => env('API_RETRY_MULTIPLIER', 2), // Exponencial
    ],

    /*
    |--------------------------------------------------------------------------
    | Headers por Defecto
    |--------------------------------------------------------------------------
    */

    'default_headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'User-Agent' => 'Laravel-Sync-App/1.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Códigos de Estado HTTP
    |--------------------------------------------------------------------------
    */

    'status_codes' => [
        'success' => [200, 201, 202, 204],
        'client_error' => [400, 401, 403, 404, 422, 429],
        'server_error' => [500, 502, 503, 504],
        'retryable' => [408, 429, 500, 502, 503, 504],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Webhooks
    |--------------------------------------------------------------------------
    */

    'webhooks' => [
        'enabled' => env('WEBHOOKS_ENABLED', false),
        'secret' => env('WEBHOOK_SECRET'),
        'verify_signature' => env('WEBHOOK_VERIFY_SIGNATURE', true),
    ],

];