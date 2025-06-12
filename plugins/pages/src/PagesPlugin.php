<?php

namespace FilaMan\Pages;

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
        // Register based on panel ID
        if ($panel->getId() === 'admin') {
            // Register the page management resource within the admin panel
            $panel->resources([
                \FilaMan\Pages\Filament\Resources\PageResource::class,
            ]);
        } elseif ($panel->getId() === 'pages') {
            // Public pages panel - no resources needed, just pages
            // Pages are registered in PagesPanelProvider
        }
    }

    public function boot(Panel $panel): void
    {
        // Runtime initialization for standard Filament v4 admin panel integration
        // All page management is handled through the admin panel resource
    }
}
