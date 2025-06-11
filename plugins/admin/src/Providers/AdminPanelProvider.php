<?php

namespace FilaMan\Admin\Providers;

use App\Http\Middleware\LocalAutoLogin;
use FilaMan\Admin\AdminPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('admin')
            ->path(config('filaman-admin.path', 'admin'))
            ->colors([
                'primary' => Color::Blue,
            ])
            ->profile()
            ->brandName(config('filaman-admin.brand_name', 'FilaMan Admin'))
            ->plugin(AdminPlugin::make());

        // Only require login in non-local environments (but always require in testing)
        if (! app()->environment('local') || app()->environment('testing')) {
            $panel = $panel
                ->login()
                ->emailVerification();
        }

        return $panel
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                LocalAutoLogin::class, // Auto-login in local environment
            ])
            ->authMiddleware(
                app()->environment('local') && ! app()->environment('testing')
                    ? [] // No auth middleware in local (except testing)
                    : [Authenticate::class] // Require auth in production and testing
            );
    }

}
