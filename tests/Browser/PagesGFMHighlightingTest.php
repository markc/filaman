<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PagesGFMHighlightingTest extends DuskTestCase
{
    public function test_syntax_highlighting_works()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/pages/test-page')
                ->waitFor('.prose')
                ->pause(2000); // Wait for Prism.js to load and highlight

            // Scroll to code blocks section
            $browser->driver->executeScript('
                const codeBlocksHeading = Array.from(document.querySelectorAll("h2")).find(h => h.textContent.includes("Code Blocks"));
                if (codeBlocksHeading) {
                    codeBlocksHeading.scrollIntoView({behavior: "smooth", block: "start"});
                }
            ');

            $browser->pause(2000)
                ->screenshot('gfm-syntax-highlighting-code-blocks');
        });
    }
}
