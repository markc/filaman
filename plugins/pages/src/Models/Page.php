<?php

namespace FilaMan\Pages\Models;

use FilaMan\Pages\Services\GfmMarkdownRenderer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class Page extends Model
{
    use HasFactory;

    protected $table = 'pages';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'order',
        'published',
        'seo_title',
        'seo_description',
        'og_image',
        'content',
        'custom_css',
        'custom_js',
        'featured_image',
    ];

    protected $casts = [
        'published' => 'boolean',
        'order' => 'integer',
    ];

    public function getFilePath(): string
    {
        return __DIR__.'/../../resources/views/pages/'.$this->slug.'.md';
    }

    public function getRenderedContent(): string
    {
        $renderer = app(GfmMarkdownRenderer::class);

        return $renderer->renderWithClasses($this->content ?? '');
    }

    public function getRenderedContentWithAnchors(): string
    {
        $renderer = app(GfmMarkdownRenderer::class);

        return $renderer->renderWithAnchors($this->content ?? '');
    }

    public function getTableOfContents(): array
    {
        $renderer = app(GfmMarkdownRenderer::class);

        return $renderer->extractHeadings($this->content ?? '');
    }

    public function saveToFile(): void
    {
        $frontMatter = [
            'title' => $this->title,
            'description' => $this->description,
            'published' => $this->published,
            'category' => $this->category,
            'order' => $this->order,
        ];

        if ($this->seo_title) {
            $frontMatter['seo_title'] = $this->seo_title;
        }

        if ($this->seo_description) {
            $frontMatter['seo_description'] = $this->seo_description;
        }

        if ($this->og_image) {
            $frontMatter['og_image'] = $this->og_image;
        }

        if ($this->custom_css) {
            $frontMatter['custom_css'] = $this->custom_css;
        }

        if ($this->custom_js) {
            $frontMatter['custom_js'] = $this->custom_js;
        }

        $yamlString = '---'.PHP_EOL;
        foreach ($frontMatter as $key => $value) {
            if (is_bool($value)) {
                $yamlString .= $key.': '.($value ? 'true' : 'false').PHP_EOL;
            } elseif (is_string($value) && str_contains($value, PHP_EOL)) {
                $yamlString .= $key.': |'.PHP_EOL;
                foreach (explode(PHP_EOL, $value) as $line) {
                    $yamlString .= '  '.$line.PHP_EOL;
                }
            } else {
                $yamlString .= $key.': '.$value.PHP_EOL;
            }
        }
        $yamlString .= '---'.PHP_EOL.PHP_EOL;

        $fileContent = $yamlString.$this->content;

        $directory = dirname($this->getFilePath());
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($this->getFilePath(), $fileContent);
    }

    public static function createFromFile(string $slug): ?self
    {
        $filePath = __DIR__.'/../../resources/views/pages/'.$slug.'.md';

        if (! File::exists($filePath)) {
            return null;
        }

        $content = File::get($filePath);
        $document = YamlFrontMatter::parse($content);
        $frontMatter = $document->matter();

        $page = new self;
        $page->slug = $slug;
        $page->title = $frontMatter['title'] ?? ucfirst(str_replace('-', ' ', $slug));
        $page->description = $frontMatter['description'] ?? '';
        $page->category = $frontMatter['category'] ?? 'Main';
        $page->order = $frontMatter['order'] ?? 999;
        $page->published = $frontMatter['published'] ?? true;
        $page->seo_title = $frontMatter['seo_title'] ?? null;
        $page->seo_description = $frontMatter['seo_description'] ?? null;
        $page->og_image = $frontMatter['og_image'] ?? null;
        $page->custom_css = $frontMatter['custom_css'] ?? null;
        $page->custom_js = $frontMatter['custom_js'] ?? null;
        $page->content = $document->body();

        return $page;
    }

    public static function getAllFromFiles(): array
    {
        $pagesPath = __DIR__.'/../../resources/views/pages/';
        $pages = [];

        if (! File::exists($pagesPath)) {
            return $pages;
        }

        $files = File::files($pagesPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'md') {
                $slug = $file->getBasename('.md');
                $page = self::createFromFile($slug);
                if ($page) {
                    $pages[] = $page;
                }
            }
        }

        return $pages;
    }

    protected static function booted()
    {
        static::created(function ($page) {
            $page->saveToFile();
        });

        static::updated(function ($page) {
            $page->saveToFile();
        });

        static::deleted(function ($page) {
            if (File::exists($page->getFilePath())) {
                File::delete($page->getFilePath());
            }
        });
    }
}
