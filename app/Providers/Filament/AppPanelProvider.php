<?php

namespace App\Providers\Filament;

use Agencetwogether\HooksHelper\HooksHelperPlugin;
use App\Filament\App\Clusters\Settings\SettingsCluster;
use App\Filament\App\Pages\Dashboard;
use App\Filament\App\Resources\Clients\ClientResource;
use App\Filament\App\Resources\Invoices\Pages\NotPayedInvoices;
use App\Filament\App\Resources\Patients\PatientResource;
use App\Filament\App\Resources\Reservations\Pages\DoctorReservations;
use App\Filament\App\Resources\Reservations\ReservationResource;
use App\Filament\App\Resources\Tasks\TaskResource;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\OrganisationApplySettings;
use App\Models\Branch;
use Awcodes\QuickCreate\QuickCreatePlugin;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Swindon\FilamentHashids\Middleware\FilamentHashidsMiddleware;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->tenant(Branch::class)
            //->spa()
            ->domain(request()->server('HTTP_HOST'))
            ->login()
            ->passwordReset()
            ->colors([
                'primary' => [
                    50 => '#e5eff9',
                    100 => '#c2daf2',
                    200 => '#99c3eb',
                    300 => '#70abd5',
                    400 => '#428fc0',
                    500 => '#1561a7',
                    600 => '#12528e',
                    700 => '#0e4374',
                    800 => '#0b345a',
                    900 => '#072341',
                    950 => '#072341',
                ],
            ])
            ->databaseTransactions()
            ->font('Mulish')
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(Width::Full)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->viteTheme('resources/css/filament/app/theme.css')
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\Filament\App\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\Filament\App\Pages')
            ->discoverClusters(in: app_path('Filament/App/Clusters'), for: 'App\Filament\App\Clusters')
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\Filament\App\Widgets')
            ->pages([
                Dashboard::class, DoctorReservations::class, NotPayedInvoices::class
            ])
            ->renderHook(
                PanelsRenderHook::PAGE_SUB_NAVIGATION_SIDEBAR_BEFORE, function () {
                    return Blade::render('@livewire(\'client-card-header\')');
            }, scopes: ClientResource::class
            )
            ->userMenuItems([
                Action::make('settings')
                    ->label('Settings')
                    ->visible(function () {
                        return auth()->user()->administrator;
                    })
                    ->url(fn(): string => SettingsCluster::getUrl())
                    ->icon(Heroicon::Cog),
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
                OrganisationApplySettings::class,
            ])
            ->plugins([
                HooksHelperPlugin::make(),
                AuthUIEnhancerPlugin::make()
                    ->emptyPanelBackgroundColor(Color::Blue, '50')
                    ->emptyPanelView('filament.app.auth.custom-login-view'),
                EasyFooterPlugin::make()
                    ->withGithub(showLogo: true, showUrl: true)
                    ->withLoadTime(),
                QuickCreatePlugin::make()
                    ->includes([
                        ClientResource::class,
                        PatientResource::class,
                        TaskResource::class,
                        ReservationResource::class,
                    ]),
                FilamentDeveloperLoginsPlugin::make()
                    ->enabled(app()->isLocal())
                    ->switchable(false)
                    ->users([
                        'Admin' => 'admin@org1.com',
                    ]),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
