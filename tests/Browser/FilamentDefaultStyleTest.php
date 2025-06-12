<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FilamentDefaultStyleTest extends DuskTestCase
{
    public function test_default_filament_admin_style()
    {
        $this->browse(function (Browser $browser) {
            // Force refresh to bypass any caching
            $browser->visit('/admin?cache_bust='.time())
                ->waitFor('.fi-sidebar', 15)
                ->pause(3000) // Allow full page load
                ->screenshot('filament-default-style-after-build');
        });
    }
}
