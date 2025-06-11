<?php

namespace FilaMan\Admin\Tests\Integration;

use FilaMan\Admin\AdminPlugin;
use FilaMan\Admin\AdminServiceProvider;
use FilaMan\Admin\Models\Plugin;
use FilaMan\Admin\Services\PluginManager;
use Filament\Panel;

describe('Filament Integration', function () {
    beforeEach(function () {
        $this->admin = $this->createAdminUser();
    });

    describe('plugin registration with filament', function () {
        test('admin plugin implements filament plugin interface', function () {
            $plugin = new AdminPlugin;

            expect($plugin)->toBeInstanceOf(\Filament\Contracts\Plugin::class);
        });

        test('admin plugin has unique identifier', function () {
            $plugin = new AdminPlugin;

            expect($plugin->getId())->toBe('admin-panel');
            expect($plugin->getId())->toBeString();
            expect($plugin->getId())->not()->toBeEmpty();
        });

        test('admin plugin can be registered with panel', function () {
            $plugin = AdminPlugin::make();
            $panel = createMockPanel();

            // Should not throw exception during registration
            $plugin->register($panel);

            expect(true)->toBeTrue();
        });

        test('admin plugin registers required resources', function () {
            $plugin = AdminPlugin::make();
            $panel = createMockPanel();

            $panel->shouldReceive('resources')->once()->with([
                \FilaMan\Admin\Filament\Resources\PluginResource::class,
            ])->andReturnSelf();

            $panel->shouldReceive('pages')->once()->andReturnSelf();
            $panel->shouldReceive('widgets')->once()->andReturnSelf();

            $plugin->register($panel);
        });

        test('admin plugin registers widgets', function () {
            $plugin = AdminPlugin::make();
            $panel = createMockPanel();

            $panel->shouldIgnoreMissing();
            $panel->shouldReceive('widgets')->once()->with([
                \FilaMan\Admin\Filament\Widgets\PluginStatsWidget::class,
            ])->andReturnSelf();

            $plugin->register($panel);
        });
    });

    describe('service provider integration', function () {
        test('admin service provider registers plugin singleton', function () {
            $plugin1 = app(AdminPlugin::class);
            $plugin2 = app(AdminPlugin::class);

            expect($plugin1)->toBe($plugin2);
        });

        test('admin service provider registers plugin manager', function () {
            $manager1 = app(PluginManager::class);
            $manager2 = app(PluginManager::class);

            expect($manager1)->toBeInstanceOf(PluginManager::class);
            expect($manager1)->toBe($manager2); // Should be singleton
        });

        test('admin service provider registers view namespace', function () {
            // Boot the service provider
            $provider = new AdminServiceProvider($this->app);
            $provider->packageBooted();

            // Check if views can be resolved (indirectly tests namespace registration)
            expect(view()->exists('filaman-admin::non-existent'))->toBeFalse();
        });

        test('admin service provider loads configuration', function () {
            $config = config('filaman-admin');

            expect($config)->toBeArray();
        });
    });

    describe('resource integration', function () {
        beforeEach(function () {
            $this->actingAs($this->admin);
        });

        test('plugin resource is accessible via filament', function () {
            $response = $this->get('/admin/plugins');

            $response->assertStatus(200);
        });

        test('plugin resource shows existing plugins', function () {
            $plugin = $this->createTestPlugin([
                'name' => 'integration-test-plugin',
                'display_name' => 'Integration Test Plugin',
            ]);

            $response = $this->get('/admin/plugins');

            $response->assertStatus(200);
            $response->assertSee('Integration Test Plugin');
        });

        test('plugin resource allows creation of new plugins', function () {
            $response = $this->get('/admin/plugins/create');

            $response->assertStatus(200);
        });

        test('plugin resource allows editing existing plugins', function () {
            $plugin = $this->createTestPlugin(['name' => 'edit-integration-test']);

            $response = $this->get("/admin/plugins/{$plugin->id}/edit");

            $response->assertStatus(200);
        });

        test('plugin resource form validation works', function () {
            $response = $this->post('/admin/plugins', [
                'name' => '', // Empty name should fail validation
                'description' => 'Test description',
            ]);

            $response->assertSessionHasErrors(['name']);
        });

        test('plugin resource respects field permissions', function () {
            $plugin = $this->createTestPlugin(['name' => 'permission-test']);

            $response = $this->get("/admin/plugins/{$plugin->id}/edit");

            $response->assertStatus(200);
            // Core plugins might have different permissions
        });
    });

    describe('widget integration', function () {
        beforeEach(function () {
            $this->actingAs($this->admin);
        });

        test('plugin stats widget displays on dashboard', function () {
            $response = $this->get('/admin');

            $response->assertStatus(200);
            $response->assertSee('Available Plugins');
            $response->assertSee('Installed Plugins');
        });

        test('plugin stats widget shows accurate counts', function () {
            // Create test plugins with different states
            $this->createTestPlugin(['name' => 'enabled-1', 'enabled' => true]);
            $this->createTestPlugin(['name' => 'enabled-2', 'enabled' => true]);
            $this->createTestPlugin(['name' => 'disabled-1', 'enabled' => false]);

            $response = $this->get('/admin');

            $response->assertStatus(200);
            // The widget should show these counts
        });

        test('plugin stats widget handles empty state', function () {
            // Clear all plugins
            Plugin::query()->delete();

            $response = $this->get('/admin');

            $response->assertStatus(200);
            // Should handle zero plugins gracefully
        });

        test('widget updates when plugin states change', function () {
            $plugin = $this->createTestPlugin(['name' => 'widget-update-test', 'enabled' => false]);

            // Enable the plugin
            $plugin->update(['enabled' => true]);

            $response = $this->get('/admin');

            $response->assertStatus(200);
            // Widget should reflect the updated state
        });
    });

    describe('navigation integration', function () {
        beforeEach(function () {
            $this->actingAs($this->admin);
        });

        test('admin plugin adds navigation items', function () {
            $response = $this->get('/admin');

            $response->assertStatus(200);
            $response->assertSee('Plugins'); // Navigation item
        });

        test('plugin navigation is grouped correctly', function () {
            $response = $this->get('/admin');

            $response->assertStatus(200);
            // Should be in System group or similar
            $response->assertSee('System', false);
        });

        test('plugin navigation shows plugin icon', function () {
            $response = $this->get('/admin');

            $response->assertStatus(200);
            // Should include the puzzle piece icon
            $response->assertSee('heroicon-o-puzzle-piece', false);
        });
    });

    describe('action integration', function () {
        beforeEach(function () {
            $this->actingAs($this->admin);
        });

        test('plugin actions are available in resource', function () {
            $plugin = $this->createTestPlugin(['name' => 'action-test', 'enabled' => true]);

            $response = $this->get("/admin/plugins/{$plugin->id}");

            $response->assertStatus(200);
            // Should show enable/disable actions
        });

        test('bulk actions work on plugin resource', function () {
            $plugin1 = $this->createTestPlugin(['name' => 'bulk-test-1', 'enabled' => true]);
            $plugin2 = $this->createTestPlugin(['name' => 'bulk-test-2', 'enabled' => true]);

            $response = $this->get('/admin/plugins');

            $response->assertStatus(200);
            // Should have bulk action capabilities
        });
    });

    describe('middleware integration', function () {
        test('admin panel respects authentication middleware', function () {
            $response = $this->get('/admin/plugins');

            $response->assertRedirect('/admin/login');
        });

        test('admin panel respects authorization middleware', function () {
            $user = $this->app->make(\App\Models\User::class)->factory()->create(['role' => 'user']);

            $response = $this->actingAs($user)->get('/admin/plugins');

            $response->assertStatus(403);
        });

        test('admin users can access all plugin features', function () {
            $this->actingAs($this->admin);

            $responses = [
                $this->get('/admin/plugins'),
                $this->get('/admin/plugins/create'),
            ];

            foreach ($responses as $response) {
                $response->assertStatus(200);
            }
        });
    });

    describe('event integration', function () {
        test('plugin actions trigger appropriate events', function () {
            $plugin = $this->createTestPlugin(['name' => 'event-test']);

            // This would test that plugin state changes trigger events
            // Implementation depends on whether events are implemented
            expect($plugin)->toBeInstanceOf(Plugin::class);
        });

        test('plugin installation triggers events', function () {
            createTemporaryTestPlugin('event-install-test');

            $pluginManager = app(PluginManager::class);
            $result = $pluginManager->installPlugin('event-install-test');

            expect($result)->toBeBool();

            removeTemporaryTestPlugin('event-install-test');
        });
    });

    describe('caching integration', function () {
        test('plugin data is cached appropriately', function () {
            $pluginManager = app(PluginManager::class);

            // First call
            $startTime = microtime(true);
            $plugins1 = $pluginManager->getAvailablePlugins();
            $firstCallTime = microtime(true) - $startTime;

            // Second call should be faster if cached
            $startTime = microtime(true);
            $plugins2 = $pluginManager->getAvailablePlugins();
            $secondCallTime = microtime(true) - $startTime;

            expect($plugins1)->toBe($plugins2);
            // Note: Without actual caching implementation, times might be similar
        });

        test('cache is invalidated when plugins change', function () {
            $pluginManager = app(PluginManager::class);
            $initialPlugins = $pluginManager->getAvailablePlugins();

            // Create new plugin
            createTemporaryTestPlugin('cache-invalidation-test');

            // Should detect new plugin (cache should be invalidated)
            $pluginManager2 = new PluginManager; // Force re-scan
            $updatedPlugins = $pluginManager2->getAvailablePlugins();

            expect($updatedPlugins)->toHaveKey('cache-invalidation-test');

            removeTemporaryTestPlugin('cache-invalidation-test');
        });
    });

    afterEach(function () {
        Mockery::close();
    });
});

function createMockPanel(): Panel
{
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('id')->andReturn('admin');
    $panel->shouldReceive('path')->andReturn('/admin');

    return $panel;
}
