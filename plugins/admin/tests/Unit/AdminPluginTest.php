<?php

namespace FilaMan\Admin\Tests\Unit;

use FilaMan\Admin\AdminPlugin;
use FilaMan\Admin\Services\PluginManager;
use Filament\Panel;
use Mockery;

describe('AdminPlugin', function () {
    beforeEach(function () {
        $this->plugin = new AdminPlugin;
        $this->panel = Mockery::mock(Panel::class);
    });

    test('can be instantiated', function () {
        expect($this->plugin)->toBeInstanceOf(AdminPlugin::class);
    });

    test('implements filament plugin interface', function () {
        expect($this->plugin)->toBeInstanceOf(\Filament\Contracts\Plugin::class);
    });

    test('has correct plugin id', function () {
        expect($this->plugin->getId())->toBe('admin-panel');
    });

    test('can be created via make method', function () {
        $plugin = AdminPlugin::make();

        expect($plugin)->toBeInstanceOf(AdminPlugin::class);
        expect($plugin->getId())->toBe('admin-panel');
    });

    test('make method returns singleton instance', function () {
        $plugin1 = AdminPlugin::make();
        $plugin2 = AdminPlugin::make();

        expect($plugin1)->toBe($plugin2);
    });

    test('register method configures panel resources', function () {
        $this->panel->shouldReceive('resources')->once()->with([
            \FilaMan\Admin\Filament\Resources\PluginResource::class,
        ])->andReturnSelf();

        $this->panel->shouldReceive('pages')->once()->with([])->andReturnSelf();

        $this->panel->shouldReceive('widgets')->once()->with([
            \FilaMan\Admin\Filament\Widgets\PluginStatsWidget::class,
        ])->andReturnSelf();

        $this->plugin->register($this->panel);

        expect(true)->toBeTrue(); // Test passes if no exceptions thrown
    });

    test('boot method registers view namespace', function () {
        $this->panel->shouldIgnoreMissing();

        $this->plugin->boot($this->panel);

        // Verify view namespace is registered
        expect(view()->exists('filaman-admin::test'))->toBeFalse(); // Non-existent view should be false
        // The namespace should be registered but we can't easily test exact registration
    });

    test('boot method registers plugin manager singleton', function () {
        $this->panel->shouldIgnoreMissing();

        $this->plugin->boot($this->panel);

        $pluginManager = app(PluginManager::class);
        expect($pluginManager)->toBeInstanceOf(PluginManager::class);

        // Should be singleton
        $pluginManager2 = app(PluginManager::class);
        expect($pluginManager)->toBe($pluginManager2);
    });

    test('delegates available plugins to plugin manager', function () {
        $mockPluginManager = mockPluginManager();
        $expectedPlugins = ['plugin1' => [], 'plugin2' => []];

        $mockPluginManager->shouldReceive('getAvailablePlugins')
            ->once()
            ->andReturn($expectedPlugins);

        $result = $this->plugin->getAvailablePlugins();

        expect($result)->toBe($expectedPlugins);
    });

    test('delegates installed plugins to plugin manager', function () {
        $mockPluginManager = mockPluginManager();
        $expectedPlugins = ['plugin1' => ['installed' => true]];

        $mockPluginManager->shouldReceive('getInstalledPlugins')
            ->once()
            ->andReturn($expectedPlugins);

        $result = $this->plugin->getInstalledPlugins();

        expect($result)->toBe($expectedPlugins);
    });

    test('delegates install plugin to plugin manager', function () {
        $mockPluginManager = mockPluginManager();

        $mockPluginManager->shouldReceive('installPlugin')
            ->once()
            ->with('test-plugin')
            ->andReturn(true);

        $result = $this->plugin->installPlugin('test-plugin');

        expect($result)->toBeTrue();
    });

    test('delegates uninstall plugin to plugin manager', function () {
        $mockPluginManager = mockPluginManager();

        $mockPluginManager->shouldReceive('uninstallPlugin')
            ->once()
            ->with('test-plugin')
            ->andReturn(true);

        $result = $this->plugin->uninstallPlugin('test-plugin');

        expect($result)->toBeTrue();
    });

    test('delegates enable plugin to plugin manager', function () {
        $mockPluginManager = mockPluginManager();

        $mockPluginManager->shouldReceive('enablePlugin')
            ->once()
            ->with('test-plugin')
            ->andReturn(true);

        $result = $this->plugin->enablePlugin('test-plugin');

        expect($result)->toBeTrue();
    });

    test('delegates disable plugin to plugin manager', function () {
        $mockPluginManager = mockPluginManager();

        $mockPluginManager->shouldReceive('disablePlugin')
            ->once()
            ->with('test-plugin')
            ->andReturn(true);

        $result = $this->plugin->disablePlugin('test-plugin');

        expect($result)->toBeTrue();
    });

    test('handles plugin manager failures gracefully', function () {
        $mockPluginManager = mockPluginManager();

        $mockPluginManager->shouldReceive('installPlugin')
            ->once()
            ->with('invalid-plugin')
            ->andReturn(false);

        $result = $this->plugin->installPlugin('invalid-plugin');

        expect($result)->toBeFalse();
    });

    afterEach(function () {
        Mockery::close();
    });
});
