---
title: Welcome to FilaMan
slug: home
description: FilaMan is a modern Filament v4.x plugin manager built with Laravel 12, providing a comprehensive plugin architecture for Filament applications.
order: 1
published: true
author: FilaMan Team
date: 2025-06-11
tags: welcome, introduction, filament, laravel
---

# Welcome to FilaMan! 🎉

FilaMan (Filament Manager) is a **modern Filament v4.x plugin manager** built with Laravel 12, Filament 4 beta, and Claude Code. This system provides a comprehensive plugin-based architecture for building and managing Filament applications.

## 🚀 What is FilaMan?

FilaMan isn't just another Laravel application—it's a **plugin management ecosystem** specifically designed for Filament v4.x applications. Think of it as a foundation that allows you to:

- **Discover and Install** Filament plugins seamlessly
- **Manage Plugin Dependencies** with automatic resolution
- **Build Custom Plugins** using our standardized architecture
- **Scale Your Application** with modular, reusable components

## ✨ Key Features

### 🔧 Plugin Management
- **Automatic Discovery**: FilaMan automatically detects and loads plugins from the `packages/` directory
- **Dependency Resolution**: Smart dependency management ensures plugins work together harmoniously
- **Version Control**: Track and manage plugin versions with built-in compatibility checks

### 🎨 Developer Experience
- **Local Auto-Login**: Skip authentication in development with automatic admin access
- **Hot Reloading**: See your changes instantly during development
- **Comprehensive Testing**: Built-in Pest test suite ensures reliability

### 🛠️ Modern Tech Stack
- **Laravel 12**: Latest Laravel framework with PHP 8.3+ features
- **Filament 4 Beta**: Cutting-edge admin panel with unified schema core
- **SQLite Database**: Lightweight, file-based database for easy deployment
- **Vite Build System**: Fast frontend asset compilation with HMR

## 🏗️ Plugin Architecture

FilaMan follows a **strict plugin-based architecture** where each feature is encapsulated in its own plugin:

```
packages/
├── pages-plugin/          # This documentation system
├── user-management-plugin/  # User & role management
├── file-manager-plugin/      # File upload & management
└── custom-plugin/           # Your custom functionality
```

Each plugin is a **self-contained Laravel package** with its own:
- Service providers and configuration
- Controllers, models, and views
- Database migrations and seeders
- Test suites and documentation

## 🎯 Getting Started

Ready to dive in? Here's how to get started with FilaMan:

1. **[About FilaMan](/pages/about)** - Learn more about our mission and architecture
2. **[Installation Guide](/pages/installation)** - Set up your development environment
3. **[Plugin Development](/pages/plugin-development)** - Create your first plugin
4. **[API Documentation](/pages/api)** - Explore the available APIs

## 🤝 Contributing

FilaMan is built with the community in mind. We welcome contributions of all kinds:

- **Plugin Development**: Create and share your own plugins
- **Core Improvements**: Help improve the core system
- **Documentation**: Help us improve our docs
- **Bug Reports**: Found an issue? Let us know!

Check out our [Contributing Guide](/pages/contributing) to get started.

## 📞 Need Help?

- **Documentation**: You're reading it! Browse all our [pages](/pages/) 
- **GitHub Issues**: [Report bugs or request features](https://github.com/markc/filaman/issues)
- **Discussions**: [Join the community conversation](https://github.com/markc/filaman/discussions)

---

**Ready to build something amazing?** Let's get started with FilaMan! 🚀