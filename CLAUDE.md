# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## CRITICAL: Filament 4 Beta Documentation

**IMPORTANT**: This project uses Filament 4 beta. You MUST only reference documentation from:
https://filamentphp.com/docs/4.x/getting-started

DO NOT use information from:
- Filament 3.x documentation
- Random blog posts or tutorials that may refer to older versions
- Stack Overflow answers unless specifically marked for Filament 4.x
- Any documentation that doesn't explicitly state it's for Filament 4.x

The Filament 4 beta has significant architectural changes from v3, and using outdated information will lead to errors.

## Development Memories

- Always use the latest filament v4 techniques and features
- http://localhost:8000 is already running
- don't ask to use curl when viewing http://localhost:8000 pages

## Development Commands

### Backend Development
```bash
# Install PHP dependencies
composer install

# Start full development environment (recommended)
composer dev  # Runs PHP server, queue worker, log viewer, and Vite

# Run tests
php artisan test
# or
composer test

# Run code formatter
./vendor/bin/pint

# Database operations
php artisan migrate        # Run migrations
php artisan migrate:fresh  # Drop all tables and re-run migrations
php artisan db:seed        # Seed database

# Interactive Laravel REPL
php artisan tinker
```

### Frontend Development
```bash
# Install Node dependencies
npm install

# Start Vite dev server (if not using composer dev)
npm run dev

# Build production assets
npm run build
```

### Filament-Specific Commands
```bash
# Install Filament 4 beta panel builder
php artisan filament:install --panels

# Create a new user for Filament panel
php artisan make:filament-user

# Generate Filament resources
php artisan make:filament-resource ResourceName

# Generate Filament pages
php artisan make:filament-page PageName

# Generate Filament widgets
php artisan make:filament-widget WidgetName

# Clear Filament caches
php artisan filament:cache-components
php artisan filament:clear-cached-components
```

## Architecture Overview

This is a Laravel 12 application with Filament 4 beta and a **fully plugin-based architecture**:
- **Backend**: Laravel 12 with PHP 8.3+, SQLite database, Eloquent ORM
- **Frontend**: Vite build tool, Tailwind CSS v4, Blade templating
- **Admin Panel**: Provided by the Admin Panel Plugin (not in core)
- **Testing**: Pest PHP with SQLite in-memory database

### FilaMan Plugin Architecture:
1. **Core Application**: Minimal Laravel installation without admin panel
2. **Admin Panel Plugin**: Provides complete Filament admin interface and plugin management
3. **Pages Plugin**: Example content management plugin demonstrating best practices
4. **Plugin-Based Everything**: All features are implemented as plugins, not in core

### Filament 4 Beta Key Concepts:
1. **Unified Schema Core**: Filament v4 uses a new `Schema` package that provides consistent UI components across forms, tables, and widgets
2. **Plugin-Based Architecture**: Each feature should be encapsulated in its own plugin using the `Filament\Contracts\Plugin` interface
3. **Panel Builder vs Components**: Admin panel plugin provides the full panel builder
4. **Resource Organization**: Resources are organized within each plugin's namespace

### Key Architectural Patterns:
- **Minimal Core**: The core application contains only essential Laravel files
- **Plugin Discovery**: Plugins are auto-discovered from the plugins/ directory
- **Optional Admin Panel**: The admin panel itself is a plugin and can be disabled
- **Service Provider Pattern**: Each plugin has its own service provider
- **Modular Features**: All functionality is added via plugins, not core modifications

### Plugin Naming Convention

All plugins MUST follow this strict naming convention:

- **Directory name**: `plugins/{name}` (all lowercase, no suffixes)
- **Package name**: `filaman/{name}` (all lowercase, no suffixes)  
- **Namespace**: `FilaMan\{Name}` (PascalCase, no suffixes)
- **Main class**: `{Name}Plugin` (PascalCase with Plugin suffix)
- **Service provider**: `{Name}ServiceProvider` (PascalCase)

**Examples:**
- Admin plugin: `plugins/admin/`, `filaman/admin`, `FilaMan\Admin`, `AdminPlugin`, `AdminServiceProvider`
- Pages plugin: `plugins/pages/`, `filaman/pages`, `FilaMan\Pages`, `PagesPlugin`, `PagesServiceProvider`  
- Blog plugin: `plugins/blog/`, `filaman/blog`, `FilaMan\Blog`, `BlogPlugin`, `BlogServiceProvider`

