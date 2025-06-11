<?php

namespace FilaMan\PagesPlugin;

use Illuminate\Support\Facades\File;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PagesPluginServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider from spatie/laravel-package-tools.
         * It helps streamline package development.
         */
        $package
            ->name('filaman-pages-plugin')
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
        // Register the view namespace manually to ensure it's available
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filaman-pages');

        // Provide a helper for plugin paths if not already globally available.
        // In a truly plugin-based architecture, this helper might live in a shared
        // 'core-utilities-plugin' or similar, but for this exercise, we define it here.
        if (! function_exists('filaman_plugin_path')) {
            function filaman_plugin_path($pluginName, $path = '')
            {
                return base_path('packages/'.$pluginName.'-plugin/'.ltrim($path, '/'));
            }
        }

        // Register additional view helpers for markdown processing
        if (! function_exists('filaman_get_pages')) {
            function filaman_get_pages()
            {
                $pages = [];
                $pagesDirectory = filaman_plugin_path('pages', 'resources/views/pages');

                if (File::isDirectory($pagesDirectory)) {
                    $files = File::files($pagesDirectory);
                    foreach ($files as $file) {
                        if ($file->getExtension() === 'md') {
                            $content = File::get($file->getPathname());
                            $document = \Spatie\YamlFrontMatter\YamlFrontMatter::parse($content);
                            $frontMatter = $document->matter();

                            // Ensure title and slug exist for navigation
                            if (isset($frontMatter['title']) && isset($frontMatter['slug'])) {
                                $pages[] = [
                                    'title' => $frontMatter['title'],
                                    'slug' => $frontMatter['slug'],
                                    'order' => $frontMatter['order'] ?? 999, // Default order for sorting
                                    'description' => $frontMatter['description'] ?? '',
                                    'published' => $frontMatter['published'] ?? true,
                                ];
                            }
                        }
                    }
                }

                // Sort pages by 'order' from front matter
                usort($pages, function ($a, $b) {
                    return $a['order'] <=> $b['order'];
                });

                // Filter published pages only
                return array_filter($pages, fn ($page) => $page['published']);
            }
        }
    }
}
