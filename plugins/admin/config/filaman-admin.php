<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Panel Enabled
    |--------------------------------------------------------------------------
    |
    | This value determines if the admin panel is enabled. When disabled,
    | the admin routes and panel will not be registered.
    |
    */
    'enabled' => env('FILAMAN_ADMIN_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where the admin panel will be accessible from.
    |
    */
    'path' => env('FILAMAN_ADMIN_PATH', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Brand Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your admin panel brand.
    |
    */
    'brand_name' => env('FILAMAN_ADMIN_BRAND', 'FilaMan Admin'),

    /*
    |--------------------------------------------------------------------------
    | Enabled Plugins
    |--------------------------------------------------------------------------
    |
    | List of plugin IDs that should be automatically loaded with the admin panel.
    | These plugins will be registered when the admin panel boots.
    |
    */
    'plugins' => [
        'pages', // Pages plugin is enabled by default
        // Add more plugin IDs here as they are developed
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin Discovery
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic plugin discovery.
    |
    */
    'discovery' => [
        'enabled' => true,
        'paths' => [
            base_path('plugins'),
        ],
        'pattern' => '*-plugin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin Repository
    |--------------------------------------------------------------------------
    |
    | Configuration for plugin repository and marketplace.
    |
    */
    'repository' => [
        'enabled' => false,
        'url' => 'https://plugins.filaman.dev/api',
        'cache_ttl' => 3600, // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin Development Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, provides additional tools for plugin development.
    |
    */
    'development_mode' => env('FILAMAN_DEV_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Default Plugin Settings
    |--------------------------------------------------------------------------
    |
    | Default settings applied to all plugins unless overridden.
    |
    */
    'plugin_defaults' => [
        'auto_discover_resources' => true,
        'auto_discover_pages' => true,
        'auto_discover_widgets' => true,
        'cache_enabled' => ! env('APP_DEBUG', false),
    ],
];
