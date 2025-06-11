---
title: Creating Plugins Guide
description: Step-by-step guide for creating new Filament 4 plugins
date: 2025-11-06
---

# Creating Plugins Guide

This guide explains how to create new plugins for your Filament 4 application using the established patterns.

## Quick Start

```bash
# Navigate to project root
cd /path/to/filaman

# Create new plugin using hydro
hydro new MyAwesomePlugin

# Move to plugins directory
mv my-awesome-plugin plugins/

# Update dependencies and register
composer update
```

## Detailed Plugin Creation Process

### Step 1: Generate Plugin Scaffold

Use `awcodes/hydro` to create the initial structure:

```bash
hydro new BlogPlugin
```

When prompted, provide:
- Author name
- Author email
- Package description
- Namespace (will be auto-generated)

### Step 2: Move to Plugins Directory

```bash
mv blog-plugin plugins/
```

### Step 3: Update Plugin Configuration

#### Fix Namespace (if needed)

Ensure PHP-compatible namespace in `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "FilaMan\\BlogPlugin\\": "src/"
        }
    }
}
```

#### Update Filament Version

Change Filament dependencies to v4.0:

```json
{
    "require": {
        "filament/filament": "^4.0",
        "filament/forms": "^4.0",
        "filament/tables": "^4.0"
    }
}
```

### Step 4: Register Plugin in Main Project

Add to main `composer.json`:

```json
{
    "require": {
        "filaman/blog-plugin": "@dev"
    },
    "repositories": [
        {
            "type": "path",
            "url": "plugins/blog-plugin"
        }
    ]
}
```

### Step 5: Implement Plugin Class

```php
<?php

namespace FilaMan\BlogPlugin;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Colors\Color;

class BlogPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }
    
    public function getId(): string
    {
        return 'blog-plugin';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverResources(
                in: __DIR__ . '/Filament/Resources',
                for: 'FilaMan\\BlogPlugin\\Filament\\Resources'
            )
            ->discoverPages(
                in: __DIR__ . '/Filament/Pages',
                for: 'FilaMan\\BlogPlugin\\Filament\\Pages'
            )
            ->discoverWidgets(
                in: __DIR__ . '/Filament/Widgets',
                for: 'FilaMan\\BlogPlugin\\Filament\\Widgets'
            )
            ->navigationGroups([
                'Blog' => [
                    'icon' => 'heroicon-o-document-text',
                ],
            ]);
    }

    public function boot(Panel $panel): void
    {
        // Boot logic here
    }
}
```

### Step 6: Create Plugin Structure

```bash
cd plugins/blog-plugin/src
mkdir -p Filament/{Resources,Pages,Widgets}
mkdir -p Models
mkdir -p Http/Controllers
```

### Step 7: Register Plugin in Panel

Update `app/Providers/Filament/AdminPanelProvider.php`:

```php
use FilaMan\BlogPlugin\BlogPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... existing configuration
        ->plugin(BlogPlugin::make())
        // ... rest of configuration
}
```

### Step 8: Run Composer Update

```bash
composer update
```

## Creating Plugin Components

### Resources

Create a Filament resource in your plugin:

```php
<?php

namespace FilaMan\BlogPlugin\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use FilaMan\BlogPlugin\Models\Post;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Blog';
    
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('content')
                    ->required(),
                Forms\Components\Toggle::make('is_published')
                    ->default(false),
            ]);
    }
    
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('published')
                    ->query(fn ($query) => $query->where('is_published', true)),
            ]);
    }
}
```

### Models

Create models within your plugin:

```php
<?php

namespace FilaMan\BlogPlugin\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'is_published',
    ];
    
    protected $casts = [
        'is_published' => 'boolean',
    ];
}
```

### Migrations

Create migrations in `plugins/blog-plugin/database/migrations/`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
```

### Service Provider

Update the plugin's service provider to load migrations:

```php
<?php

namespace FilaMan\BlogPlugin;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BlogPluginServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('blog-plugin')
            ->hasMigrations(['create_blog_posts_table'])
            ->hasViews();
    }
}
```

## Plugin Development Workflow

### 1. Development Commands

```bash
# From plugin directory
cd plugins/blog-plugin

# Run tests
vendor/bin/pest

# Format code
vendor/bin/pint

# Analyze code
vendor/bin/phpstan analyse
```

### 2. Testing Your Plugin

Create tests in `plugins/blog-plugin/tests/`:

```php
<?php

use FilaMan\BlogPlugin\Models\Post;

it('can create a blog post', function () {
    $post = Post::create([
        'title' => 'Test Post',
        'content' => 'Test content',
        'is_published' => true,
    ]);
    
    expect($post)->toBeInstanceOf(Post::class);
    expect($post->title)->toBe('Test Post');
});
```

### 3. Publishing Plugin Assets

If your plugin has config files or views to publish:

```php
// In service provider
public function configurePackage(Package $package): void
{
    $package
        ->name('blog-plugin')
        ->hasConfigFile()
        ->hasViews()
        ->hasAssets()
        ->publishesServiceProvider('BlogPluginServiceProvider');
}
```

## Best Practices

### 1. Naming Conventions

- Plugin class: `BlogPlugin`
- Service provider: `BlogPluginServiceProvider`
- Resources: `PostResource`, `CategoryResource`
- Models: `Post`, `Category`
- Tables: `blog_posts`, `blog_categories` (prefixed)

### 2. Dependencies

- Declare all dependencies explicitly
- Use version constraints appropriately
- Avoid unnecessary dependencies

### 3. Configuration

Create a config file at `config/blog-plugin.php`:

```php
<?php

return [
    'posts_per_page' => 10,
    'enable_comments' => true,
    'moderation_required' => false,
];
```

### 4. Localization

Add translations in `resources/lang/en/blog.php`:

```php
<?php

return [
    'post' => [
        'singular' => 'Post',
        'plural' => 'Posts',
    ],
    'actions' => [
        'publish' => 'Publish',
        'unpublish' => 'Unpublish',
    ],
];
```

### 5. Documentation

Always include a README.md in your plugin:

```markdown
# Blog Plugin for Filament

Description of your plugin...

## Installation

```bash
composer require filaman/blog-plugin
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="blog-plugin-config"
```

## Usage

Register the plugin in your panel provider...
```

## Common Patterns

### Adding Navigation Items

```php
public function register(Panel $panel): void
{
    $panel->navigationItems([
        NavigationItem::make('Blog Settings')
            ->url('/admin/blog-settings')
            ->icon('heroicon-o-cog-6-tooth')
            ->group('Blog')
            ->sort(99),
    ]);
}
```

### Custom Permissions

```php
public function boot(Panel $panel): void
{
    Gate::define('manage-blog', function ($user) {
        return $user->hasRole('editor');
    });
}
```

### Widget Registration

```php
public function register(Panel $panel): void
{
    $panel->widgets([
        BlogStatsWidget::class,
        RecentPostsWidget::class,
    ]);
}
```