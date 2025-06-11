<?php

use App\Models\User;

describe('Authentication', function () {
    test('admin users can access filament logout', function () {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post('/admin/logout');

        $response->assertRedirect();
        $this->assertGuest();
    });

    test('users can be authenticated via filament', function () {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->assertInstanceOf(User::class, $admin);
        expect($admin->email)->toBe('admin@test.com');
        expect($admin->role)->toBe('admin');
    });

    test('user factory creates valid authenticated users', function () {
        $user = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        expect($user->email)->toBeString();
        expect($user->name)->toBeString();
        expect($user->hasVerifiedEmail())->toBeTrue();
    });

    test('user roles work correctly', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        expect($admin->isAdmin())->toBeTrue();
        expect($admin->isUser())->toBeFalse();
        expect($user->isAdmin())->toBeFalse();
        expect($user->isUser())->toBeTrue();
    });

    test('panel access control works correctly', function () {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $panel = app(\Filament\Panel::class);

        expect($admin->canAccessPanel($panel))->toBeTrue();
        expect($user->canAccessPanel($panel))->toBeFalse();
    });
});
