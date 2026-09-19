<?php

return [
    'issuer' => env('SSO_ISSUER'),
    'client_id' => env('SSO_CLIENT_ID'),
    'client_secret' => env('SSO_CLIENT_SECRET'),
    'redirect_uri' => env('SSO_REDIRECT_URI'),
    'post_logout_redirect_uri' => env('SSO_POST_LOGOUT_REDIRECT_URI'),
    'scopes' => ['openid', 'profile', 'email'],
    'http' => [
        'timeout' => 10,
        'connect_timeout' => 3,
        'retry_times' => 2,
        'retry_sleep_ms' => 200,
    ],
    'clock_skew' => 60,
    'state_ttl' => 600,
    'discovery_cache_ttl' => 3600,
    'jwks_cache_ttl' => 3600,
    'routes' => [
        'redirect' => '/login/sso',
        'callback' => '/login/sso/callback',
        'logout' => '/logout/sso',
    ],
    'production_require_https' => true,
];
