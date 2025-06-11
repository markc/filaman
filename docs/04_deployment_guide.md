---
title: Deployment Guide
description: Production deployment instructions for Filament 4 plugin-based applications
date: 2025-11-06
---

# Deployment Guide

This guide covers deploying a Filament 4 plugin-based application to production environments.

## Pre-Deployment Checklist

### Code Quality

- [ ] All tests passing: `php artisan test`
- [ ] Code formatted: `./vendor/bin/pint`
- [ ] No syntax errors: `php -l`
- [ ] Dependencies up to date: `composer update --no-dev`

### Configuration

- [ ] Environment variables configured
- [ ] Database connection tested
- [ ] Cache drivers configured
- [ ] Queue drivers configured
- [ ] File storage configured

### Security

- [ ] APP_KEY generated and secure
- [ ] APP_DEBUG set to false
- [ ] HTTPS configured
- [ ] Admin user credentials secure
- [ ] File permissions correct

## Environment Configuration

### Required Environment Variables

```env
# Application
APP_NAME="FilaMan"
APP_ENV=production
APP_KEY=your-32-character-secret-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

# Cache
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Session
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Filament
FILAMENT_FILESYSTEM_DISK=public
```

## Deployment Methods

### Method 1: Traditional Server Deployment

#### 1. Server Requirements

- PHP 8.3+
- Composer
- Node.js 18+
- Web server (Nginx/Apache)
- Database (MySQL/PostgreSQL)
- Redis (recommended)

#### 2. Upload Files

```bash
# Upload to server (exclude development files)
rsync -av --exclude-from='.rsyncignore' ./ user@server:/path/to/app/
```

Create `.rsyncignore`:
```
.git/
node_modules/
tests/
.env.example
.gitignore
.editorconfig
phpunit.xml
vite.config.js
package.json
package-lock.json
```

#### 3. Server Setup

```bash
# SSH into server
ssh user@server

# Navigate to app directory
cd /path/to/app

# Install dependencies
composer install --no-dev --optimize-autoloader

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Generate application key (if not set)
php artisan key:generate

# Run migrations
php artisan migrate --force

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Build assets
npm ci
npm run build

# Create admin user
php artisan make:filament-user
```

#### 4. Web Server Configuration

**Nginx Configuration:**

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Method 2: Docker Deployment

#### 1. Create Dockerfile

```dockerfile
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files
COPY composer*.json ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy application code
COPY . .

# Copy package files and install node dependencies
COPY package*.json ./
RUN npm ci && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
```

#### 2. Docker Compose

```yaml
version: '3.8'

services:
  app:
    build: .
    container_name: filament-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
      - ./storage:/var/www/storage
    networks:
      - app-network

  webserver:
    image: nginx:alpine
    container_name: filament-webserver
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
    networks:
      - app-network

  db:
    image: mysql:8.0
    container_name: filament-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_USER: ${DB_USERNAME}
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - app-network

  redis:
    image: redis:alpine
    container_name: filament-redis
    restart: unless-stopped
    networks:
      - app-network

networks:
  app-network:
    driver: bridge

volumes:
  dbdata:
    driver: local
```

### Method 3: Cloud Platform Deployment

#### Laravel Forge

1. Connect your server to Forge
2. Create new site pointing to your repository
3. Configure environment variables
4. Set up deployment script:

```bash
cd /home/forge/yourdomain.com
git pull origin $FORGE_SITE_BRANCH

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock

if [ -f artisan ]; then
    $FORGE_PHP artisan migrate --force
    $FORGE_PHP artisan config:cache
    $FORGE_PHP artisan route:cache
    $FORGE_PHP artisan view:cache
    $FORGE_PHP artisan optimize
fi

npm ci
npm run build
```

#### Heroku

1. Create `Procfile`:
```
web: vendor/bin/heroku-php-apache2 public/
worker: php artisan queue:work --verbose --tries=3 --timeout=90
```

2. Configure buildpacks:
```bash
heroku buildpacks:set heroku/php
heroku buildpacks:add --index 1 heroku/nodejs
```

3. Set environment variables in Heroku dashboard

## Plugin-Specific Deployment Considerations

### Plugin Dependencies

Ensure all plugin dependencies are properly installed:

```bash
# Check all local packages are symlinked
ls -la vendor/
composer show | grep "filaman"

# Install production versions if needed
composer install --no-dev
```

### Plugin Migrations

Run migrations for all plugins:

```bash
# This will run migrations from all registered plugins
php artisan migrate --force
```

### Plugin Assets

Build and publish plugin assets:

```bash
# Build main application assets
npm run build

# Publish plugin assets if any
php artisan vendor:publish --tag="plugin-assets" --force
```

### Plugin Configuration

Publish configuration files:

```bash
# Publish all plugin configs
php artisan vendor:publish --tag="config"

# Or specific plugin configs
php artisan vendor:publish --tag="blog-plugin-config"
```

## Performance Optimization

### Application Optimization

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Optimize application
php artisan optimize
```

### Database Optimization

- Enable query caching
- Add appropriate indexes
- Optimize database configuration
- Set up read replicas if needed

### Caching Strategy

```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),

'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
    ],
],
```

### Queue Management

Set up queue workers:

```bash
# Install supervisor for queue management
sudo apt install supervisor

# Create supervisor config
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Supervisor configuration:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/app/storage/logs/worker.log
stopwaitsecs=3600
```

## Monitoring and Maintenance

### Health Checks

Create health check endpoint:

```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => app()->version(),
    ]);
});
```

### Log Management

Configure log rotation and monitoring:

```bash
# Configure logrotate
sudo nano /etc/logrotate.d/laravel
```

### Backup Strategy

Set up automated backups:

```bash
# Install spatie/laravel-backup
composer require spatie/laravel-backup

# Configure backup settings
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"

# Schedule backups in crontab
php artisan backup:run
```

### Updates and Maintenance

Create maintenance workflow:

1. Enable maintenance mode: `php artisan down`
2. Pull latest code: `git pull`
3. Update dependencies: `composer install --no-dev`
4. Run migrations: `php artisan migrate --force`
5. Clear caches: `php artisan optimize:clear`
6. Rebuild caches: `php artisan optimize`
7. Disable maintenance mode: `php artisan up`

## Security Hardening

### File Permissions

```bash
# Application files
find /path/to/app -type f -exec chmod 644 {} \;
find /path/to/app -type d -exec chmod 755 {} \;

# Storage and cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Security Headers

Add security headers in web server configuration:

```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```

### Environment Protection

- Use strong passwords
- Restrict database access
- Enable firewall
- Use SSL certificates
- Regular security updates