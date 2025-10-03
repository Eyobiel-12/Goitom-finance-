<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Expense;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        $totalInvoices = Invoice::count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('total_amount');
        $totalExpenses = Expense::sum('amount');
        $overdueInvoices = Invoice::where('status', 'overdue')->count();

        return [
            Stat::make('Totaal Gebruikers', $totalUsers)
                ->description('Alle geregistreerde gebruikers')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Nieuwe Gebruikers (30d)', $activeUsers)
                ->description('Gebruikers afgelopen 30 dagen')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),

            Stat::make('Totaal Facturen', $totalInvoices)
                ->description('Alle facturen in het systeem')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Totale Omzet', '€' . number_format($totalRevenue, 2))
                ->description('Betaalde facturen')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            Stat::make('Totale Uitgaven', '€' . number_format($totalExpenses, 2))
                ->description('Alle uitgaven')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Achterstallige Facturen', $overdueInvoices)
                ->description('Facturen die te laat zijn')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
