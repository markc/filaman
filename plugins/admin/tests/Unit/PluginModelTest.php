<?php

namespace FilaMan\Admin\Tests\Unit;

use FilaMan\Admin\Models\Plugin;

describe('Plugin Model', function () {
    test('can be created with factory', function () {
        $plugin = Plugin::create([
            'name' => 'test-plugin',
            'version' => '1.0.0',
            'enabled' => true,
        ]);
        
        expect($plugin)->toBeInstanceOf(Plugin::class);
        expect($plugin->name)->toBe('test-plugin');
        expect($plugin->version)->toBe('1.0.0');
    });

    test('has correct fillable attributes', function () {
        $plugin = new Plugin;
        
        expect($plugin->getFillable())->toContain(
            'name',
            'display_name', 
            'description',
            'version',
            'enabled',
            'settings',
            'metadata',
            'author',
            'url'
        );
    });

    test('casts attributes correctly', function () {
        $plugin = new Plugin;
        
        expect($plugin->getCasts())->toHaveKey('enabled');
        expect($plugin->getCasts())->toHaveKey('settings');
        expect($plugin->getCasts())->toHaveKey('metadata');
        expect($plugin->getCasts()['enabled'])->toBe('boolean');
        expect($plugin->getCasts()['settings'])->toBe('array');
        expect($plugin->getCasts()['metadata'])->toBe('array');
    });

    describe('display name attribute', function () {
        test('returns display_name when set', function () {
            $plugin = Plugin::create([
                'name' => 'test-plugin',
                'display_name' => 'Custom Display Name'
            ]);
            
            expect($plugin->display_name)->toBe('Custom Display Name');
        });

        test('generates display name from plugin name when not set', function () {
            $plugin = Plugin::create([
                'name' => 'my-awesome-plugin',
                'display_name' => null
            ]);
            
            expect($plugin->display_name)->toBe('My Awesome Plugin');
        });

        test('removes plugin suffix from generated display name', function () {
            $plugin = Plugin::create([
                'name' => 'blog-plugin',
                'display_name' => null
            ]);
            
            expect($plugin->display_name)->toBe('Blog');
        });

        test('handles single word plugin names', function () {
            $plugin = Plugin::create([
                'name' => 'blog',
                'display_name' => null
            ]);
            
            expect($plugin->display_name)->toBe('Blog');
        });

        test('handles plugin names with underscores', function () {
            $plugin = Plugin::create([
                'name' => 'user_management',
                'display_name' => null
            ]);
            
            expect($plugin->display_name)->toBe('User Management');
        });
    });

    describe('core plugin detection', function () {
        test('identifies admin as core plugin', function () {
            $plugin = Plugin::create(['name' => 'admin']);
            
            expect($plugin->isCorePlugin())->toBeTrue();
        });

        test('identifies non-core plugins correctly', function () {
            $plugin = Plugin::create(['name' => 'blog']);
            
            expect($plugin->isCorePlugin())->toBeFalse();
        });

        test('identifies custom plugins as non-core', function () {
            $plugin = Plugin::create(['name' => 'my-custom-plugin']);
            
            expect($plugin->isCorePlugin())->toBeFalse();
        });
    });

    describe('configuration management', function () {
        test('can get entire configuration', function () {
            $settings = ['key1' => 'value1', 'key2' => 'value2'];
            $plugin = Plugin::create(['settings' => $settings]);
            
            expect($plugin->getConfig())->toBe($settings);
        });

        test('can get specific configuration key', function () {
            $settings = ['feature_enabled' => true, 'max_items' => 100];
            $plugin = Plugin::create(['settings' => $settings]);
            
            expect($plugin->getConfig('feature_enabled'))->toBeTrue();
            expect($plugin->getConfig('max_items'))->toBe(100);
        });

        test('returns default value for non-existent key', function () {
            $plugin = Plugin::create(['settings' => []]);
            
            expect($plugin->getConfig('non_existent_key', 'default'))->toBe('default');
        });

        test('can get nested configuration values', function () {
            $settings = [
                'ui' => [
                    'theme' => 'dark',
                    'sidebar' => ['collapsed' => true]
                ]
            ];
            $plugin = Plugin::create(['settings' => $settings]);
            
            expect($plugin->getConfig('ui.theme'))->toBe('dark');
            expect($plugin->getConfig('ui.sidebar.collapsed'))->toBeTrue();
        });

        test('can set configuration values', function () {
            $plugin = Plugin::create(['settings' => []]);
            
            $plugin->setConfig('new_key', 'new_value');
            
            expect($plugin->fresh()->getConfig('new_key'))->toBe('new_value');
        });

        test('can set nested configuration values', function () {
            $plugin = Plugin::create(['settings' => []]);
            
            $plugin->setConfig('ui.theme', 'light');
            
            expect($plugin->fresh()->getConfig('ui.theme'))->toBe('light');
        });

        test('preserves existing settings when setting new ones', function () {
            $plugin = Plugin::create(['settings' => ['existing' => 'value']]);
            
            $plugin->setConfig('new_key', 'new_value');
            
            $freshPlugin = $plugin->fresh();
            expect($freshPlugin->getConfig('existing'))->toBe('value');
            expect($freshPlugin->getConfig('new_key'))->toBe('new_value');
        });
    });

    describe('query scopes', function () {
        test('enabled scope returns only enabled plugins', function () {
            Plugin::create(['name' => 'enabled-plugin', 'enabled' => true]);
            Plugin::create(['name' => 'disabled-plugin', 'enabled' => false]);
            
            $enabledPlugins = Plugin::enabled()->get();
            
            expect($enabledPlugins)->toHaveCount(1);
            expect($enabledPlugins->first()->name)->toBe('enabled-plugin');
        });

        test('disabled scope returns only disabled plugins', function () {
            Plugin::create(['name' => 'enabled-plugin', 'enabled' => true]);
            Plugin::create(['name' => 'disabled-plugin', 'enabled' => false]);
            
            $disabledPlugins = Plugin::disabled()->get();
            
            expect($disabledPlugins)->toHaveCount(1);
            expect($disabledPlugins->first()->name)->toBe('disabled-plugin');
        });

        test('scopes can be chained', function () {
            Plugin::create(['name' => 'blog-enabled', 'enabled' => true]);
            Plugin::create(['name' => 'blog-disabled', 'enabled' => false]);
            Plugin::create(['name' => 'admin-enabled', 'enabled' => true]);
            
            $blogEnabledPlugins = Plugin::enabled()
                ->where('name', 'like', 'blog%')
                ->get();
            
            expect($blogEnabledPlugins)->toHaveCount(1);
            expect($blogEnabledPlugins->first()->name)->toBe('blog-enabled');
        });
    });

    describe('data validation', function () {
        test('requires name field', function () {
            expect(function () {
                Plugin::create(['name' => null]);
            })->toThrow(Exception::class);
        });

        test('name must be unique', function () {
            Plugin::create(['name' => 'unique-plugin']);
            
            expect(function () {
                Plugin::create(['name' => 'unique-plugin']);
            })->toThrow(Exception::class);
        });

        test('enabled defaults to true', function () {
            $plugin = Plugin::create();
            
            expect($plugin->enabled)->toBeTrue();
        });

        test('version should be valid', function () {
            $plugin = Plugin::create(['version' => '1.2.3']);
            
            expect($plugin->version)->toBeValidVersion();
        });

        test('settings can be null', function () {
            $plugin = Plugin::create(['settings' => null]);
            
            expect($plugin->settings)->toBeNull();
            expect($plugin->getConfig())->toBeArray(); // Should return empty array
        });

        test('metadata can be null', function () {
            $plugin = Plugin::create(['metadata' => null]);
            
            expect($plugin->metadata)->toBeNull();
        });
    });

    describe('relationship tests', function () {
        test('model uses HasFactory trait', function () {
            $plugin = new Plugin;
            $traits = class_uses($plugin);
            expect($traits)->toHaveKey(\Illuminate\Database\Eloquent\Factories\HasFactory::class);
        });
    });
});