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
            'enabled' =>  true,

            // POST /auth/forgot-password
            'forgot_password' => [
                'max_attempts' => 5,
                'decay_seconds' => 60,
            ],

            // POST /auth/reset-password
            'reset_password' => [
                'max_attempts' => 10,
                'decay_seconds' => 60,
            ],

            // POST /auth/register
            'register' => [
                'max_attempts' => 10,
                'decay_seconds' => 60,
            ],
        ],
    ],
];


