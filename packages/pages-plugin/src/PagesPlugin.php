<?php

namespace FilaMan\PagesPlugin;

use Filament\Contracts\Plugin;
use Filament\Panel;

class PagesPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'pages';
    }

    public function register(Panel $panel): void
    {
        // The pages themselves are unauthenticated, so they won't be
        // registered directly within the Filament panel.
        // This plugin primarily serves frontend functionality outside the panel.
        // However, we ensure its service provider and routes are loaded.

        // Future: Could register admin resources for managing pages here
        // $panel->resources([
        //     Resources\PageResource::class,
        // ]);
    }

    public function boot(Panel $panel): void
    {
        // Any runtime initialization specific to the plugin
        // (e.g., event listeners, view composers, if needed)

        // Register view namespace for the plugin
        view()->addNamespace('filaman-pages', __DIR__.'/../resources/views');
    }
}
