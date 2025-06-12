<?php

namespace FilaMan\Pages\Services;

use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class NavigationService
{
    public function getNavigationItems(): array
    {
        $pagesPath = base_path('plugins/pages/resources/views/pages');

        if (! File::exists($pagesPath)) {
            return [];
        }

        $pages = [];
        $files = File::files($pagesPath);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            $slug = $file->getBasename('.md');
            $content = File::get($file->getPathname());
            $document = YamlFrontMatter::parse($content);

            // Check if page is published (default to true if not specified)
            $published = $document->matter('published', true);
            if (! $published) {
                continue;
            }

            $pages[] = [
                'slug' => $slug,
                'title' => $document->matter('title', $this->formatTitle($slug)),
                'order' => $document->matter('order', 999),
                'icon' => $document->matter('icon', 'heroicon-o-document-text'),
                'description' => $document->matter('description', ''),
            ];
        }

        // Sort pages by order, then by title
        usort($pages, function ($a, $b) {
            if ($a['order'] === $b['order']) {
                return strcmp($a['title'], $b['title']);
            }

            return $a['order'] <=> $b['order'];
        });

        // Add "Pages" navigation item first (gets special Filament treatment) - this will be the Home page
        $navigationItems = [];

        // Find the home page and add it first as "Pages"
        $homePageIndex = null;
        foreach ($pages as $index => $page) {
            if ($page['slug'] === 'home') {
                $homePageIndex = $index;
                break;
            }
        }

        if ($homePageIndex !== null) {
            $homePage = $pages[$homePageIndex];
            $navigationItems[] = NavigationItem::make('Pages')
                ->url(url('/pages/'.$homePage['slug']))
                ->icon('heroicon-o-home')
                ->isActiveWhen(fn () => request()->is('pages/'.$homePage['slug']) ||
                    (request()->route() && request()->route()->parameter('slug') === $homePage['slug'])
                );
            // Remove home page from the regular pages array
            unset($pages[$homePageIndex]);
        }

        // Add "Home" navigation item for the pages index
        $navigationItems[] = NavigationItem::make('Home')
            ->url(url('/pages'))
            ->icon('heroicon-o-document-duplicate')
            ->isActiveWhen(fn () => request()->is('pages') && ! request()->route()->parameter('slug'));

        // Convert remaining pages to NavigationItem objects
        foreach ($pages as $page) {
            $navigationItems[] = NavigationItem::make($page['title'])
                ->url(url('/pages/'.$page['slug']))
                ->icon($page['icon'])
                ->isActiveWhen(fn () => request()->is('pages/'.$page['slug']) ||
                    (request()->route() && request()->route()->parameter('slug') === $page['slug'])
                );
        }

        return $navigationItems;
    }

    public function getNavigationGroups(): array
    {
        return [
            NavigationGroup::make('Pages')
                ->items($this->getNavigationItems())
                ->icon('heroicon-o-document-text')
                ->collapsible(false),
        ];
    }

    protected function formatTitle(string $slug): string
    {
        return Str::title(str_replace(['-', '_'], ' ', $slug));
    }
}
