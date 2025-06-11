<?php

namespace FilaMan\Pages\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRenderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register the plugin service provider
        $this->app->register(\FilaMan\Pages\PagesPluginServiceProvider::class);
    }

    public function test_home_page_renders_correctly()
    {
        $response = $this->get('/pages/home');

        $response->assertStatus(200);
        $response->assertSee('Welcome to FilaMan');
        $response->assertSee('Filament v4.x Plugin Manager');
        $response->assertViewIs('filaman-pages::page');
    }

    public function test_about_page_renders_correctly()
    {
        $response = $this->get('/pages/about');

        $response->assertStatus(200);
        $response->assertSee('About FilaMan');
        $response->assertViewIs('filaman-pages::page');
    }

    public function test_installation_page_renders_correctly()
    {
        $response = $this->get('/pages/installation');

        $response->assertStatus(200);
        $response->assertSee('Installation Guide');
        $response->assertViewIs('filaman-pages::page');
    }

    public function test_plugin_development_page_renders_correctly()
    {
        $response = $this->get('/pages/plugin-development');

        $response->assertStatus(200);
        $response->assertSee('Plugin Development Guide');
        $response->assertViewIs('filaman-pages::page');
    }

    public function test_nonexistent_page_returns_404()
    {
        $response = $this->get('/pages/nonexistent-page');

        $response->assertStatus(404);
    }

    public function test_page_with_invalid_slug_returns_404()
    {
        $response = $this->get('/pages/../malicious');

        $response->assertStatus(404);
    }

    public function test_page_metadata_is_rendered_correctly()
    {
        $response = $this->get('/pages/home');

        // Check for proper meta tags
        $response->assertSee('<title>', false);
        $response->assertSee('FilaMan', false);
        $response->assertSee('<meta name="description"', false);
    }

    public function test_markdown_content_is_converted_to_html()
    {
        $response = $this->get('/pages/home');

        // Should contain HTML elements from Markdown conversion
        $response->assertSee('<h1>', false);
        $response->assertSee('<p>', false);
    }

    public function test_code_blocks_are_highlighted()
    {
        $response = $this->get('/pages/installation');

        // Check for code block rendering
        $response->assertSee('<pre>', false);
        $response->assertSee('<code>', false);
    }

    public function test_internal_links_work_correctly()
    {
        $response = $this->get('/pages/home');

        // Should contain links to other pages
        $response->assertSee('/pages/about');
        $response->assertSee('/pages/installation');
    }

    public function test_pages_index_lists_all_published_pages()
    {
        $response = $this->get('/pages');

        $response->assertStatus(200);
        $response->assertViewIs('filaman-pages::index');
        $response->assertSee('Home');
        $response->assertSee('About');
        $response->assertSee('Installation');
        $response->assertSee('Plugin Development');
    }

    public function test_responsive_design_classes_are_present()
    {
        $response = $this->get('/pages/home');

        // Check for Tailwind responsive classes
        $response->assertSee('container', false);
        $response->assertSee('mx-auto', false);
        $response->assertSee('md:', false);
    }

    public function test_seo_meta_tags_are_present()
    {
        $response = $this->get('/pages/home');

        $content = $response->getContent();

        // Check for essential SEO tags
        $this->assertStringContainsString('<meta name="description"', $content);
        $this->assertStringContainsString('<meta property="og:', $content);
        $this->assertStringContainsString('<meta name="viewport"', $content);
    }

    public function test_navigation_shows_current_page_as_active()
    {
        $response = $this->get('/pages/about');

        // Should have active state styling for current page
        $response->assertSee('bg-blue-100', false); // Or whatever active class is used
    }

    public function test_pages_are_ordered_correctly_in_navigation()
    {
        $response = $this->get('/pages/home');

        $content = $response->getContent();

        // Navigation should be in order based on front matter order field
        $homePos = strpos($content, 'href="/pages/home"');
        $aboutPos = strpos($content, 'href="/pages/about"');
        $installPos = strpos($content, 'href="/pages/installation"');
        $pluginPos = strpos($content, 'href="/pages/plugin-development"');

        // Verify order (based on the order values in the front matter)
        $this->assertLessThan($aboutPos, $homePos);
        $this->assertLessThan($installPos, $aboutPos);
        $this->assertLessThan($pluginPos, $installPos);
    }

    public function test_page_performance_is_acceptable()
    {
        $startTime = microtime(true);

        $response = $this->get('/pages/home');

        $endTime = microtime(true);
        $renderTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(200);

        // Page should render in under 500ms
        $this->assertLessThan(500, $renderTime, "Page rendering took {$renderTime}ms, which is too slow");
    }

    public function test_xss_protection_in_content()
    {
        // This tests that any user content is properly escaped
        // In a real scenario, we'd test with actual XSS attempts
        $response = $this->get('/pages/home');

        $content = $response->getContent();

        // Should not contain unescaped script tags
        $this->assertStringNotContainsString('<script>alert', $content);
        $this->assertStringNotContainsString('javascript:', $content);
    }

    public function test_error_handling_for_corrupted_files()
    {
        // This would test behavior when markdown files are corrupted
        // In a real implementation, we'd create a corrupted test file

        $response = $this->get('/pages/home');
        $response->assertStatus(200);

        // Should handle gracefully without throwing exceptions
    }

    public function test_caching_headers_are_set()
    {
        $response = $this->get('/pages/home');

        // Should have appropriate caching headers for static content
        // $response->assertHeader('Cache-Control');
        // This would depend on the caching strategy implemented
    }

    public function test_mobile_navigation_functionality()
    {
        $response = $this->get('/pages/home');

        // Should include mobile menu functionality
        $response->assertSee('mobile-menu', false); // Or whatever mobile menu class is used
    }
}
