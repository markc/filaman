<?php

namespace FilaMan\AdminPanelPlugin;

use FilaMan\AdminPanelPlugin\Filament\Resources\PluginResource;
use FilaMan\AdminPanelPlugin\Services\PluginManager;
use Filament\Contracts\Plugin;
use Filament\Panel;

class AdminPanelPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'admin-panel';
    }

    public function register(Panel $panel): void
    {
        // Register resources specific to admin panel management
        $panel->resources([
            PluginResource::class,
        ]);

        // Register pages
        $panel->pages([
            // We'll add custom pages here if needed
        ]);

        // Register widgets
        $panel->widgets([
            \FilaMan\AdminPanelPlugin\Filament\Widgets\PluginStatsWidget::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        // Runtime initialization
        view()->addNamespace('filaman-admin', __DIR__.'/../resources/views');

        // Register plugin manager
        app()->singleton(PluginManager::class, function () {
            return new PluginManager;
        });
    }

    /**
     * Get all available plugins
     */
    public function getAvailablePlugins(): array
    {
        return app(PluginManager::class)->getAvailablePlugins();
    }

    /**
     * Get all installed plugins
     */
    public function getInstalledPlugins(): array
    {
        return app(PluginManager::class)->getInstalledPlugins();
    }

    /**
     * Install a plugin
     */
    public function installPlugin(string $pluginName): bool
    {
        return app(PluginManager::class)->installPlugin($pluginName);
    }

    /**
     * Uninstall a plugin
     */
    public function uninstallPlugin(string $pluginName): bool
    {
        return app(PluginManager::class)->uninstallPlugin($pluginName);
    }

    /**
     * Enable a plugin
     */
    public function enablePlugin(string $pluginName): bool
    {
        return app(PluginManager::class)->enablePlugin($pluginName);
    }

    /**
     * Disable a plugin
     */
    public function disablePlugin(string $pluginName): bool
    {
        return app(PluginManager::class)->disablePlugin($pluginName);
    }
}
