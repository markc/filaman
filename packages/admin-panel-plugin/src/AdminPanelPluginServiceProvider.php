<?php

namespace FilaMan\AdminPanelPlugin;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AdminPanelPluginServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filaman-admin-panel-plugin')
            ->hasConfigFile('filaman-admin')
            ->hasViews('filaman-admin')
            ->hasRoute('web')
            ->hasMigrations(['create_plugins_table']);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(AdminPanelPlugin::class, function () {
            return new AdminPanelPlugin;
        });
    }

    public function packageBooted(): void
    {
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filaman-admin');

        // Register the admin panel if enabled
        if (config('filaman-admin.enabled', true)) {
            $this->registerAdminPanel();
        }
    }

    protected function registerAdminPanel(): void
    {
        // Register the admin panel provider
        $this->app->register(\FilaMan\AdminPanelPlugin\Providers\AdminPanelProvider::class);
    }
}
