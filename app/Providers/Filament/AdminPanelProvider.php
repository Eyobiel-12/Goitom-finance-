<?php

namespace App\Providers\Filament;

use App\Filament\Pages\AdminDashboard;
use App\Filament\Pages\DataManagementPage;
use App\Filament\Pages\SecurityMonitoringPage;
use App\Filament\Widgets\AdminStatsWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\ExpenseChartWidget;
use App\Filament\Widgets\UserActivityWidget;
use App\Filament\Widgets\SaaSOverviewWidget;
use App\Filament\Widgets\GrowthChartWidget;
use App\Filament\Widgets\AdvancedStatsWidget;
use App\Filament\Widgets\RevenueTrendWidget;
use App\Filament\Widgets\InvoiceStatusChartWidget;
use App\Filament\Widgets\BusinessMetricsWidget;
use App\Http\Middleware\EnsureAdminRole;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
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
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Goitom Finance Admin')
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate,
            ])
            ->brandLogo(asset('logo.png'))
            ->brandLogoHeight('2rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                AdminDashboard::class,
                DataManagementPage::class,
                SecurityMonitoringPage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
                   ->widgets([
                       SaaSOverviewWidget::class,
                       AdvancedStatsWidget::class,
                       BusinessMetricsWidget::class,
                       GrowthChartWidget::class,
                       RevenueTrendWidget::class,
                       InvoiceStatusChartWidget::class,
                       RevenueChartWidget::class,
                       ExpenseChartWidget::class,
                       UserActivityWidget::class,
                       Widgets\AccountWidget::class,
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
                Authenticate::class,
                EnsureAdminRole::class,
            ]);
    }
}
