<?php

use App\Models\User;

describe('Authentication', function () {
    test('users can be created and authenticated', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => now(),
        ]);

        $this->assertInstanceOf(User::class, $user);
        expect($user->email)->toBe('test@example.com');
        expect($user->hasVerifiedEmail())->toBeTrue();
    });

    test('user factory creates valid users', function () {
        $user = User::factory()->create([
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

});
