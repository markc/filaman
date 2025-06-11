<?php

namespace FilaMan\Pages\Tests;

use FilaMan\Pages\PagesServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends \Tests\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->setUpFilesystem();
        $this->registerPlugin();
    }

    protected function setUpDatabase(): void
    {
        $this->artisan('migrate:fresh');

        // Run any necessary seeders
        if (class_exists(\Database\Seeders\DatabaseSeeder::class)) {
            $this->seed(\Database\Seeders\DatabaseSeeder::class);
        }
    }

    protected function setUpFilesystem(): void
    {
        // Ensure test pages directory exists
        $pagesPath = $this->getTestPagesPath();
        File::ensureDirectoryExists($pagesPath);

        // Copy test fixture files if they don't exist
        $this->createTestPages();
    }

    protected function registerPlugin(): void
    {
        $this->app->register(PagesServiceProvider::class);
    }

    protected function getTestPagesPath(): string
    {
        return base_path('plugins/pages/resources/views/pages');
    }

    protected function createTestPages(): void
    {
        $pagesPath = $this->getTestPagesPath();

        // Create test pages if they don't exist
        $testPages = [
            'test-page.md' => $this->getTestPageContent(),
            'unpublished-page.md' => $this->getUnpublishedPageContent(),
        ];

        foreach ($testPages as $filename => $content) {
            $filePath = $pagesPath.'/'.$filename;
            if (! File::exists($filePath)) {
                File::put($filePath, $content);
            }
        }
    }

    protected function getTestPageContent(): string
    {
        return <<<'MD'
---
title: Test Page Title
slug: test-page
description: Test page description
order: 1
published: true
author: Test Author
date: 2025-06-11
tags: test, example
---

# Test Page Title

This is test page content for testing purposes.

## Features

- Test feature 1
- Test feature 2
- Test feature 3

```php
<?php
echo "Hello, World!";
```

[Link to home](/pages/home)
MD;
    }

    protected function getUnpublishedPageContent(): string
    {
        return <<<'MD'
---
title: Unpublished Page
slug: unpublished-page
description: This page should not appear in navigation
order: 999
published: false
---

# Unpublished Page

This page is not published and should not appear in navigation.
MD;
    }

    protected function tearDown(): void
    {
        // Clean up test files
        $this->cleanupTestFiles();

        parent::tearDown();
    }

    protected function cleanupTestFiles(): void
    {
        $testFiles = [
            'test-page.md',
            'unpublished-page.md',
        ];

        $pagesPath = $this->getTestPagesPath();

        foreach ($testFiles as $filename) {
            $filePath = $pagesPath.'/'.$filename;
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }
    }

    /**
     * Get application providers.
     */
    protected function getPackageProviders($app): array
    {
        return [
            PagesServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     */
    protected function defineEnvironment($app): void
    {
        // Setup test environment configuration
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Plugin-specific configuration
        $app['config']->set('filaman-pages.default_page', 'home');
        $app['config']->set('filaman-pages.navigation.enabled', true);
        $app['config']->set('filaman-pages.seo.site_name', 'Test FilaMan');
    }

    /**
     * Helper to assert page exists and is accessible.
     */
    protected function assertPageExists(string $slug): void
    {
        $response = $this->get("/pages/{$slug}");
        $response->assertStatus(200);
    }

    /**
     * Helper to assert page does not exist.
     */
    protected function assertPageNotExists(string $slug): void
    {
        $response = $this->get("/pages/{$slug}");
        $response->assertStatus(404);
    }

    /**
     * Helper to get page content for testing.
     */
    protected function getPageContent(string $slug): string
    {
        $response = $this->get("/pages/{$slug}");

        return $response->getContent();
    }

    /**
     * Helper to extract navigation from page content.
     */
    protected function extractNavigation(string $content): string
    {
        preg_match('/<nav.*?<\/nav>/s', $content, $matches);

        return $matches[0] ?? '';
    }

    /**
     * Helper to create a test user with specific role.
     */
    protected function createTestUser(string $role = 'user'): \App\Models\User
    {
        return \App\Models\User::factory()->create([
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Helper to login as admin user.
     */
    protected function loginAsAdmin(): \App\Models\User
    {
        $admin = $this->createTestUser('admin');
        $this->actingAs($admin);

        return $admin;
    }

    /**
     * Helper to check if a string contains valid HTML.
     */
    protected function assertValidHtml(string $html): void
    {
        // Basic HTML validation
        $this->assertStringContainsString('<', $html);
        $this->assertStringContainsString('>', $html);

        // Should not contain unescaped front matter
        $this->assertStringNotContainsString('---', $html);
    }

    /**
     * Helper to measure execution time.
     */
    protected function measureExecutionTime(callable $callback): float
    {
        $start = microtime(true);
        $callback();
        $end = microtime(true);

        return ($end - $start) * 1000; // Return in milliseconds
    }
}
