<?php

use App\Models\User;

describe('User Model', function () {
    test('user factory creates valid user', function () {
        $user = User::factory()->create();

        expect($user->name)->toBeString();
        expect($user->email)->toBeString();
        expect($user->password)->toBeString();
        expect($user->email_verified_at)->toBeNull()
            ->or->toBeInstanceOf(DateTime::class);
    });

    test('user has correct fillable attributes', function () {
        $user = new User;

        expect($user->getFillable())->toContain('name', 'email', 'password');
    });

    test('user has correct hidden attributes', function () {
        $user = new User;

        expect($user->getHidden())->toContain('password', 'remember_token');
    });

    test('user has correct casts', function () {
        $user = new User;

        expect($user->getCasts())->toHaveKey('email_verified_at');
        expect($user->getCasts())->toHaveKey('password');
    });

    test('user can be created with role', function () {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        expect($user->role)->toBe('admin');
    });

    test('user can be created with 2fa enabled', function () {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
        ]);

        expect($user->two_factor_enabled)->toBe(true);
    });

    test('user email must be unique', function () {
        User::factory()->create(['email' => 'test@example.com']);

        expect(function () {
            User::factory()->create(['email' => 'test@example.com']);
        })->toThrow(Exception::class);
    });
});
