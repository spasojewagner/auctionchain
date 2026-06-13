<?php

return [
    'name' => env('APP_NAME', 'AuctionChain'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'Europe/Belgrade',
    'locale' => 'sr',
    'fallback_locale' => 'en',
    'faker_locale' => 'sr_RS',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [],
    'maintenance' => [
        'driver' => 'file',
    ],
];
