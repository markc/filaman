# Screenshot Testing with Laravel Dusk and Firefox

This document explains how to use the screenshot testing capabilities that have been set up in the FilaMan project.

## Overview

The project now includes comprehensive browser testing capabilities with screenshot support using:

- **Laravel Dusk 8.3** - Browser automation framework
- **Symfony Panther 2.2** - Firefox support
- **GeckoDriver 0.36.0** - Firefox WebDriver
- **ChromeDriver** - Chrome WebDriver (pre-installed with Dusk)

## Browser Support

### Chrome (Default)
```bash
# Use Chrome browser (default)
composer test:dusk
composer test:screenshots
```

### Firefox
```bash
# Use Firefox browser
composer test:dusk:firefox
composer test:screenshots:firefox
```

## Available Test Commands

### Basic Dusk Testing
```bash
# Run all Dusk tests with Chrome
composer test:dusk

# Run all Dusk tests with Firefox
composer test:dusk:firefox
```

### Screenshot Testing
```bash
# Run screenshot tests with Chrome
composer test:screenshots

# Run screenshot tests with Firefox
composer test:screenshots:firefox
```

### Manual Browser Selection
```bash
# Use environment variable to select browser
DUSK_BROWSER=firefox php artisan dusk
DUSK_BROWSER=chrome php artisan dusk
```

## Screenshot Functionality

### Basic Screenshots
```php
// In your Dusk test
$browser->visit('/admin')
        ->screenshot('admin-dashboard');
```

### Responsive Screenshots
```php
// Desktop view
$browser->resize(1920, 1080)
        ->visit('/')
        ->screenshot('homepage-desktop');

// Tablet view
$browser->resize(768, 1024)
        ->visit('/')
        ->screenshot('homepage-tablet');

// Mobile view
$browser->resize(375, 667)
        ->visit('/')
        ->screenshot('homepage-mobile');
```

### Custom Screenshot Helpers
```php
// Using helper methods from DuskTestCase
protected function takeScreenshot(string $name = null, string $directory = null): string
protected function takeFullPageScreenshot(string $name = null, string $directory = null): string
```

## Filament-Specific Testing

### Login Helper
```php
// Login as admin user in Filament
$this->loginAsFilamentAdmin($browser);

$browser->visit('/admin')
        ->waitFor('.fi-topbar', 10)
        ->screenshot('filament-admin-panel');
```

### Admin User Creation
```php
// Create admin user for testing
$user = $this->createFilamentUser();
```

## Screenshot Storage

Screenshots are saved to:
- **Default location**: `tests/Browser/screenshots/`
- **Custom location**: `storage/app/screenshots/`

### Screenshot Files
- Format: PNG
- Naming: `{test-name}.png` or custom names
- Timestamped: `screenshot-2025-06-11-09-44-09.png`

## Environment Configuration

### Chrome Configuration (Default)
```env
# .env.dusk.local
DUSK_BROWSER=chrome
DUSK_DRIVER_URL=http://localhost:9515
```

### Firefox Configuration
```env
# .env.dusk.firefox (automatically used by composer test:screenshots:firefox)
DUSK_BROWSER=firefox
DUSK_DRIVER_URL=http://localhost:4444
DUSK_HEADLESS_DISABLED=false
```

## Advanced Configuration

### Headless Mode
```bash
# Disable headless mode (show browser window)
DUSK_HEADLESS_DISABLED=true php artisan dusk

# Enable headless mode (default)
DUSK_HEADLESS_DISABLED=false php artisan dusk
```

### Window Size
```bash
# Start maximized
DUSK_START_MAXIMIZED=true php artisan dusk
```

## Example Test Class

```php
<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MyScreenshotTest extends DuskTestCase
{
    public function test_can_take_admin_screenshots(): void
    {
        $this->browse(function (Browser $browser) {
            // Login to Filament admin
            $this->loginAsFilamentAdmin($browser);

            // Take dashboard screenshot
            $browser->visit('/admin')
                    ->waitFor('.fi-topbar', 10)
                    ->screenshot('admin-dashboard');

            // Take plugins page screenshot
            $browser->visit('/admin/plugins')
                    ->waitFor('.fi-page-content', 10)
                    ->screenshot('admin-plugins');

            $this->assertTrue(true);
        });
    }

    public function test_responsive_design(): void
    {
        $this->browse(function (Browser $browser) {
            $sizes = [
                'desktop' => [1920, 1080],
                'tablet' => [768, 1024],
                'mobile' => [375, 667],
            ];

            foreach ($sizes as $name => $size) {
                $browser->resize($size[0], $size[1])
                        ->visit('/')
                        ->screenshot("homepage-{$name}");
            }

            $this->assertTrue(true);
        });
    }
}
```

## Troubleshooting

### Common Issues

1. **Firefox not starting**
   ```bash
   # Check if GeckoDriver is installed
   geckodriver --version
   
   # Check if Firefox is installed
   firefox --version
   ```

2. **Screenshots not saving**
   ```bash
   # Ensure directory exists and is writable
   mkdir -p tests/Browser/screenshots
   chmod 755 tests/Browser/screenshots
   ```

3. **Filament login issues**
   ```bash
   # Ensure admin user exists in database
   php artisan make:filament-user
   ```

### Debug Mode
```bash
# Run with debug output
DUSK_HEADLESS_DISABLED=true php artisan dusk --verbose
```

## Integration with CI/CD

The screenshot tests can be integrated into CI/CD pipelines:

```yaml
# GitHub Actions example
- name: Run Dusk Screenshot Tests
  run: |
    composer test:screenshots
    composer test:screenshots:firefox
```

Screenshots can be uploaded as artifacts for visual regression testing or documentation purposes.