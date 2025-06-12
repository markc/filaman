<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminPluginsScreenshotTest extends DuskTestCase
{
    /**
     * Take screenshot of admin plugins page.
     */
    public function test_admin_plugins_page_screenshot(): void
    {
        $this->browse(function (Browser $browser) {
            // Visit admin plugins page and take screenshot
            $browser->visit('http://localhost:8000/admin/plugins')
                ->pause(8000) // Allow full page load
                ->screenshot('admin-plugins-page-final')
                ->assertPresent('body'); // Just check page loads
        });
    }
}
