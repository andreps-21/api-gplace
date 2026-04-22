<?php

/*
| Se CORS_ALLOWED_ORIGINS no .env estiver vazio ou ausente, usa-se esta lista
| (evita produção sem nenhuma origem e browser a mostrar só "CORS Missing").
*/
$corsOriginsFromEnv = env('CORS_ALLOWED_ORIGINS');
$defaultOriginsList = 'https://gplace.gooding.solutions,https://www.gplace.gooding.solutions,http://localhost:3000,http://127.0.0.1:3000,http://localhost:3005,http://127.0.0.1:3005';
$corsOriginsList = (is_string($corsOriginsFromEnv) && trim($corsOriginsFromEnv) !== '')
    ? $corsOriginsFromEnv
    : $defaultOriginsList;

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    /*
    | Origens permitidas (lista separada por vírgulas no .env: CORS_ALLOWED_ORIGINS).
    */
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', $corsOriginsList)
    ))),

    /*
    | HTTPS em subdomínios *.gooding.solutions (útil se o front usar outro host).
    | Em APP_ENV=local, qualquer porta em localhost/127.0.0.1 (ex.: Next em :3005).
    */
    'allowed_origins_patterns' => array_values(array_filter([
        '#^https://([a-z0-9-]+\.)*gooding\.solutions$#i',
        env('APP_ENV') === 'local' ? '#^http://(localhost|127\.0\.0\.1):\d+$#' : null,
    ])),

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
