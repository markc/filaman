<?php

namespace FilaMan\Pages;

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
            ->hasMigrations(['create_pages_table']);
    }

    public function packageRegistered(): void
    {
        // Bind the main plugin class to the service container
        $this->app->singleton(PagesPlugin::class, function () {
            return new PagesPlugin;
        });

        // Bind services
        $this->app->singleton(\FilaMan\Pages\Services\PageCacheService::class);
        $this->app->singleton(\FilaMan\Pages\Services\GfmMarkdownRenderer::class);
    }

    public function packageBooted(): void
    {
        // Register the view namespace for remaining admin views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filaman-pages');

        // Register migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register web routes (now empty - pages handled by Filament panel)
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Register admin routes (now empty - handled by Filament resources)
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');

        // Register API routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \FilaMan\Pages\Commands\MakePageCommand::class,
                \FilaMan\Pages\Commands\ListPagesCommand::class,
                \FilaMan\Pages\Commands\CachePagesCommand::class,
            ]);
        }
    }
}
