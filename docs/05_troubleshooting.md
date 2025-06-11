---
title: Troubleshooting Guide
description: Common issues and solutions for Filament 4 plugin-based applications
date: 2025-11-06
---

# Troubleshooting Guide

This guide covers common issues encountered when working with Filament 4 plugin-based applications.

## Installation Issues

### Composer Dependency Conflicts

**Problem:** Cannot install Filament 4 beta due to dependency conflicts.

**Solution:**
```bash
# Update composer.json with minimum-stability
{
    "minimum-stability": "beta"
}

# Force dependency resolution
composer update --with-all-dependencies

# Clear composer cache if needed
composer clear-cache
```

### Hydro Installation Fails

**Problem:** `awcodes/hydro` installation fails with dependency errors.

**Solution:**
```bash
# Update global composer first
composer global update

# Install with dependency resolution
composer global require awcodes/hydro --with-all-dependencies

# Check global PATH
echo $PATH | grep composer
```

### PHP Version Conflicts

**Problem:** PHP version requirements not met.

**Solution:**
```bash
# Check current PHP version
php -v

# Install PHP 8.3+ (Ubuntu/Debian)
sudo apt update
sudo apt install php8.3-cli php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring

# Update alternatives
sudo update-alternatives --config php
```

## Plugin Development Issues

### Namespace Errors

**Problem:** `Class 'FilaMan\AdminPanelPlugin\...' not found`

**Root Cause:** Invalid PHP namespace with hyphens.

**Solution:**
```json
// In plugin's composer.json
{
    "autoload": {
        "psr-4": {
            "FilaMan\\AdminPanelPlugin\\": "src/"
        }
    }
}
```

Update all PHP files to use the corrected namespace:
```php
<?php
namespace FilaMan\AdminPanelPlugin;
```

### Autoload Issues

**Problem:** Plugin classes not found after creation.

**Solution:**
```bash
# Regenerate autoload files
composer dump-autoload

# Check if plugin is properly registered
composer show filaman/admin-panel-plugin

# Verify autoload mapping
composer config autoloader.psr-4
```

### Plugin Registration Errors

**Problem:** Plugin not appearing in Filament panel.

**Solution:**
1. Check plugin is registered in AdminPanelProvider:
```php
use FilaMan\AdminPanelPlugin\AdminPanelPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(AdminPanelPlugin::make())
        // ... other config
}
```

2. Verify plugin implements the interface correctly:
```php
class AdminPanelPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }
    
    public function getId(): string
    {
        return 'admin-panel-plugin';
    }
}
```

### Theme/Asset Errors

**Problem:** `Failed to open stream: No such file or directory` for theme files.

**Solution:**
Remove theme registration if not using custom themes:
```php
// Remove these lines from plugin
FilamentAsset::register([
    Theme::make('admin-panel-plugin', __DIR__ . '/../resources/dist/admin-panel-plugin.css'),
]);
```

## Filament 4 Specific Issues

### Property Type Errors

**Problem:** `Type of ... must be BackedEnum|string|null`

**Solution:**
Use proper type declarations for Filament 4:
```php
// Instead of
protected static ?string $navigationIcon = 'heroicon-o-home';

// Use
protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
```

### Migration Errors

**Problem:** Plugin migrations not running.

**Solution:**
1. Ensure service provider is properly configured:
```php
public function configurePackage(Package $package): void
{
    $package
        ->name('admin-panel-plugin')
        ->hasMigrations(['create_posts_table']);
}
```

2. Check migration file naming:
```
database/migrations/2024_01_01_000000_create_posts_table.php
```

3. Run migrations manually:
```bash
php artisan migrate --path=plugins/admin-panel-plugin/database/migrations
```

### Resource Discovery Issues

**Problem:** Resources not appearing in navigation.

**Solution:**
1. Check discovery paths in plugin:
```php
$panel->discoverResources(
    in: __DIR__ . '/Filament/Resources',
    for: 'FilaMan\\AdminPanelPlugin\\Filament\\Resources'
);
```

2. Verify directory structure:
```
src/
└── Filament/
    └── Resources/
        └── PostResource.php
```

