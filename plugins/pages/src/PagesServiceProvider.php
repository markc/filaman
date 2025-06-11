<?php

namespace FilaMan\Pages;

use Illuminate\Support\Facades\File;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PagesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider from spatie/laravel-package-tools.
         * It helps streamline package development.
         */
        $package
            ->name('filaman-pages')
            ->hasConfigFile('filaman-pages') // For publishing plugin-specific config
            ->hasViews('filaman-pages') // Publishes views from `resources/views`
            ->hasRoute('web'); // Loads routes from `routes/web.php`
    }

    public function packageRegistered(): void
    {
        // Bind the main plugin class to the service container
        $this->app->singleton(PagesPlugin::class, function () {
            return new PagesPlugin;
        });
    }

    public function packageBooted(): void
    {
        // Load helper functions
        require_once __DIR__.'/helpers.php';
        
        // Register the view namespace manually to ensure it's available
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filaman-pages');
    }
}
