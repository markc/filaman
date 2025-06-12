<?php

namespace FilaMan\Pages\Providers;

use FilaMan\Pages\Services\NavigationService;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PagesPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $navigationService = app(NavigationService::class);

        return $panel
            ->id('pages')
            ->path('pages') // Public pages interface using full Filament layout
            ->brandName(config('app.name').' - Pages')
            ->viteTheme('resources/css/filament/admin/theme.css') // Use same theme as admin panel
            ->colors([
                'primary' => Color::Blue, // Match admin panel primary color
            ])
            ->sidebarCollapsibleOnDesktop() // Allow sidebar to be collapsed
            ->pages([
                \FilaMan\Pages\Filament\Pages\PagesList::class,
                \FilaMan\Pages\Filament\Pages\DynamicPage::class,
            ])
            ->navigationGroups($navigationService->getNavigationGroups())
            ->widgets([
                // No widgets needed for pages panel
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([]); // Empty array makes all pages public - no login required
    }
}
