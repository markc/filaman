<?php

use App\Models\User;

describe('Filament Admin Panel', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $this->user = User::factory()->create([
            'email' => 'user@example.com',
            'role' => 'user',
        ]);
    });

    test('admin can access admin panel', function () {
        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
    });

    test('regular users cannot access admin panel', function () {
        $response = $this->actingAs($this->user)->get('/admin');

        $response->assertStatus(403);
    });

    test('unauthenticated users cannot access admin panel', function () {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    });

    test('admin panel login page loads', function () {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    });

    test('admin panel shows dashboard widgets', function () {
        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        // Add specific widget assertions based on your dashboard
    });
});
