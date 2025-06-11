# Technical Implementation

This document provides detailed technical information about how the Pages Plugin is implemented, including code architecture, design patterns, and implementation details.

## Core Architecture

### Plugin Class Implementation

The main plugin class implements the Filament Plugin interface:

```php
<?php

namespace FilaMan\PagesPlugin;

use Filament\Contracts\Plugin;
use Filament\Panel;

class PagesPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'pages';
    }

    public function register(Panel $panel): void
    {
        // Register any Filament resources, pages, or widgets
        // This plugin doesn't register admin components
        // but demonstrates the pattern for other plugins
    }

    public function boot(Panel $panel): void
    {
        // Runtime initialization
        view()->addNamespace('filaman-pages', __DIR__.'/../resources/views');
    }
}
```

Key implementation details:

- **Singleton Pattern**: The plugin is registered as a singleton in the service container
- **Filament Integration**: Implements the required Plugin interface methods
- **View Namespace**: Registers a view namespace for template isolation
- **Lifecycle Hooks**: Uses `register()` and `boot()` for different initialization phases

### Service Provider Architecture

The service provider uses Spatie's Laravel Package Tools for standardization:

```php
<?php

namespace FilaMan\PagesPlugin;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PagesPluginServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filaman-pages-plugin')
            ->hasConfigFile('filaman-pages')
            ->hasViews('filaman-pages')
            ->hasRoute('web');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PagesPlugin::class, function () {
            return new PagesPlugin();
        });
    }

    public function packageBooted(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filaman-pages');
        
        // Register helper functions
        $this->registerHelpers();
    }

    private function registerHelpers(): void
    {
        if (!function_exists('filaman_plugin_path')) {
            function filaman_plugin_path($pluginName, $path = '')
            {
                return base_path('packages/'.$pluginName.'-plugin/'.ltrim($path, '/'));
            }
        }

        if (!function_exists('filaman_get_pages')) {
            function filaman_get_pages()
            {
                // Implementation for page discovery and parsing
            }
        }
    }
}
```

Benefits of this approach:

- **Standardization**: Uses proven patterns from the Laravel ecosystem
- **Configuration**: Centralized configuration through the Package class
- **Helper Functions**: Provides utility functions for the broader ecosystem
- **View Loading**: Handles view namespace registration and publishing

## Content Processing Pipeline

### Markdown Processing Flow

The content processing follows a multi-stage pipeline:

```
1. File Discovery
   ├── Scan pages directory
   ├── Filter for .md files
   └── Build file list

2. Content Parsing
   ├── Read file contents
   ├── Parse YAML front matter
   ├── Extract Markdown body
   └── Validate structure

3. Content Processing
   ├── Process Markdown to HTML
   ├── Apply syntax highlighting
   ├── Process internal links
   └── Generate table of contents

4. Metadata Extraction
   ├── Extract SEO metadata
   ├── Process navigation data
   ├── Handle publication status
   └── Apply sorting/filtering

5. Template Rendering
   ├── Select appropriate template
   ├── Inject processed content
   ├── Apply layout and styling
   └── Generate final HTML
```

### File Discovery Implementation

```php
<?php

public function discoverPages(): array
{
    $pages = [];
    $pagesDirectory = filaman_plugin_path('pages', 'resources/views/pages');

    if (!File::isDirectory($pagesDirectory)) {
        return $pages;
    }

    $files = File::files($pagesDirectory);
    
    foreach ($files as $file) {
        if ($file->getExtension() !== 'md') {
            continue;
        }

        $pageData = $this->parsePageFile($file);
        
        if ($this->isValidPage($pageData)) {
            $pages[] = $pageData;
        }
    }

    return $this->sortPages($pages);
}

private function parsePageFile(SplFileInfo $file): array
{
    $content = File::get($file->getPathname());
    $document = YamlFrontMatter::parse($content);

    return [
        'slug' => $file->getFilenameWithoutExtension(),
        'title' => $document->matter('title'),
        'description' => $document->matter('description'),
        'order' => $document->matter('order', 999),
        'published' => $document->matter('published', true),
        'content' => $document->body(),
        'metadata' => $document->matter(),
    ];
}
```

Key implementation features:

- **Error Handling**: Graceful handling of missing or malformed files
- **Validation**: Ensures required fields are present
- **Caching**: Results can be cached for performance
- **Extensibility**: Pipeline stages can be extended or modified

## Request Handling

### Route Definition

