<?php

namespace App\Providers\Filament;

use App\Filament\Management\Widgets\ManagementProgressDistributionChart;
use App\Filament\Management\Widgets\ManagementProjectProgressChart;
use App\Filament\Management\Widgets\ManagementStatsOverview;
use App\Filament\Management\Widgets\ManagementUnitProgressChart;
use App\Http\Middleware\FilamentAuthenticate;
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

class ManagementPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('management')
            ->path('management')
            ->colors([
                'primary' => Color::Indigo,
                'gray' => Color::Slate,
            ])
            ->font('Poppins')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->brandName('Sistem Monitoring Progres Perumahan')
            ->brandLogo(fn () => view('filament.logo'))
            ->discoverResources(in: app_path('Filament/Management/Resources'), for: 'App\Filament\Management\Resources')
            ->discoverPages(in: app_path('Filament/Management/Pages'), for: 'App\Filament\Management\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Management/Widgets'), for: 'App\Filament\Management\Widgets')
            ->widgets([
                ManagementStatsOverview::class,
                ManagementProjectProgressChart::class,
                ManagementUnitProgressChart::class,
                ManagementProgressDistributionChart::class,
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
            ])
            ->authMiddleware([
                FilamentAuthenticate::class,
            ]);
    }
}
