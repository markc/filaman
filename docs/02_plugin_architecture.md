---
title: Plugin-Based Architecture
description: Understanding the modular plugin architecture in Filament 4
date: 2025-11-06
---

# Plugin-Based Architecture

## Overview

This project follows a strict plugin-based architecture where:
- The core Laravel application remains minimal
- All features are implemented as separate plugins
- Each plugin is a self-contained Laravel package
- Plugins can be developed, tested, and deployed independently

## Architecture Principles

### 1. Core Application Responsibilities

The core application only handles:
- Framework bootstrapping
- Plugin registration
- Shared infrastructure (database, cache, queues)
- Environment configuration
- Asset compilation pipeline

### 2. Plugin Responsibilities

Each plugin is responsible for:
- Its own resources, pages, and widgets
- Database migrations and models
- Service providers and configurations
- Tests and documentation
- Dependencies management

## Filament 4 Plugin System

### Key Concepts

1. **Unified Schema Core**: Filament 4 introduces a Schema package providing consistent UI components
2. **Plugin Interface**: All plugins implement `Filament\Contracts\Plugin`
3. **Panel Registration**: Plugins modify panel configuration during registration
4. **Resource Discovery**: Automatic discovery of resources within plugin namespaces

### Plugin Lifecycle

```
1. Plugin Creation (hydro)
   ↓
2. Plugin Development
   ↓
3. Plugin Registration (composer)
   ↓
4. Panel Configuration (register method)
   ↓
5. Runtime Execution (boot method)
```

## Plugin Structure

```
packages/
└── admin-panel-plugin/
    ├── composer.json           # Package dependencies
    ├── src/
    │   ├── AdminPanelPlugin.php       # Main plugin class
    │   ├── AdminPanelPluginServiceProvider.php
    │   └── Filament/
    │       ├── Resources/      # Filament resources
    │       │   └── UserResource.php
    │       ├── Pages/          # Custom pages
    │       │   └── Dashboard.php
    │       └── Widgets/        # Dashboard widgets
    │           └── StatsWidget.php
    ├── resources/
    │   └── views/              # Blade templates
    ├── database/
    │   └── migrations/         # Plugin-specific migrations
    └── tests/                  # Plugin tests
```

## Creating a Plugin

### Basic Plugin Template

```php
<?php

namespace FilaMan\PluginName;

use Filament\Contracts\Plugin;
use Filament\Panel;

class MyPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }
    
    public function getId(): string
    {
        return 'my-plugin';
    }

    public function register(Panel $panel): void
    {
        // Configure panel settings
        // Register resources, pages, widgets
        // Add navigation items
        // Configure permissions
    }

    public function boot(Panel $panel): void
    {
        // Runtime initialization
        // Event listeners
        // View composers
    }
}
```

## Plugin Communication

### Service Container

Plugins can communicate through Laravel's service container:

```php
// In PluginA
app()->bind('plugin-a.service', function () {
    return new MyService();
});

// In PluginB
$service = app('plugin-a.service');
```

### Events

Use Laravel events for decoupled communication:

```php
// PluginA fires event
event(new UserCreated($user));

// PluginB listens
Event::listen(UserCreated::class, function ($event) {
    // React to user creation
});
```

### Contracts

Define interfaces for plugin interoperability:

```php
// Shared contract
interface DataProviderInterface {
    public function getData(): array;
}

// Plugin implementation
class MyPlugin implements Plugin, DataProviderInterface {
    public function getData(): array {
        return ['key' => 'value'];
    }
}
```

## Best Practices

### 1. Namespace Organization

- Use vendor namespaces: `FilaMan\PluginName`
- Follow PSR-4 autoloading standards
- Keep consistent naming conventions

### 2. Dependency Management

- Declare all dependencies in plugin's composer.json
- Use semantic versioning
- Avoid tight coupling between plugins

### 3. Configuration

- Use Laravel's config system
- Publish configuration files
- Provide sensible defaults

### 4. Database

- Prefix tables with plugin identifier
- Use migrations for schema changes
- Avoid modifying core tables

### 5. Testing

- Write tests within plugin directory
- Use Orchestra Testbench for package testing
- Mock external dependencies

## Plugin Types

### 1. Feature Plugins

Complete features like:
- Blog system
- E-commerce module
- CRM functionality

### 2. Integration Plugins

External service integrations:
- Payment gateways
- Email services
- Analytics tools

### 3. Theme Plugins

UI customizations:
- Custom themes
- Additional components
- Layout modifications

### 4. Utility Plugins

Helper functionality:
- Audit logging
- Backup systems
- Performance monitoring

## Security Considerations

1. **Isolation**: Plugins should not access each other's internals directly
2. **Permissions**: Use Filament's authorization system
3. **Validation**: Validate all inputs within plugins
4. **Updates**: Keep plugins updated independently
5. **Auditing**: Log plugin activities

## Performance Optimization

1. **Lazy Loading**: Only load plugin assets when needed
2. **Caching**: Cache plugin configurations
3. **Service Providers**: Defer loading when possible
4. **Asset Optimization**: Minimize and bundle plugin assets
5. **Database Queries**: Optimize within plugin boundaries