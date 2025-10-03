<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Client;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BusinessMetricsWidget extends BaseWidget
{
    protected ?string $heading = 'Business Metrics';
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        // Calculate business-specific metrics
        $totalRevenue = Invoice::where('status', 'paid')->sum('total_amount');
        $totalExpenses = Expense::sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        // Average invoice value
        $avgInvoiceValue = Invoice::where('status', 'paid')->avg('total_amount') ?? 0;

        // Customer lifetime value (simplified)
        $totalClients = Client::count();
        $avgClientValue = $totalClients > 0 ? round($totalRevenue / $totalClients, 2) : 0;

        // Monthly recurring revenue (simplified)
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_date', now()->month)
            ->whereYear('paid_date', now()->year)
            ->sum('total_amount');

        // Churn rate (simplified - based on inactive clients)
        $activeClients = Client::whereHas('invoices', function($query) {
            $query->where('created_at', '>=', now()->subMonths(3));
        })->count();
        $churnRate = $totalClients > 0 ? round((($totalClients - $activeClients) / $totalClients) * 100, 1) : 0;

        // Cash flow
        $thisMonthRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_date', now()->month)
            ->whereYear('paid_date', now()->year)
            ->sum('total_amount');
        $thisMonthExpenses = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');
        $cashFlow = $thisMonthRevenue - $thisMonthExpenses;

        return [
            Stat::make('Netto Winst', '€' . number_format($netProfit, 2, ',', '.'))
                ->description("Winstmarge: {$profitMargin}%")
                ->descriptionIcon($profitMargin >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($profitMargin >= 0 ? 'success' : 'danger')
                ->chart($this->getProfitChart()),

            Stat::make('Gem. Factuurwaarde', '€' . number_format($avgInvoiceValue, 2, ',', '.'))
                ->description('Per betaalde factuur')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary')
                ->chart($this->getInvoiceValueChart()),

            Stat::make('Klantwaarde', '€' . number_format($avgClientValue, 2, ',', '.'))
                ->description("Van {$totalClients} klanten")
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->chart($this->getClientValueChart()),

            Stat::make('Maandelijkse Omzet', '€' . number_format($monthlyRevenue, 2, ',', '.'))
                ->description('Deze maand')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success')
                ->chart($this->getMonthlyRevenueChart()),

            Stat::make('Churn Rate', $churnRate . '%')
                ->description($churnRate < 10 ? 'Uitstekend' : ($churnRate < 20 ? 'Goed' : 'Verbetering nodig'))
                ->descriptionIcon($churnRate < 10 ? 'heroicon-m-check-circle' : ($churnRate < 20 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-x-circle'))
                ->color($churnRate < 10 ? 'success' : ($churnRate < 20 ? 'warning' : 'danger')),

            Stat::make('Cash Flow', '€' . number_format($cashFlow, 2, ',', '.'))
                ->description('Deze maand')
                ->descriptionIcon($cashFlow >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($cashFlow >= 0 ? 'success' : 'danger')
                ->chart($this->getCashFlowChart()),
        ];
    }

    private function getProfitChart(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = Invoice::where('status', 'paid')
                ->whereMonth('paid_date', $month->month)
                ->whereYear('paid_date', $month->year)
                ->sum('total_amount');
            $expenses = Expense::whereMonth('expense_date', $month->month)
                ->whereYear('expense_date', $month->year)
                ->sum('amount');
            $data[] = $revenue - $expenses;
        }
        return $data;
    }

    private function getInvoiceValueChart(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $avg = Invoice::where('status', 'paid')
                ->whereMonth('paid_date', $month->month)
                ->whereYear('paid_date', $month->year)
                ->avg('total_amount') ?? 0;
            $data[] = round($avg, 2);
        }
        return $data;
    }

    private function getClientValueChart(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = Invoice::where('status', 'paid')
                ->whereMonth('paid_date', $month->month)
                ->whereYear('paid_date', $month->year)
                ->sum('total_amount');
            $clients = Client::whereHas('invoices', function($query) use ($month) {
                $query->whereMonth('created_at', $month->month)
                      ->whereYear('created_at', $month->year);
            })->count();
            $avg = $clients > 0 ? $revenue / $clients : 0;
            $data[] = round($avg, 2);
        }
        return $data;
    }

    private function getMonthlyRevenueChart(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = Invoice::where('status', 'paid')
                ->whereMonth('paid_date', $month->month)
                ->whereYear('paid_date', $month->year)
                ->sum('total_amount');
            $data[] = $revenue;
        }
        return $data;
    }

    private function getCashFlowChart(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = Invoice::where('status', 'paid')
                ->whereMonth('paid_date', $month->month)
                ->whereYear('paid_date', $month->year)
                ->sum('total_amount');
            $expenses = Expense::whereMonth('expense_date', $month->month)
                ->whereYear('expense_date', $month->year)
                ->sum('amount');
            $data[] = $revenue - $expenses;
        }
        return $data;
    }
}
