<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Maandelijkse Omzet';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $revenueData = Invoice::where('status', 'paid')
            ->select(
                DB::raw('MONTH(paid_date) as month'),
                DB::raw('YEAR(paid_date) as year'),
                DB::raw('SUM(total_amount) as total')
            )
            ->where('paid_date', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthYear = $date->format('M Y');
            $labels[] = $monthYear;
            
            $revenue = $revenueData->firstWhere(function ($item) use ($date) {
                return $item->month == $date->month && $item->year == $date->year;
            });
            
            $data[] = $revenue ? $revenue->total : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Omzet (€)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "€" + value.toLocaleString(); }'
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return "Omzet: €" + context.parsed.y.toLocaleString(); }'
                    ],
                ],
            ],
        ];
    }
}
