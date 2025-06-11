---
title: Plugin Development Guide
slug: plugin-development
description: Learn how to create powerful Filament v4.x plugins using FilaMan's standardized architecture and development tools.
order: 4
published: true
author: FilaMan Team
date: 2025-06-11
tags: plugins, development, filament, architecture, tutorial
---

# Plugin Development Guide

Creating plugins for FilaMan is designed to be straightforward and follows Laravel and Filament conventions. This guide will walk you through creating your first plugin from scratch.

## 🎯 What You'll Learn

- FilaMan plugin architecture principles
- Step-by-step plugin creation process  
- Best practices for plugin development
- Testing and documentation strategies
- Integration with Filament v4.x panels

## 🏗️ Plugin Architecture Overview

Every FilaMan plugin follows a standardized structure that ensures consistency and interoperability:

```
your-plugin/
├── src/
│   ├── YourPlugin.php                    # Main plugin class (implements Plugin)
│   ├── YourPluginServiceProvider.php     # Laravel service provider
│   ├── Http/Controllers/                 # HTTP controllers
│   ├── Models/                           # Eloquent models
│   ├── Filament/                         # Filament resources, pages, widgets
│   └── Events/                           # Domain events
├── resources/
│   ├── views/                            # Blade templates  
│   ├── lang/                             # Language files
│   └── assets/                           # CSS, JS, images
├── routes/
│   ├── web.php                           # Public web routes
│   └── admin.php                         # Admin panel routes (optional)
├── config/
│   └── your-plugin.php                   # Plugin configuration
├── database/
│   ├── migrations/                       # Database migrations
│   └── seeders/                          # Database seeders
├── tests/                                # Test suites
├── docs/                                 # Plugin documentation
├── plan/                                 # Development planning stages
└── composer.json                         # Package definition
```

## 🚀 Creating Your First Plugin

Let's create a simple "Task Manager" plugin to demonstrate the process.

### Step 1: Generate Plugin Structure

Use the FilaMan CLI tool (coming soon) or create manually:

```bash
# Create plugin directory
mkdir -p packages/task-manager-plugin/{src/{Http/Controllers,Models,Filament/Resources},resources/views,routes,config,database/{migrations,seeders},tests,docs,plan}

cd packages/task-manager-plugin
```

### Step 2: Create composer.json

```json
{
    "name": "filaman/task-manager-plugin",
    "description": "A simple task management plugin for FilaMan",
    "type": "laravel-plugin",
    "keywords": ["filament", "laravel", "tasks", "todo", "filaman"],
    "license": "MIT",
    "authors": [
        {
            "name": "Your Name",
            "email": "your.email@example.com"
        }
    ],
    "require": {
        "php": "^8.3",
        "filament/filament": "^4.0",
        "spatie/laravel-package-tools": "^1.15.0"
    },
    "autoload": {
        "psr-4": {
            "FilaMan\\TaskManagerPlugin\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "FilaMan\\TaskManagerPlugin\\TaskManagerPluginServiceProvider"
            ]
        }
    }
}
```

### Step 3: Create the Main Plugin Class

`src/TaskManagerPlugin.php`:

```php
<?php

namespace FilaMan\TaskManagerPlugin;

use Filament\Contracts\Plugin;
use Filament\Panel;

class TaskManagerPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'task-manager';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            Filament\Resources\TaskResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        // Register any runtime functionality
    }
}
```

### Step 4: Create the Service Provider

`src/TaskManagerPluginServiceProvider.php`:

```php
<?php

namespace FilaMan\TaskManagerPlugin;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TaskManagerPluginServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('task-manager-plugin')
            ->hasConfigFile('task-manager')
            ->hasViews('task-manager')
            ->hasRoutes('web')
            ->hasMigrations('create_tasks_table');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(TaskManagerPlugin::class);
    }
}
```

### Step 5: Create the Task Model

`src/Models/Task.php`:

```php
<?php

namespace FilaMan\TaskManagerPlugin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description', 
        'status',
        'priority',
        'due_date',
        'assigned_to',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo  
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
```

### Step 6: Create the Migration

`database/migrations/create_tasks_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};
```

### Step 7: Create the Filament Resource

`src/Filament/Resources/TaskResource.php`:

