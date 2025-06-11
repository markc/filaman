You are an expert Laravel and Filament developer. Your goal is to guide me through setting up a new Filament 4 project with a strong emphasis on a modular, plugin-based architecture. The core Laravel project should be kept as lightweight as possible, serving primarily as the foundation. All significant functionality, including the Filament Admin Panel itself, should be implemented as separate, independently developed plugins.

I need a step-by-step guide focusing on the initial setup, assuming I have a fresh Laravel project. Please structure your response with clear commands and explanations, referencing the official Filament 4.x documentation (https://filamentphp.com/docs/4.x/getting-started) where appropriate.

**Phase 1: Laravel Project Setup & Initial Core Configuration**

1.  **Fresh Laravel Project:** Assume I've already run `laravel new my-filament-core` or `composer create-project laravel/laravel my-filament-core`. I am inside the `my-filament-core` directory. [2, 3, 8]
2.  **Basic Laravel Configuration:**
    *   What are the minimal `php artisan` commands or configuration file adjustments needed in the *core* Laravel project to prepare it for Filament plugins, specifically concerning environment variables, database, and any core service providers?
        *   Ensure the `.env` file is set up with your database connection details. The `APP_KEY` is usually generated automatically during the `create-project` command. If not, run `php artisan key:generate`. [3]
        *   For the core application, you might not need to make many explicit config changes beyond the `.env` file for a "lightweight" core.
    *   Do I need to generate an `APP_KEY`? Yes, ensure `php artisan key:generate` has been run. [6]
    *   Do I need to run `php artisan migrate` at this stage, or will plugin migrations handle themselves? You can run `php artisan migrate` here if you have any initial core application tables (e.g., `users` table for authentication), but the migrations for your plugins will typically be run *after* the plugins are installed. [5, 7] If your core app does not require a database until a plugin is installed, you can skip this for now.

**Phase 2: Filament Core Installation & Plugin Setup**

1.  **Installing Filament Core Dependencies:**
    *   What is the *minimal* Composer command to install the necessary Filament packages into the *core* project, keeping in mind that the Admin Panel itself will be a separate plugin?
        *   You'll need to set the `minimum-stability` in your `composer.json` to `beta` since Filament v4 is in beta. [9]
        *   Then, install the core Filament package:
            ```bash
            composer require filament/filament:"^4.0"
            ```
            This command is for the "panel builder" flavor, which is what we need to enable the plugin system. [9]
    *   Are there any `php artisan` commands specifically for the *core* Filament setup that should be run here (e.g., `filament:install` or similar, but without installing the panel)? If so, what should I select if prompted?
        *   Run the initial Filament installation command, but specify that you want to set up panels without scaffolding the default admin panel:
            ```bash
            php artisan filament:install --panels
            ```
            This command will create and register a new Laravel service provider in `app/Providers/Filament/AdminPanelProvider.php`. This provider will be the entry point for registering your plugins. [9]
        *   If prompted, ensure you *don't* select options that scaffold default resources or pages into the main `app/Filament` directory, as these will live in your plugins.
    *   How do I publish any core Filament assets or configuration files *without* generating panel-specific files?
        *   The `filament:install --panels` command should handle the core asset publication. Filament v4 now uses Tailwind CSS v4, and the installation process integrates with that. [9, 18, 23]
        *   If you need to publish individual Filament component assets or config files (e.g., for specific form fields), you would typically do this on a per-plugin basis as they are integrated. [9]

2.  **Creating the First Plugin (for the Admin Panel):**
    *   How do I use the Filament 4 CLI to generate a *new* plugin that will house the main global admin panel? Let's call this plugin `AdminPanelPlugin`.
        *   Currently, Filament 4 doesn't have a direct `php artisan filament:make-plugin` command in its core. However, plugin development is based on Laravel packages, and Filament recommends using a [Plugin Skeleton](https://github.com/filamentphp/plugin-skeleton) or a CLI tool like `awcodes/hydro` [12, 14, 19] or `tomatophp/filament-plugins` [11] for scaffolding.
        *   **Recommended approach:** Use the `awcodes/hydro` CLI tool for ease of use in generating the plugin boilerplate:
            *   Install `awcodes/hydro` globally:
                ```bash
                composer global require awcodes/hydro
                ```
            *   Then, within your `my-filament-core` project directory, generate the plugin:
                ```bash
                hydro new AdminPanelPlugin --path=packages/admin-panel-plugin
                ```
                This command will create a new directory (e.g., `packages/admin-panel-plugin`) for your plugin. You might choose to put your plugins in a `packages` directory or `modules` for better organization.
                *   Follow any prompts from `hydro` to configure the plugin's details (e.g., vendor name, package name).
                *   This will create a new Laravel package structure that's ready for Filament plugin development.
        *   **Alternative (Manual/Skeleton):** If not using `hydro`, you would manually create a new Laravel package and follow the Filament plugin development guide. The skeleton is a good starting point. [12]
        *   Within your new `AdminPanelPlugin` package (e.g., `packages/admin-panel-plugin`), locate its service provider (e.g., `src/AdminPanelPluginServiceProvider.php`). This is where you'll register the Filament panel.

**Phase 3: Integrating the Admin Panel Plugin**

1.  **Registering the Plugin in the Core Project:**
    *   Open `app/Providers/Filament/AdminPanelProvider.php` (which was created by `filament:install --panels`).
    *   In the `panel()` method, register your `AdminPanelPlugin`. Assuming your `AdminPanelPlugin` has a static `make()` method (which is common practice for Filament plugins [4]), it would look something like this:
        ```php
        <?php

        namespace App\Providers\Filament;

        use Filament\Http\Middleware\Authenticate;
        use Filament\Http\Middleware\DisableBladeIconComponents;
        use Filament\Http\Middleware\DispatchServingFilamentEvent;
        use Filament\Pages;
        use Filament\Panel;
        use Filament\PanelProvider;
        use Filament\Support\Colors\Color;
        use Filament\Widgets;
        use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
        use Illuminate\Cookie\Middleware\EncryptCookies;
        use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
        use Illuminate\Routing\Middleware\SubstituteBindings;
        use Illuminate\Session\Middleware\AuthenticateSession;
        use Illuminate\Session\Middleware\StartSession;
        use Illuminate\View\Middleware\ShareErrorsFromSession;
        use AdminPanelPlugin\AdminPanelPlugin; // Adjust namespace based on your plugin's composer.json

        class AdminPanelProvider extends PanelProvider
        {
            public function panel(): Panel
            {
                return Panel::make()
                    ->id('admin')
                    ->path('admin')
                    ->colors([
                        'primary' => Color::Amber,
                    ])
                    // Register your AdminPanelPlugin here
                    ->plugin(AdminPanelPlugin::make())
                    // ... other core panel configurations (middleware, auth, etc.)
                    ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
                    ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
                    ->pages([
                        Pages\Dashboard::class,
                    ])
                    ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
                    ->widgets([
                        Widgets\AccountWidget::class,
                        Widgets\FilamentInfoWidget::class,
                    ])
                    ->middleware([
                        EncryptCookies::class,
                        AddQueuedCookiesToResponse::class,
                        StartSession::class,
                        AuthenticateSession::class,
                        ShareErrorsFromSession::class,
                        VerifyCsrfToken::class,
                        SubstituteBindings::class,
                        DispatchServingFilamentEvent::class,
                        DisableBladeIconComponents::class,
                    ])
                    ->authMiddleware([
                        Authenticate::class,
                    ]);
            }
        }
        ```
    *   **Autoloading the Plugin:** Ensure your `composer.json` in the root Laravel project has an entry for your new plugin in the `autoload` section (typically in `psr-4`) so Laravel can find its classes. If you used `hydro`, this might be added for you, or you'll need to manually add it and run `composer dump-autoload`. For example, in your main `composer.json`:
        ```json
        {
            "autoload": {
                "psr-4": {
                    "App\\": "app/",
                    "Database\\Factories\\": "database/factories/",
                    "Database\\Seeders\\": "database/seeders/",
                    "AdminPanelPlugin\\": "packages/admin-panel-plugin/src/" // Add this line
                }
            },
            "repositories": [
                {
                    "type": "path",
                    "url": "packages/admin-panel-plugin"
                }
            ],
            // ... other configurations
        }
        ```
        Then, run:
        ```bash
        composer dump-autoload
        ```

2.  **Developing the Admin Panel within the Plugin:**
    *   Inside `packages/admin-panel-plugin/src/AdminPanelPlugin.php` (or similar, depending on your plugin structure), you will define the panel's configuration.
    *   This is where you'll register any resources, pages, widgets, or custom routes that belong to your *main* admin panel.
    *   **Example `AdminPanelPlugin.php` structure (within your plugin):**
        ```php
        <?php

        namespace AdminPanelPlugin; // Your plugin's namespace

        use Filament\Contracts\Plugin;
        use Filament\Panel;
        use Filament\PanelProvider;
        use Filament\Support\Colors\Color;
        use AdminPanelPlugin\Filament\Pages\Dashboard; // Example: Your custom dashboard page
        use AdminPanelPlugin\Filament\Resources\UserResource; // Example: Your User management resource

        class AdminPanelPlugin implements Plugin
        {
            public static function make(): static
            {
                return app(static::class);
            }

            public function getId(): string
            {
                return 'admin-panel-plugin';
            }

            public function register(Panel $panel): void
            {
                $panel
                    ->id('admin')
                    ->path('admin')
                    ->colors([
                        'primary' => Color::Blue, // Customize panel color
                    ])
                    ->discoverResources(in: __DIR__ . '/Filament/Resources', for: 'AdminPanelPlugin\\Filament\\Resources')
                    ->discoverPages(in: __DIR__ . '/Filament/Pages', for: 'AdminPanelPlugin\\Filament\\Pages')
                    ->pages([
                        Dashboard::class, // Register your plugin's dashboard
                    ])
                    // You might want to remove the default Filament widgets if you're building custom ones in your plugin
                    // ->widgets([])
                    // ->discoverWidgets(in: __DIR__ . '/Filament/Widgets', for: 'AdminPanelPlugin\\Filament\\Widgets')
                    ->authGuard('web') // Assuming you're using Laravel's default 'web' guard for authentication
                    ->login(); // Enable Filament's login page
            }

            public function boot(Panel $panel): void
            {
                //
            }
        }
        ```
    *   Now, you'll create the Filament components (Resources, Pages, Widgets) *within your plugin's directory structure*, e.g., `packages/admin-panel-plugin/src/Filament/Resources/UserResource.php`.
    *   For authentication, create a Filament user:
        ```bash
        php artisan make:filament-user
        ```
        This user will be able to log in to your `admin` panel (which is now managed by your plugin).

**Phase 4: Running the Application and Testing**

1.  **Run Migrations (if applicable):** If your `AdminPanelPlugin` (or any other future plugin) introduces database migrations, run them now.
    ```bash
    php artisan migrate
    ```
2.  **Start the Development Server:**
    ```bash
    php artisan serve
    ```
3.  **Access the Admin Panel:**
    Open your browser and navigate to `http://127.0.0.1:8000/admin` (or whatever path you defined in your plugin's `register` method). [1] You should see the Filament login page.

**Phase 5: Adding More Independent Plugins**

1.  **Repeat Plugin Creation:** For each new piece of functionality (e.g., a Blog module, a CRM module, an E-commerce store), you would repeat the process:
    *   Use `hydro new MyFeaturePlugin --path=packages/my-feature-plugin` to generate a new plugin skeleton.
    *   Develop the Filament Resources, Pages, Widgets, etc., *within that new plugin's `src/Filament` directory*.
    *   In the main `AdminPanelProvider.php` of your core project (or in a dedicated PanelProvider for other panels), register the new plugin:
        ```php
        ->plugin(MyFeaturePlugin\MyFeaturePlugin::make())
        ```
    *   Ensure proper `composer.json` autoloading and `composer dump-autoload` for each new plugin.
    *   Run `php artisan migrate` if the new plugin has its own database migrations.

By following this detailed plan, you'll achieve a highly modular Filament 4 application where the core remains lean, and all major functionalities are encapsulated within independent, reusable plugins. This makes development, maintenance, and future scaling much more manageable.
