<?php

namespace FilaMan\PagesPlugin\Services;

use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use SplFileInfo;

class PageDiscoveryService
{
    private string $pagesPath;

    public function __construct(?string $pagesPath = null)
    {
        $this->pagesPath = $pagesPath ?? base_path('packages/pages-plugin/resources/views/pages');
    }

    public function discoverPages(): array
    {
        $pages = [];

        if (! File::isDirectory($this->pagesPath)) {
            return $pages;
        }

        $files = File::files($this->pagesPath);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            try {
                $pageData = $this->parsePageFile($file);

                if ($this->isValidPage($pageData)) {
                    $pages[] = $pageData;
                }
            } catch (\Exception $e) {
                // Log error but continue processing other files
                \Log::warning("Failed to parse page file: {$file->getPathname()}", [
                    'error' => $e->getMessage(),
                ]);

                continue;
            }
        }

        return $this->sortPages($pages);
    }

    public function parsePageFile(SplFileInfo $file): array
    {
        $content = File::get($file->getPathname());

        try {
            $document = YamlFrontMatter::parse($content);
        } catch (\Exception $e) {
            // If YAML parsing fails, treat as plain content
            $document = YamlFrontMatter::parse("---\n---\n".$content);
        }

        $slug = $file->getFilenameWithoutExtension();
        $metadata = $document->matter();

        return [
            'slug' => $slug,
            'title' => $metadata['title'] ?? $this->generateTitleFromSlug($slug),
            'description' => $metadata['description'] ?? '',
            'order' => (int) ($metadata['order'] ?? 999),
            'published' => (bool) ($metadata['published'] ?? true),
            'author' => $metadata['author'] ?? '',
            'date' => $metadata['date'] ?? '',
            'tags' => $metadata['tags'] ?? '',
            'keywords' => $metadata['keywords'] ?? '',
            'content' => $document->body(),
            'metadata' => $metadata,
        ];
    }

    public function isValidPage(array $pageData): bool
    {
        return ! empty($pageData['slug']) &&
               ! empty($pageData['content']) &&
               $this->validateSlug($pageData['slug']);
    }

    public function validateSlug(string $slug): bool
    {
        // Only allow alphanumeric characters, hyphens, and underscores
        return ! empty($slug) && preg_match('/^[a-zA-Z0-9_-]+$/', $slug);
    }

    public function sortPages(array $pages): array
    {
        return collect($pages)
            ->sortBy([
                ['order', 'asc'],
                ['title', 'asc'],
            ])
            ->values()
            ->toArray();
    }

    public function getPublishedPages(): array
    {
        return collect($this->discoverPages())
            ->filter(fn ($page) => $page['published'])
            ->toArray();
    }

    public function findPageBySlug(string $slug): ?array
    {
        if (! $this->validateSlug($slug)) {
            return null;
        }

        $pages = $this->discoverPages();

        return collect($pages)->first(fn ($page) => $page['slug'] === $slug);
    }

    public function pageExists(string $slug): bool
    {
        if (! $this->validateSlug($slug)) {
            return false;
        }

        $filePath = $this->pagesPath.'/'.$slug.'.md';

        return File::exists($filePath);
    }

    public function getPageFilePath(string $slug): string
    {
        return $this->pagesPath.'/'.$slug.'.md';
    }

    private function generateTitleFromSlug(string $slug): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $slug));
    }
}