Routes are defined using Laravel's standard routing:

```php
<?php

use FilaMan\PagesPlugin\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::prefix('pages')->name('filaman.pages.')->group(function () {
    Route::get('/', [PageController::class, 'index'])->name('index');
    Route::get('{slug}', [PageController::class, 'show'])->name('show');
});
```

### Controller Implementation

The controller handles page rendering with comprehensive error handling:

```php
<?php

namespace FilaMan\PagesPlugin\Http\Controllers;

class PageController extends Controller
{
    public function show(Request $request, $slug = 'home')
    {
        try {
            $filePath = $this->getPageFilePath($slug);
            
            if (!$this->pageExists($filePath)) {
                return $this->handlePageNotFound($slug);
            }

            $pageData = $this->parsePageFile($filePath);
            
            if (!$this->isPagePublished($pageData)) {
                return $this->handleUnpublishedPage($slug);
            }

            return $this->renderPage($pageData, $slug);

        } catch (Exception $e) {
            return $this->handleRenderingError($e, $slug);
        }
    }

    private function renderPage(array $pageData, string $slug): Response
    {
        $htmlContent = app(MarkdownRenderer::class)->toHtml($pageData['content']);

        $viewData = [
            'title' => $pageData['title'],
            'description' => $pageData['description'],
            'content' => $htmlContent,
            'slug' => $slug,
            'frontMatter' => $pageData['metadata'],
            'pages' => filaman_get_pages(),
        ];

        return view('filaman-pages::page', $viewData);
    }
}
```

## Template System

### Template Hierarchy

The plugin uses a hierarchical template system:

```
Base Layout (page.blade.php)
├── Header Section
│   ├── Meta tags and SEO
│   ├── Title generation
│   └── CSS/JS includes
├── Navigation (partials/navbar.blade.php)
│   ├── Brand/logo area
│   ├── Dynamic page links
│   └── Admin panel access
├── Main Content Area
│   ├── Page header
│   ├── Content rendering
│   └── Navigation controls
└── Footer Section
    ├── Copyright information
    └── Additional links
```

### Template Rendering Process

```php
<?php

// Template selection logic
private function selectTemplate(array $pageData): string
{
    // Check for page-specific template
    if (isset($pageData['template'])) {
        $customTemplate = 'filaman-pages::'.$pageData['template'];
        if (view()->exists($customTemplate)) {
            return $customTemplate;
        }
    }

    // Check for category-specific template
    if (isset($pageData['category'])) {
        $categoryTemplate = 'filaman-pages::categories.'.$pageData['category'];
        if (view()->exists($categoryTemplate)) {
            return $categoryTemplate;
        }
    }

    // Fall back to default template
    return config('filaman-pages.page_template', 'filaman-pages::page');
}
```

### View Composer Integration

View composers provide consistent data across templates:

```php
<?php

class PageViewComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'siteConfig' => config('filaman-pages.seo'),
            'navigationPages' => filaman_get_pages(),
            'currentUser' => auth()->user(),
            'adminAccess' => auth()->check() && auth()->user()->isAdmin(),
        ]);
    }
}
```

## Performance Optimizations

### Caching Strategy

The plugin implements multi-layer caching:

```php
<?php

class PageCacheManager
{
    private const CACHE_PREFIX = 'filaman_pages';
    private const CACHE_TTL = 3600; // 1 hour

    public function getCachedPage(string $slug): ?array
    {
        $cacheKey = $this->getCacheKey($slug);
        return Cache::get($cacheKey);
    }

    public function cachePage(string $slug, array $pageData): void
    {
        $cacheKey = $this->getCacheKey($slug);
        Cache::put($cacheKey, $pageData, self::CACHE_TTL);
    }

    public function invalidatePageCache(string $slug = null): void
    {
        if ($slug) {
            Cache::forget($this->getCacheKey($slug));
        } else {
            Cache::tags([self::CACHE_PREFIX])->flush();
        }
    }

    private function getCacheKey(string $slug): string
    {
        return self::CACHE_PREFIX.'.'.$slug.'.'.filemtime($this->getPageFilePath($slug));
    }
}
```

### Asset Optimization

