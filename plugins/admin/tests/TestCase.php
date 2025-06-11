<?php

namespace FilaMan\Admin\Tests;

use FilaMan\Admin\AdminServiceProvider;
use FilaMan\Admin\Models\Plugin;
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

        // Run admin plugin migrations
        $this->artisan('migrate', [
            '--path' => 'plugins/admin/database/migrations',
        ]);

        // Run any necessary seeders
        if (class_exists(\Database\Seeders\DatabaseSeeder::class)) {
            $this->seed(\Database\Seeders\DatabaseSeeder::class);
        }
    }

    protected function setUpFilesystem(): void
    {
        // Ensure test plugins directory exists
        $testPluginsPath = $this->getTestPluginsPath();
        File::ensureDirectoryExists($testPluginsPath);

        // Create test plugin directories if needed
        $this->createTestPluginStructure();
    }

    protected function registerPlugin(): void
    {
        $this->app->register(AdminServiceProvider::class);
    }

    protected function getTestPluginsPath(): string
    {
        return base_path('plugins');
    }

    protected function createTestPluginStructure(): void
    {
        $testPluginsPath = $this->getTestPluginsPath();

        // Create a test plugin structure
        $testPluginPath = $testPluginsPath.'/test-plugin';
        File::ensureDirectoryExists($testPluginPath);

        $composerContent = [
            'name' => 'filaman/test-plugin',
            'description' => 'Test plugin for unit testing',
            'type' => 'laravel-plugin',
            'version' => '1.0.0',
            'authors' => [
                ['name' => 'Test Author', 'email' => 'test@example.com'],
            ],
            'require' => ['php' => '^8.3'],
            'autoload' => [
                'psr-4' => ['FilaMan\\TestPlugin\\' => 'src/'],
            ],
        ];

        File::put($testPluginPath.'/composer.json', json_encode($composerContent, JSON_PRETTY_PRINT));
    }

    protected function tearDown(): void
    {
        $this->cleanupTestFiles();
        parent::tearDown();
    }

    protected function cleanupTestFiles(): void
    {
        $testPluginPath = $this->getTestPluginsPath().'/test-plugin';
        if (File::exists($testPluginPath)) {
            File::deleteDirectory($testPluginPath);
        }
    }

    /**
     * Get application providers.
     */
    protected function getPackageProviders($app): array
    {
        return [
            AdminServiceProvider::class,
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
        $app['config']->set('filaman-admin.enabled', true);
        $app['config']->set('filaman-admin.plugins', ['pages']);
    }

    /**
     * Helper to create a test plugin record in database
     */
    protected function createTestPlugin(array $attributes = []): Plugin
    {
        return Plugin::create(array_merge([
            'name' => 'test-plugin',
            'display_name' => 'Test Plugin',
            'description' => 'A test plugin for unit testing',
            'version' => '1.0.0',
            'enabled' => true,
        ], $attributes));
    }

    /**
     * Helper to create test admin user
     */
    protected function createAdminUser(): \App\Models\User
    {
        return \App\Models\User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Helper to login as admin user
     */
    protected function loginAsAdmin(): \App\Models\User
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        return $admin;
    }

    /**
     * Helper to assert plugin exists in filesystem
     */
    protected function assertPluginExists(string $pluginName): void
    {
        $pluginPath = $this->getTestPluginsPath().'/'.$pluginName;
        $this->assertTrue(File::exists($pluginPath), "Plugin directory {$pluginName} should exist");

        $composerFile = $pluginPath.'/composer.json';
        $this->assertTrue(File::exists($composerFile), "Plugin {$pluginName} should have composer.json");
    }

    /**
     * Helper to assert plugin does not exist in filesystem
     */
    protected function assertPluginNotExists(string $pluginName): void
    {
        $pluginPath = $this->getTestPluginsPath().'/'.$pluginName;
        $this->assertFalse(File::exists($pluginPath), "Plugin directory {$pluginName} should not exist");
    }

    /**
     * Helper to assert plugin is installed in database
     */
    protected function assertPluginInstalled(string $pluginName): void
    {
        $this->assertDatabaseHas('plugins', ['name' => $pluginName]);
    }

    /**
     * Helper to assert plugin is not installed in database
     */
    protected function assertPluginNotInstalled(string $pluginName): void
    {
        $this->assertDatabaseMissing('plugins', ['name' => $pluginName]);
    }

    /**
     * Helper to assert plugin is enabled
     */
    protected function assertPluginEnabled(string $pluginName): void
    {
        $this->assertDatabaseHas('plugins', ['name' => $pluginName, 'enabled' => true]);
    }

    /**
     * Helper to assert plugin is disabled
     */
    protected function assertPluginDisabled(string $pluginName): void
    {
        $this->assertDatabaseHas('plugins', ['name' => $pluginName, 'enabled' => false]);
    }

    /**
     * Helper to create temporary plugin for testing
     */
    protected function createTemporaryPlugin(string $name, array $composerData = []): string
    {
        $pluginPath = $this->getTestPluginsPath().'/'.$name;
        File::ensureDirectoryExists($pluginPath);

        $defaultComposerData = [
            'name' => "filaman/{$name}",
            'description' => "Test plugin {$name}",
            'type' => 'laravel-plugin',
            'version' => '1.0.0',
            'authors' => [['name' => 'Test', 'email' => 'test@example.com']],
            'require' => ['php' => '^8.3'],
        ];

        $composerData = array_merge($defaultComposerData, $composerData);
        File::put($pluginPath.'/composer.json', json_encode($composerData, JSON_PRETTY_PRINT));

        return $pluginPath;
    }

    /**
     * Helper to remove temporary plugin
     */
    protected function removeTemporaryPlugin(string $name): void
    {
        $pluginPath = $this->getTestPluginsPath().'/'.$name;
        if (File::exists($pluginPath)) {
            File::deleteDirectory($pluginPath);
        }
    }
}
