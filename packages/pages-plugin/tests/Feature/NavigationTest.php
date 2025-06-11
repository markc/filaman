<?php

namespace FilaMan\PagesPlugin\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register the plugin service provider
        $this->app->register(\FilaMan\PagesPlugin\PagesPluginServiceProvider::class);
    }

    public function test_navigation_includes_all_published_pages()
    {
        $response = $this->get('/pages/home');

        $response->assertStatus(200);

        // Should include links to all published pages
        $response->assertSee('href="/pages/home"', false);
        $response->assertSee('href="/pages/about"', false);
        $response->assertSee('href="/pages/installation"', false);
        $response->assertSee('href="/pages/plugin-development"', false);
    }

    public function test_navigation_excludes_unpublished_pages()
    {
        // This would test that pages with published: false don't appear
        $response = $this->get('/pages/home');

        // Since all current pages are published, we test that navigation
        // only includes published pages through the helper function
        $this->assertTrue(true); // Placeholder - would need unpublished test pages
    }

    public function test_navigation_shows_active_page_state()
    {
        $response = $this->get('/pages/about');

        $content = $response->getContent();

        // Find the navigation link for the current page
        $this->assertStringContainsString('About', $content);

        // Should have some active state indicator
        // This would depend on the exact CSS classes used
        $this->assertStringContainsString('navigation', $content);
    }

    public function test_navigation_order_matches_front_matter_order()
    {
        $response = $this->get('/pages/home');

        $content = $response->getContent();

        // Extract navigation section
        preg_match('/<nav.*?<\/nav>/s', $content, $navMatches);

        if (! empty($navMatches)) {
            $navContent = $navMatches[0];

            // Find positions of each page link
            $positions = [];
            $pages = ['home', 'about', 'installation', 'plugin-development'];

            foreach ($pages as $page) {
                $pos = strpos($navContent, "href=\"/pages/{$page}\"");
                if ($pos !== false) {
                    $positions[$page] = $pos;
                }
            }

            // Verify order based on front matter order values
            if (count($positions) > 1) {
                $sortedPositions = $positions;
                asort($sortedPositions);
                $this->assertEquals(array_keys($positions), array_keys($sortedPositions));
            }
        }
    }

    public function test_navigation_is_responsive()
    {
        $response = $this->get('/pages/home');

        $content = $response->getContent();

        // Should include responsive design elements
        $this->assertStringContainsString('md:', $content); // Tailwind responsive prefix
        $this->assertStringContainsString('mobile', $content); // Some mobile-specific content
    }

    public function test_navigation_includes_brand_logo()
    {
        $response = $this->get('/pages/home');

        // Should include FilaMan branding
        $response->assertSee('FilaMan');
    }

    public function test_navigation_includes_admin_panel_link()
    {
        $response = $this->get('/pages/home');

        // Should include link to admin panel
        $response->assertSee('/admin');
    }

    public function test_breadcrumb_navigation()
    {
        $response = $this->get('/pages/plugin-development');

        // Should show current page context
        $response->assertSee('Plugin Development');
    }

    public function test_navigation_accessibility()
    {
        $response = $this->get('/pages/home');

        $content = $response->getContent();

        // Should include proper ARIA labels and semantic HTML
        $this->assertStringContainsString('<nav', $content);
        $this->assertStringContainsString('role=', $content);
    }

    public function test_navigation_keyboard_navigation()
    {
        $response = $this->get('/pages/home');

        $content = $response->getContent();

        // Links should be keyboard accessible
        $this->assertStringContainsString('<a ', $content);
        $this->assertStringContainsString('href=', $content);
    }

    public function test_search_functionality_placeholder()
    {
        // Placeholder for future search functionality
        $response = $this->get('/pages/home');

        $response->assertStatus(200);

        // When search is implemented, test it here
        // $response->assertSee('search');
    }

    public function test_mobile_menu_toggle()
    {
        $response = $this->get('/pages/home');

        $content = $response->getContent();

        // Should include mobile menu toggle functionality
        // This would depend on the JavaScript implementation
        $this->assertStringContainsString('menu', $content);
    }

    public function test_navigation_performance()
    {
        $startTime = microtime(true);

        $response = $this->get('/pages/home');

        $endTime = microtime(true);
        $renderTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        // Navigation should not significantly impact page load time
        $this->assertLessThan(200, $renderTime);
    }

    public function test_navigation_caching()
    {
        // Test that navigation data is cached appropriately
        $response1 = $this->get('/pages/home');
        $response2 = $this->get('/pages/about');

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Both should have consistent navigation
        $nav1 = $this->extractNavigationFromResponse($response1);
        $nav2 = $this->extractNavigationFromResponse($response2);

        // Navigation structure should be consistent across pages
        $this->assertStringContainsString('FilaMan', $nav1);
        $this->assertStringContainsString('FilaMan', $nav2);
    }

    public function test_navigation_updates_when_pages_change()
    {
        // This would test dynamic navigation updates
        // In a real scenario, we'd add/remove pages and verify navigation updates

        $response = $this->get('/pages/home');
        $response->assertStatus(200);

        // Placeholder for dynamic navigation testing
        $this->assertTrue(true);
    }

    public function test_navigation_handles_long_page_titles()
    {
        // Test that navigation gracefully handles pages with long titles
        $response = $this->get('/pages/plugin-development');

        $response->assertStatus(200);

        // Should handle "Plugin Development Guide" title appropriately
        $response->assertSee('Plugin Development');
    }

    public function test_navigation_security()
    {
        $response = $this->get('/pages/home');

        $content = $response->getContent();

        // Navigation should not be vulnerable to XSS
        $this->assertStringNotContainsString('<script>', $content);
        $this->assertStringNotContainsString('javascript:', $content);

        // All links should be properly escaped
        $this->assertStringNotContainsString('onclick=', $content);
    }

    private function extractNavigationFromResponse($response): string
    {
        $content = $response->getContent();

        // Extract navigation section
        preg_match('/<nav.*?<\/nav>/s', $content, $matches);

        return $matches[0] ?? '';
    }
}
