<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class GrowthChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Gebruikersgroei';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $userGrowth = User::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(12))
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
            
            $growth = $userGrowth->firstWhere(function ($item) use ($date) {
                return $item->month == $date->month && $item->year == $date->year;
            });
            
            $data[] = $growth ? $growth->total : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Nieuwe Gebruikers',
                    'data' => $data,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
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
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return "Nieuwe Gebruikers: " + context.parsed.y; }'
                    ],
                ],
            ],
        ];
    }
}
