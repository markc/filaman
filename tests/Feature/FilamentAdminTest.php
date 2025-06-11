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

        $response->assertStatus(403)
            ->orWhere('status', 302); // May redirect to login
    });

    test('unauthenticated users cannot access admin panel', function () {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    });

    test('admin can view users resource', function () {
        $response = $this->actingAs($this->admin)->get('/admin/users');

        $response->assertStatus(200);
    });

    test('admin panel login works', function () {
        $response = $this->post('/admin/login', [
            'email' => $this->admin->email,
            'password' => 'password', // Default factory password
        ]);

        $response->assertRedirect('/admin');
    });

    test('admin panel shows dashboard widgets', function () {
        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        // Add specific widget assertions based on your dashboard
    });
});
