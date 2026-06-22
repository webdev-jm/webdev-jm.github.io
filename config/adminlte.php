<?php

use JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter;
use JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter;
use JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter;
use JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter;
use JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter;
use JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter;
use JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter;

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'Laravel Starter',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => 'Laravel Starter',
    'logo_img' => 'images/JM Starter.png',
    'logo_img_class' => 'brand-image elevation-0',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Admin Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'images/JM Starter.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'images/JM Starter.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => true,
    'usermenu_desc' => true,
    'usermenu_profile_url' => true,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => false,
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => true,
    'layout_dark_mode' => false,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'bg-gradient-dark',
    'classes_auth_header' => '',
    'classes_auth_body' => 'bg-gradient-dark',
    'classes_auth_footer' => 'text-center',
    'classes_auth_icon' => 'fa-fw text-light',
    'classes_auth_btn' => 'btn-flat btn-light',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => 'text-sm',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => 'nav-compact',
    'classes_topnav' => 'navbar-dark navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => array_values(array_filter([
        env('FEATURE_TICKETS', true) ? [
            'text' => 'tickets',
            'url' => 'tickets',
            'icon' => 'fas fa-fw fa-ticket-alt',
            'can' => ['ticket access', 'ticket response'],
            'active' => ['tickets', 'ticket*'],
        ] : null,

        env('FEATURE_AI_TESTING', true) ? [
            'text' => 'ai_testing',
            'url' => 'ai-testing',
            'icon' => 'fas fa-fw fa-robot',
            'can' => 'ai access',
            'active' => ['ai-testing'],
        ] : null,

        [
            'text' => 'settings',
            'url' => '#',
            'icon' => 'fa fa-fw fa-cog',
            'can' => ['user access', 'role access', 'company access', 'position access', 'system logs', 'system settings'],
            'submenu' => array_values(array_filter([
                env('FEATURE_ORG_STRUCTURES', true) ? [
                    'text' => 'org_structures',
                    'url' => 'org-structures',
                    'icon' => 'fas fa-fw fa-code-branch',
                    'can' => 'org structure access',
                    'active' => ['org-structures', 'org-structure*'],
                ] : null,

                env('FEATURE_POSITIONS', true) ? [
                    'text' => 'positions',
                    'url' => 'positions',
                    'icon' => 'fas fa-fw fa-user-tag',
                    'can' => 'position access',
                    'active' => ['positions', 'position*'],
                ] : null,

                env('FEATURE_COMPANIES', true) ? [
                    'text' => 'companies',
                    'url' => 'companies',
                    'icon' => 'fas fa-fw fa-building',
                    'can' => 'company access',
                    'active' => ['companies', 'company*'],
                ] : null,

                env('FEATURE_USERS', true) ? [
                    'text' => 'users',
                    'url' => 'users',
                    'icon' => 'fas fa-fw fa-users',
                    'can' => 'user access',
                    'active' => ['users', 'user*'],
                ] : null,

                env('FEATURE_ROLES', true) ? [
                    'text' => 'roles',
                    'url' => 'roles',
                    'icon' => 'fas fa-fw fa-user-lock',
                    'can' => 'role access',
                    'active' => ['roles', 'role*'],
                ] : null,

                env('FEATURE_SYSTEM_SETTINGS', true) ? [
                    'text' => 'system_settings',
                    'url' => 'system-setting',
                    'icon' => 'fas fa-fw fa-cogs',
                    'can' => 'system settings',
                    'active' => ['system-setting'],
                ] : null,

                env('FEATURE_SYSTEM_LOGS', true) ? [
                    'text' => 'system_logs',
                    'url' => 'system-logs',
                    'icon' => 'fas fa-fw fa-stream',
                    'can' => 'system logs',
                    'active' => ['system-logs'],
                ] : null,

                env('FEATURE_TRASH_BIN', true) ? [
                    'text' => 'trash_bin',
                    'url' => 'trash-bin',
                    'icon' => 'fas fa-fw fa-trash',
                    'can' => 'trash bin',
                    'active' => ['trash-bin'],
                ] : null,

                env('FEATURE_ERROR_LOGS', true) ? [
                    'text' => 'error_logs',
                    'url' => 'error-logs',
                    'icon' => 'fas fa-fw fa-bug',
                    'can' => 'system logs',
                    'active' => ['error-logs'],
                ] : null,

                env('FEATURE_PULSE', true) ? [
                    'text' => 'pulse',
                    'url' => '/pulse',
                    'icon' => 'fas fa-fw fa-wave-square',
                    'can' => 'system logs',
                    'active' => ['pulse'],
                ] : null,
            ])),
        ],
    ])),

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        GateFilter::class,
        HrefFilter::class,
        SearchFilter::class,
        ActiveFilter::class,
        ClassesFilter::class,
        LangFilter::class,
        DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '\vendor\select2\js\select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '\vendor\select2-bootstrap4-theme\select2-bootstrap4.min.css',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '\vendor\select2\css\select2.min.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '/vendor/sweetalert2/sweetalert2.all.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '/vendor/sweetalert2/sweetalert2.min.css',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
        'iCheckBoostrap' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '/vendor/icheck-bootstrap/icheck-bootstrap.min.css',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => true,

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    |
    | Master on/off switches for each system feature. Set via .env or directly.
    | These control whether entire UI sections are rendered, independently of
    | permissions (which still gate access when a feature is enabled).
    |
    */

    'features' => [

        // Auth & appearance
        'gmail_login' => env('FEATURE_GMAIL_LOGIN', false),
        'glass_skin' => env('FEATURE_GLASS_SKIN', true),
        'neumorphic_skin'  => env('FEATURE_NEUMORPHIC_SKIN', false),
        'claymorphic_skin' => env('FEATURE_CLAYMORPHIC_SKIN', false),
        'dark_mode' => env('FEATURE_DARK_MODE', true),
        'skin_switcher' => env('FEATURE_SKIN_SWITCHER', true),

        // Top navbar items
        'notifications' => env('FEATURE_NOTIFICATIONS', true),
        'online_users' => env('FEATURE_ONLINE_USERS', true),
        'chat_message' => env('FEATURE_CHAT_MESSAGE', true),

        // Sidebar modules
        'tickets' => env('FEATURE_TICKETS', true),
        'ai_testing' => env('FEATURE_AI_TESTING', true),
        'pulse' => env('FEATURE_PULSE', true),

        // User management sidebar sub-items
        'org_structures' => env('FEATURE_ORG_STRUCTURES', true),
        'positions' => env('FEATURE_POSITIONS', true),
        'companies' => env('FEATURE_COMPANIES', true),
        'users' => env('FEATURE_USERS', true),
        'roles' => env('FEATURE_ROLES', true),
        'system_settings' => env('FEATURE_SYSTEM_SETTINGS', true),
        'system_logs' => env('FEATURE_SYSTEM_LOGS', true),
        'trash_bin' => env('FEATURE_TRASH_BIN', true),
        'error_logs' => env('FEATURE_ERROR_LOGS', true),

        // Other
        'impersonation' => env('FEATURE_IMPERSONATION', true),
    ],
];
