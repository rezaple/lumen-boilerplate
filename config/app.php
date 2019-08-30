<?php

return [
    'name' => env('APP_NAME', 'Project Title'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'Asia/Jakarta',
    'locale' => 'id',
    'locales' => ['id','en'],
    'fallback_locale' => 'en', 
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
];
