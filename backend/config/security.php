<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Hardening Defaults
    |--------------------------------------------------------------------------
    |
    | Central place for rate limits / security-related toggles that should be
    | adjustable without code changes (via environment variables).
    |
    */

    'throttling' => [
        'auth' => [
            'enabled' => env('AUTH_THROTTLE_ENABLED', true),

            // POST /auth/forgot-password
            'forgot_password' => [
                'max_attempts' => (int) env('AUTH_THROTTLE_FORGOT_PASSWORD_MAX', 5),
                'decay_seconds' => (int) env('AUTH_THROTTLE_FORGOT_PASSWORD_DECAY', 60),
            ],

            // POST /auth/reset-password
            'reset_password' => [
                'max_attempts' => (int) env('AUTH_THROTTLE_RESET_PASSWORD_MAX', 10),
                'decay_seconds' => (int) env('AUTH_THROTTLE_RESET_PASSWORD_DECAY', 60),
            ],

            // POST /auth/register
            'register' => [
                'max_attempts' => (int) env('AUTH_THROTTLE_REGISTER_MAX', 10),
                'decay_seconds' => (int) env('AUTH_THROTTLE_REGISTER_DECAY', 60),
            ],
        ],
    ],
];


