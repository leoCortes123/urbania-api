<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Límites de tasa para los diferentes rate limiters de la aplicación.
    | Estos valores pueden ser sobrescritos via variables de entorno.
    |
    */

    'login' => [
        'max_attempts' => (int) env('RATE_LIMIT_LOGIN_MAX_ATTEMPTS', 5),
        'decay_minutes' => (int) env('RATE_LIMIT_LOGIN_DECAY_MINUTES', 15),
    ],

    'refresh' => [
        'max_attempts' => (int) env('RATE_LIMIT_REFRESH_MAX_ATTEMPTS', 10),
        'decay_minutes' => (int) env('RATE_LIMIT_REFRESH_DECAY_MINUTES', 15),
    ],

    'api' => [
        'max_attempts' => (int) env('RATE_LIMIT_API_MAX_ATTEMPTS', 1000),
        'decay_minutes' => (int) env('RATE_LIMIT_API_DECAY_MINUTES', 1),
    ],
];
