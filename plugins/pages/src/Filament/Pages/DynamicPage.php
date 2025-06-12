<?php

namespace FilaMan\Pages\Filament\Pages;

use FilaMan\Pages\Services\GfmMarkdownRenderer;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class DynamicPage extends Page
{
    protected string $view = 'filaman-pages::filament.pages.dynamic-page';

    public string $pageSlug = '';

    public string $pageTitle = '';

    public array $frontMatter = [];

    public string $content = '';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = '{slug}';

    public function mount(string $slug = 'home'): void
    {
        $this->pageSlug = $slug;
        $this->loadMarkdownContent($slug);
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
        // Use GfmMarkdownRenderer service to render markdown content
        $markdownService = resolve(\FilaMan\Pages\Services\GfmMarkdownRenderer::class);
        $htmlOutput = $markdownService->renderWithClasses($this->content);

        return [
            'htmlContent' => $htmlOutput,
        ];
    }

    public function getTitle(): string
    {
        return $this->pageTitle;
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
        $currentUrl = $baseUrl.'/pages/'.$this->pageSlug;

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
}
