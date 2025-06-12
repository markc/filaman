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
                return base_path('plugins/'.$pluginName.'-plugin/'.ltrim($path, '/'));
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

### GitHub Flavored Markdown Processing Flow

The content processing follows a comprehensive multi-stage pipeline optimized for GitHub Flavored Markdown:

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

3. GFM Content Processing (GfmMarkdownRenderer)
   ├── Process GitHub Flavored Markdown to HTML
   ├── Apply syntax highlighting with Prism.js
   ├── Render tables with responsive styling
   ├── Process task lists and checkboxes
   ├── Handle collapsible sections
   ├── Generate proper heading hierarchy
   ├── Process code blocks with language detection
   ├── Apply custom CSS classes for styling
   └── Sanitize output for security

4. Metadata Extraction
   ├── Extract SEO metadata
   ├── Process navigation data
   ├── Handle publication status
   └── Apply sorting/filtering

5. Template Rendering
   ├── Select appropriate template
   ├── Inject processed content with GFM styling
   ├── Apply Filament v4.x admin layout
   └── Generate final HTML with full panel integration
```

### GfmMarkdownRenderer Service Implementation

The core of the markdown processing is handled by the `GfmMarkdownRenderer` service:

```php
<?php

namespace FilaMan\Pages\Services;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;

class GfmMarkdownRenderer
{
    private CommonMarkConverter $converter;
    
    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 10,
        ]);
        
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new TaskListExtension());
        
        $this->converter = new CommonMarkConverter([], $environment);
    }
    
    public function renderWithClasses(string $markdown): string
    {
        $html = $this->converter->convert($markdown)->getContent();
        return $this->applyCustomStyling($html);
    }
    
    private function applyCustomStyling(string $html): string
    {
        // Apply comprehensive GFM styling classes
        $patterns = [
            // Headings with proper hierarchy
            '/<h1([^>]*)>/' => '<h1$1 class="text-4xl font-bold text-gray-900 dark:text-white mb-6 mt-8 first:mt-0 border-b border-gray-200 dark:border-gray-700 pb-2">',
            '/<h2([^>]*)>/' => '<h2$1 class="text-3xl font-semibold text-gray-900 dark:text-white mb-5 mt-7 border-b border-gray-200 dark:border-gray-700 pb-2">',
            '/<h3([^>]*)>/' => '<h3$1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4 mt-6">',
            '/<h4([^>]*)>/' => '<h4$1 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 mt-5">',
            '/<h5([^>]*)>/' => '<h5$1 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 mt-4">',
            '/<h6([^>]*)>/' => '<h6$1 class="text-base font-semibold text-gray-900 dark:text-white mb-2 mt-3">',
            
            // Paragraphs and text
            '/<p([^>]*)>/' => '<p$1 class="text-gray-700 dark:text-gray-300 mb-4 leading-relaxed">',
            
            // Lists with proper indentation
            '/<ul([^>]*)>/' => '<ul$1 class="list-disc list-outside ml-6 mb-4 space-y-2 text-gray-700 dark:text-gray-300">',
            '/<ol([^>]*)>/' => '<ol$1 class="list-decimal list-outside ml-6 mb-4 space-y-2 text-gray-700 dark:text-gray-300">',
            '/<li([^>]*)>/' => '<li$1 class="leading-relaxed">',
            
            // Code blocks and inline code
            '/<pre><code([^>]*)>/' => '<pre class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 mb-4 overflow-x-auto border border-gray-200 dark:border-gray-700"><code$1 class="text-sm font-mono text-gray-800 dark:text-gray-200">',
            '/<code([^>]*class="[^"]*language-([^"]*)[^>]*)>/' => '<code$1 data-language="$2">',
            '/<code(?![^>]*class)([^>]*)>/' => '<code$1 class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 px-2 py-1 rounded text-sm font-mono">',
            
            // Tables with responsive design
            '/<table([^>]*)>/' => '<div class="overflow-x-auto mb-6"><table$1 class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg">',
            '/<\/table>/' => '</table></div>',
            '/<thead([^>]*)>/' => '<thead$1 class="bg-gray-50 dark:bg-gray-800">',
            '/<th([^>]*)>/' => '<th$1 class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">',
            '/<tbody([^>]*)>/' => '<tbody$1 class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">',
            '/<td([^>]*)>/' => '<td$1 class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">',
            
            // Task lists
            '/<li([^>]*class="[^"]*task-list-item[^>]*)>/' => '<li$1 class="flex items-start space-x-2 leading-relaxed">',
            '/<input([^>]*type="checkbox"[^>]*disabled[^>]*)>/' => '<input$1 class="mt-1 h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">',
            
            // Blockquotes
            '/<blockquote([^>]*)>/' => '<blockquote$1 class="border-l-4 border-gray-300 dark:border-gray-600 pl-4 py-2 mb-4 italic text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-r">',
            
            // Links
            '/<a([^>]*)>/' => '<a$1 class="text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-200 underline transition-colors duration-200">',
            
            // Horizontal rules
            '/<hr([^>]*)>/' => '<hr$1 class="my-8 border-t border-gray-300 dark:border-gray-600">',
        ];
        
        return preg_replace(array_keys($patterns), array_values($patterns), $html);
    }
}
```

### File Discovery Implementation

```php
<?php

