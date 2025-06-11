<?php

namespace FilaMan\Admin\Tests\Integration;

use FilaMan\Admin\Models\Plugin;
use Illuminate\Support\Facades\Schema;

describe('Database Integration', function () {
    describe('plugins table schema', function () {
        test('plugins table exists', function () {
            expect(Schema::hasTable('plugins'))->toBeTrue();
        });

        test('plugins table has required columns', function () {
            $columns = Schema::getColumnListing('plugins');

            expect($columns)->toContain('id');
            expect($columns)->toContain('name');
            expect($columns)->toContain('display_name');
            expect($columns)->toContain('description');
            expect($columns)->toContain('version');
            expect($columns)->toContain('enabled');
            expect($columns)->toContain('settings');
            expect($columns)->toContain('metadata');
            expect($columns)->toContain('author');
            expect($columns)->toContain('url');
            expect($columns)->toContain('created_at');
            expect($columns)->toContain('updated_at');
        });

        test('plugins table has correct column types', function () {
            $nameColumn = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableDetails('plugins')
                ->getColumn('name');

            expect($nameColumn->getType()->getName())->toBe('string');

            $enabledColumn = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableDetails('plugins')
                ->getColumn('enabled');

            expect($enabledColumn->getType()->getName())->toBeIn(['boolean', 'smallint']);
        });

        test('plugins table has appropriate indexes', function () {
            $indexes = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableIndexes('plugins');

            // Should have primary key
            expect($indexes)->toHaveKey('primary');

            // Should have unique constraint on name
            $hasNameIndex = false;
            foreach ($indexes as $index) {
                if ($index->isUnique() && in_array('name', $index->getColumns())) {
                    $hasNameIndex = true;
                    break;
                }
            }
            expect($hasNameIndex)->toBeTrue();
        });
    });

    describe('plugin model database operations', function () {
        test('can create plugin record', function () {
            $plugin = Plugin::factory()->create([
                'name' => 'database-test-plugin',
            ]);

            expect($plugin)->toBeInstanceOf(Plugin::class);
            expect($plugin->id)->toBeInt();
            expect($plugin->name)->toBe('database-test-plugin');

            $this->assertDatabaseHas('plugins', [
                'name' => 'database-test-plugin',
            ]);
        });

        test('can update plugin record', function () {
            $plugin = Plugin::factory()->create(['enabled' => true]);

            $plugin->update(['enabled' => false]);

            expect($plugin->fresh()->enabled)->toBeFalse();

            $this->assertDatabaseHas('plugins', [
                'id' => $plugin->id,
                'enabled' => false,
            ]);
        });

        test('can delete plugin record', function () {
            $plugin = Plugin::factory()->create();
            $pluginId = $plugin->id;

            $plugin->delete();

            $this->assertDatabaseMissing('plugins', [
                'id' => $pluginId,
            ]);
        });

        test('enforces unique constraint on name', function () {
            Plugin::factory()->create(['name' => 'unique-test']);

            expect(function () {
                Plugin::factory()->create(['name' => 'unique-test']);
            })->toThrow();
        });

        test('handles null values correctly', function () {
            $plugin = Plugin::factory()->create([
                'settings' => null,
                'metadata' => null,
                'author' => null,
                'url' => null,
            ]);

            expect($plugin->settings)->toBeNull();
            expect($plugin->metadata)->toBeNull();
            expect($plugin->author)->toBeNull();
            expect($plugin->url)->toBeNull();
        });
    });

    describe('data integrity and constraints', function () {
        test('name field cannot be null', function () {
            expect(function () {
                Plugin::factory()->create(['name' => null]);
            })->toThrow();
        });

        test('enabled field defaults to true', function () {
            $plugin = Plugin::factory()->create();

            expect($plugin->enabled)->toBeTrue();
        });

        test('timestamps are automatically managed', function () {
            $plugin = Plugin::factory()->create();

            expect($plugin->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
            expect($plugin->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);

            $originalUpdatedAt = $plugin->updated_at;

            // Wait a moment and update
            sleep(1);
            $plugin->update(['description' => 'Updated description']);

            expect($plugin->fresh()->updated_at->isAfter($originalUpdatedAt))->toBeTrue();
        });

        test('json fields are properly cast', function () {
            $settings = ['feature_enabled' => true, 'max_items' => 100];
            $metadata = ['tags' => ['test', 'plugin'], 'license' => 'MIT'];

            $plugin = Plugin::factory()->create([
                'settings' => $settings,
                'metadata' => $metadata,
            ]);

            // Should be arrays in memory
            expect($plugin->settings)->toBe($settings);
            expect($plugin->metadata)->toBe($metadata);

            // Should be JSON in database
            $rawPlugin = \DB::table('plugins')->where('id', $plugin->id)->first();
            expect(json_decode($rawPlugin->settings, true))->toBe($settings);
            expect(json_decode($rawPlugin->metadata, true))->toBe($metadata);
        });
    });

    describe('query scopes', function () {
        beforeEach(function () {
            Plugin::factory()->create(['name' => 'enabled-plugin', 'enabled' => true]);
            Plugin::factory()->create(['name' => 'disabled-plugin', 'enabled' => false]);
            Plugin::factory()->create(['name' => 'another-enabled', 'enabled' => true]);
        });

        test('enabled scope returns only enabled plugins', function () {
            $enabledPlugins = Plugin::enabled()->get();

            expect($enabledPlugins)->toHaveCount(2);
            expect($enabledPlugins->pluck('name')->toArray())->toContain('enabled-plugin', 'another-enabled');
            expect($enabledPlugins->pluck('name')->toArray())->not()->toContain('disabled-plugin');
        });

        test('disabled scope returns only disabled plugins', function () {
            $disabledPlugins = Plugin::disabled()->get();

            expect($disabledPlugins)->toHaveCount(1);
            expect($disabledPlugins->first()->name)->toBe('disabled-plugin');
        });

        test('scopes can be combined with other conditions', function () {
            $enabledPluginStartingWithAnother = Plugin::enabled()
                ->where('name', 'like', 'another%')
                ->get();

            expect($enabledPluginStartingWithAnother)->toHaveCount(1);
            expect($enabledPluginStartingWithAnother->first()->name)->toBe('another-enabled');
        });
    });

    describe('configuration persistence', function () {
        test('configuration changes are persisted', function () {
            $plugin = Plugin::factory()->create();

            $plugin->setConfig('test_setting', 'test_value');

            // Verify in current instance
            expect($plugin->getConfig('test_setting'))->toBe('test_value');

            // Verify in fresh instance from database
            $freshPlugin = Plugin::find($plugin->id);
            expect($freshPlugin->getConfig('test_setting'))->toBe('test_value');
        });

        test('nested configuration is persisted correctly', function () {
            $plugin = Plugin::factory()->create();

            $plugin->setConfig('ui.theme', 'dark');
            $plugin->setConfig('ui.sidebar.width', 250);

            $freshPlugin = Plugin::find($plugin->id);
            expect($freshPlugin->getConfig('ui.theme'))->toBe('dark');
            expect($freshPlugin->getConfig('ui.sidebar.width'))->toBe(250);
        });

        test('configuration updates preserve existing settings', function () {
            $plugin = Plugin::factory()->create([
                'settings' => ['existing_key' => 'existing_value'],
            ]);

            $plugin->setConfig('new_key', 'new_value');

            $freshPlugin = Plugin::find($plugin->id);
            expect($freshPlugin->getConfig('existing_key'))->toBe('existing_value');
            expect($freshPlugin->getConfig('new_key'))->toBe('new_value');
        });
    });

    describe('database performance', function () {
        test('plugin queries are performant', function () {
            // Create multiple plugins for performance testing
            Plugin::factory()->count(50)->create();

            $startTime = microtime(true);

            $enabledCount = Plugin::enabled()->count();
            $disabledCount = Plugin::disabled()->count();
            $totalCount = Plugin::count();

            $endTime = microtime(true);
            $queryTime = ($endTime - $startTime) * 1000;

            expect($queryTime)->toBeLessThan(100, "Plugin queries took {$queryTime}ms");
            expect($enabledCount + $disabledCount)->toBe($totalCount);
        });

        test('plugin updates are performant', function () {
            $plugins = Plugin::factory()->count(10)->create();

            $startTime = microtime(true);

            foreach ($plugins as $plugin) {
                $plugin->update(['enabled' => ! $plugin->enabled]);
            }

            $endTime = microtime(true);
            $updateTime = ($endTime - $startTime) * 1000;

            expect($updateTime)->toBeLessThan(500, "Plugin updates took {$updateTime}ms");
        });

        test('bulk operations are efficient', function () {
            $startTime = microtime(true);

            // Bulk disable all plugins
            Plugin::query()->update(['enabled' => false]);

            $endTime = microtime(true);
            $bulkUpdateTime = ($endTime - $startTime) * 1000;

            expect($bulkUpdateTime)->toBeLessThan(100, "Bulk update took {$bulkUpdateTime}ms");

            // Verify all plugins are disabled
            expect(Plugin::enabled()->count())->toBe(0);
        });
    });

    describe('data migration compatibility', function () {
        test('can handle migration rollbacks', function () {
            // This test ensures the migration can be rolled back safely
            $this->artisan('migrate:rollback', ['--path' => 'plugins/admin/database/migrations']);

            expect(Schema::hasTable('plugins'))->toBeFalse();

            // Re-run migration
            $this->artisan('migrate', ['--path' => 'plugins/admin/database/migrations']);

            expect(Schema::hasTable('plugins'))->toBeTrue();
        });

        test('migration is idempotent', function () {
            // Running migration again should not cause errors
            $this->artisan('migrate', ['--path' => 'plugins/admin/database/migrations']);

            expect(Schema::hasTable('plugins'))->toBeTrue();
        });
    });

    describe('factory integration', function () {
        test('factory creates valid plugin data', function () {
            $plugin = Plugin::factory()->create();

            expect($plugin->name)->toBeValidPluginName();
            expect($plugin->version)->toBeValidVersion();
            expect($plugin->enabled)->toBeBool();
        });

        test('factory can create plugins with custom attributes', function () {
            $plugin = Plugin::factory()->create([
                'name' => 'custom-factory-plugin',
                'enabled' => false,
                'settings' => ['custom' => 'setting'],
            ]);

            expect($plugin->name)->toBe('custom-factory-plugin');
            expect($plugin->enabled)->toBeFalse();
            expect($plugin->getConfig('custom'))->toBe('setting');
        });

        test('factory creates unique plugins', function () {
            $plugins = Plugin::factory()->count(5)->create();

            $names = $plugins->pluck('name')->toArray();
            $uniqueNames = array_unique($names);

            expect(count($names))->toBe(count($uniqueNames));
        });
    });
});
