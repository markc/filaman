<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto-discover and enable plugins
        $this->autoDiscoverPlugins();
    }

    /**
     * Automatically discover and enable all plugins in plugins/ directory
     */
    protected function autoDiscoverPlugins(): void
    {
        // Only run if database is available
        if (! app()->runningInConsole() || $this->app->runningUnitTests()) {
            return;
        }

        // Skip if plugins table doesn't exist
        if (! Schema::hasTable('plugins')) {
            return;
        }

        $pluginsPath = base_path('plugins');

        if (! File::exists($pluginsPath)) {
            return;
        }

        $directories = File::directories($pluginsPath);

        foreach ($directories as $directory) {
            $composerFile = $directory.'/composer.json';

            if (File::exists($composerFile)) {
                $composerData = json_decode(File::get($composerFile), true);

                if (isset($composerData['type']) && $composerData['type'] === 'laravel-plugin') {
                    $pluginName = basename($directory);

                    // Auto-enable plugin if not already in database
                    DB::table('plugins')->updateOrInsert(
                        ['name' => $pluginName],
                        [
                            'name' => $pluginName,
                            'display_name' => $composerData['name'] ?? $pluginName,
                            'description' => $composerData['description'] ?? '',
                            'version' => $composerData['version'] ?? 'dev',
                            'enabled' => true,
                            'author' => $composerData['authors'][0]['name'] ?? 'Unknown',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
