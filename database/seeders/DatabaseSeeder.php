<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
        ]);

        // Ensure there's always an admin user for local development
        if (app()->environment('local')) {
            User::firstOrCreate(
                ['email' => 'admin@local.dev'],
                [
                    'name' => 'Local Admin',
                    'role' => 'admin',
                    'email_verified_at' => now(),
                    'password' => bcrypt('password'),
                ]
            );
        }
    }
}
