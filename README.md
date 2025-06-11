# Filaman

[![CI](https://github.com/markc/filaman/actions/workflows/ci.yml/badge.svg)](https://github.com/markc/filaman/actions/workflows/ci.yml)
[![Deploy](https://github.com/markc/filaman/actions/workflows/deploy.yml/badge.svg)](https://github.com/markc/filaman/actions/workflows/deploy.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A modern Filament v4.x plugin manager built with Laravel 12, Filament 4 beta, and Claude Code.

## ✨ Features

### 🔧 Core Functionality
- **Filament v4.x Plugin Manager**: Discover, install, and manage Filament plugins with advanced metadata
- **Plugin Processing**: Automatic plugin validation and dependency resolution
- **Search & Filter**: Powerful search capabilities with advanced filtering options
- **User Management**: Role-based access control with 2FA support
- **Admin Dashboard**: Comprehensive admin panel powered by Filament 4 beta

### 🎯 User Experience
- **Modern Interface**: Clean, responsive UI built with Tailwind CSS v4
- **Plugin Architecture**: Extensible system with custom Filament plugins
- **Real-time Updates**: Live notifications and status updates
- **Multi-language Support**: Internationalization ready

### 🛠️ Technical Features
- **Laravel 12**: Latest Laravel framework with modern PHP 8.3+ features
- **Filament 4 Beta**: Cutting-edge admin panel with unified schema core
- **SQLite Database**: Lightweight, file-based database for easy deployment
- **Vite Build System**: Fast frontend asset compilation
- **Pest Testing**: Comprehensive test suite with feature and unit tests

## 🚀 Development Workflow

This project uses a **mandatory git workflow** with custom aliases:

```bash
git start [branch-name]    # Start new feature branch
git finish [msg]           # Auto-commit, create PR, merge, and cleanup
git check                  # Check repository status
git cleanup                # Clean up old branches (weekly)
```

**⚠️ CRITICAL**: All merges to main branch MUST use these aliases. Direct pushes are forbidden.

## 🏃‍♂️ Quick Start

### Prerequisites
- PHP 8.3+
- Composer
- Node.js 20+
- SQLite

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/markc/filaman.git
   cd filaman
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   touch database/database.sqlite
   php artisan migrate
   php artisan db:seed
   ```

5. **Start development**
   ```bash
   composer dev  # Runs PHP server, queue worker, log viewer, and Vite
   ```

Visit `http://localhost:8000` for the main application and `http://localhost:8000/admin` for the admin panel.

## 📚 Documentation

Comprehensive documentation is available in the `docs/` directory:

- [Installation & Setup](docs/01_installation_setup.md)
- [Plugin Architecture](docs/02_plugin_architecture.md)
- [Creating Plugins](docs/03_creating_plugins.md)
- [Deployment Guide](docs/04_deployment_guide.md)
- [Authentication Setup](docs/06_authentication_setup.md)
- [Troubleshooting](docs/05_troubleshooting.md)

## 🔧 Development

### Available Commands

```bash
# Backend Development
composer dev              # Start full development environment
php artisan test          # Run Pest test suite
./vendor/bin/pint         # Code formatting
php artisan tinker        # Laravel REPL

# Frontend Development
npm run dev               # Vite development server
npm run build             # Production build

# Filament Commands
php artisan make:filament-resource ResourceName
php artisan make:filament-page PageName
php artisan make:filament-widget WidgetName
php artisan make:filament-user    # Create admin user
```

### Testing

Run the comprehensive test suite:

```bash
php artisan test                           # All tests
php artisan test --filter AuthenticationTest  # Specific test
php artisan test tests/Feature/            # Feature tests only
```

## 🏗️ Architecture

### Technology Stack
- **Backend**: Laravel 12, PHP 8.3+, SQLite
- **Frontend**: Vite, Tailwind CSS v4, Blade templates
- **Admin Panel**: Filament 4 beta with plugin system
- **Testing**: Pest PHP framework
- **CI/CD**: GitHub Actions

### Project Structure
```
filaman/
├── app/
│   ├── Filament/Resources/     # Filament admin resources
│   ├── Http/Controllers/       # Laravel controllers
│   ├── Models/                 # Eloquent models
│   └── Providers/Filament/     # Filament panel providers
├── database/
│   ├── migrations/             # Database migrations
│   └── seeders/               # Database seeders
├── docs/                      # Project documentation
├── packages/                  # Filament plugin packages
├── resources/                 # Frontend assets and views
├── scripts/                   # Development scripts
└── tests/                     # Pest test suite
```

### Plugin Development

Create new plugins using the recommended `awcodes/hydro` package:

```bash
composer global require awcodes/hydro
hydro new:plugin YourPluginName
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch using `git start feature-name`
3. Make your changes and write tests
4. Ensure tests pass: `php artisan test`
5. Run code formatting: `./vendor/bin/pint`
6. Complete the feature using `git finish "Your commit message"`

Please follow the [contributing guidelines](CONTRIBUTING.md) and ensure all tests pass.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: Check the `docs/` directory
- **Issues**: [GitHub Issues](https://github.com/markc/filaman/issues)
- **Discussions**: [GitHub Discussions](https://github.com/markc/filaman/discussions)

---

Built with ❤️ using [Laravel](https://laravel.com), [Filament](https://filamentphp.com), and [Claude Code](https://claude.ai/code)