```php
<?php

namespace FilaMan\TaskManagerPlugin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilaMan\TaskManagerPlugin\Models\Task;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Task Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress', 
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending')
                    ->required(),
                
                Forms\Components\Select::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High', 
                        'urgent' => 'Urgent',
                    ])
                    ->default('medium')
                    ->required(),
                
                Forms\Components\DateTimePicker::make('due_date'),
                
                Forms\Components\Select::make('assigned_to')
                    ->relationship('assignedTo', 'name')
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'in_progress',
                        'success' => 'completed', 
                        'danger' => 'cancelled',
                    ]),
                
                Tables\Columns\BadgeColumn::make('priority')
                    ->colors([
                        'secondary' => 'low',
                        'primary' => 'medium',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ]),
                
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium', 
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
```

## 🧪 Testing Your Plugin

Create comprehensive tests for your plugin:

### Feature Test Example

`tests/Feature/TaskManagementTest.php`:

```php
<?php

namespace FilaMan\TaskManagerPlugin\Tests\Feature;

use FilaMan\TaskManagerPlugin\Models\Task;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_task()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->post('/admin/tasks', [
                'title' => 'Test Task',
                'description' => 'Test Description',
                'status' => 'pending',
                'priority' => 'medium',
                'assigned_to' => $user->id,
                'created_by' => $admin->id,
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'assigned_to' => $user->id,
        ]);
    }

    public function test_task_status_can_be_updated()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $task = Task::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)
            ->patch("/admin/tasks/{$task->id}", [
                'status' => 'completed',
            ]);

        $this->assertEquals('completed', $task->fresh()->status);
    }
}
```

## 📝 Documentation Standards

Every plugin should include comprehensive documentation:

### README Structure

```markdown
# Task Manager Plugin

Brief description of what the plugin does.

## Installation

How to install and configure the plugin.

## Features  

- List of key features
- What problems it solves
- Integration points

## Usage

Examples of how to use the plugin.

## Configuration

Available configuration options.

## API Reference

Public API documentation.

## Contributing

How others can contribute to the plugin.
```

### Code Documentation

Use PHPDoc standards:

```php
/**
 * Create a new task with the given attributes.
 *
 * @param array $attributes Task attributes
 * @return Task The created task instance
 * @throws \InvalidArgumentException If required attributes are missing
 */
public function createTask(array $attributes): Task
{
    // Implementation
}
```

## 🚀 Plugin Integration

### Register in Main Application

Add to `app/Providers/Filament/AdminPanelProvider.php`:

```php
use FilaMan\TaskManagerPlugin\TaskManagerPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->plugin(TaskManagerPlugin::make());
}
```

### Add to Main Composer

In the root `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/task-manager-plugin",
            "options": {"symlink": true}
        }
    ],
    "require": {
        "filaman/task-manager-plugin": "*"
    }
}
```

## 🎯 Best Practices

### 1. Follow Laravel Conventions
- Use standard Laravel directory structure
- Follow PSR-4 autoloading standards
- Use Laravel's validation and authorization

### 2. Filament Integration
- Implement the `Plugin` interface properly
- Use Filament's form and table builders
- Follow Filament v4.x patterns

### 3. Configuration Management
- Provide sensible defaults
- Make everything configurable
- Use environment variables for secrets

### 4. Error Handling
- Provide meaningful error messages
- Log appropriately
- Handle edge cases gracefully

### 5. Performance
- Use database indexes appropriately
- Implement caching where beneficial
- Optimize queries and avoid N+1 problems

### 6. Security
- Validate all inputs
- Use proper authorization
- Sanitize outputs

## 🛠️ Advanced Topics

### Custom Form Components

Create reusable form components:

```php
class StatusBadge extends Component
{
    protected string $view = 'task-manager::components.status-badge';
    
    public function getState(): string
    {
        return $this->evaluate($this->status);
    }
}
```

### Plugin Events

Dispatch events for integration:

```php
class TaskCreated
{
    public function __construct(
        public Task $task
    ) {}
}

// In your service
event(new TaskCreated($task));
```

### Custom Middleware

Add plugin-specific middleware:

```php
class TaskMiddleware
{
    public function handle($request, Closure $next)
    {
        // Plugin-specific logic
        return $next($request);
    }
}
```

## 📞 Getting Help

- **Plugin Development Discord**: Join our community
- **Code Reviews**: Submit PRs for feedback
- **Mentorship**: Connect with experienced developers
- **Documentation**: Check our comprehensive guides

---

**Ready to build your first plugin?** Start with our [Plugin Template](https://github.com/markc/filaman-plugin-template) for a quick start! 🎉