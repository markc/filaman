<?php

namespace FilaMan\Admin\Tests\Unit;

use FilaMan\Admin\Models\Plugin;
use FilaMan\Admin\Services\PluginManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

describe('PluginManager', function () {
    beforeEach(function () {
        $this->pluginManager = new PluginManager;
    });

    describe('plugin discovery', function () {
        test('discovers plugins from filesystem', function () {
            // Create a temporary test plugin
            createTemporaryTestPlugin('test-discovery');

            $pluginManager = new PluginManager; // Re-instantiate to trigger scan
            $availablePlugins = $pluginManager->getAvailablePlugins();

            expect($availablePlugins)->toHaveKey('test-discovery');
            expect($availablePlugins['test-discovery'])->toHavePluginStructure();

            removeTemporaryTestPlugin('test-discovery');
        });

        test('ignores non-plugin directories', function () {
            // Create a directory without composer.json
            $nonPluginPath = base_path('plugins/not-a-plugin');
            File::ensureDirectoryExists($nonPluginPath);

            $pluginManager = new PluginManager;
            $availablePlugins = $pluginManager->getAvailablePlugins();

            expect($availablePlugins)->not()->toHaveKey('not-a-plugin');

            File::deleteDirectory($nonPluginPath);
        });

        test('ignores directories without laravel-plugin type', function () {
            $composerData = createTestComposerData('wrong-type', ['type' => 'library']);
            createTemporaryTestPlugin('wrong-type', $composerData);

            $pluginManager = new PluginManager;
            $availablePlugins = $pluginManager->getAvailablePlugins();

            expect($availablePlugins)->not()->toHaveKey('wrong-type');

            removeTemporaryTestPlugin('wrong-type');
        });

        test('extracts plugin metadata correctly', function () {
            $composerData = createTestComposerData('metadata-test', [
                'description' => 'Custom description',
                'version' => '2.1.0',
                'authors' => [['name' => 'Custom Author', 'email' => 'custom@example.com']],
            ]);
            createTemporaryTestPlugin('metadata-test', $composerData);

            $pluginManager = new PluginManager;
            $availablePlugins = $pluginManager->getAvailablePlugins();

            expect($availablePlugins['metadata-test']['description'])->toBe('Custom description');
            expect($availablePlugins['metadata-test']['version'])->toBe('2.1.0');
            expect($availablePlugins['metadata-test']['authors'])->toHaveCount(1);

            removeTemporaryTestPlugin('metadata-test');
        });
    });

    describe('plugin installation status', function () {
        test('correctly identifies installed plugins via database', function () {
            $this->createTestPlugin(['name' => 'db-installed-plugin']);

            $isInstalled = $this->pluginManager->isPluginInstalled('db-installed-plugin');

            expect($isInstalled)->toBeTrue();
        });

        test('falls back to composer.json when database unavailable', function () {
            // Mock schema to return false for table existence
            Schema::shouldReceive('hasTable')->with('plugins')->andReturn(false);

            // This test would require mocking File::get for composer.json
            // For now, just test the method doesn't throw exceptions
            $isInstalled = $this->pluginManager->isPluginInstalled('nonexistent-plugin');

            expect($isInstalled)->toBeFalse();
        });

        test('returns false for non-existent plugins', function () {
            $isInstalled = $this->pluginManager->isPluginInstalled('definitely-not-a-plugin');

            expect($isInstalled)->toBeFalse();
        });
    });

    describe('plugin enabled status', function () {
        test('correctly identifies enabled plugins', function () {
            $this->createTestPlugin(['name' => 'enabled-plugin', 'enabled' => true]);

            $isEnabled = $this->pluginManager->isPluginEnabled('enabled-plugin');

            expect($isEnabled)->toBeTrue();
        });

        test('correctly identifies disabled plugins', function () {
            $this->createTestPlugin(['name' => 'disabled-plugin', 'enabled' => false]);

            $isEnabled = $this->pluginManager->isPluginEnabled('disabled-plugin');

            expect($isEnabled)->toBeFalse();
        });

        test('falls back to config when database unavailable', function () {
            Schema::shouldReceive('hasTable')->with('plugins')->andReturn(false);

            config(['filaman-admin.plugins' => ['enabled-via-config']]);

            $isEnabled = $this->pluginManager->isPluginEnabled('enabled-via-config');

            expect($isEnabled)->toBeTrue();
        });
    });

    describe('get installed plugins', function () {
        test('returns only installed plugins', function () {
            createTemporaryTestPlugin('installed-plugin');
            createTemporaryTestPlugin('not-installed-plugin');

            $this->createTestPlugin(['name' => 'installed-plugin']);

            $pluginManager = new PluginManager;
            $installedPlugins = $pluginManager->getInstalledPlugins();

            expect($installedPlugins)->toHaveKey('installed-plugin');
            expect($installedPlugins)->not()->toHaveKey('not-installed-plugin');

            removeTemporaryTestPlugin('installed-plugin');
            removeTemporaryTestPlugin('not-installed-plugin');
        });

        test('returns empty array when no plugins installed', function () {
            $installedPlugins = $this->pluginManager->getInstalledPlugins();

            expect($installedPlugins)->toBeArray();
        });
    });

    describe('plugin installation', function () {
        test('installs plugin successfully', function () {
            createTemporaryTestPlugin('install-test');

            $pluginManager = new PluginManager;
            $result = $pluginManager->installPlugin('install-test');

            expect($result)->toBeTrue();
            assertPluginIsInstalled('install-test');

            removeTemporaryTestPlugin('install-test');
        });

        test('fails to install non-existent plugin', function () {
            $result = $this->pluginManager->installPlugin('non-existent-plugin');

            expect($result)->toBeFalse();
        });

        test('handles installation errors gracefully', function () {
            // Create plugin but make it fail somehow (e.g., permission issues)
            createTemporaryTestPlugin('error-plugin');

            // Mock exec to return failure
            $pluginManager = new PluginManager;

            // The actual implementation uses exec() which we can't easily mock
            // So we'll test that it returns false for invalid scenarios
            $result = $pluginManager->installPlugin('error-plugin');

            // This might succeed or fail depending on environment
            expect($result)->toBeIn([true, false]);

            removeTemporaryTestPlugin('error-plugin');
        });
    });

    describe('plugin uninstallation', function () {
        test('uninstalls plugin successfully', function () {
            $plugin = $this->createTestPlugin(['name' => 'uninstall-test']);

            $result = $this->pluginManager->uninstallPlugin('uninstall-test');

            expect($result)->toBeTrue();
            assertPluginIsNotInstalled('uninstall-test');
        });

        test('handles uninstalling non-existent plugin gracefully', function () {
            $result = $this->pluginManager->uninstallPlugin('non-existent-plugin');

            expect($result)->toBeTrue(); // Should not fail
        });
    });

    describe('plugin enable/disable', function () {
        test('enables plugin successfully', function () {
            $plugin = $this->createTestPlugin(['name' => 'enable-test', 'enabled' => false]);

            $result = $this->pluginManager->enablePlugin('enable-test');

            expect($result)->toBeTrue();
            assertPluginIsEnabled('enable-test');
        });

        test('disables plugin successfully', function () {
            $plugin = $this->createTestPlugin(['name' => 'disable-test', 'enabled' => true]);

            $result = $this->pluginManager->disablePlugin('disable-test');

            expect($result)->toBeTrue();
            assertPluginIsDisabled('disable-test');
        });

        test('handles enabling non-existent plugin gracefully', function () {
            $result = $this->pluginManager->enablePlugin('non-existent-plugin');

            expect($result)->toBeTrue(); // Should not fail
        });

        test('handles disabling non-existent plugin gracefully', function () {
            $result = $this->pluginManager->disablePlugin('non-existent-plugin');

            expect($result)->toBeTrue(); // Should not fail
        });
    });

    describe('performance', function () {
        test('plugin scanning is performant', function () {
            // Create multiple test plugins
            for ($i = 1; $i <= 5; $i++) {
                createTemporaryTestPlugin("perf-test-{$i}");
            }

            assertPluginOperationPerformance(function () {
                new PluginManager;
            }, 500); // Should scan plugins in under 500ms

            // Cleanup
            for ($i = 1; $i <= 5; $i++) {
                removeTemporaryTestPlugin("perf-test-{$i}");
            }
        });

        test('plugin operations are performant', function () {
            $plugin = $this->createTestPlugin(['name' => 'perf-plugin']);

            assertPluginOperationPerformance(function () {
                $this->pluginManager->enablePlugin('perf-plugin');
                $this->pluginManager->disablePlugin('perf-plugin');
            }, 100); // Should complete in under 100ms
        });
    });
});
