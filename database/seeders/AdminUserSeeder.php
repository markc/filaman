<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'], // Find by email
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'), // Change to a strong password for production!
                'email_verified_at' => now(), // Important for Filament access
                'role' => User::ROLE_ADMIN, // Assign admin role
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => User::ROLE_USER,
            ]
        );
    }
}