3. Ensure resource extends correct base class:
```php
use Filament\Resources\Resource;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
}
```

## Runtime Errors

### Memory Limit Exceeded

**Problem:** `Fatal error: Allowed memory size exhausted`

**Solution:**
```bash
# Increase PHP memory limit
ini_set('memory_limit', '512M');

# Or in php.ini
memory_limit = 512M

# For Composer operations
COMPOSER_MEMORY_LIMIT=-1 composer install
```

### Permission Errors

**Problem:** Cannot write to storage directories.

**Solution:**
```bash
# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# For development (less secure)
chmod -R 777 storage bootstrap/cache
```

### Database Connection Issues

**Problem:** `SQLSTATE[HY000] [2002] Connection refused`

**Solution:**
1. Check database service:
```bash
sudo service mysql status
sudo service mysql start
```

2. Verify connection settings in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

3. Test connection:
```bash
php artisan tinker
DB::connection()->getPdo();
```

### Asset Compilation Errors

**Problem:** Vite build fails or assets not loading.

**Solution:**
```bash
# Clear node modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Rebuild assets
npm run build

# For development
npm run dev
```

## Panel Access Issues

### Cannot Access Admin Panel

**Problem:** 404 error when accessing `/admin`

**Solution:**
1. Check panel configuration:
```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        ->path('admin')  // This sets the URL path
        ->login();
}
```

2. Clear route cache:
```bash
php artisan route:clear
php artisan route:cache
```

3. Check web server configuration points to `public/` directory.

### Login Issues

**Problem:** Cannot login to admin panel.

**Solution:**
1. Create admin user:
```bash
php artisan make:filament-user
```

2. Check user model configuration:
```php
// In User model
protected $fillable = [
    'name',
    'email',
    'password',
];
```

3. Verify authentication guard:
```php
$panel->authGuard('web')
```

### Authorization Errors

**Problem:** Access denied errors in admin panel.

**Solution:**
1. Check user permissions:
```php
// In resource
public static function canViewAny(): bool
{
    return true; // For testing
}
```

2. Verify policies:
```bash
php artisan make:policy PostPolicy --model=Post
```

## Performance Issues

### Slow Loading

**Problem:** Admin panel loads slowly.

**Solution:**
```bash
# Optimize application
php artisan optimize

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

### High Memory Usage

**Problem:** High memory consumption.

**Solution:**
1. Enable query logging to identify N+1 queries:
```php
// In AppServiceProvider
DB::enableQueryLog();
```

2. Use eager loading in resources:
```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()->with(['category', 'author']);
}
```

3. Implement pagination:
```php
protected static int $recordsPerPage = 25;
```

## Debugging Techniques

### Enable Debug Mode

```env
APP_DEBUG=true
APP_LOG_LEVEL=debug
```

### Log Plugin Activity

```php
// In plugin class
use Illuminate\Support\Facades\Log;

public function register(Panel $panel): void
{
    Log::info('AdminPanelPlugin registering', [
        'panel_id' => $panel->getId()
    ]);
    
    // ... registration logic
}
```

### Check Plugin Loading

```bash
# List all service providers
php artisan route:list | grep filament

# Check registered panels
php artisan about
```

### Verify Composer Autoload

```bash
# Check if classes are mapped
composer dump-autoload -o

# Verify specific class loading
php artisan tinker
new FilaMan\AdminPanelPlugin\AdminPanelPlugin;
```

## Getting Help

### Debug Information to Include

When seeking help, include:

1. **Environment details:**
```bash
php artisan about
composer show | grep filament
```

2. **Error messages:** Full stack traces
3. **Configuration:** Relevant config files
4. **Steps to reproduce:** Exact commands/actions

### Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# View configuration
php artisan config:show

# List all routes
php artisan route:list

# Check queue status
php artisan queue:work --once

# View logs
tail -f storage/logs/laravel.log
```

### Community Resources

- [Filament Discord](https://discord.gg/filamentphp)
- [Filament GitHub Issues](https://github.com/filamentphp/filament/issues)
- [Laravel Discord](https://discord.gg/laravel)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/filament)