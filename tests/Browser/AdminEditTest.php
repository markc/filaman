<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminEditTest extends DuskTestCase
{
    public function test_admin_edit_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('http://localhost:8000/admin/plugins/1/edit')
                ->pause(3000)
                ->screenshot('admin-edit-page-error')
                ->assertPresent('body');
        });
    }
}
