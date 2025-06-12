<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FilamentScreenshotTest extends DuskTestCase
{
    /**
     * Test taking screenshots of Filament admin panel.
     */
    public function test_can_take_screenshots_of_admin_panel(): void
    {
        $this->browse(function (Browser $browser) {
            // Create and login as admin user
            $this->loginAsFilamentAdmin($browser);

            // Take screenshot of dashboard
            $browser->visit('/admin')
                ->waitFor('.fi-topbar', 10) // Wait for Filament topbar to load
                ->screenshot('filament-dashboard');

            $this->assertTrue(true); // Test passes if no exceptions thrown
        });
    }

    /**
     * Test taking full page screenshots.
     */
    public function test_can_take_full_page_screenshots(): void
    {
        $this->browse(function (Browser $browser) {
            // Visit homepage
            $browser->visit('/')
                ->waitFor('body', 5)
                ->screenshot('homepage-full-page');

            $this->assertTrue(true);
        });
    }

    /**
     * Test different screen sizes for responsive screenshots.
     */
    public function test_can_take_responsive_screenshots(): void
    {
        $this->browse(function (Browser $browser) {
            // Desktop view
            $browser->resize(1920, 1080)
                ->visit('/')
                ->screenshot('homepage-desktop');

            // Tablet view
            $browser->resize(768, 1024)
                ->visit('/')
                ->screenshot('homepage-tablet');

            // Mobile view
            $browser->resize(375, 667)
                ->visit('/')
                ->screenshot('homepage-mobile');

            $this->assertTrue(true);
        });
    }

    /**
     * Test screenshot with custom names and directories.
     */
    public function test_can_take_custom_screenshots(): void
    {
        $timestamp = date('Y-m-d-H-i-s');

        $this->browse(function (Browser $browser) use ($timestamp) {
            $browser->visit('/')
                ->waitFor('body', 5)
                ->screenshot("custom-homepage-{$timestamp}");

            $this->assertTrue(true);
        });
    }

    /**
     * Test Firefox-specific screenshot functionality.
     */
    public function test_firefox_screenshots(): void
    {
        // This test will only run if DUSK_BROWSER=firefox is set
        if (env('DUSK_BROWSER') !== 'firefox') {
            $this->markTestSkipped('Firefox browser tests require DUSK_BROWSER=firefox');
        }

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->screenshot('firefox-homepage');

            // Test Firefox with admin panel
            $this->loginAsFilamentAdmin($browser);

            $browser->visit('/admin')
                ->waitForText('Dashboard')
                ->screenshot('firefox-admin-dashboard');

            $this->assertTrue(true);
        });
    }
}
