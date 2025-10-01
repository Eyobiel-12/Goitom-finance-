<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Expense;
use Carbon\Carbon;

final class GenerateForecast extends Command
{
    /** @var string */
    protected $signature = 'forecast:generate {--months=6 : Aantal maanden vooruit} {--window=3 : Moving average window} {--dry-run : Toon alleen resultaat}';

    /** @var string */
    protected $description = 'Genereer een eenvoudige forecast voor inkomsten en uitgaven op basis van moving average en groeifactor';

    public function handle(): int
    {
        $months = (int) $this->option('months');
        $window = (int) $this->option('window');

        $historyMonths = 12;
        $now = Carbon::now()->startOfMonth();

        // Verzamel historische data per maand
        $incomeSeries = [];
        $expenseSeries = [];

        for ($i = $historyMonths; $i >= 1; $i--) {
            $start = $now->copy()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $income = Invoice::where('status', 'paid')
                ->whereBetween('issue_date', [$start, $end])
                ->sum('total_amount');

            $expenses = Expense::whereBetween('expense_date', [$start, $end])
                ->sum('amount');

            $label = $start->format('Y-m');
            $incomeSeries[$label] = (float) $income;
            $expenseSeries[$label] = (float) $expenses;
        }

        // Bereken moving average en simpele groeifactor (laatste maand t.o.v. gemiddeld)
        $incomeForecast = $this->forecastSeries($incomeSeries, $months, $window);
        $expenseForecast = $this->forecastSeries($expenseSeries, $months, $window);

        $result = [
            'history' => [
                'income' => $incomeSeries,
                'expenses' => $expenseSeries,
            ],
            'forecast' => [
                'income' => $incomeForecast,
                'expenses' => $expenseForecast,
                'net_profit' => $this->combineNet($incomeForecast, $expenseForecast),
            ],
        ];

        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return Command::SUCCESS;
    }

    private function forecastSeries(array $series, int $months, int $window): array
    {
        $values = array_values($series);
        $labels = array_keys($series);

        $movingAvg = function(array $arr, int $w): float {
            $w = max(1, min($w, count($arr)));
            $slice = array_slice($arr, -$w);
            return array_sum($slice) / max(1, count($slice));
        };

        $last = end($values) ?: 0.0;
        $avg = $movingAvg($values, $window);
        $growth = $avg > 0 ? ($last / $avg) : 1.0;
        if (!is_finite($growth) || $growth <= 0) { $growth = 1.0; }

        $forecast = [];
        $cursor = Carbon::now()->startOfMonth();
        for ($i = 1; $i <= $months; $i++) {
            $cursor = $cursor->copy()->addMonth();
            // damped growth: beweeg 50% richting growth * last
            $next = 0.5 * ($last * $growth) + 0.5 * $avg;
            $next = max(0.0, round($next, 2));

            $forecast[$cursor->format('Y-m')] = $next;

            // update voor volgende stap
            $values[] = $next;
            $last = $next;
            $avg = $movingAvg($values, $window);
        }

        return $forecast;
    }

    private function combineNet(array $income, array $expenses): array
    {
        $allMonths = array_unique(array_merge(array_keys($income), array_keys($expenses)));
        sort($allMonths);
        $net = [];
        foreach ($allMonths as $m) {
            $net[$m] = round(($income[$m] ?? 0) - ($expenses[$m] ?? 0), 2);
        }
        return $net;
    }
}
