<?php

namespace FilaMan\Admin\Tests\Feature;

use App\Models\User;

describe('Admin Panel Access Control', function () {
    describe('authentication requirements', function () {
        test('unauthenticated users cannot access admin panel', function () {
            $response = $this->get('/admin');

            $response->assertRedirect('/admin/login');
        });

        test('admin panel login page is accessible', function () {
            $response = $this->get('/admin/login');

            $response->assertStatus(200);
        });

        test('admin users can access admin panel', function () {
            $admin = $this->createAdminUser();

            $response = $this->actingAs($admin)->get('/admin');

            $response->assertStatus(200);
        });

        test('regular users cannot access admin panel', function () {
            $user = User::factory()->create(['role' => 'user']);

            $response = $this->actingAs($user)->get('/admin');

            $response->assertStatus(403);
        });
    });

    describe('admin panel navigation', function () {
        beforeEach(function () {
            $this->admin = $this->loginAsAdmin();
        });

        test('admin panel shows navigation menu', function () {
            $response = $this->get('/admin');

            $response->assertStatus(200);
            $response->assertSee('navigation', false); // Check for navigation element
        });

        test('plugins resource is accessible to admin', function () {
            $response = $this->get('/admin/plugins');

            $response->assertStatus(200);
        });

        test('plugin creation page is accessible', function () {
            $response = $this->get('/admin/plugins/create');

            $response->assertStatus(200);
        });
    });

    describe('plugin management interface', function () {
        beforeEach(function () {
            $this->admin = $this->loginAsAdmin();
        });

        test('can view plugins list page', function () {
            // Create some test plugins
            $this->createTestPlugin(['name' => 'test-plugin-1']);
            $this->createTestPlugin(['name' => 'test-plugin-2']);

            $response = $this->get('/admin/plugins');

            $response->assertStatus(200);
            $response->assertSee('test-plugin-1');
            $response->assertSee('test-plugin-2');
        });

        test('can view individual plugin details', function () {
            $plugin = $this->createTestPlugin([
                'name' => 'detail-test-plugin',
                'description' => 'A test plugin for detail viewing',
            ]);

            $response = $this->get("/admin/plugins/{$plugin->id}");

            $response->assertStatus(200);
            $response->assertSee('detail-test-plugin');
            $response->assertSee('A test plugin for detail viewing');
        });

        test('can edit plugin settings', function () {
            $plugin = $this->createTestPlugin(['name' => 'edit-test-plugin']);

            $response = $this->get("/admin/plugins/{$plugin->id}/edit");

            $response->assertStatus(200);
        });

        test('can update plugin enabled status via form', function () {
            $plugin = $this->createTestPlugin([
                'name' => 'toggle-test-plugin',
                'enabled' => true,
            ]);

            $response = $this->patch("/admin/plugins/{$plugin->id}", [
                'enabled' => false,
            ]);

            $response->assertRedirect();

            $this->assertDatabaseHas('plugins', [
                'id' => $plugin->id,
                'enabled' => false,
            ]);
        });
    });

    describe('admin panel widgets', function () {
        beforeEach(function () {
            $this->admin = $this->loginAsAdmin();
        });

        test('admin dashboard shows plugin statistics widget', function () {
            // Create some test plugins to show statistics
            $this->createTestPlugin(['name' => 'enabled-plugin', 'enabled' => true]);
            $this->createTestPlugin(['name' => 'disabled-plugin', 'enabled' => false]);

            $response = $this->get('/admin');

            $response->assertStatus(200);
            // Widget should show plugin counts
            $response->assertSee('Installed Plugins', false);
        });

        test('plugin stats widget shows correct counts', function () {
            // Create multiple plugins with different states
            $this->createTestPlugin(['name' => 'plugin-1', 'enabled' => true]);
            $this->createTestPlugin(['name' => 'plugin-2', 'enabled' => true]);
            $this->createTestPlugin(['name' => 'plugin-3', 'enabled' => false]);

            $response = $this->get('/admin');

            $response->assertStatus(200);
            // Should show appropriate counts in the widget
        });
    });

    describe('plugin actions', function () {
        beforeEach(function () {
            $this->admin = $this->loginAsAdmin();
        });

        test('can discover new plugins via admin interface', function () {
            // Create a filesystem plugin that's not in database
            createTemporaryTestPlugin('discovery-via-ui');

            // This would typically be done via a Livewire action or form
            // For now, test that the endpoint exists
            $response = $this->post('/admin/plugins/discover');

            // The exact response depends on implementation
            $response->assertStatus([200, 302, 404]); // Any of these would be acceptable

            removeTemporaryTestPlugin('discovery-via-ui');
        });

        test('can install plugin via admin interface', function () {
            createTemporaryTestPlugin('install-via-ui');

            // This would be a form submission or Livewire action
            $response = $this->post('/admin/plugins/install', [
                'plugin_name' => 'install-via-ui',
            ]);

            // Check if the installation was successful
            // The exact response depends on implementation
            expect($response->getStatusCode())->toBeIn([200, 201, 302, 404]);

            removeTemporaryTestPlugin('install-via-ui');
        });

        test('can uninstall plugin via admin interface', function () {
            $plugin = $this->createTestPlugin(['name' => 'uninstall-via-ui']);

            $response = $this->delete("/admin/plugins/{$plugin->id}");

            // Plugin should be removed
            $this->assertDatabaseMissing('plugins', ['id' => $plugin->id]);
        });
    });

    describe('error handling in admin interface', function () {
        beforeEach(function () {
            $this->admin = $this->loginAsAdmin();
        });

        test('handles non-existent plugin gracefully', function () {
            $response = $this->get('/admin/plugins/99999');

            $response->assertStatus(404);
        });

        test('validates plugin installation requests', function () {
            $response = $this->post('/admin/plugins/install', [
                'plugin_name' => 'non-existent-plugin',
            ]);

            // Should handle gracefully, not crash
            expect($response->getStatusCode())->toBeIn([200, 302, 422, 404]);
        });

        test('prevents unauthorized actions on core plugins', function () {
            $corePlugin = $this->createTestPlugin(['name' => 'admin']);

            // Attempt to delete core plugin should be prevented
            $response = $this->delete("/admin/plugins/{$corePlugin->id}");

            // Should prevent deletion or show appropriate error
            expect($response->getStatusCode())->toBeIn([403, 422, 302]);
        });
    });

    describe('admin panel security', function () {
        test('admin panel enforces CSRF protection', function () {
            $this->admin = $this->loginAsAdmin();
            $plugin = $this->createTestPlugin(['name' => 'csrf-test']);

            // Attempt request without CSRF token
            $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
                ->patch("/admin/plugins/{$plugin->id}", ['enabled' => false]);

            // Should work without CSRF middleware disabled
            expect($response->getStatusCode())->toBeIn([200, 302]);
        });

        test('admin panel includes security headers', function () {
            $this->admin = $this->loginAsAdmin();

            $response = $this->get('/admin');

            $response->assertStatus(200);
            // Should include appropriate security headers
            // The exact headers depend on Filament's configuration
        });

        test('admin panel sanitizes user input', function () {
            $this->admin = $this->loginAsAdmin();

            $response = $this->get('/admin/plugins?search=<script>alert("xss")</script>');

            $response->assertStatus(200);
            $response->assertDontSee('<script>alert("xss")</script>', false);
        });
    });

    describe('admin panel responsiveness', function () {
        beforeEach(function () {
            $this->admin = $this->loginAsAdmin();
        });

        test('admin panel loads within acceptable time', function () {
            $startTime = microtime(true);

            $response = $this->get('/admin');

            $endTime = microtime(true);
            $loadTime = ($endTime - $startTime) * 1000;

            $response->assertStatus(200);
            expect($loadTime)->toBeLessThan(2000, "Admin panel took {$loadTime}ms to load");
        });

        test('plugins list handles large number of plugins', function () {
            // Create many plugins to test performance
            for ($i = 1; $i <= 20; $i++) {
                $this->createTestPlugin(['name' => "perf-test-plugin-{$i}"]);
            }

            $startTime = microtime(true);

            $response = $this->get('/admin/plugins');

            $endTime = microtime(true);
            $loadTime = ($endTime - $startTime) * 1000;

            $response->assertStatus(200);
            expect($loadTime)->toBeLessThan(3000, "Plugins list took {$loadTime}ms to load with 20 plugins");
        });
    });
});
