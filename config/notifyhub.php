<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable or disable the package entirely
    |--------------------------------------------------------------------------
    |
    | Set NOTIFYHUB_ENABLED=false in local/test environments to silence all
    | outbound requests without removing any code.
    |
    */
    'enabled' => env('NOTIFYHUB_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | NotifyHub server URL
    |--------------------------------------------------------------------------
    |
    | Full base URL of the NotifyHub server, e.g. https://notifyhub.example.com
    | The package appends /api/v1/events automatically.
    |
    */
    'url' => env('NOTIFYHUB_URL'),

    /*
    |--------------------------------------------------------------------------
    | Project ingest key
    |--------------------------------------------------------------------------
    |
    | The X-Project-Key value created by `php artisan notifyhub:setup` on the
    | NotifyHub server. Each project has its own key.
    |
    */
    'ingest_key' => env('NOTIFYHUB_INGEST_KEY'),

    /*
    |--------------------------------------------------------------------------
    | HTTP request timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => env('NOTIFYHUB_TIMEOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Retry configuration
    |--------------------------------------------------------------------------
    |
    | retry_times      - number of retry attempts (0 to disable)
    | retry_sleep_ms   - wait between attempts in milliseconds
    |
    */
    'retry_times' => env('NOTIFYHUB_RETRY_TIMES', 2),
    'retry_sleep_ms' => env('NOTIFYHUB_RETRY_SLEEP_MS', 200),

    /*
    |--------------------------------------------------------------------------
    | Auto-report logged exceptions
    |--------------------------------------------------------------------------
    |
    | When enabled, the package automatically forwards exceptions that are
    | logged at or above the configured level.
    | This hooks into Laravel's MessageLogged event.
    |
    */
    'auto_report_exceptions' => env('NOTIFYHUB_AUTO_REPORT', false),
    'auto_report_min_level' => env('NOTIFYHUB_AUTO_REPORT_MIN_LEVEL', 'error'),

];
