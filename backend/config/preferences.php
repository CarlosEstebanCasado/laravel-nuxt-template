<?php
declare(strict_types=1);

return [
    'themes' => [
        'system' => 'System default',
        'light' => 'Light',
        'dark' => 'Dark',
    ],

    'default_theme' => env('APP_DEFAULT_THEME', 'system'),
];
