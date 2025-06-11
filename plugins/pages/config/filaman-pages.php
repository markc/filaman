<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pages Plugin Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the FilaMan Pages plugin.
    | You can publish this config file and modify these settings as needed.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Pages Directory
    |--------------------------------------------------------------------------
    |
    | The directory where markdown pages are stored relative to the plugin root.
    | Default: resources/views/pages
    |
    */
    'pages_directory' => 'resources/views/pages',

    /*
    |--------------------------------------------------------------------------
    | Default Page
    |--------------------------------------------------------------------------
    |
    | The default page slug to display when no specific page is requested.
    |
    */
    'default_page' => 'home',

    /*
    |--------------------------------------------------------------------------
    | Page Template
    |--------------------------------------------------------------------------
    |
    | The default Blade template to use for rendering pages.
    |
    */
    'page_template' => 'filaman-pages::page',

    /*
    |--------------------------------------------------------------------------
    | Navigation Settings
    |--------------------------------------------------------------------------
    |
    | Settings for the automatic navigation generation.
    |
    */
    'navigation' => [
        'enabled' => true,
        'show_unpublished' => false,
        'sort_by' => 'order', // order, title, slug
        'sort_direction' => 'asc',
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for markdown processing.
    |
    */
    'markdown' => [
        'code_highlighting' => true,
        'table_of_contents' => false,
        'auto_links' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Settings
    |--------------------------------------------------------------------------
    |
    | Default SEO settings for pages.
    |
    */
    'seo' => [
        'site_name' => 'FilaMan',
        'default_description' => 'Filament v4.x Plugin Manager',
        'default_keywords' => 'filament, laravel, plugins, management',
    ],
];
