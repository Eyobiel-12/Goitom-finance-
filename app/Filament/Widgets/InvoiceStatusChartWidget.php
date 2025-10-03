<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class InvoiceStatusChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Factuur Status Verdeling';
    protected static ?int $sort = 3;
    protected static ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $statusData = Invoice::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statusData as $status) {
            $labels[] = $this->getStatusLabel($status->status);
            $data[] = $status->count;
            $colors[] = $this->getStatusColor($status->status);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
        ];
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
                        'label' => 'function(context) { return context.label + ": " + context.parsed + " facturen"; }',
                    ],
                ],
            ],
        ];
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'draft' => 'Concept',
            'sent' => 'Verzonden',
            'paid' => 'Betaald',
            'overdue' => 'Achterstallig',
            'cancelled' => 'Geannuleerd',
            default => ucfirst($status),
        };
    }

    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'draft' => '#6B7280',
            'sent' => '#F59E0B',
            'paid' => '#10B981',
            'overdue' => '#EF4444',
            'cancelled' => '#9CA3AF',
            default => '#6B7280',
        };
    }
}
