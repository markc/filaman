<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    // With plugins present, root route should redirect to admin
    $response->assertRedirect('/admin');
});

test('the admin panel is accessible', function () {
    // Create a user for admin access
    $user = \App\Models\User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->actingAs($user)->get('/admin');
    
    // Should get successful response from admin panel
    $response->assertStatus(200);
});

test('the pages panel is accessible', function () {
    $response = $this->get('/pages');
    
    // Pages panel should be publicly accessible
    $response->assertStatus(200);
});
