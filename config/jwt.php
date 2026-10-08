<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Signing
    |--------------------------------------------------------------------------
    |
    | Access tokens are JWTs signed with HMAC-SHA256 (HS256). The secret is
    | read from the JWT_SECRET environment variable and never leaves the server.
    |
    */

    'secret' => env('JWT_SECRET'),

    'algorithm' => 'HS256',

    'issuer' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Lifetimes
    |--------------------------------------------------------------------------
    |
    | The access token is short-lived and sent in the Authorization header.
    | The refresh token lives longer and is used only to get a new access token.
    |
    */

    'access_ttl' => (int) env('JWT_ACCESS_TTL', 15), // minutes

    'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 60 * 24 * 7), // minutes (7 days)

    'two_factor_ttl' => 5, // minutes to enter the 2FA code after a correct password

    /*
    | A rotated refresh token keeps working for a few seconds, so two browser
    | tabs that refresh at the same time do not log each other out.
    */
    'rotation_grace' => 30, // seconds

    /*
    |--------------------------------------------------------------------------
    | Refresh token cookie
    |--------------------------------------------------------------------------
    */

    'cookie' => [
        'name' => 'finplan_refresh',
        'path' => '/api/v1/auth',
        'secure' => (bool) env('JWT_COOKIE_SECURE', true),
        'same_site' => 'strict',
    ],

];
