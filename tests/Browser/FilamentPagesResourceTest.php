<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FilamentPagesResourceTest extends DuskTestCase
{
    public function test_pages_resource_crud_interface()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/pages') // Go directly to pages resource
                ->waitFor('body', 10)
                ->pause(3000)
                ->screenshot('filament-pages-resource-attempt');
        });
    }
}
