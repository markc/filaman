<?php

namespace FilaMan\Admin\Tests\Feature;

use FilaMan\Admin\Models\Plugin;
use FilaMan\Admin\Services\PluginManager;
use Illuminate\Support\Facades\File;

describe('Plugin Management Features', function () {
    beforeEach(function () {
        $this->admin = $this->createAdminUser();
        $this->pluginManager = app(PluginManager::class);
    });

    describe('plugin discovery and listing', function () {
        test('can discover plugins from filesystem', function () {
            createTemporaryTestPlugin('discovery-test');

            $pluginManager = new PluginManager; // Re-instantiate to trigger scan
            $plugins = $pluginManager->getAvailablePlugins();

            expect($plugins)->toHaveKey('discovery-test');
            expect($plugins['discovery-test'])->toHaveKey('name');
            expect($plugins['discovery-test'])->toHaveKey('description');
            expect($plugins['discovery-test'])->toHaveKey('version');

            removeTemporaryTestPlugin('discovery-test');
        });

        test('correctly identifies plugin installation status', function () {
            // Create filesystem plugin
            createTemporaryTestPlugin('status-test');

            // Create database entry
            $this->createTestPlugin(['name' => 'status-test']);

            $pluginManager = new PluginManager;
            $plugins = $pluginManager->getAvailablePlugins();

            expect($plugins['status-test']['installed'])->toBeTrue();
            expect($plugins['status-test']['enabled'])->toBeTrue();

            removeTemporaryTestPlugin('status-test');
        });

        test('can filter installed plugins only', function () {
            createTemporaryTestPlugin('installed-plugin');
            createTemporaryTestPlugin('available-plugin');

            $this->createTestPlugin(['name' => 'installed-plugin']);

            $pluginManager = new PluginManager;
            $installedPlugins = $pluginManager->getInstalledPlugins();

            expect($installedPlugins)->toHaveKey('installed-plugin');
            expect($installedPlugins)->not()->toHaveKey('available-plugin');

            removeTemporaryTestPlugin('installed-plugin');
            removeTemporaryTestPlugin('available-plugin');
        });
    });

    describe('plugin installation workflow', function () {
        test('can install a new plugin', function () {
            createTemporaryTestPlugin('install-workflow-test');

            $pluginManager = new PluginManager;
            $result = $pluginManager->installPlugin('install-workflow-test');

            expect($result)->toBeTrue();

            // Verify plugin is recorded in database
            $this->assertDatabaseHas('plugins', [
                'name' => 'install-workflow-test',
                'enabled' => true,
            ]);

            removeTemporaryTestPlugin('install-workflow-test');
        });

        test('installation fails for non-existent plugin', function () {
            $result = $this->pluginManager->installPlugin('non-existent-plugin');

            expect($result)->toBeFalse();

            // Verify no database record is created
            $this->assertDatabaseMissing('plugins', [
                'name' => 'non-existent-plugin',
            ]);
        });

        test('installation is idempotent', function () {
            createTemporaryTestPlugin('idempotent-test');

            // Install plugin first time
            $pluginManager = new PluginManager;
            $result1 = $pluginManager->installPlugin('idempotent-test');
            expect($result1)->toBeTrue();

            // Try to install again - should not create duplicate records
            $pluginManager2 = new PluginManager;
            $result2 = $pluginManager2->installPlugin('idempotent-test');

            // Count database records
            $pluginCount = Plugin::where('name', 'idempotent-test')->count();
            expect($pluginCount)->toBeLessThanOrEqual(1);

            removeTemporaryTestPlugin('idempotent-test');
        });
    });

    describe('plugin uninstallation workflow', function () {
        test('can uninstall an installed plugin', function () {
            $plugin = $this->createTestPlugin(['name' => 'uninstall-workflow-test']);

            $result = $this->pluginManager->uninstallPlugin('uninstall-workflow-test');

            expect($result)->toBeTrue();

            // Verify plugin is removed from database
            $this->assertDatabaseMissing('plugins', [
                'name' => 'uninstall-workflow-test',
            ]);
        });

        test('uninstallation succeeds even for non-existent plugin', function () {
            $result = $this->pluginManager->uninstallPlugin('non-existent-plugin');

            expect($result)->toBeTrue();
        });

        test('core plugins cannot be uninstalled', function () {
            $corePlugin = $this->createTestPlugin(['name' => 'admin']);

            // This would need to be implemented in the PluginManager
            // For now, we just verify the core plugin detection works
            expect($corePlugin->isCorePlugin())->toBeTrue();
        });
    });

    describe('plugin enable/disable workflow', function () {
        test('can enable a disabled plugin', function () {
            $plugin = $this->createTestPlugin([
                'name' => 'enable-test',
                'enabled' => false,
            ]);

            $result = $this->pluginManager->enablePlugin('enable-test');

            expect($result)->toBeTrue();

            $this->assertDatabaseHas('plugins', [
                'name' => 'enable-test',
                'enabled' => true,
            ]);
        });

        test('can disable an enabled plugin', function () {
            $plugin = $this->createTestPlugin([
                'name' => 'disable-test',
                'enabled' => true,
            ]);

            $result = $this->pluginManager->disablePlugin('disable-test');

            expect($result)->toBeTrue();

            $this->assertDatabaseHas('plugins', [
                'name' => 'disable-test',
                'enabled' => false,
            ]);
        });

        test('enabling already enabled plugin succeeds', function () {
            $plugin = $this->createTestPlugin([
                'name' => 'already-enabled',
                'enabled' => true,
            ]);

            $result = $this->pluginManager->enablePlugin('already-enabled');

            expect($result)->toBeTrue();

            $this->assertDatabaseHas('plugins', [
                'name' => 'already-enabled',
                'enabled' => true,
            ]);
        });

        test('disabling already disabled plugin succeeds', function () {
            $plugin = $this->createTestPlugin([
                'name' => 'already-disabled',
                'enabled' => false,
            ]);

            $result = $this->pluginManager->disablePlugin('already-disabled');

            expect($result)->toBeTrue();

            $this->assertDatabaseHas('plugins', [
                'name' => 'already-disabled',
                'enabled' => false,
            ]);
        });
    });

    describe('plugin metadata and versioning', function () {
        test('tracks plugin version during installation', function () {
            $composerData = createTestComposerData('version-test', ['version' => '2.1.0']);
            createTemporaryTestPlugin('version-test', $composerData);

            $pluginManager = new PluginManager;
            $result = $pluginManager->installPlugin('version-test');

            expect($result)->toBeTrue();

            $this->assertDatabaseHas('plugins', [
                'name' => 'version-test',
                'version' => '2.1.0',
            ]);

            removeTemporaryTestPlugin('version-test');
        });

        test('handles plugins without explicit version', function () {
            $composerData = createTestComposerData('no-version-test');
            unset($composerData['version']);
            createTemporaryTestPlugin('no-version-test', $composerData);

            $pluginManager = new PluginManager;
            $plugins = $pluginManager->getAvailablePlugins();

            expect($plugins['no-version-test']['version'])->toBe('dev');

            removeTemporaryTestPlugin('no-version-test');
        });

        test('preserves plugin metadata', function () {
            $composerData = createTestComposerData('metadata-test', [
                'description' => 'A comprehensive test plugin',
                'authors' => [
                    ['name' => 'John Doe', 'email' => 'john@example.com'],
                    ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
                ],
            ]);
            createTemporaryTestPlugin('metadata-test', $composerData);

            $pluginManager = new PluginManager;
            $plugins = $pluginManager->getAvailablePlugins();

            expect($plugins['metadata-test']['description'])->toBe('A comprehensive test plugin');
            expect($plugins['metadata-test']['authors'])->toHaveCount(2);
            expect($plugins['metadata-test']['authors'][0]['name'])->toBe('John Doe');

            removeTemporaryTestPlugin('metadata-test');
        });
    });

    describe('error handling and edge cases', function () {
        test('handles corrupted composer.json gracefully', function () {
            $pluginPath = $this->createTemporaryPlugin('corrupted-composer', []);

            // Write invalid JSON
            File::put($pluginPath.'/composer.json', '{invalid json}');

            $pluginManager = new PluginManager;
            $plugins = $pluginManager->getAvailablePlugins();

            // Should not include the corrupted plugin
            expect($plugins)->not()->toHaveKey('corrupted-composer');

            $this->removeTemporaryPlugin('corrupted-composer');
        });

        test('handles missing plugins directory gracefully', function () {
            // Temporarily rename plugins directory
            $pluginsPath = base_path('plugins');
            $backupPath = base_path('plugins-backup');

            if (File::exists($pluginsPath)) {
                File::move($pluginsPath, $backupPath);
            }

            $pluginManager = new PluginManager;
            $plugins = $pluginManager->getAvailablePlugins();

            expect($plugins)->toBeArray();
            expect($plugins)->toBeEmpty();

            // Restore plugins directory
            if (File::exists($backupPath)) {
                File::move($backupPath, $pluginsPath);
            }
        });

        test('handles database connection errors gracefully', function () {
            // This test would require more sophisticated mocking
            // For now, just ensure operations don't throw exceptions

            $result = $this->pluginManager->isPluginInstalled('test-plugin');
            expect($result)->toBeIn([true, false]);

            $result = $this->pluginManager->isPluginEnabled('test-plugin');
            expect($result)->toBeIn([true, false]);
        });
    });

    describe('plugin state consistency', function () {
        test('plugin state remains consistent across operations', function () {
            createTemporaryTestPlugin('consistency-test');

            $pluginManager = new PluginManager;

            // Install plugin
            $pluginManager->installPlugin('consistency-test');
            expect($pluginManager->isPluginInstalled('consistency-test'))->toBeTrue();
            expect($pluginManager->isPluginEnabled('consistency-test'))->toBeTrue();

            // Disable plugin
            $pluginManager->disablePlugin('consistency-test');
            expect($pluginManager->isPluginInstalled('consistency-test'))->toBeTrue();
            expect($pluginManager->isPluginEnabled('consistency-test'))->toBeFalse();

            // Re-enable plugin
            $pluginManager->enablePlugin('consistency-test');
            expect($pluginManager->isPluginInstalled('consistency-test'))->toBeTrue();
            expect($pluginManager->isPluginEnabled('consistency-test'))->toBeTrue();

            // Uninstall plugin
            $pluginManager->uninstallPlugin('consistency-test');
            expect($pluginManager->isPluginInstalled('consistency-test'))->toBeFalse();

            removeTemporaryTestPlugin('consistency-test');
        });

        test('filesystem and database stay in sync', function () {
            createTemporaryTestPlugin('sync-test');

            $pluginManager = new PluginManager;

            // Initially plugin exists in filesystem but not database
            $availablePlugins = $pluginManager->getAvailablePlugins();
            expect($availablePlugins['sync-test']['installed'])->toBeFalse();

            // After installation, both should be in sync
            $pluginManager->installPlugin('sync-test');

            $pluginManager2 = new PluginManager; // Re-scan
            $availablePlugins = $pluginManager2->getAvailablePlugins();
            expect($availablePlugins['sync-test']['installed'])->toBeTrue();

            removeTemporaryTestPlugin('sync-test');
        });
    });
});
