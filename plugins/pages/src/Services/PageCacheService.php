<?php

namespace FilaMan\Pages\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class PageCacheService
{
    protected string $cacheKey = 'pages.content.';

    protected int $cacheTtl = 3600; // 1 hour

    public function getCachedPage(string $slug): ?array
    {
        $cacheKey = $this->cacheKey.$slug;

        if (! config('app.debug', false)) {
            return Cache::get($cacheKey);
        }

        return null;
    }

    public function cachePage(string $slug, array $pageData): void
    {
        if (! config('app.debug', false)) {
            $cacheKey = $this->cacheKey.$slug;
            Cache::put($cacheKey, $pageData, $this->cacheTtl);
        }
    }

    public function clearPageCache(string $slug): void
    {
        $cacheKey = $this->cacheKey.$slug;
        Cache::forget($cacheKey);
    }

    public function clearAllPagesCache(): void
    {
        Cache::forget('pages.all');

        // Clear individual page caches
        $pagesPath = __DIR__.'/../../resources/views/pages/';
        if (File::exists($pagesPath)) {
            $files = File::files($pagesPath);
            foreach ($files as $file) {
                if ($file->getExtension() === 'md') {
                    $slug = $file->getBasename('.md');
                    $this->clearPageCache($slug);
                }
            }
        }
    }

    public function getCachedAllPages(): ?array
    {
        if (! config('app.debug', false)) {
            return Cache::get('pages.all');
        }

        return null;
    }

    public function cacheAllPages(array $pages): void
    {
        if (! config('app.debug', false)) {
            Cache::put('pages.all', $pages, $this->cacheTtl);
        }
    }

    public function warmCache(): void
    {
        $this->info('Warming page cache...');

        // Get all pages and cache them
        $pages = \FilaMan\Pages\Models\Page::getAllFromFiles();
        $this->cacheAllPages($pages);

        // Cache individual pages
        foreach ($pages as $page) {
            if ($page->published) {
                $pageData = [
                    'slug' => $page->slug,
                    'title' => $page->title,
                    'description' => $page->description,
                    'category' => $page->category,
                    'order' => $page->order,
                    'seo_title' => $page->seo_title,
                    'seo_description' => $page->seo_description,
                    'content' => $page->content,
                    'content_html' => \Illuminate\Support\Str::markdown($page->content),
                    'custom_css' => $page->custom_css,
                    'custom_js' => $page->custom_js,
                    'updated_at' => file_exists($page->getFilePath()) ? date('c', filemtime($page->getFilePath())) : null,
                ];
                $this->cachePage($page->slug, $pageData);
            }
        }
    }

    protected function info(string $message): void
    {
        if (app()->runningInConsole()) {
            echo $message.PHP_EOL;
        }
    }
}
