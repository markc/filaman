# FilaMan Pages Plugin

[![Tests](https://github.com/markc/filaman/actions/workflows/ci.yml/badge.svg)](https://github.com/markc/filaman/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

The **Pages Plugin** is a foundational plugin for FilaMan that provides a complete documentation and static page management system. It renders Markdown files with YAML front matter into beautiful, responsive web pages with automatic navigation generation.

## ✨ Features

### 📄 Markdown-Powered Pages
- **GitHub-Flavored Markdown** support with syntax highlighting
- **YAML Front Matter** for metadata (title, description, order, etc.)
- **Automatic HTML Generation** with customizable templates
- **SEO-Optimized** with meta tags and Open Graph support

### 🧭 Automatic Navigation  
- **Dynamic Navbar** generated from available pages
- **Ordered Navigation** based on front matter `order` field
- **Active State Indicators** for current page
- **Responsive Design** that works on all devices

### 🎨 Modern Design
- **Tailwind CSS** for beautiful, responsive styling
- **Clean Typography** optimized for reading
- **Code Highlighting** for technical documentation
- **Mobile-First** responsive design

### 🔧 Developer Experience
- **Hot Reloading** during development
- **Plugin Architecture** fully compatible with FilaMan standards
- **Extensible Templates** for custom page layouts
- **Configuration Options** for customization

## 📦 Installation

### Automatic Installation (Recommended)

The Pages Plugin comes pre-installed with FilaMan. No additional installation required!

### Manual Installation

If you need to install manually or in a different project:

```bash
# Add the plugin repository
composer config repositories.pages-plugin path packages/pages-plugin

# Install the plugin
composer require filaman/pages-plugin:*

# Register in your panel provider
// app/Providers/Filament/AdminPanelProvider.php
->plugin(FilaMan\PagesPlugin\PagesPlugin::make())
```

## 🚀 Quick Start

### 1. Create a New Page

Create a new Markdown file in `packages/pages-plugin/resources/views/pages/`:

```markdown
---
title: My New Page
slug: my-page
description: This is my awesome new page
order: 5
published: true
author: Your Name
date: 2025-06-11
tags: example, documentation
---

# My New Page

Welcome to my new page! This content is written in **Markdown**.

## Features

- Easy to write
- Automatic navigation
- Beautiful rendering

[Link to another page](/pages/about)
```

### 2. Access Your Page

Visit `http://localhost:8000/pages/my-page` to see your new page!

### 3. Navigation Updates Automatically

Your new page will automatically appear in the top navigation bar, ordered according to the `order` field in the front matter.

## 📖 Usage Guide

### Front Matter Options

Configure your pages using YAML front matter:

```yaml
---
title: Page Title                    # Required: Page title and H1
slug: page-url-slug                  # Required: URL slug for the page  
description: Brief description       # Optional: Meta description and subtitle
order: 1                            # Optional: Navigation order (default: 999)
published: true                     # Optional: Whether page is published (default: true)
author: Author Name                 # Optional: Page author
date: 2025-06-11                   # Optional: Publication date
tags: tag1, tag2, tag3             # Optional: Comma-separated tags
keywords: seo, keywords            # Optional: SEO keywords
---
```

### Markdown Features

The plugin supports GitHub-Flavored Markdown with additional features:

#### Code Highlighting

```php
<?php
namespace App\Models;

class User extends Model
{
    protected $fillable = ['name', 'email'];
}
```

#### Tables

| Feature | Supported | Notes |
|---------|-----------|-------|
| Basic Tables | ✅ | Full support |
| Code Highlighting | ✅ | Syntax highlighting |
| Task Lists | ✅ | GitHub-style checkboxes |

#### Task Lists

- [x] Write documentation
- [x] Create examples  
- [ ] Add more features

#### Blockquotes

> This is a blockquote that provides additional context or highlights important information.

### Internal Linking

Link to other pages using the route helper:

```markdown
[Check out our About page](/pages/about)
[Installation Guide](/pages/installation)
[Plugin Development](/pages/plugin-development)
```

### Navigation Configuration

The navigation is automatically generated based on:

1. **Published pages only** (`published: true`)
2. **Ordered by `order` field** (ascending)
3. **Falls back to alphabetical** if no order specified
4. **Shows current page** with active styling

## ⚙️ Configuration

### Plugin Configuration

Publish and customize the configuration file:

```bash
php artisan vendor:publish --tag=filaman-pages-config
```

Edit `config/filaman-pages.php`:

```php
<?php

return [
    // Default page when no slug provided
    'default_page' => 'home',
    
    // Template for rendering pages
    'page_template' => 'filaman-pages::page',
    
    // Navigation settings
    'navigation' => [
        'enabled' => true,
        'show_unpublished' => false,
        'sort_by' => 'order',
        'sort_direction' => 'asc',
    ],
    
    // SEO defaults
    'seo' => [
        'site_name' => 'FilaMan',
        'default_description' => 'Filament v4.x Plugin Manager',
        'default_keywords' => 'filament, laravel, plugins',
    ],
];
```

### Custom Templates

Create custom page templates by publishing the views:

```bash
php artisan vendor:publish --tag=filaman-pages-views
```

Customize the templates in `resources/views/vendor/filaman-pages/`:
- `page.blade.php` - Main page template
- `index.blade.php` - Pages listing template  
- `partials/navbar.blade.php` - Navigation component

### Styling Customization

The plugin uses Tailwind CSS for styling. To customize:

1. **Override CSS classes** in your custom templates
2. **Add custom CSS** to your application's stylesheet
3. **Modify the Tailwind config** to include plugin styles

## 🧪 Testing

Run the plugin's test suite:

```bash
# Run all plugin tests
cd packages/pages-plugin
composer test

# Run with coverage
composer test-coverage

# Check code style
composer format
```

### Writing Tests

Example test for page rendering:

```php
<?php

namespace FilaMan\PagesPlugin\Tests\Feature;

use Tests\TestCase;

class PageRenderingTest extends TestCase
{
    public function test_home_page_renders_correctly()
    {
        $response = $this->get('/pages/home');
        
        $response->assertStatus(200);
        $response->assertSee('Welcome to FilaMan');
        $response->assertSee('FilaMan Plugin Manager'); // Navbar brand
    }
    
    public function test_nonexistent_page_returns_404()
    {
        $response = $this->get('/pages/nonexistent');
        
        $response->assertStatus(404);
    }
}
```

## 🛠️ Development

### Local Development Setup

```bash
# Clone the FilaMan repository
git clone https://github.com/markc/filaman.git
cd filaman

# Install dependencies
composer install
npm install

# Set up environment
cp .env.example .env
php artisan key:generate

# Create database
touch database/database.sqlite
php artisan migrate
php artisan db:seed

# Start development server
composer dev
```

### Plugin Development Workflow

1. **Create new pages** in `resources/views/pages/`
2. **Test locally** at `http://localhost:8000/pages/your-slug`
3. **Run tests** to ensure nothing breaks
4. **Update documentation** as needed
5. **Submit pull request** following contribution guidelines

### File Structure

```
packages/pages-plugin/
├── src/
│   ├── PagesPlugin.php                 # Main plugin class
│   ├── PagesPluginServiceProvider.php  # Service provider
│   └── Http/Controllers/
│       └── PageController.php          # Page rendering logic
├── resources/views/
│   ├── page.blade.php                  # Main page template
│   ├── index.blade.php                 # Pages listing
│   ├── partials/
│   │   └── navbar.blade.php           # Navigation component
│   └── pages/
│       ├── home.md                     # Sample pages
│       ├── about.md
│       └── installation.md
├── routes/
│   └── web.php                         # Plugin routes
├── config/
│   └── filaman-pages.php              # Plugin configuration
├── tests/                              # Test suites
├── docs/                               # Plugin documentation
└── composer.json                       # Package definition
```

## 🤝 Contributing

We welcome contributions! Here's how to get involved:

### Reporting Issues

- **Bug Reports**: Use GitHub Issues with the `bug` label
- **Feature Requests**: Use GitHub Issues with the `enhancement` label
- **Questions**: Use GitHub Discussions

### Contributing Code

1. **Fork the repository**
2. **Create a feature branch**: `git start your-feature-name`
3. **Write tests** for your changes
4. **Ensure tests pass**: `composer test`
5. **Follow code style**: `composer format`
6. **Submit pull request**: `git finish "Your feature description"`

### Documentation

Help improve our documentation:

- **Fix typos and errors**
- **Add missing examples**
- **Improve explanations**
- **Translate to other languages**

## 📄 License

This plugin is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- **Laravel Team** for the amazing framework
- **Filament Team** for the powerful admin panel
- **Spatie** for excellent Laravel packages
- **Tailwind CSS** for beautiful styling
- **Claude Code** for development assistance

## 📞 Support

- **Documentation**: [FilaMan Pages Documentation](https://filaman.dev/pages)
- **Issues**: [GitHub Issues](https://github.com/markc/filaman/issues)
- **Discussions**: [GitHub Discussions](https://github.com/markc/filaman/discussions)
- **Discord**: [FilaMan Community Discord](https://discord.gg/filaman)

---

Built with ❤️ by the FilaMan community