# FilaMan Admin Panel Plugin

[![Tests](https://github.com/markc/filaman/actions/workflows/ci.yml/badge.svg)](https://github.com/markc/filaman/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

The **Admin Panel Plugin** is the core management interface for FilaMan. It provides a complete plugin management system with a beautiful Filament v4.x admin interface for installing, configuring, and managing plugins.

## ✨ Features

### 🎛️ Complete Admin Interface
- **Filament v4.x Panel** with modern, responsive design
- **Plugin Management** interface for installing/uninstalling plugins
- **Configuration Management** for each plugin
- **User Management** with role-based access control

### 🔌 Plugin Management System
- **Automatic Plugin Discovery** from plugins directory
- **One-Click Installation** and removal of plugins
- **Enable/Disable Plugins** without uninstalling
- **Plugin Dependencies** tracking and management

### ⚙️ Advanced Configuration
- **Per-Plugin Settings** with custom configuration forms
- **Global Settings** for the admin panel itself
- **Environment-Aware** configuration (development/production)
- **Database-Driven** plugin registry

### 🔒 Security & Access Control
- **Role-Based Access** to admin features
- **Local Auto-Login** for development environments
- **Two-Factor Authentication** support
- **Audit Logging** for plugin changes

## 📦 Installation

### Automatic Installation (Recommended)

The Admin Panel Plugin is included with FilaMan by default but can be disabled for minimal installations.

### Manual Installation

For custom setups or to reinstall:

```bash
# Add the plugin repository
composer config repositories.admin-panel-plugin path plugins/admin-panel-plugin

# Install the plugin
composer require filaman/admin-panel-plugin:*

# Run migrations
php artisan migrate

# Publish configuration (optional)
php artisan vendor:publish --tag=filaman-admin-config
```

## 🚀 Quick Start

### 1. Access the Admin Panel

Navigate to `/admin` in your browser (configurable via `FILAMAN_ADMIN_PATH`).

### 2. First-Time Setup

On first access:
- Create an admin user: `php artisan make:filament-user`
- Or use auto-login in local development

### 3. Discover Plugins

Click "Discover Plugins" to scan for available plugins in the plugins directory.

### 4. Install Plugins

Select plugins from the available list and click "Install" to add them to your FilaMan installation.

## 📖 Usage Guide

### Managing Plugins

#### Installing a Plugin

1. Navigate to **Plugins** in the admin panel
2. Click **Install Plugin**
3. Select the plugin from the dropdown
4. Configure installation options
5. Click **Install**

#### Configuring a Plugin

1. Navigate to **Plugins**
2. Click the **Settings** icon for the plugin
3. Update configuration values
4. Click **Save**

#### Enabling/Disabling Plugins

- Use the toggle switch in the plugin list
- Or click the Enable/Disable action button
- Disabled plugins remain installed but inactive

### Admin Panel Configuration

Configure the admin panel via `config/filaman-admin.php`:

```php
return [
    // Enable/disable the entire admin panel
    'enabled' => env('FILAMAN_ADMIN_ENABLED', true),
    
    // Admin panel URL path
    'path' => env('FILAMAN_ADMIN_PATH', 'admin'),
    
    // Brand name
    'brand_name' => env('FILAMAN_ADMIN_BRAND', 'FilaMan Admin'),
    
    // Auto-enabled plugins
    'plugins' => [
        'pages',
        // Add more plugin IDs here
    ],
    
    // Plugin discovery settings
    'discovery' => [
        'enabled' => true,
        'paths' => [base_path('plugins')],
        'pattern' => '*-plugin',
    ],
];
```

### Environment Variables

```env
# Enable/disable admin panel
FILAMAN_ADMIN_ENABLED=true

# Admin panel URL path
FILAMAN_ADMIN_PATH=admin

# Admin panel brand name
FILAMAN_ADMIN_BRAND="My FilaMan Admin"

# Development mode
FILAMAN_DEV_MODE=false
```

## 🏗️ Architecture

### Plugin Manager Service

The `PluginManager` service handles all plugin operations:

```php
use FilaMan\AdminPanelPlugin\Services\PluginManager;

$pluginManager = app(PluginManager::class);

// Get available plugins
$available = $pluginManager->getAvailablePlugins();

// Install a plugin
$pluginManager->installPlugin('my-plugin');

// Enable/disable
$pluginManager->enablePlugin('my-plugin');
$pluginManager->disablePlugin('my-plugin');
```

### Plugin Model

The `Plugin` model tracks installed plugins:

```php
use FilaMan\AdminPanelPlugin\Models\Plugin;

// Get all enabled plugins
$enabled = Plugin::enabled()->get();

// Get plugin configuration
$plugin = Plugin::where('name', 'my-plugin')->first();
$config = $plugin->getConfig('key', 'default');

// Update plugin settings
$plugin->setConfig('key', 'value');
```

### Database Schema

The `plugins` table stores plugin information:

```sql
- id
- name (unique)
- display_name
- description  
- version
- enabled
- settings (JSON)
- metadata (JSON)
- author
- url
- created_at
- updated_at
```

## 🔧 Advanced Usage

### Creating Custom Plugin Resources

Add custom resources to the admin panel:

```php
namespace MyPlugin\Filament\Resources;

use Filament\Resources\Resource;

class MyResource extends Resource
{
    // Resource implementation
}

// Register in your plugin
public function register(Panel $panel): void
{
    $panel->resources([
        MyResource::class,
    ]);
}
```

### Custom Admin Pages

Add custom pages to the admin panel:

```php
namespace MyPlugin\Filament\Pages;

use Filament\Pages\Page;

class MyCustomPage extends Page
{
    // Page implementation
}
```

### Plugin Dependencies

Declare dependencies in your plugin's `composer.json`:

```json
{
    "require": {
        "filaman/admin-panel-plugin": "^1.0",
        "filaman/another-plugin": "^1.0"
    }
}
```

### Hooks and Events

The admin panel fires events during plugin lifecycle:

```php
// Listen for plugin events
Event::listen('filaman.plugin.installed', function ($plugin) {
    // Handle plugin installation
});

Event::listen('filaman.plugin.enabled', function ($plugin) {
    // Handle plugin enabling
});
```

## 🧪 Testing

Run the admin panel plugin tests:

```bash
cd plugins/admin-panel-plugin
composer test
```

### Test Coverage

- Unit tests for PluginManager service
- Feature tests for plugin operations
- Integration tests for Filament resources
- Browser tests for UI interactions

## 🚫 Disabling the Admin Panel

For minimal installations without admin interface:

### 1. Environment Configuration

```env
FILAMAN_ADMIN_ENABLED=false
```

### 2. Remove from Composer (Optional)

```bash
composer remove filaman/admin-panel-plugin
```

### 3. Direct Plugin Management

Without the admin panel, manage plugins via:
- Composer commands
- Manual configuration files
- Artisan commands (if provided by plugins)

## 🤝 Contributing

### Development Setup

1. Fork and clone the repository
2. Install dependencies: `composer install`
3. Create feature branch: `git start your-feature`
4. Make changes and test
5. Submit PR: `git finish "Your feature description"`

### Plugin Standards

When creating plugins for the admin panel:
- Implement the `Plugin` interface
- Provide configuration schema
- Include proper metadata
- Write tests for admin features

## 📄 License

This plugin is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- **Laravel Team** for the framework
- **Filament Team** for the admin panel
- **Spatie** for Laravel packages
- **FilaMan Community** for feedback and contributions

## 📞 Support

- **Documentation**: [FilaMan Admin Documentation](https://filaman.dev/docs/admin-panel)
- **Issues**: [GitHub Issues](https://github.com/markc/filaman/issues)
- **Discord**: [FilaMan Community](https://discord.gg/filaman)

---

Built with ❤️ for the FilaMan ecosystem