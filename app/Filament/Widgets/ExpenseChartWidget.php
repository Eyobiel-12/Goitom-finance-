<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ExpenseChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Uitgaven per Categorie';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $expenseData = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->where('expense_date', '>=', now()->subMonths(6))
            ->groupBy('category')
            ->get();

        $labels = [];
        $data = [];
        $colors = [
            'office' => '#3B82F6',
            'travel' => '#10B981',
            'meals' => '#F59E0B',
            'equipment' => '#8B5CF6',
            'software' => '#6366F1',
            'marketing' => '#EC4899',
            'utilities' => '#F59E0B',
            'other' => '#6B7280',
        ];

        $categoryLabels = [
            'office' => 'Kantoor',
            'travel' => 'Reizen',
            'meals' => 'Maaltijden',
            'equipment' => 'Uitrusting',
            'software' => 'Software',
            'marketing' => 'Marketing',
            'utilities' => 'Nutsvoorzieningen',
            'other' => 'Overig',
        ];

        foreach ($expenseData as $expense) {
            $labels[] = $categoryLabels[$expense->category] ?? $expense->category;
            $data[] = $expense->total;
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_values($colors),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.label + ": €" + context.parsed.toLocaleString(); }'
                    ],
                ],
            ],
        ];
    }
}
