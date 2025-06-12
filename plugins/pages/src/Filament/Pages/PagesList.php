<?php

namespace FilaMan\Pages\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class PagesList extends Page
{
    protected string $view = 'filaman-pages::filament.pages.pages-list';

    protected static ?string $slug = '/';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationLabel = 'Home';

    protected static ?int $navigationSort = 1;

    public string $searchQuery = '';

    public function getPages(): array
    {
        $pagesPath = __DIR__.'/../../../resources/views/pages/';
        $pages = [];

        if (! File::exists($pagesPath)) {
            return $pages;
        }

        $files = File::files($pagesPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'md') {
                $slug = $file->getBasename('.md');
                $content = File::get($file->getPathname());
                $document = YamlFrontMatter::parse($content);
                $frontMatter = $document->matter();

                // Only include published pages
                if ($frontMatter['published'] ?? true) {
                    $pageData = [
                        'slug' => $slug,
                        'title' => $frontMatter['title'] ?? ucfirst(str_replace('-', ' ', $slug)),
                        'description' => $frontMatter['description'] ?? '',
                        'category' => $frontMatter['category'] ?? 'General',
                        'order' => $frontMatter['order'] ?? 999,
                        'url' => '/pages/'.$slug,
                        'content' => $document->body(),
                        'updated_at' => date('Y-m-d H:i:s', $file->getMTime()),
                    ];

                    // Apply search filter if provided
                    if ($this->searchQuery) {
                        $searchableText = strtolower($pageData['title'].' '.$pageData['description'].' '.$pageData['content']);
                        if (str_contains($searchableText, strtolower($this->searchQuery))) {
                            $pages[] = $pageData;
                        }
                    } else {
                        $pages[] = $pageData;
                    }
                }
            }
        }

        // Sort by category and order
        usort($pages, function ($a, $b) {
            if ($a['category'] !== $b['category']) {
                return strcmp($a['category'], $b['category']);
            }

            return $a['order'] <=> $b['order'];
        });

        return $pages;
    }

    public function getPagesByCategory(): array
    {
        $pages = $this->getPages();
        $categorized = [];

        foreach ($pages as $page) {
            $category = $page['category'];
            if (! isset($categorized[$category])) {
                $categorized[$category] = [];
            }
            $categorized[$category][] = $page;
        }

        return $categorized;
    }

    public function search(): void
    {
        // This will trigger a re-render with the search query
    }

    protected static bool $isDiscovered = false;
}
