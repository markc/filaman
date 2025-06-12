<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class GfmTestScreenshot extends DuskTestCase
{
    /**
     * Take screenshot of GFM test page
     */
    public function test_gfm_rendering(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/test-page')
                ->waitFor('h1')
                ->screenshot('gfm-test-page-full')
                ->pause(1000);

            // Screenshot the headers section
            $browser->scrollTo('h2:contains("Headers")')
                ->pause(500)
                ->screenshot('gfm-headers-section');

            // Screenshot just the lists section
            $browser->scrollTo('h2:contains("Lists")')
                ->pause(500)
                ->screenshot('gfm-lists-section');

            // Screenshot task lists
            $browser->scrollTo('h2:contains("Task Lists")')
                ->pause(500)
                ->screenshot('gfm-task-lists');

            // Screenshot tables
            $browser->scrollTo('h2:contains("Tables")')
                ->pause(500)
                ->screenshot('gfm-tables');

            // Screenshot code blocks
            $browser->scrollTo('h2:contains("Code")')
                ->pause(500)
                ->screenshot('gfm-code-blocks');

            // Screenshot more code examples (scroll down further)
            $browser->scrollTo('h3:contains("Code Blocks")')
                ->pause(500)
                ->screenshot('gfm-code-examples-detailed');
        });
    }
}
