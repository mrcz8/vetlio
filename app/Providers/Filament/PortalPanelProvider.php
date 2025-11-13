<?php

namespace App\Providers\Filament;

use App\Filament\Portal\Pages\Dashboard;
use App\Http\Middleware\IdentifyTenant;
use App\Models\Client;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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
use Swindon\FilamentHashids\Middleware\FilamentHashidsMiddleware;

class PortalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('portal')
            ->path('portal')
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->colors([
                'primary' => Color::Green,
            ])
            ->font('Mulish')
            ->domain(request()->server('HTTP_HOST'))
            ->topNavigation()
            ->databaseNotifications()
            ->viteTheme('resources/css/filament/portal/theme.css')
            ->discoverResources(in: app_path('Filament/Portal/Resources'), for: 'App\Filament\Portal\Resources')
            ->discoverPages(in: app_path('Filament/Portal/Pages'), for: 'App\Filament\Portal\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Portal/Widgets'), for: 'App\Filament\Portal\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->plugins([
                AuthUIEnhancerPlugin::make()
                    ->emptyPanelBackgroundColor(Color::Green, '50')
                    ->emptyPanelView('filament.portal.auth.custom-login-view'),
                EasyFooterPlugin::make()
                    ->withGithub(showLogo: true, showUrl: true)
                    ->withLoadTime(),
                FilamentDeveloperLoginsPlugin::make()
                    ->modelClass(Client::class)
                    ->enabled(app()->isLocal())
                    ->switchable(false)
                    ->users([
                        'Client' => 'client@org1.com',
                    ]),
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
                IdentifyTenant::class,
                FilamentHashidsMiddleware::class,
            ])
            ->authGuard('portal')
            ->authPasswordBroker('clients')
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
