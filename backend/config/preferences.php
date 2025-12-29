<?php
declare(strict_types=1);

return [
    'themes' => [
        'system' => 'System default',
        'light' => 'Light',
        'dark' => 'Dark',
    ],

    'default_theme' => env('APP_DEFAULT_THEME', 'system'),

    'primary_colors' => [
        'red' => 'Red',
        'orange' => 'Orange',
        'amber' => 'Amber',
        'yellow' => 'Yellow',
        'lime' => 'Lime',
        'green' => 'Green',
        'emerald' => 'Emerald',
        'teal' => 'Teal',
        'cyan' => 'Cyan',
        'sky' => 'Sky',
        'blue' => 'Blue',
        'indigo' => 'Indigo',
        'violet' => 'Violet',
        'purple' => 'Purple',
        'fuchsia' => 'Fuchsia',
        'pink' => 'Pink',
        'rose' => 'Rose',
    ],

    'neutral_colors' => [
        'slate' => 'Slate',
        'gray' => 'Gray',
        'zinc' => 'Zinc',
        'neutral' => 'Neutral',
        'stone' => 'Stone',
    ],

    'default_primary_color' => env('APP_DEFAULT_PRIMARY_COLOR', 'blue'),
    'default_neutral_color' => env('APP_DEFAULT_NEUTRAL_COLOR', 'slate'),
];
