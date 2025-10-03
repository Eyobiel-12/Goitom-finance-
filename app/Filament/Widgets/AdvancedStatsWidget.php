<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Expense;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AdvancedStatsWidget extends BaseWidget
{
    protected ?string $heading = 'Geavanceerde Statistieken';
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Revenue metrics
        $totalRevenue = Invoice::where('status', 'paid')->sum('total_amount');
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_date', now()->month)
            ->whereYear('paid_date', now()->year)
            ->sum('total_amount');
        $lastMonthRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_date', now()->subMonth()->month)
            ->whereYear('paid_date', now()->subMonth()->year)
            ->sum('total_amount');
        
        $revenueGrowth = $lastMonthRevenue > 0 
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        // User metrics
        $totalUsers = User::count();
        $activeUsers = User::whereHas('invoices', function($query) {
            $query->where('created_at', '>=', now()->subDays(30));
        })->count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Expense metrics
        $totalExpenses = Expense::sum('amount');
        $monthlyExpenses = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        // Invoice metrics
        $totalInvoices = Invoice::count();
        $overdueInvoices = Invoice::where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->count();
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $paymentRate = $totalInvoices > 0 ? round(($paidInvoices / $totalInvoices) * 100, 1) : 0;

        return [
            Stat::make('Maandelijkse Omzet', '€' . number_format($monthlyRevenue, 2, ',', '.'))
                ->description($revenueGrowth >= 0 ? "+{$revenueGrowth}% vs vorige maand" : "{$revenueGrowth}% vs vorige maand")
                ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueGrowth >= 0 ? 'success' : 'danger')
                ->chart([$lastMonthRevenue, $monthlyRevenue]),

            Stat::make('Actieve Gebruikers', $activeUsers)
                ->description("Van {$totalUsers} totaal gebruikers")
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([$totalUsers - $activeUsers, $activeUsers]),

            Stat::make('Betaalpercentage', $paymentRate . '%')
                ->description("{$paidInvoices} van {$totalInvoices} facturen betaald")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($paymentRate >= 80 ? 'success' : ($paymentRate >= 60 ? 'warning' : 'danger'))
                ->chart([$totalInvoices - $paidInvoices, $paidInvoices]),

            Stat::make('Maandelijkse Uitgaven', '€' . number_format($monthlyExpenses, 2, ',', '.'))
                ->description("Van €" . number_format($totalExpenses, 2, ',', '.') . " totaal")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning')
                ->chart([$totalExpenses - $monthlyExpenses, $monthlyExpenses]),

            Stat::make('Achterstallige Facturen', $overdueInvoices)
                ->description($overdueInvoices > 0 ? 'Actie vereist!' : 'Alles op tijd')
                ->descriptionIcon($overdueInvoices > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($overdueInvoices > 0 ? 'danger' : 'success'),

            Stat::make('Nieuwe Gebruikers', $newUsersThisMonth)
                ->description('Deze maand aangemeld')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info')
                ->chart([$totalUsers - $newUsersThisMonth, $newUsersThisMonth]),
        ];
    }
}
