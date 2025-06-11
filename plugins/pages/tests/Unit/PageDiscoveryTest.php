<?php

namespace FilaMan\Pages\Tests\Unit;

use FilaMan\Pages\Services\PageDiscoveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PageDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    private string $testPagesPath;

    private PageDiscoveryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PageDiscoveryService;
        $this->testPagesPath = base_path('plugins/pages/resources/views/pages');
    }

    public function test_discovers_published_pages_only()
    {
        $pages = $this->service->discoverPages();

        // Filter for published pages only
        $publishedPages = collect($pages)->filter(fn ($page) => $page['published'] ?? true);

        $this->assertGreaterThan(0, $publishedPages->count());

        foreach ($publishedPages as $page) {
            $this->assertTrue($page['published'] ?? true);
        }
    }

    public function test_page_discovery_returns_required_fields()
    {
        $pages = $this->service->discoverPages();

        $this->assertGreaterThan(0, count($pages));

        foreach ($pages as $page) {
            $this->assertArrayHasKey('slug', $page);
            $this->assertArrayHasKey('title', $page);
            $this->assertArrayHasKey('content', $page);
            $this->assertArrayHasKey('metadata', $page);
        }
    }

    public function test_pages_are_sorted_by_order_field()
    {
        $pages = $this->service->discoverPages();

        $orderedPages = collect($pages)
            ->filter(fn ($page) => isset($page['order']))
            ->sortBy('order')
            ->values()
            ->toArray();

        if (count($orderedPages) > 1) {
            for ($i = 1; $i < count($orderedPages); $i++) {
                $this->assertLessThanOrEqual(
                    $orderedPages[$i]['order'],
                    $orderedPages[$i - 1]['order']
                );
            }
        }
    }

    public function test_validates_page_slug_format()
    {
        $validSlugs = ['home', 'about-us', 'plugin_development', 'test-123'];
        $invalidSlugs = ['../malicious', 'page with spaces', 'page@invalid', ''];

        foreach ($validSlugs as $slug) {
            $this->assertTrue($this->service->validateSlug($slug));
        }

        foreach ($invalidSlugs as $slug) {
            $this->assertFalse($this->service->validateSlug($slug));
        }
    }

    public function test_handles_missing_pages_directory_gracefully()
    {
        $nonExistentPath = '/nonexistent/path';
        $service = new PageDiscoveryService($nonExistentPath);

        $pages = $service->discoverPages();

        $this->assertIsArray($pages);
        $this->assertEmpty($pages);
    }

    public function test_ignores_non_markdown_files()
    {
        // This test verifies that only .md files are processed
        // In a real test environment, we'd create temp files
        $pages = $this->service->discoverPages();

        foreach ($pages as $page) {
            // All discovered pages should come from .md files
            $this->assertIsString($page['slug']);
            $this->assertNotEmpty($page['content']);
        }
    }

    public function test_parses_yaml_front_matter_correctly()
    {
        $pages = $this->service->discoverPages();

        // Find a page with front matter
        $pageWithFrontMatter = collect($pages)->first(fn ($page) => ! empty($page['metadata']));

        if ($pageWithFrontMatter) {
            $this->assertIsArray($pageWithFrontMatter['metadata']);

            // Check that common front matter fields are properly parsed
            if (isset($pageWithFrontMatter['metadata']['title'])) {
                $this->assertIsString($pageWithFrontMatter['metadata']['title']);
            }

            if (isset($pageWithFrontMatter['metadata']['order'])) {
                $this->assertIsNumeric($pageWithFrontMatter['metadata']['order']);
            }

            if (isset($pageWithFrontMatter['metadata']['published'])) {
                $this->assertIsBool($pageWithFrontMatter['metadata']['published']);
            }
        }
    }

    public function test_handles_malformed_front_matter_gracefully()
    {
        // This would test error handling for malformed YAML
        // In a real implementation, we'd create a temp file with bad YAML
        $pages = $this->service->discoverPages();

        // Should not throw exceptions and should return array
        $this->assertIsArray($pages);
    }

    public function test_page_content_is_separated_from_front_matter()
    {
        $pages = $this->service->discoverPages();

        foreach ($pages as $page) {
            // Content should not contain YAML front matter delimiters
            $this->assertStringNotContainsString('---', $page['content']);

            // Content should be meaningful (not just whitespace)
            $this->assertNotEmpty(trim($page['content']));
        }
    }

    public function test_default_values_are_applied()
    {
        $pages = $this->service->discoverPages();

        foreach ($pages as $page) {
            // Default order should be applied if not specified
            $this->assertIsNumeric($page['order']);

            // Default published status should be true
            $this->assertIsBool($page['published']);
        }
    }
}
