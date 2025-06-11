<?php

use Illuminate\Support\Facades\Schema;

describe('Database Structure', function () {
    test('users table has expected columns', function () {
        expect(Schema::hasTable('users'))->toBeTrue();

        expect(Schema::hasColumns('users', [
            'id', 'name', 'email', 'email_verified_at',
            'password', 'remember_token', 'created_at', 'updated_at',
        ]))->toBeTrue();
    });

    test('users table has role column', function () {
        expect(Schema::hasColumn('users', 'role'))->toBeTrue();
    });

    test('users table has two_factor_enabled column', function () {
        expect(Schema::hasColumn('users', 'two_factor_enabled'))->toBeTrue();
    });

    test('cache table exists', function () {
        expect(Schema::hasTable('cache'))->toBeTrue();

        expect(Schema::hasColumns('cache', [
            'key', 'value', 'expiration',
        ]))->toBeTrue();
    });

    test('jobs table exists', function () {
        expect(Schema::hasTable('jobs'))->toBeTrue();

        expect(Schema::hasColumns('jobs', [
            'id', 'queue', 'payload', 'attempts', 'reserved_at', 'available_at', 'created_at',
        ]))->toBeTrue();
    });

    test('migrations have run successfully', function () {
        expect(Schema::hasTable('migrations'))->toBeTrue();

        $migrations = DB::table('migrations')->count();
        expect($migrations)->toBeGreaterThan(0);
    });
});
