<?php

namespace FilaMan\Pages\Filament\Pages;

use FilaMan\Pages\Services\GfmMarkdownRenderer;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class HomePage extends Page
{
    protected static ?string $slug = '/';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filaman-pages::filament.pages.dynamic-page';

    public string $pageSlug = 'home';

    public string $pageTitle = '';

    public array $frontMatter = [];

    public string $content = '';

    public function mount(): void
    {
        $this->loadMarkdownContent('home');
    }

    protected function loadMarkdownContent(string $slug): void
    {
        $filePath = __DIR__.'/../../../resources/views/pages/'.$slug.'.md';

        if (! File::exists($filePath)) {
            abort(404, "Page '{$slug}' not found.");
        }

        $content = File::get($filePath);
        $document = YamlFrontMatter::parse($content);

        $this->frontMatter = $document->matter();
        $this->content = $document->body();
        $this->pageTitle = $this->frontMatter['title'] ?? ucfirst(str_replace('-', ' ', $slug));

        // Check if page is published
        if (! ($this->frontMatter['published'] ?? true)) {
            abort(404, "Page '{$slug}' is not published.");
        }
    }

    public function getViewData(): array
    {
        $markdownService = resolve(GfmMarkdownRenderer::class);
        $htmlOutput = $markdownService->renderWithClasses($this->content);

        return [
            'htmlContent' => $htmlOutput,
            'frontMatter' => $this->frontMatter,
            'pageTitle' => $this->pageTitle,
            'pageSlug' => $this->pageSlug,
        ];
    }

    public function getTitle(): string
    {
        return ''; // Hide page title
    }

    public function getSeoTitle(): string
    {
        return $this->frontMatter['seo_title'] ?? $this->pageTitle;
    }

    public function getSeoDescription(): string
    {
        return $this->frontMatter['seo_description'] ?? $this->frontMatter['description'] ?? '';
    }

    public function getMetaTags(): array
    {
        $baseUrl = config('app.url');
        $currentUrl = $baseUrl.'/pages/';

        return [
            'title' => $this->getSeoTitle(),
            'description' => $this->getSeoDescription(),
            'og:title' => $this->getSeoTitle(),
            'og:description' => $this->getSeoDescription(),
            'og:url' => $currentUrl,
            'og:type' => 'article',
            'og:site_name' => config('app.name'),
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $this->getSeoTitle(),
            'twitter:description' => $this->getSeoDescription(),
            'canonical' => $currentUrl,
        ];
    }

    public function getCustomCss(): string
    {
        return $this->frontMatter['custom_css'] ?? '';
    }

    public function getCustomJs(): string
    {
        return $this->frontMatter['custom_js'] ?? '';
    }

    public function getFeaturedImage(): ?string
    {
        return $this->frontMatter['featured_image'] ?? null;
    }

    public function hasHeader(): bool
    {
        return false;
    }

    public function hasHeading(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