This convention eliminates redundancy since plugins are already in the `plugins/` directory.

## Plugin System Usage

### Installation Modes

FilaMan supports three installation modes:

1. **Bare Bones Mode**: Core Laravel only, no admin panel
   ```bash
   # Remove admin panel plugin from composer.json
   composer remove filaman/admin-panel-plugin
   ```

2. **Single Plugin Mode**: Core + one specific plugin
   ```bash
   # Install only the plugin you need
   composer require filaman/pages-plugin
   ```

3. **Full Admin Mode**: Core + Admin Panel + managed plugins
   ```bash
   # Default installation includes admin panel
   composer install
   ```

### Managing Plugins

With Admin Panel:
- Navigate to `/admin/plugins`
- Click "Discover Plugins" to find available plugins
- Install/uninstall/enable/disable via UI

Without Admin Panel:
```bash
# Add plugin repository to composer.json
composer config repositories.my-plugin path packages/my-plugin

# Install plugin
composer require filaman/my-plugin:*

# Run plugin migrations
php artisan migrate --path=packages/my-plugin/database/migrations
```

### Core Application Structure

The core application is minimal:
- `app/` - Only essential Laravel files (User model, base controllers)
- `packages/` - All plugins live here
- `bootstrap/providers.php` - Admin panel provider is commented out by default
- No Filament resources or pages in core

## Filament 4 Plugin Development

### Using awcodes/hydro for Plugin Scaffolding

The `awcodes/hydro` package is the recommended tool for creating Filament plugins:

```bash
# Install hydro globally
composer global require awcodes/hydro

# Create a new Filament plugin
hydro new:plugin YourPluginName

# Navigate to the plugin directory
cd your-plugin-name

# Install dependencies
composer install
```

### Plugin Structure
Each plugin should follow this structure:
```
your-plugin-name/
├── src/
│   ├── YourPluginNamePlugin.php    # Main plugin class
│   ├── YourPluginNameServiceProvider.php
│   ├── Resources/                   # Filament resources
│   ├── Pages/                       # Custom pages
│   └── Widgets/                     # Dashboard widgets
├── resources/
│   └── views/                       # Blade views
├── composer.json
└── README.md
```

### Registering Plugins
Register plugins in your Panel Provider (e.g., `app/Providers/Filament/AdminPanelProvider.php`):

```php
use YourVendor\YourPlugin\YourPluginNamePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->plugin(YourPluginNamePlugin::make())
        // ... other configuration
}
```

## Testing

Tests use Pest PHP framework:
- Unit tests: `tests/Unit/`
- Feature tests: `tests/Feature/`
- Run single test: `php artisan test --filter TestName`
- Run specific file: `php artisan test tests/Feature/ExampleTest.php`

For Filament-specific testing:
- Use Filament's testing helpers for resources and pages
- Test database should include Filament tables (run migrations in tests)

## Important Configuration

- **Composer**: `minimum-stability` set to "beta" for Filament 4
- Database: SQLite file at `database/database.sqlite`
- Environment: `.env` file (create from `.env.example` if missing)
- Frontend assets compiled to `public/build/`
- Logs stored in `storage/logs/`
- Cache and sessions in `storage/framework/`
- Filament panels configured in `app/Providers/Filament/`
- Filament resources in `app/Filament/Resources/`

## Git Workflow Requirements

**CRITICAL**: All merges to the main branch MUST use the git workflow aliases:

### Required Workflow
1. **Start new feature**: `git start [branch-name]` (creates and switches to feature branch)
2. **Finish feature**: `git finish [commit-msg]` (commits, creates PR, merges, and cleans up)

### Forbidden Actions
- **NO direct pushes to main branch**
- **NO manual merging without PR**
- **ALL changes must go through feature branches**

### Git Aliases Available
```bash
git start [branch-name]    # Start new feature branch
git finish [msg]           # Auto-commit, PR, and merge
git check                  # Check repository status
git cleanup                # Clean up old branches (weekly)
```

### Branch Protection
- Main branch should be protected on GitHub
- Require PR reviews before merging
- Delete head branches automatically after merge
- Run CI/CD checks before allowing merge

## Development Workflow

1. **For new features**: Create a plugin using `hydro new:plugin`
2. **For resources**: Use `php artisan make:filament-resource` within the plugin
3. **For UI consistency**: Always use Filament's Schema components
4. **For testing**: Write tests for both Laravel and Filament components
5. **For deployment**: Ensure all plugins are properly registered in panel providers
```