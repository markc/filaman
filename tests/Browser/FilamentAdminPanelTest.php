<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FilamentAdminPanelTest extends DuskTestCase
{
    public function test_admin_panel_with_pages_resource()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin')
                ->waitFor('.fi-sidebar', 10)
                ->pause(2000)
                ->screenshot('filament-admin-panel-with-pages');
        });
    }
}
