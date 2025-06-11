<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Define roles as constants for consistency.
     */
    public const ROLE_USER = 'user';

    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // --- Local Development Bypass ---
        if (App::environment('local')) {
            // In local environment, if an admin user exists, automatically log them in
            // This assumes at least one user with role 'admin' exists in your database
            if (Auth::guest()) { // Only attempt auto-login if not already logged in
                $adminUser = static::where('role', self::ROLE_ADMIN)->first();
                if ($adminUser) {
                    Auth::login($adminUser);

                    return true; // Allow access once logged in
                }
            }

            // If no admin user found or already logged in, proceed as normal
            return Auth::check(); // If already logged in, allow access
        }

        // --- Production Authentication & Authorization ---
        // In production, ensure the user's email is verified and they have a valid role
        return $this->hasVerifiedEmail() && in_array($this->role, [self::ROLE_USER, self::ROLE_ADMIN]);
    }

    /**
     * Determine if the user is an admin.
     * This will be used for granular authorization within Filament (e.g., hide/show navigation items).
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Determine if the user is a regular user.
     */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }
}
