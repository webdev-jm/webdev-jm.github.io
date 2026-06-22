<?php

return [
    'name' => 'JM Starter',
    'offline_sync_enabled' => env('FEATURE_OFFLINE_SYNC', true),
    'manifest' => [
        'name' => env('APP_NAME', 'My PWA App'),
        'short_name' => 'JM Starter',
        'start_url' => '/',
        'background_color' => '#ffffff',
        'theme_color' => '#000000',
        'display' => 'standalone',
        'orientation'=> 'any',
        'status_bar'=> 'black',
        'icons' => [
            '72x72' => [
                'path' => '/images/pwa/android/launchericon-72x72.png',
                'purpose' => 'any'
            ],
            '96x96' => [
                'path' => '/images/pwa/android/launchericon-96x96.png',
                'purpose' => 'any'
            ],
            '128x128' => [
                'path' => '/images/pwa/ios/128.png',
                'purpose' => 'any'
            ],
            '144x144' => [
                'path' => '/images/pwa/android/launchericon-144x144.png',
                'purpose' => 'any'
            ],
            '152x152' => [
                'path' => '/images/pwa/ios/152.png',
                'purpose' => 'any'
            ],
            '192x192' => [
                'path' => '/images/pwa/android/launchericon-192x192.png',
                'purpose' => 'any'
            ],
            '512x512' => [
                'path' => '/images/pwa/android/launchericon-512x512.png',
                'purpose' => 'any'
            ],
        ],
        'splash' => [],
        'shortcuts' => [
            [
                'name' => 'Shortcut Link 1',
                'description' => 'Shortcut Link 1 Description',
                'url' => '/shortcutlink1',
                'icons' => [
                    "src" => "/favicons/favicon.png",
                    "purpose" => "any"
                ]
            ],
            [
                'name' => 'Shortcut Link 2',
                'description' => 'Shortcut Link 2 Description',
                'url' => '/shortcutlink2'
            ]
        ],
        'custom' => []
    ]
];
