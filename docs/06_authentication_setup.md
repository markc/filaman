---
title: Authentication and Authorization Setup
description: Complete guide for implementing multi-factor authentication and role-based access control
date: 2025-11-06
---

# Authentication and Authorization Setup

This guide covers the implementation of a comprehensive authentication system with environment-aware behavior and role-based authorization.

## Overview

The authentication system provides:
- **Local Development**: Automatic admin login (no authentication required)
- **Production**: Full email-based multi-factor authentication (MFA)
- **Role-based Authorization**: Simple Admin/User distinction
- **Filament Integration**: Native Filament 4 authentication features

## Implementation Details

### User Model Updates

The User model has been updated to implement `FilamentUser` interface with the following key features:

**File: `app/Models/User.php`**

```php
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
    use HasFactory, Notifiable;

    // Role constants
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name', 'email', 'password', 'role',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

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
     * Environment-aware panel access control
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Local Development: Auto-login as admin
        if (App::environment('local')) {
            if (Auth::guest()) {
                $adminUser = static::where('role', self::ROLE_ADMIN)->first();
                if ($adminUser) {
                    Auth::login($adminUser);
                    return true;
                }
            }
            return Auth::check();
        }

        // Production: Email verification + valid role required
        return $this->hasVerifiedEmail() && 
               in_array($this->role, [self::ROLE_USER, self::ROLE_ADMIN]);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }
}
```

### Database Schema

**Migration: `add_role_and_2fa_to_users_table.php`**

Added columns to users table:
- `role` (string, default 'user', indexed)
- `two_factor_secret` (text, nullable, encrypted)
- `two_factor_recovery_codes` (text, nullable, encrypted)
- `two_factor_confirmed_at` (timestamp, nullable)

### Panel Configuration

**File: `app/Providers/Filament/AdminPanelProvider.php`**

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->colors(['primary' => Color::Blue])
        ->login()                    // Enable login page
        ->emailVerification()        // Enable email verification
        ->profile()                 // Enable profile management
        ->plugin(AdminPanelPlugin::make())
        // ... middleware and other configuration
}
```

### User Seeding

**File: `database/seeders/AdminUserSeeder.php`**

Creates default users:
- **Admin User**: `admin@example.com` / `password` (role: admin)
- **Regular User**: `user@example.com` / `password` (role: user)

Run with: `php artisan db:seed --class=AdminUserSeeder`

## Environment Configuration

### Local Development (APP_ENV=local)

- **Auto-login**: Automatically logs in as the first admin user
- **No authentication required**: Bypasses login screen entirely
- **Instant access**: Perfect for development workflow

### Production (APP_ENV=production)

- **Full MFA**: Email-based multi-factor authentication
- **Email verification**: Users must verify email addresses
- **Role validation**: Only users with valid roles can access

### Environment Variables

```env
# For local development
APP_ENV=local
APP_DEBUG=true
MAIL_MAILER=log

# For production
APP_ENV=production
APP_DEBUG=false
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

## Role-Based Authorization

### Available Roles

- **Admin (`admin`)**: Full access to all panel features
- **User (`user`)**: Limited access (customizable per resource)

### Authorization Patterns

#### Resource-Level Authorization

```php
// In any Filament Resource
public static function canViewAny(): bool
{
    return auth()->user()?->isAdmin() ?? false;
}

public static function canCreate(): bool
{
    return auth()->user()?->isAdmin() ?? false;
}

public static function canEdit($record): bool
{
    $user = auth()->user();
    if (!$user) return false;
    
    // Admins can edit anyone, users can edit themselves
    return $user->isAdmin() || 
           ($user->isUser() && $user->id === $record->id);
}

public static function canDelete($record): bool
{
    $user = auth()->user();
    if (!$user) return false;
    
    // Only admins can delete, can't delete themselves
    return $user->isAdmin() && $user->id !== $record->id;
}
```

#### Navigation Control

```php
// Hide navigation items based on role
public static function shouldRegisterNavigation(): bool
{
    return auth()->user()?->isAdmin() ?? false;
}
```

## Testing the Implementation

### Test Local Auto-Login

1. Ensure `APP_ENV=local` in `.env`
2. Visit `http://127.0.0.1:8000/admin`
3. Should automatically log in as admin user
4. No login form should appear

### Test Production Authentication

1. Change `APP_ENV=production` in `.env`
2. Configure mail settings for actual email sending
3. Visit `http://127.0.0.1:8000/admin`
4. Should see Filament login form
5. Login with `admin@example.com` / `password`
6. Should prompt for email verification and MFA

### Verify User Creation

```bash
# Check users were created
php artisan tinker --execute="
echo 'Admin users: ' . App\Models\User::where('role', 'admin')->count() . PHP_EOL;
echo 'Total users: ' . App\Models\User::count() . PHP_EOL;
App\Models\User::all(['name', 'email', 'role'])->each(function(\$user) { 
    echo \$user->name . ' (' . \$user->email . ') - ' . \$user->role . PHP_EOL; 
});
"
```

## Security Considerations

### Local Development Security

- Auto-login is **only active** when `APP_ENV=local`
- Never use `local` environment in production
- Auto-login requires existing admin user in database

### Production Security

- Email verification is mandatory
- MFA provides additional security layer
- Role validation prevents unauthorized access
- Encrypted storage for 2FA secrets

### Password Security

- Default password is `password` - **CHANGE IN PRODUCTION**
- Use strong passwords for production deployments
- Consider implementing password policies

## Extending the System

### Adding New Roles

1. Add role constants to User model:
```php
public const ROLE_MANAGER = 'manager';
```

2. Update role validation:
```php
return $this->hasVerifiedEmail() && 
       in_array($this->role, [self::ROLE_USER, self::ROLE_ADMIN, self::ROLE_MANAGER]);
```

3. Add helper methods:
```php
public function isManager(): bool
{
    return $this->role === self::ROLE_MANAGER;
}
```

### Custom Authorization Logic

```php
// Complex authorization example
public static function canEdit($record): bool
{
    $user = auth()->user();
    if (!$user) return false;
    
    return match($user->role) {
        User::ROLE_ADMIN => true,
        User::ROLE_MANAGER => $record->department_id === $user->department_id,
        User::ROLE_USER => $user->id === $record->id,
        default => false,
    };
}
```

### Email Customization

Customize MFA and verification emails by publishing Filament's email templates:

```bash
php artisan vendor:publish --tag="filament-email-templates"
```

## Troubleshooting

### Auto-Login Not Working

- Check `APP_ENV=local` in `.env`
- Verify admin user exists: `User::where('role', 'admin')->exists()`
- Clear cache: `php artisan optimize:clear`

### MFA Issues

- Verify mail configuration in production
- Check email delivery logs
- Ensure email_verified_at is set for users

### Permission Denied

- Check user role assignments
- Verify authorization methods in resources
- Review panel access configuration

## Next Steps

1. **Create Resources**: Build Filament resources with role-based authorization
2. **Custom Policies**: Implement Laravel policies for complex authorization
3. **Audit Logging**: Track user actions and authentication events
4. **API Authentication**: Extend authentication to API endpoints
5. **Social Login**: Add OAuth providers for additional login options