```php
<?php

// CSS/JS bundling and minification
class AssetManager
{
    public function getPageAssets(array $pageData): array
    {
        $assets = [
            'css' => $this->getBaseStyles(),
            'js' => $this->getBaseScripts(),
        ];

        // Add page-specific assets
        if (isset($pageData['assets'])) {
            $assets = array_merge_recursive($assets, $pageData['assets']);
        }

        return $this->optimizeAssets($assets);
    }

    private function optimizeAssets(array $assets): array
    {
        if (app()->environment('production')) {
            $assets['css'] = $this->minifyCSS($assets['css']);
            $assets['js'] = $this->minifyJS($assets['js']);
        }

        return $assets;
    }
}
```

## Security Implementation

### Input Validation

```php
<?php

class PageValidator
{
    public function validateSlug(string $slug): bool
    {
        // Only allow alphanumeric characters, hyphens, and underscores
        return preg_match('/^[a-zA-Z0-9_-]+$/', $slug);
    }

    public function validatePageFile(string $filePath): bool
    {
        // Ensure file is within allowed directory
        $allowedPath = filaman_plugin_path('pages', 'resources/views/pages');
        $realPath = realpath($filePath);
        
        return $realPath && str_starts_with($realPath, realpath($allowedPath));
    }

    public function sanitizeContent(string $content): string
    {
        // Remove potentially dangerous HTML while preserving Markdown
        return $this->markdownSanitizer->sanitize($content);
    }
}
```

### XSS Prevention

```php
<?php

// Content sanitization pipeline
class ContentSanitizer
{
    public function sanitizeHTML(string $html): string
    {
        $config = HTMLPurifier_Config::createDefault();
        
        // Allow safe HTML tags for documentation
        $config->set('HTML.Allowed', 'p,br,strong,em,ul,ol,li,h1,h2,h3,h4,h5,h6,blockquote,code,pre,a[href],img[src|alt]');
        
        // Configure link policies
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.TargetBlank', true);
        
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }
}
```

## Testing Architecture

### Test Structure

```php
<?php

namespace FilaMan\PagesPlugin\Tests;

abstract class TestCase extends \Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->setUpDatabase();
        $this->setUpFilesystem();
        $this->registerPlugin();
    }

    protected function setUpDatabase(): void
    {
        $this->artisan('migrate:fresh');
        $this->seed(DatabaseSeeder::class);
    }

    protected function setUpFilesystem(): void
    {
        // Create test pages directory
        $pagesPath = $this->getTestPagesPath();
        File::ensureDirectoryExists($pagesPath);
        
        // Copy test fixture files
        File::copyDirectory(
            __DIR__.'/fixtures/pages',
            $pagesPath
        );
    }

    protected function registerPlugin(): void
    {
        $this->app->register(PagesPluginServiceProvider::class);
    }
}
```

### Feature Testing

```php
<?php

class PageRenderingTest extends TestCase
{
    public function test_page_renders_with_correct_content(): void
    {
        $response = $this->get('/pages/test-page');
        
        $response->assertStatus(200);
        $response->assertSee('Test Page Title');
        $response->assertSee('Test page content');
        $response->assertViewIs('filaman-pages::page');
    }

    public function test_navigation_includes_published_pages_only(): void
    {
        $response = $this->get('/pages/home');
        
        $response->assertSee('Published Page');
        $response->assertDontSee('Unpublished Page');
    }

    public function test_page_metadata_is_rendered_correctly(): void
    {
        $response = $this->get('/pages/test-page');
        
        $response->assertSee('<title>Test Page - FilaMan</title>', false);
        $response->assertSee('<meta name="description" content="Test description">', false);
    }
}
```

## Error Handling

### Exception Management

```php
<?php

class PageNotFoundException extends Exception
{
    public function __construct(string $slug)
    {
        parent::__construct("Page '{$slug}' not found.");
    }
}

class PageRenderingException extends Exception
{
    public function __construct(string $slug, Throwable $previous = null)
    {
        parent::__construct("Failed to render page '{$slug}'.", 0, $previous);
    }
}

// Global exception handler for plugin
class PageExceptionHandler
{
    public function handle(Exception $e, string $slug): Response
    {
        Log::error('Page rendering error', [
            'slug' => $slug,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        if ($e instanceof PageNotFoundException) {
            return response()->view('filaman-pages::errors.404', ['slug' => $slug], 404);
        }

        if (app()->environment('production')) {
            return response()->view('filaman-pages::errors.500', ['slug' => $slug], 500);
        }

        throw $e; // Re-throw in development for debugging
    }
}
```

This technical implementation provides a solid foundation for the Pages Plugin while demonstrating best practices for FilaMan plugin development.