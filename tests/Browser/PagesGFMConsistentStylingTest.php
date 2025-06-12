<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PagesGFMConsistentStylingTest extends DuskTestCase
{
    public function test_code_blocks_have_consistent_styling()
    {
        $this->browse(function (Browser $browser) {
            // Force hard refresh to bypass browser cache
            $browser->visit('/pages/test-page?cache_bust='.time())
                ->waitFor('.prose')
                ->pause(3000); // Wait longer for Prism.js to load and highlight

            // Scroll to the very beginning of code blocks section
            $browser->driver->executeScript('
                window.scrollTo(0, document.body.scrollHeight * 0.25);
            ');

            $browser->pause(2000)
                ->screenshot('gfm-final-consistent-styling');
        });
    }
}
