---
title: Installation Guide
slug: installation
description: Complete step-by-step guide to installing and setting up FilaMan for development and production environments.
order: 3
published: true
author: FilaMan Team
date: 2025-06-11
tags: installation, setup, development, production
---

# Installation Guide

Getting started with FilaMan is straightforward. This guide will walk you through setting up your development environment and deploying to production.

## 📋 Prerequisites

Before installing FilaMan, ensure your system meets these requirements:

### System Requirements

| Component | Minimum Version | Recommended |
|-----------|----------------|-------------|
| **PHP** | 8.3.0 | 8.3+ |
| **Composer** | 2.0 | Latest |
| **Node.js** | 18.0 | 20+ LTS |
| **NPM** | 8.0 | Latest |
| **SQLite** | 3.35 | Latest |

### PHP Extensions

FilaMan requires these PHP extensions:

```bash
# Required extensions
php -m | grep -E "(mbstring|xml|ctype|iconv|intl|pdo_sqlite|dom|filter|gd|json|pdo|zip|bcmath|curl|fileinfo|openssl|tokenizer)"
```

If any extensions are missing, install them using your system's package manager:

```bash
# Ubuntu/Debian
sudo apt-get install php8.3-mbstring php8.3-xml php8.3-sqlite3 php8.3-gd php8.3-curl php8.3-zip

# macOS (Homebrew)
brew install php@8.3

# Windows (enable in php.ini)
extension=mbstring
extension=pdo_sqlite
# ... etc
```

## 🚀 Quick Start (Development)

### 1. Clone the Repository

```bash
git clone https://github.com/markc/filaman.git
cd filaman
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite
```

### 4. Database Setup

```bash
# Run database migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed
```

### 5. Git Workflow Setup

FilaMan uses a mandatory git workflow. Set it up:

```bash
# Set up git aliases
./scripts/setup-git-aliases.sh

# Verify aliases are working
git start test-branch
git finish "Test commit message"
```

### 6. Start Development

```bash
# Start the full development environment
composer dev
```

This command starts:
- **Laravel development server** (http://localhost:8000)
- **Vite development server** with hot reloading
- **Queue worker** for background jobs
- **Log viewer** for real-time debugging

### 7. Access the Application

- **Main Site**: http://localhost:8000
- **Pages**: http://localhost:8000/pages/home
- **Admin Panel**: http://localhost:8000/admin (auto-login in development)

## 🏗️ Manual Installation

If you prefer more control over the installation process:

### 1. Download FilaMan

```bash
# Option A: Clone with Git
git clone https://github.com/markc/filaman.git filaman-project
cd filaman-project

# Option B: Download ZIP
curl -L https://github.com/markc/filaman/archive/main.zip -o filaman.zip
unzip filaman.zip
cd filaman-main
```

### 2. Configure Environment

Create your `.env` file with custom settings:

```bash
# Copy and edit environment file
cp .env.example .env
```

Edit `.env` to match your setup:

```env
APP_NAME=FilaMan
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Mail Configuration (optional for development)
MAIL_MAILER=log

# Queue Configuration (optional)
QUEUE_CONNECTION=sync
```

### 3. Install and Build

```bash
# Install PHP dependencies
composer install --optimize-autoloader

# Install and build frontend assets
npm ci
npm run build
```

### 4. Database Initialization

```bash
# Ensure database file exists
mkdir -p database
touch database/database.sqlite

# Run migrations
php artisan migrate --force

# Create admin user
php artisan make:filament-user
```

### 5. Optimize for Development

```bash
# Clear all caches
php artisan optimize:clear

# Cache configuration for better performance
php artisan config:cache
php artisan route:cache
```

## 🧪 Verify Installation

Run the test suite to ensure everything is working:

```bash
# Run all tests
php artisan test

# Run with coverage (optional)
php artisan test --coverage

# Check code style
./vendor/bin/pint --test
```

Expected output:
```
✓ 35 tests passed (70 assertions)
✓ Code style: All files conform to standards
```

## 🌐 Production Deployment

### Environment Preparation

```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Generate secure app key
php artisan key:generate

# Use a production database
DB_CONNECTION=mysql  # or postgresql
DB_HOST=your-db-host
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-secure-password
```

### Web Server Configuration

#### Apache (.htaccess)

FilaMan includes a properly configured `.htaccess` file. Ensure:

```apache
# Enable mod_rewrite
a2enmod rewrite

# Set document root to /public
DocumentRoot /var/www/filaman/public
```

#### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/filaman/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Deployment Script

Create a deployment script for automated deployments:

```bash
#!/bin/bash
# deploy.sh

set -e

echo "🚀 Starting FilaMan deployment..."

# Pull latest code
git pull origin main

# Install/update dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Database migrations
php artisan migrate --force

# Clear and cache everything
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "✅ Deployment complete!"
```

## 🔧 Plugin Installation

### Installing the Pages Plugin

The Pages plugin comes pre-installed, but here's how to install it manually:

```bash
# Add to main composer.json
composer config repositories.pages-plugin path plugins/pages-plugin
composer require filaman/pages-plugin:*
```

### Installing Additional Plugins

```bash
# Example: Installing a hypothetical file-manager plugin
composer config repositories.file-manager path plugins/file-manager-plugin
composer require filaman/file-manager-plugin:*

# Register in panel provider
# Edit app/Providers/Filament/AdminPanelProvider.php
->plugin(\FilaMan\FileManagerPlugin\FileManagerPlugin::make())
```

## 🐛 Troubleshooting

### Common Issues

#### 1. Permission Errors

```bash
# Fix storage permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

#### 2. Database Connection Issues

```bash
# Verify SQLite file exists and is writable
ls -la database/database.sqlite
chmod 664 database/database.sqlite
```

#### 3. Asset Build Failures

```bash
# Clear npm cache and reinstall
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

#### 4. Composer Issues

```bash
# Clear composer cache
composer clear-cache

# Update composer itself
composer self-update

# Reinstall dependencies
rm -rf vendor composer.lock
composer install
```

### Debug Mode

Enable debug mode for development:

```bash
# In .env
APP_DEBUG=true
APP_LOG_LEVEL=debug

# Clear caches
php artisan optimize:clear
```

### Log Files

Check log files for detailed error information:

```bash
# View latest logs
tail -f storage/logs/laravel.log

# View specific date
cat storage/logs/laravel-$(date +%Y-%m-%d).log
```

## 📞 Getting Help

If you encounter issues:

1. **Check the logs**: `storage/logs/laravel.log`
2. **Verify requirements**: Run `php artisan about`
3. **Search issues**: [GitHub Issues](https://github.com/markc/filaman/issues)
4. **Ask the community**: [GitHub Discussions](https://github.com/markc/filaman/discussions)

---

**Ready to start building?** Check out our [Plugin Development Guide](/pages/plugin-development) next! 🛠️