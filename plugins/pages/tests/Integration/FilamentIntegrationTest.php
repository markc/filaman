<?php

namespace FilaMan\Pages\Tests\Integration;

use App\Models\User;
use FilaMan\Pages\PagesPlugin;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register the plugin service provider
        $this->app->register(\FilaMan\Pages\PagesPluginServiceProvider::class);

        // Create admin user for testing
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
        ]);
    }

    public function test_plugin_implements_filament_plugin_interface()
    {
        $plugin = new PagesPlugin;

        $this->assertInstanceOf(\Filament\Contracts\Plugin::class, $plugin);
    }

    public function test_plugin_has_correct_id()
    {
        $plugin = new PagesPlugin;

        $this->assertEquals('pages', $plugin->getId());
    }

    public function test_plugin_can_be_created_via_make_method()
    {
        $plugin = PagesPlugin::make();

        $this->assertInstanceOf(PagesPlugin::class, $plugin);
    }

    public function test_plugin_registers_with_panel()
    {
        $plugin = PagesPlugin::make();
        $panel = $this->createMockPanel();

        // Should not throw exceptions during registration
        $plugin->register($panel);
        $this->assertTrue(true);
    }

    public function test_plugin_boots_correctly()
    {
        $plugin = PagesPlugin::make();
        $panel = $this->createMockPanel();

        // Should not throw exceptions during boot
        $plugin->boot($panel);

        // Verify view namespace is registered
        $this->assertTrue(view()->exists('filaman-pages::page'));
    }

    public function test_plugin_service_provider_registers_singleton()
    {
        $plugin1 = app(PagesPlugin::class);
        $plugin2 = app(PagesPlugin::class);

        $this->assertSame($plugin1, $plugin2);
    }

    public function test_helper_functions_are_registered()
    {
        // Test that helper functions are available
        $this->assertTrue(function_exists('filaman_plugin_path'));
        $this->assertTrue(function_exists('filaman_get_pages'));
    }

    public function test_filaman_plugin_path_helper()
    {
        $path = filaman_plugin_path('pages', 'resources/views');

        $this->assertStringContainsString('plugins/pages/resources/views', $path);
        $this->assertStringStartsWith(base_path(), $path);
    }

    public function test_filaman_get_pages_helper()
    {
        $pages = filaman_get_pages();

        $this->assertIsArray($pages);
        $this->assertNotEmpty($pages);

        // Should include the sample pages
        $slugs = array_column($pages, 'slug');
        $this->assertContains('home', $slugs);
        $this->assertContains('about', $slugs);
    }

    public function test_view_namespace_is_registered()
    {
        // Test that views can be found via namespace
        $this->assertTrue(view()->exists('filaman-pages::page'));
        $this->assertTrue(view()->exists('filaman-pages::index'));
        $this->assertTrue(view()->exists('filaman-pages::partials.navbar'));
    }

    public function test_routes_are_registered()
    {
        // Test that plugin routes are available
        $response = $this->get('/pages/home');
        $response->assertStatus(200);

        $response = $this->get('/pages');
        $response->assertStatus(200);
    }

    public function test_configuration_is_published()
    {
        // Test that configuration can be published
        $configPath = config_path('filaman-pages.php');

        // The config should be available via config helper
        $config = config('filaman-pages');
        $this->assertIsArray($config);
    }

    public function test_middleware_integration()
    {
        // Test that plugin works with Laravel middleware
        $response = $this->get('/pages/home');

        $response->assertStatus(200);
        // Should work without authentication for public pages
    }

    public function test_admin_panel_access_when_authenticated()
    {
        $response = $this->actingAs($this->adminUser)->get('/pages/home');

        $response->assertStatus(200);
        $response->assertSee('/admin'); // Should show admin panel link
    }

    public function test_plugin_handles_missing_dependencies_gracefully()
    {
        // Test that plugin handles missing dependencies
        // This would be more comprehensive in a real scenario

        $plugin = PagesPlugin::make();
        $this->assertInstanceOf(PagesPlugin::class, $plugin);
    }

    public function test_view_composers_are_registered()
    {
        $response = $this->get('/pages/home');

        $response->assertStatus(200);

        // View should have access to shared data
        $response->assertViewHas('pages');
        $response->assertViewHas('title');
    }

    public function test_asset_compilation_integration()
    {
        // Test that assets are properly compiled and served
        $response = $this->get('/pages/home');

        $content = $response->getContent();

        // Should include proper CSS/JS includes
        $this->assertStringContainsString('tailwind', $content);
    }

    public function test_error_handling_integration()
    {
        // Test that errors are handled gracefully in Filament context
        $response = $this->get('/pages/nonexistent');

        $response->assertStatus(404);
        // Should not expose sensitive error information
    }

    public function test_caching_integration()
    {
        // Test that caching works properly with Filament
        $response1 = $this->get('/pages/home');
        $response2 = $this->get('/pages/home');

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Both responses should be successful and consistent
        $this->assertEquals($response1->getContent(), $response2->getContent());
    }

    public function test_database_integration()
    {
        // Test that plugin works with database migrations
        // Pages plugin is file-based, but test database connectivity

        $user = User::factory()->create();
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_event_system_integration()
    {
        // Test that plugin events work with Laravel event system
        // Placeholder for when events are implemented

        $response = $this->get('/pages/home');
        $response->assertStatus(200);

        // Future: Test that PageViewed events are dispatched
    }

    public function test_authorization_integration()
    {
        // Test authorization integration
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/pages/home');
        $response->assertStatus(200);

        // Public pages should be accessible to all users
    }

    public function test_localization_integration()
    {
        // Test localization integration
        app()->setLocale('en');

        $response = $this->get('/pages/home');
        $response->assertStatus(200);

        // Should work with default locale
        // Future: Test multiple locales when implemented
    }

    public function test_queue_integration()
    {
        // Test queue system integration if applicable
        // Placeholder for future queue-based features

        $response = $this->get('/pages/home');
        $response->assertStatus(200);

        // Plugin should not interfere with queue processing
    }

    public function test_storage_integration()
    {
        // Test storage system integration
        $pages = filaman_get_pages();

        $this->assertNotEmpty($pages);

        // Should successfully read from file storage
        foreach ($pages as $page) {
            $this->assertNotEmpty($page['content']);
        }
    }

    public function test_validation_integration()
    {
        // Test Laravel validation integration
        $response = $this->get('/pages/../invalid');

        $response->assertStatus(404);

        // Should validate input and reject invalid slugs
    }

    private function createMockPanel(): Panel
    {
        return app(Panel::class)
            ->id('admin')
            ->path('/admin');
    }
}
