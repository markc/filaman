---
title: GitHub Flavored Markdown Test
slug: gfm-test
description: A comprehensive test page showcasing all GitHub Flavored Markdown features supported by FilaMan's markdown rendering system.
category: Documentation
order: 1
published: true
author: FilaMan Team
date: 2025-06-12
tags: markdown, gfm, test, documentation
seo_title: GFM Test - Markdown Rendering Showcase
seo_description: Test page demonstrating GitHub Flavored Markdown features including tables, code blocks, task lists, and more in FilaMan.
---

# GitHub Flavored Markdown Test

This page demonstrates all the GitHub Flavored Markdown features supported by FilaMan's rendering system.

## 📝 Text Formatting

### Basic Formatting
**Bold text** and *italic text* and ***bold italic***

~~Strikethrough text~~

`Inline code` with backticks

### Headings
# H1 Heading
## H2 Heading  
### H3 Heading
#### H4 Heading
##### H5 Heading
###### H6 Heading

## 🔗 Links and References

### Links
[External link to Filament](https://filamentphp.com)
[Internal link to home](/pages/home)
[Link with title](https://laravel.com "Laravel Framework")

### Auto-linking
https://github.com/markc/filaman

### Reference Links
[Reference link][1] and [another reference][ref2]

[1]: https://filamentphp.com "Filament PHP"
[ref2]: https://laravel.com "Laravel"

## 📋 Lists

### Unordered Lists
- Item 1
- Item 2
  - Nested item 2.1
  - Nested item 2.2
    - Deep nested item
- Item 3

### Ordered Lists
1. First item
2. Second item
   1. Nested numbered item
   2. Another nested item
3. Third item

### Task Lists
- [x] Completed task
- [x] Another completed task
- [ ] Incomplete task
- [ ] Another incomplete task
  - [x] Nested completed task
  - [ ] Nested incomplete task

## 📊 Tables

### Basic Table
| Feature | Status | Priority |
|---------|--------|----------|
| Plugin System | ✅ Complete | High |
| Admin Panel | ✅ Complete | High |
| Public Pages | ✅ Complete | Medium |
| API Endpoints | 🔄 In Progress | Low |

### Table with Alignment
| Left Aligned | Center Aligned | Right Aligned |
|:-------------|:--------------:|--------------:|
| Text | Text | Text |
| More text | More text | More text |
| Even more | Even more | Even more |

## 💻 Code Examples

### Inline Code
Use `php artisan migrate` to run migrations.

### Code Blocks

#### PHP Code
```php
<?php

namespace FilaMan\Pages;

use Filament\Contracts\Plugin;
use Filament\Panel;

class PagesPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'pages';
    }

    public function register(Panel $panel): void
    {
        // Register plugin resources
    }
}
```

#### JavaScript Code
```javascript
// Filament theme configuration
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#eff6ff',
                    500: '#3b82f6',
                    600: '#2563eb',
                }
            }
        }
    }
}
```

#### JSON Configuration
```json
{
  "name": "filaman/pages",
  "description": "A Filament v4.x plugin for managing static pages",
  "type": "laravel-plugin",
  "require": {
    "php": "^8.3",
    "filament/filament": "^4.0",
    "spatie/laravel-package-tools": "^1.15.0"
  }
}
```

#### Bash Commands
```bash
# Install FilaMan
composer create-project filaman/filaman my-app
cd my-app

# Run migrations
php artisan migrate

# Start development server
php artisan serve
```

#### SQL Query
```sql
-- Create pages table
CREATE TABLE pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT,
    published BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## 🖼️ Media and Embeds

### Images
![FilaMan Logo](https://via.placeholder.com/400x200/3B82F6/FFFFFF?text=FilaMan)

### Image with Link
[![Filament Logo](https://via.placeholder.com/200x100/EF4444/FFFFFF?text=Filament)](https://filamentphp.com)

## 📖 Blockquotes

> This is a blockquote demonstrating how quoted text appears in FilaMan.
> 
> It can span multiple paragraphs and include other formatting like **bold** and *italic*.

### Nested Blockquotes
> This is the first level of quoting.
>
> > This is nested blockquote.
> >
> > > And this is a third level.

## 🎨 Advanced Features

### Horizontal Rules
---

### Line Breaks
First line with two trailing spaces  
Second line

Hard break with empty line

Third line

### Escape Characters
\*This text is not italic\*

\`This is not code\`

### HTML in Markdown
<details>
<summary>🔍 Basic Collapsible Section - Click to expand and see hidden content</summary>

This content is hidden by default but can be expanded.

**Markdown works here too!**

- List item 1
- List item 2

</details>

<details>
<summary>⚙️ Advanced Configuration Options - Database, Cache, and Performance Settings</summary>

This section contains advanced configuration details that most users won't need to modify.

#### Database Configuration
```php
'connections' => [
    'sqlite' => [
        'driver' => 'sqlite',
        'database' => database_path('database.sqlite'),
        'prefix' => '',
        'foreign_key_constraints' => true,
    ],
],
```

#### Cache Settings
- **File cache**: Default for development
- **Redis cache**: Recommended for production
- **Database cache**: Fallback option

> **Warning**: Changing these settings incorrectly can break your application.

</details>

<details>
<summary>📚 Code Examples and Implementation Details - PHP, JavaScript, and Configuration Files</summary>

Here are detailed code examples showing how to implement various features:

#### Plugin Registration
```php
public function register(Panel $panel): void
{
    return $panel
        ->plugin(PagesPlugin::make())
        ->resources([
            PageResource::class,
        ]);
}
```

#### JavaScript Integration
```javascript
document.addEventListener('livewire:navigated', function() {
    // Initialize components after Livewire navigation
    initializeMarkdownContent();
});
```

#### Markdown Processing
The system uses **GfmMarkdownRenderer** with these features:
- [x] GitHub Flavored Markdown support
- [x] Syntax highlighting with Prism.js
- [x] Table styling with Tailwind CSS
- [x] Task list rendering
- [ ] Math equation support (planned)

</details>

### Definition Lists
HTML
: HyperText Markup Language

CSS
: Cascading Style Sheets

JavaScript
: A programming language for web development

## 🚀 FilaMan-Specific Features

### Plugin Structure
```
plugins/
├── admin/
│   ├── src/
│   │   ├── AdminPlugin.php
│   │   └── Resources/
│   └── composer.json
└── pages/
    ├── src/
    │   ├── PagesPlugin.php
    │   └── Filament/
    └── resources/views/pages/
```

### Configuration Example
```php
// Panel configuration
return $panel
    ->id('pages')
    ->path('pages')
    ->brandName('FilaMan - Pages')
    ->viteTheme('resources/css/filament/admin/theme.css')
    ->colors(['primary' => Color::Blue])
    ->plugin(PagesPlugin::make());
```

## ✅ Feature Checklist

### Markdown Support
- [x] Headers (H1-H6)
- [x] Text formatting (bold, italic, strikethrough)
- [x] Links (internal, external, reference)
- [x] Lists (ordered, unordered, task lists)
- [x] Tables with alignment
- [x] Code blocks with syntax highlighting
- [x] Blockquotes (including nested)
- [x] Images and media
- [x] Horizontal rules
- [x] HTML support
- [x] Auto-linking URLs
- [x] Escape characters

### FilaMan Integration
- [x] Filament v4 styling
- [x] Admin panel layout
- [x] Plugin architecture
- [x] Auto-discovery
- [x] Database integration
- [x] SEO metadata
- [x] Category organization

## 🔗 Navigation

### Quick Links
- **🏠 Home**: [Welcome to FilaMan](/pages/home)
- **ℹ️ About**: [Learn about our mission](/pages/about)
- **🛠️ Services**: [Explore our services](/pages/services)
- **📞 Contact**: [Get in touch](/pages/contact)

---

**This concludes the GitHub Flavored Markdown test page.** All features should render correctly in FilaMan's Filament v4 admin panel layout! 🎉