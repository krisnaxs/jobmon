<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | PWA Enabled
    |--------------------------------------------------------------------------
    */

    'enabled' => (bool) env('PWA_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | PWA Manifest Output Path
    |--------------------------------------------------------------------------
    */

    'manifest_path' => env('PWA_MANIFEST_PATH', 'manifest.json'),

    /*
    |--------------------------------------------------------------------------
    | PWA Service Worker Ignored Paths
    |--------------------------------------------------------------------------
    */

    'ignore_paths' => env('PWA_IGNORE_PATHS', ['/api/', '/livewire/', '/_inertia/']),

    /*
    |--------------------------------------------------------------------------
    | PWA Service Worker Path
    |--------------------------------------------------------------------------
    */

    'sw_path' => env('PWA_SW_PATH', 'sw.js'),

    /*
    |--------------------------------------------------------------------------
    | PWA Manifest
    |--------------------------------------------------------------------------
    */

    'manifest' => [
        'id' => env('PWA_ID', '/'),
        'name' => env('APP_NAME', 'Laravel'),
        'short_name' => env('PWA_SHORT_NAME', 'Laravel'),
        'description' => env('PWA_DESCRIPTION', 'A Progressive Web Application setup for Laravel projects.'),
        'start_url' => env('PWA_START_URL', '/'),
        'scope' => env('PWA_SCOPE', '/'),
        'display_override' => ['standalone', 'minimal-ui'],
        'display' => env('PWA_DISPLAY', 'standalone'),
        'orientation' => env('PWA_ORIENTATION', 'any'),
        'background_color' => env('PWA_BACKGROUND_COLOR', '#ffffff'),
        'theme_color' => env('PWA_THEME_COLOR', '#000000'),
        'lang' => env('PWA_LANG', 'en'),
        'dir' => env('PWA_DIR', 'ltr'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PWA Icons
    |--------------------------------------------------------------------------
    */

    'icons' => [
        [
            'disk' => env('PWA_ICON_DISK', null),
            'path' => env('PWA_ICON_MOBILE_PATH', '/images/icons/icon-192x192.png'),
            'sizes' => env('PWA_ICON_MOBILE_SIZES', '192x192'),
            'type' => env('PWA_ICON_MOBILE_TYPE', 'image/png'),
        ],
        [
            'disk' => env('PWA_ICON_DISK', null),
            'path' => env('PWA_ICON_DESKTOP_PATH', '/images/icons/icon-512x512.png'),
            'sizes' => env('PWA_ICON_DESKTOP_SIZES', '512x512'),
            'type' => env('PWA_ICON_DESKTOP_TYPE', 'image/png'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PWA Apple Touch Icon
    |--------------------------------------------------------------------------
    */

    'apple_touch_icon' => env('PWA_APPLE_TOUCH_ICON', '/storage/images/icons/apple-touch-icon.png'),

    /*
    |--------------------------------------------------------------------------
    | PWA Service Worker Update Interval
    |--------------------------------------------------------------------------
    */

    // TAMBAHKAN (int) DI SINI
    'update_interval' => (int) env('PWA_UPDATE_INTERVAL', 24),

];
