<?php

use Laravel\Dusk\Browser;

test('new filament pages layout', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/pages/home')
            ->waitFor('.fi-layout')
            ->screenshot('pages-new-filament-layout')
            ->assertSee('FilaMan')
            ->assertSee('Home');
    });
});

test('mobile sidebar functionality', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/pages/home')
            ->resize(768, 1024)
            ->waitFor('.fi-topbar')
            ->click('button[title="Open navigation"]')
            ->waitFor('.fi-sidebar')
            ->screenshot('pages-mobile-sidebar')
            ->assertSee('FilaMan')
            ->assertSee('Sign in to Admin');
    });
});