public function discoverPages(): array
{
    $pages = [];
    $pagesDirectory = base_path('plugins/pages/resources/views/pages');

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

### Filament Panel Integration

The Pages Plugin now uses a dedicated Filament panel for public pages with full admin layout:

```php
<?php

namespace FilaMan\Pages\Providers;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PagesPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('pages')
            ->path('pages')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverPages(in: app_path('Filament/Pages/Pages'), for: 'App\\Filament\\Pages\\Pages')
            ->pages([
                // Pages are discovered dynamically
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                // No authentication required for public pages
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->favicon(asset('favicon.ico'))
            ->brandName('FilaMan')
            ->brandLogo(asset('img/logo.svg'))
            ->brandLogoHeight('2rem')
            ->sidebarCollapsibleOnDesktop();
    }
}
```

### Dynamic Page Class Implementation

The `DynamicPage` class renders individual markdown pages within the Filament panel:

```php
<?php

namespace FilaMan\Pages\Filament\Pages;

use Filament\Pages\Page;
use FilaMan\Pages\Services\GfmMarkdownRenderer;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class DynamicPage extends Page
{
    protected static string $view = 'filaman-pages::filament.pages.dynamic-page';
    
    public string $slug;
    public array $frontMatter = [];
    public string $content = '';
    public string $title = '';
    public string $description = '';
    
    public function mount(string $slug = 'home'): void
    {
        $this->slug = $slug;
        $this->loadPageContent();
    }
    
    protected function loadPageContent(): void
    {
        $filePath = base_path("plugins/pages/resources/views/pages/{$this->slug}.md");
        
        if (!File::exists($filePath)) {
            abort(404, "Page '{$this->slug}' not found.");
        }
        
        $fileContent = File::get($filePath);
        $document = YamlFrontMatter::parse($fileContent);
        
        $this->frontMatter = $document->matter();
        $this->content = $document->body();
        $this->title = $this->frontMatter['title'] ?? ucfirst(str_replace('-', ' ', $this->slug));
        $this->description = $this->frontMatter['description'] ?? '';
    }
    
    public function getTitle(): string
    {
        return $this->title;
    }
    
    public function getViewData(): array
    {
        $markdownService = resolve(GfmMarkdownRenderer::class);
        $htmlOutput = $markdownService->renderWithClasses($this->content);
        
        return [
            'htmlContent' => $htmlOutput,
            'title' => $this->title,
            'description' => $this->description,
            'slug' => $this->slug,
            'frontMatter' => $this->frontMatter,
        ];
    }
}
```
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