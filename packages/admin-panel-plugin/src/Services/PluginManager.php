<?php

namespace FilaMan\AdminPanelPlugin\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PluginManager
{
    protected array $availablePlugins = [];

    protected array $installedPlugins = [];

    protected string $pluginsPath;

    public function __construct()
    {
        $this->pluginsPath = base_path('packages');
        $this->scanPlugins();
    }

    /**
     * Scan for available plugins in the packages directory
     */
    protected function scanPlugins(): void
    {
        if (! File::exists($this->pluginsPath)) {
            return;
        }

        $directories = File::directories($this->pluginsPath);

        foreach ($directories as $directory) {
            $composerFile = $directory.'/composer.json';

            if (File::exists($composerFile)) {
                $composerData = json_decode(File::get($composerFile), true);

                if (isset($composerData['type']) && $composerData['type'] === 'laravel-plugin') {
                    $pluginName = basename($directory);
                    $this->availablePlugins[$pluginName] = [
                        'name' => $composerData['name'] ?? $pluginName,
                        'description' => $composerData['description'] ?? '',
                        'version' => $composerData['version'] ?? 'dev',
                        'authors' => $composerData['authors'] ?? [],
                        'path' => $directory,
                        'installed' => $this->isPluginInstalled($pluginName),
                        'enabled' => $this->isPluginEnabled($pluginName),
                    ];
                }
            }
        }
    }

    /**
     * Get all available plugins
     */
    public function getAvailablePlugins(): array
    {
        return $this->availablePlugins;
    }

    /**
     * Get all installed plugins
     */
    public function getInstalledPlugins(): array
    {
        return array_filter($this->availablePlugins, function ($plugin) {
            return $plugin['installed'];
        });
    }

    /**
     * Check if a plugin is installed
     */
    public function isPluginInstalled(string $pluginName): bool
    {
        // Check if plugin exists in database (if table exists)
        if (Schema::hasTable('plugins')) {
            return DB::table('plugins')
                ->where('name', $pluginName)
                ->exists();
        }

        // Fallback: check if plugin is in composer.json
        $rootComposer = json_decode(File::get(base_path('composer.json')), true);
        $packageName = $this->availablePlugins[$pluginName]['name'] ?? null;

        if ($packageName) {
            return isset($rootComposer['require'][$packageName]) ||
                   isset($rootComposer['require-dev'][$packageName]);
        }

        return false;
    }

    /**
     * Check if a plugin is enabled
     */
    public function isPluginEnabled(string $pluginName): bool
    {
        // Check database first
        if (Schema::hasTable('plugins')) {
            $plugin = DB::table('plugins')
                ->where('name', $pluginName)
                ->first();

            return $plugin && $plugin->enabled;
        }

        // Fallback to config
        $enabledPlugins = config('filaman-admin.plugins', []);

        return in_array(str_replace('-plugin', '', $pluginName), $enabledPlugins);
    }

    /**
     * Install a plugin
     */
    public function installPlugin(string $pluginName): bool
    {
        try {
            if (! isset($this->availablePlugins[$pluginName])) {
                throw new \Exception("Plugin {$pluginName} not found");
            }

            $plugin = $this->availablePlugins[$pluginName];

            // Add to composer.json repositories if not already there
            $this->addToComposerRepositories($pluginName, $plugin['path']);

            // Run composer require
            $packageName = $plugin['name'];
            Artisan::call('config:clear');

            // Since we're using path repositories, we can just update autoload
            exec('composer dump-autoload', $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Failed to update autoloader');
            }

            // Run plugin migrations if any
            $migrationsPath = $plugin['path'].'/database/migrations';
            if (File::exists($migrationsPath)) {
                Artisan::call('migrate', [
                    '--path' => str_replace(base_path().'/', '', $migrationsPath),
                ]);
            }

            // Record in database
            if (Schema::hasTable('plugins')) {
                DB::table('plugins')->insert([
                    'name' => $pluginName,
                    'enabled' => true,
                    'version' => $plugin['version'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Clear caches
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to install plugin {$pluginName}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Uninstall a plugin
     */
    public function uninstallPlugin(string $pluginName): bool
    {
        try {
            // Remove from database
            if (Schema::hasTable('plugins')) {
                DB::table('plugins')->where('name', $pluginName)->delete();
            }

            // Remove from composer.json require section
            $this->removeFromComposer($pluginName);

            // Clear caches
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to uninstall plugin {$pluginName}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Enable a plugin
     */
    public function enablePlugin(string $pluginName): bool
    {
        try {
            if (Schema::hasTable('plugins')) {
                DB::table('plugins')
                    ->where('name', $pluginName)
                    ->update(['enabled' => true, 'updated_at' => now()]);
            }

            // Clear caches
            Artisan::call('config:clear');
            Artisan::call('route:clear');

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to enable plugin {$pluginName}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Disable a plugin
     */
    public function disablePlugin(string $pluginName): bool
    {
        try {
            if (Schema::hasTable('plugins')) {
                DB::table('plugins')
                    ->where('name', $pluginName)
                    ->update(['enabled' => false, 'updated_at' => now()]);
            }

            // Clear caches
            Artisan::call('config:clear');
            Artisan::call('route:clear');

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to disable plugin {$pluginName}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Add plugin to composer repositories
     */
    protected function addToComposerRepositories(string $pluginName, string $path): void
    {
        $composerFile = base_path('composer.json');
        $composer = json_decode(File::get($composerFile), true);

        // Add to repositories if not exists
        if (! isset($composer['repositories'])) {
            $composer['repositories'] = [];
        }

        $repoExists = false;
        foreach ($composer['repositories'] as $repo) {
            if (isset($repo['url']) && $repo['url'] === $path) {
                $repoExists = true;
                break;
            }
        }

        if (! $repoExists) {
            $composer['repositories'][] = [
                'type' => 'path',
                'url' => 'packages/'.$pluginName,
                'options' => ['symlink' => true],
            ];
        }

        // Add to require if not exists
        $packageName = $this->availablePlugins[$pluginName]['name'];
        if (! isset($composer['require'][$packageName])) {
            $composer['require'][$packageName] = '*';
        }

        File::put($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Remove plugin from composer
     */
    protected function removeFromComposer(string $pluginName): void
    {
        $composerFile = base_path('composer.json');
        $composer = json_decode(File::get($composerFile), true);

        $packageName = $this->availablePlugins[$pluginName]['name'] ?? null;

        if ($packageName) {
            unset($composer['require'][$packageName]);
            unset($composer['require-dev'][$packageName]);
        }

        File::put($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
