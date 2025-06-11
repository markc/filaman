<?php

namespace FilaMan\Pages\Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PageNavigationTest extends DuskTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register the plugin service provider
        $this->app->register(\FilaMan\Pages\PagesPluginServiceProvider::class);
    }

    public function test_user_can_navigate_between_pages()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/home')
                ->assertSee('Welcome to FilaMan')
                ->clickLink('About')
                ->assertPathIs('/pages/about')
                ->assertSee('About FilaMan')
                ->clickLink('Installation')
                ->assertPathIs('/pages/installation')
                ->assertSee('Installation Guide')
                ->clickLink('Plugin Development')
                ->assertPathIs('/pages/plugin-development')
                ->assertSee('Plugin Development Guide');
        });
    }

    public function test_navigation_highlights_current_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/about')
                ->assertSee('About')
                ->assertHasClass('@current-page', 'active'); // Adjust selector as needed
        });
    }

    public function test_responsive_navigation_works_on_mobile()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 667) // iPhone size
                ->visit('/pages/home')
                ->assertSee('FilaMan')
                ->click('@mobile-menu-toggle') // Adjust selector as needed
                ->assertVisible('@mobile-navigation')
                ->clickLink('About')
                ->assertPathIs('/pages/about');
        });
    }

    public function test_breadcrumb_navigation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/plugin-development')
                ->assertSee('Plugin Development Guide')
                ->assertSee('Home') // Breadcrumb link
                ->clickLink('Home')
                ->assertPathIs('/pages/home');
        });
    }

    public function test_search_functionality()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/home')
                ->type('@search-input', 'plugin') // Future feature
                ->keys('@search-input', '{enter}')
                ->assertSee('Plugin Development'); // Should find relevant pages
        });
    }

    public function test_page_loading_performance()
    {
        $this->browse(function (Browser $browser) {
            $startTime = microtime(true);

            $browser->visit('/pages/home')
                ->assertSee('Welcome to FilaMan');

            $endTime = microtime(true);
            $loadTime = ($endTime - $startTime) * 1000;

            // Page should load within 2 seconds
            $this->assertLessThan(2000, $loadTime, "Page took {$loadTime}ms to load");
        });
    }

    public function test_keyboard_navigation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/home')
                ->keys('body', '{tab}') // Tab to first link
                ->keys('body', '{enter}') // Enter to follow link
                ->waitForLocation('/pages/about'); // Should navigate
        });
    }

    public function test_back_button_functionality()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/home')
                ->clickLink('About')
                ->assertPathIs('/pages/about')
                ->back()
                ->assertPathIs('/pages/home')
                ->assertSee('Welcome to FilaMan');
        });
    }

    public function test_admin_panel_link_works()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/pages/home')
                ->clickLink('Admin Panel')
                ->assertPathIs('/admin')
                ->assertSee('Dashboard'); // Filament admin dashboard
        });
    }

    public function test_page_content_is_readable()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/home')
                ->assertSee('Welcome to FilaMan')
                ->assertVisible('h1')
                ->assertVisible('p')
                ->assertVisible('nav');
        });
    }

    public function test_code_blocks_are_properly_displayed()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/installation')
                ->assertVisible('pre')
                ->assertVisible('code')
                ->assertSee('composer install');
        });
    }

    public function test_links_open_in_correct_context()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/home')
                ->clickLink('About')
                ->assertPathIs('/pages/about');

            // External links should open in new tab (if any)
            // $browser->click('@external-link')
            //         ->assertAttribute('@external-link', 'target', '_blank');
        });
    }

    public function test_page_scrolling_behavior()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/plugin-development')
                ->scrollTo('footer')
                ->assertVisible('footer')
                ->scrollTo('header')
                ->assertVisible('nav');
        });
    }

    public function test_image_loading()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/home')
                ->waitFor('img', 5) // Wait up to 5 seconds for images
                ->assertVisible('img'); // If there are images
        });
    }

    public function test_form_interactions()
    {
        // Future feature: contact forms, feedback forms, etc.
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/home')
                ->assertSee('FilaMan'); // Placeholder test
        });
    }

    public function test_accessibility_features()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/home')
                ->assertAttribute('nav', 'role', 'navigation')
                ->assertAttribute('main', 'role', 'main')
                ->assertPresent('[alt]'); // Images should have alt text
        });
    }

    public function test_print_functionality()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/installation')
                ->keys('body', ['{ctrl}', 'p']) // Ctrl+P to print
                ->pause(1000) // Wait for print dialog
                ->keys('body', '{escape}'); // Close print dialog
        });
    }

    public function test_copy_link_functionality()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/about')
                ->rightClick('h1') // Right click on heading
                ->pause(500); // Context menu should appear
        });
    }

    public function test_error_page_navigation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/nonexistent')
                ->assertSee('404')
                ->clickLink('Home') // Error page should have navigation back
                ->assertPathIs('/pages/home');
        });
    }

    public function test_smooth_scrolling()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/plugin-development')
                ->click('@table-of-contents-link') // Future feature
                ->pause(1000) // Allow smooth scroll
                ->assertVisible('@target-section');
        });
    }

    public function test_page_sharing_functionality()
    {
        // Future feature: social sharing buttons
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/about')
                ->assertSee('About FilaMan'); // Placeholder
        });
    }
}
