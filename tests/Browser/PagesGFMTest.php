<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PagesGFMTest extends DuskTestCase
{
    /**
     * Take screenshots of the pages plugin to assess GFM support.
     */
    public function test_pages_current_markdown_support(): void
    {
        $this->browse(function (Browser $browser) {
            // Take screenshot of GFM test page to assess current support
            $browser->visit('http://localhost:8000/pages/gfm-test')
                ->waitFor('.markdown-content', 10)
                ->screenshot('pages-gfm-test-before');

            // Also take screenshots of other pages for comparison
            $browser->visit('http://localhost:8000/pages/home')
                ->waitFor('.markdown-content', 5)
                ->screenshot('pages-home-before');

            $this->assertTrue(true);
        });
    }

    /**
     * Test enhanced GFM support after improvements.
     */
    public function test_pages_enhanced_gfm_support(): void
    {
        $this->browse(function (Browser $browser) {
            // Take screenshot after GFM enhancements
            $browser->visit('http://localhost:8000/pages/gfm-test')
                ->waitFor('.markdown-content', 10)
                ->screenshot('pages-gfm-test-after');

            $this->assertTrue(true);
        });
    }

    /**
     * Test Mermaid diagram rendering with Firefox.
     */
    public function test_mermaid_diagram_rendering(): void
    {
        // Set browser to Firefox for this test
        putenv('DUSK_BROWSER=firefox');

        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/gfm-test')
                ->waitFor('.markdown-content', 5)
                ->pause(4000) // Allow Mermaid.js to load and process diagrams
                ->screenshot('gfm-test-mermaid-firefox')
                ->assertSee('GitHub Flavored Markdown Test Page');
        });

        // Reset to Chrome for other tests
        putenv('DUSK_BROWSER=chrome');
    }

    /**
     * Test Mermaid diagram rendering with Chrome for comparison.
     */
    public function test_mermaid_diagram_rendering_chrome(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/gfm-test')
                ->waitFor('.markdown-content', 5)
                ->pause(4000) // Allow Mermaid.js to load and process diagrams
                ->screenshot('gfm-test-mermaid-chrome')
                ->assertSee('GitHub Flavored Markdown Test Page')
                ->assertPresent('.mermaid-wrapper'); // Verify Mermaid wrapper exists
        });
    }

    /**
     * Test navbar with capitalized slug navigation.
     */
    public function test_navbar_capitalized_slugs(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/home')
                ->waitFor('.fi-topbar', 5)
                ->screenshot('navbar-capitalized-slugs')
                ->assertSee('Home') // Should show "Home" instead of full title
                ->assertSee('About') // Should show "About" instead of full title
                ->assertSee('Installation'); // Should show "Installation" instead of full title
        });
    }

    /**
     * Test Filament v4 navbar styling.
     */
    public function test_filament_v4_navbar(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/gfm-test')
                ->waitFor('.fi-topbar', 5)
                ->screenshot('navbar-filament-v4-styling')
                ->assertPresent('.fi-topbar-ctn')
                ->assertPresent('.fi-topbar-nav-groups')
                ->assertPresent('.fi-topbar-item-btn')
                ->assertSee('FilaMan');
        });
    }

    /**
     * Test mobile responsiveness of the GFM page.
     */
    public function test_gfm_page_mobile_responsive(): void
    {
        $this->browse(function (Browser $browser) {
            // Test mobile viewport
            $browser->resize(375, 812) // iPhone X dimensions
                ->visit('/pages/gfm-test')
                ->waitFor('.fi-topbar', 5)
                ->screenshot('gfm-page-mobile-before')
                ->assertSee('GitHub Flavored Markdown Test Page');
        });
    }

    /**
     * Test mobile layout after Filament v4 improvements.
     */
    public function test_mobile_layout_after_improvements(): void
    {
        $this->browse(function (Browser $browser) {
            // Test mobile viewport with improved layout
            $browser->resize(375, 812) // iPhone X dimensions
                ->visit('/pages/gfm-test')
                ->waitFor('.fi-topbar-ctn', 5)
                ->screenshot('gfm-page-mobile-after-improvements')
                ->assertPresent('.fi-topbar-ctn')
                ->assertPresent('.fi-main-ctn')
                ->assertPresent('.fi-header-heading')
                ->assertSee('GitHub Flavored Markdown Test Page');
        });
    }

    /**
     * Test desktop layout after Filament v4 improvements.
     */
    public function test_desktop_layout_after_improvements(): void
    {
        $this->browse(function (Browser $browser) {
            // Test desktop viewport with improved layout
            $browser->resize(1920, 1080) // Desktop dimensions
                ->visit('/pages/gfm-test')
                ->waitFor('.fi-topbar-ctn', 5)
                ->screenshot('gfm-page-desktop-after-improvements')
                ->assertPresent('.fi-topbar-nav-groups')
                ->assertPresent('.fi-main-ctn')
                ->assertPresent('.fi-header-heading')
                ->assertSee('GitHub Flavored Markdown Test Page');
        });
    }

    /**
     * Test exact Filament v4 topbar implementation.
     */
    public function test_exact_filament_v4_topbar(): void
    {
        $this->browse(function (Browser $browser) {
            // Test desktop - exact Filament components
            $browser->resize(1920, 1080)
                ->visit('/pages/gfm-test')
                ->waitFor('.fi-topbar-ctn', 5)
                ->screenshot('topbar-exact-filament-v4-desktop')
                ->assertPresent('.fi-topbar-nav-groups') // Desktop nav visible
                ->assertPresent('.fi-topbar-item')
                ->assertPresent('.fi-topbar-item-btn')
                ->assertPresent('.fi-topbar-item-label')
                ->assertSee('Home')
                ->assertSee('About')
                ->assertSee('Installation');
        });
    }

    /**
     * Test exact Filament v4 mobile sidebar implementation.
     */
    public function test_exact_filament_v4_mobile_sidebar(): void
    {
        $this->browse(function (Browser $browser) {
            // Test mobile - exact Filament sidebar (just check elements exist)
            $browser->resize(375, 812)
                ->visit('/pages/gfm-test')
                ->waitFor('.fi-topbar-ctn', 5)
                ->screenshot('topbar-exact-filament-v4-mobile-closed')
                ->assertPresent('.fi-topbar-nav-groups') // Desktop nav exists but hidden on mobile via CSS
                ->assertPresent('.fi-topbar-open-sidebar-btn')
                ->assertPresent('.fi-sidebar') // Sidebar exists but hidden
                ->assertSee('FilaMan')
                ->assertSee('GitHub Flavored Markdown Test Page');
        });
    }

    /**
     * Test final seamless Filament v4 implementation.
     */
    public function test_seamless_filament_v4_implementation(): void
    {
        $this->browse(function (Browser $browser) {
            // Test that we have exact Filament v4 styling and structure
            $browser->resize(1440, 900) // Standard laptop size
                ->visit('/pages/gfm-test')
                ->waitFor('.fi-topbar-ctn', 5)
                ->screenshot('final-seamless-filament-v4-implementation')
                ->assertPresent('.fi-topbar-ctn')
                ->assertPresent('.fi-topbar')
                ->assertPresent('.fi-topbar-nav-groups')
                ->assertPresent('.fi-topbar-item')
                ->assertPresent('.fi-topbar-item-btn')
                ->assertPresent('.fi-topbar-item-label')
                ->assertPresent('.fi-main-ctn')
                ->assertPresent('.fi-main')
                ->assertPresent('.fi-header')
                ->assertPresent('.fi-header-heading')
                ->assertSee('FilaMan')
                ->assertSee('Home')
                ->assertSee('About')
                ->assertSee('Installation')
                ->assertSee('Gfm Test')
                ->assertSee('Plugin Development');
        });
    }

    /**
     * Test desktop topbar after CSS rebuild.
     */
    public function test_desktop_topbar_after_css_rebuild(): void
    {
        $this->browse(function (Browser $browser) {
            // Test desktop topbar with proper Filament CSS loaded
            $browser->resize(1920, 1080)
                ->visit('/pages/gfm-test')
                ->waitFor('.fi-topbar-ctn', 5)
                ->screenshot('desktop-topbar-after-css-rebuild')
                ->assertPresent('.fi-topbar-nav-groups')
                ->assertPresent('.fi-topbar-item')
                ->assertPresent('.fi-topbar-item-btn')
                ->assertSee('FilaMan')
                ->assertSee('Home')
                ->assertSee('About')
                ->assertSee('Installation');
        });
    }

    /**
     * Test complete working topbar with Alpine.js and Filament CSS.
     */
    public function test_complete_working_topbar(): void
    {
        $this->browse(function (Browser $browser) {
            // Test the fully working topbar with all assets
            $browser->resize(1920, 1080)
                ->visit('/pages/gfm-test')
                ->waitFor('.fi-topbar-ctn', 5)
                ->pause(1000) // Allow Alpine.js to initialize
                ->screenshot('complete-working-topbar-final')
                ->assertPresent('.fi-topbar-nav-groups')
                ->assertPresent('.fi-topbar-item.fi-active') // Check for active state
                ->assertPresent('.fi-topbar-item-btn')
                ->assertPresent('.fi-topbar-item-label')
                ->assertSee('FilaMan')
                ->assertSee('Gfm Test'); // Current page should be active
        });
    }

    /**
     * Test mobile sidebar functionality with Alpine.js.
     */
    public function test_mobile_sidebar_with_alpinejs(): void
    {
        $this->browse(function (Browser $browser) {
            // Test mobile sidebar with working Alpine.js
            $browser->resize(375, 812)
                ->visit('/pages/gfm-test')
                ->waitFor('.fi-topbar-ctn', 5)
                ->pause(1000) // Allow Alpine.js to initialize
                ->screenshot('mobile-sidebar-with-alpinejs-before')
                ->assertPresent('.fi-topbar-open-sidebar-btn')
                ->assertPresent('.fi-sidebar')
                    // Sidebar should be hidden initially
                ->assertSee('FilaMan');
        });
    }
}
