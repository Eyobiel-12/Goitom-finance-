<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Expense;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class DashboardService
{
    private const CACHE_TTL = 300; // 5 minutes

    public function getDashboardData(User $user): array
    {
        $cacheKey = "dashboard_data_user_{$user->id}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return [
                'recentInvoices' => $this->safely(fn () => $this->getRecentInvoices($user), collect()),
                'recentExpenses' => $this->safely(fn () => $this->getRecentExpenses($user), collect()),
                'stats' => $this->safely(fn () => $this->getStatistics($user), [
                    'totalInvoices' => 0,
                    'totalRevenue' => 0.0,
                    'totalExpenses' => 0.0,
                    'overdueInvoices' => 0,
                    'netProfit' => 0.0,
                ]),
                'monthlyRevenue' => $this->safely(fn () => $this->getMonthlyRevenue($user), collect()),
                'monthlyExpenses' => $this->safely(fn () => $this->getMonthlyExpenses($user), collect()),
            ];
        });
    }

    private function getRecentInvoices(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Invoice::where('user_id', $user->id)
            ->with(['client', 'project'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    private function getRecentExpenses(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Expense::where('user_id', $user->id)
            ->with('project')
            ->orderBy('expense_date', 'desc')
            ->limit(5)
            ->get();
    }

    private function getStatistics(User $user): array
    {
        // Use a single query with aggregations for better performance
        $stats = DB::table('invoices')
            ->where('user_id', $user->id)
            ->selectRaw('
                COUNT(*) as total_invoices,
                SUM(CASE WHEN status = "paid" THEN total_amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = "sent" AND due_date < ? THEN 1 ELSE 0 END) as overdue_invoices
            ', [now()->toDateString()])
            ->first();

        $totalExpenses = Expense::where('user_id', $user->id)->sum('amount');

        return [
            'totalInvoices' => (int) $stats->total_invoices,
            'totalRevenue' => (float) $stats->total_revenue,
            'totalExpenses' => (float) $totalExpenses,
            'overdueInvoices' => (int) $stats->overdue_invoices,
            'netProfit' => (float) $stats->total_revenue - (float) $totalExpenses,
        ];
    }

    private function getMonthlyRevenue(User $user): \Illuminate\Support\Collection
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $dateFormat = $isSqlite ? "strftime('%Y-%m', paid_date)" : "DATE_FORMAT(paid_date, '%Y-%m')";
        
        return Invoice::where('user_id', $user->id)
            ->where('status', 'paid')
            ->where('paid_date', '>=', now()->subMonths(6))
            ->selectRaw("{$dateFormat} as month, SUM(total_amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    private function getMonthlyExpenses(User $user): \Illuminate\Support\Collection
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $dateFormat = $isSqlite ? "strftime('%Y-%m', expense_date)" : "DATE_FORMAT(expense_date, '%Y-%m')";
        
        return Expense::where('user_id', $user->id)
            ->where('expense_date', '>=', now()->subMonths(6))
            ->selectRaw("{$dateFormat} as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Execute a callback and return a fallback value on failure.
     */
    private function safely(callable $callback, mixed $fallback): mixed
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            // In productie tonen we geen fout op het dashboard; log wel voor diagnose
            report($e);
            return $fallback;
        }
    }

    public function clearDashboardCache(User $user): void
    {
        $cacheKey = "dashboard_data_user_{$user->id}";
        Cache::forget($cacheKey);
    }

    public function clearAllDashboardCache(): void
    {
        // In a real application, you might want to use cache tags
        // For now, we'll clear the entire cache
        Cache::flush();
    }
}
