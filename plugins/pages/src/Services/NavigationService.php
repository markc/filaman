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

        // Convert to NavigationItem objects
        $navigationItems = [];
        foreach ($pages as $page) {
            $navigationItems[] = NavigationItem::make($page['title'])
                ->url('/pages/'.$page['slug'])
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
