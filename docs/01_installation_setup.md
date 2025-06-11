---
title: Installation and Setup Guide
description: Step-by-step guide for setting up a Filament 4 beta project with plugin architecture
date: 2025-11-06
---

# Installation and Setup Guide

This guide walks through setting up a fresh Laravel 12 project with Filament 4 beta using a plugin-based architecture.

## Prerequisites

- PHP 8.3 or higher
- Composer 2.x
- Node.js 18+ and NPM
- SQLite (or your preferred database)

## Step 1: Create Laravel Project

```bash
# Create new Laravel project
composer create-project laravel/laravel filaman
cd filaman

# Set minimum stability for beta packages
# Add to composer.json:
"minimum-stability": "beta"
```

## Step 2: Install Filament 4 Beta

```bash
# Install Filament 4 beta
composer require filament/filament:"^4.0"

# Run Filament installer for panels
php artisan filament:install --panels
```

This creates:
- `app/Providers/Filament/AdminPanelProvider.php`
- Registers the provider in `bootstrap/providers.php`
- Publishes Filament assets

## Step 3: Install Plugin Scaffolding Tool

```bash
# Install hydro globally
composer global require awcodes/hydro --with-all-dependencies

# Ensure global composer bin is in PATH
export PATH="$HOME/.config/composer/vendor/bin:$PATH"
```

## Step 4: Create Plugin Structure

```bash
# Create packages directory
mkdir packages

# Create your first plugin
hydro new AdminPanelPlugin

# Move to packages directory
mv admin-panel-plugin packages/
```

## Step 5: Configure Plugin

### Update Plugin's composer.json

1. Change Filament version to ^4.0
2. Fix namespace (remove hyphens if present)

```json
{
    "require": {
        "filament/filament": "^4.0",
        "filament/forms": "^4.0",
        "filament/tables": "^4.0"
    },
    "autoload": {
        "psr-4": {
            "FilaMan\\AdminPanelPlugin\\": "src/"
        }
    }
}
```

### Update Main composer.json

Add local repository and require the plugin:

```json
{
    "require": {
        "filaman/admin-panel-plugin": "@dev"
    },
    "repositories": [
        {
            "type": "path",
            "url": "packages/admin-panel-plugin"
        }
    ]
}
```

## Step 6: Implement Plugin

### Create Plugin Class

```php
<?php

namespace FilaMan\AdminPanelPlugin;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Colors\Color;

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

    public function register(Panel $panel): void
    {
        $panel
            ->brandName('FilaMan Admin Panel')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: __DIR__ . '/Filament/Resources', for: 'FilaMan\\AdminPanelPlugin\\Filament\\Resources')
            ->discoverPages(in: __DIR__ . '/Filament/Pages', for: 'FilaMan\\AdminPanelPlugin\\Filament\\Pages')
            ->discoverWidgets(in: __DIR__ . '/Filament/Widgets', for: 'FilaMan\\AdminPanelPlugin\\Filament\\Widgets');
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
```

### Create Directory Structure

```bash
mkdir -p packages/admin-panel-plugin/src/Filament/{Resources,Pages,Widgets}
```

## Step 7: Register Plugin

Update `app/Providers/Filament/AdminPanelProvider.php`:

```php
use FilaMan\AdminPanelPlugin\AdminPanelPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()
        ->plugin(AdminPanelPlugin::make())
        ->middleware([
            // ... middleware configuration
        ]);
}
```

## Step 8: Install Dependencies

```bash
# Update composer autoload
composer update

# Install NPM dependencies
npm install

# Build assets
npm run build
```

## Step 9: Database Setup

```bash
# Run migrations
php artisan migrate

# Create admin user
php artisan make:filament-user
```

## Step 10: Run Application

```bash
# Start development server
php artisan serve

# Or use the full dev environment
composer dev
```

Visit `http://localhost:8000/admin` to access the Filament admin panel.

## Troubleshooting

### Common Issues

1. **Namespace errors**: Ensure PHP-compatible namespaces (no hyphens)
2. **Autoload issues**: Run `composer dump-autoload` after changes
3. **Asset errors**: Remove theme references if not using custom themes
4. **Type errors**: Check property types match Filament 4 requirements

### Verifying Installation

Check that:
- AdminPanelProvider is registered in `bootstrap/providers.php`
- Plugin appears in `composer show`
- No errors in `php artisan about`