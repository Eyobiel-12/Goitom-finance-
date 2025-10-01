<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class ExpenseService
{
    public function getExpensesForUser(User $user, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Expense::where('user_id', $user->id)
            ->with('project')
            ->orderBy('expense_date', 'desc');

        // Apply filters
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('expense_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('expense_date', '<=', $filters['date_to']);
        }

        if (isset($filters['is_billable'])) {
            $query->where('is_billable', $filters['is_billable']);
        }

        return $query->paginate(15);
    }

    public function createExpense(User $user, array $data): Expense
    {
        return DB::transaction(function () use ($user, $data) {
            return $user->expenses()->create($data);
        });
    }

    public function updateExpense(Expense $expense, array $data): Expense
    {
        return DB::transaction(function () use ($expense, $data) {
            $expense->update($data);
            return $expense->fresh();
        });
    }

    public function deleteExpense(Expense $expense): bool
    {
        return DB::transaction(function () use ($expense) {
            return $expense->delete();
        });
    }

    public function getExpenseStatistics(User $user): array
    {
        return Expense::where('user_id', $user->id)
            ->selectRaw('
                COUNT(*) as total_expenses,
                SUM(amount) as total_amount,
                AVG(amount) as average_amount,
                SUM(CASE WHEN is_billable = 1 THEN amount ELSE 0 END) as billable_amount,
                SUM(CASE WHEN is_billable = 0 THEN amount ELSE 0 END) as non_billable_amount
            ')
            ->first()
            ->toArray();
    }

    public function getExpensesByCategory(User $user): Collection
    {
        return Expense::where('user_id', $user->id)
            ->selectRaw('category, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();
    }

    public function getExpensesByProject(User $user): Collection
    {
        return Expense::where('user_id', $user->id)
            ->with('project')
            ->selectRaw('project_id, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('project_id')
            ->orderBy('total', 'desc')
            ->get();
    }

    public function getMonthlyExpenses(User $user, int $months = 6): Collection
    {
        $isSqlite = DB::getDriverName() === 'sqlite';
        $dateFormat = $isSqlite ? 'strftime("%Y-%m", expense_date)' : 'DATE_FORMAT(expense_date, "%Y-%m")';
        
        return Expense::where('user_id', $user->id)
            ->where('expense_date', '>=', now()->subMonths($months))
            ->selectRaw("{$dateFormat} as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public function getActiveProjectsForUser(User $user): Collection
    {
        return Project::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getExpenseCategories(): array
    {
        return [
            'office' => 'Kantoor',
            'travel' => 'Reizen',
            'marketing' => 'Marketing',
            'software' => 'Software',
            'equipment' => 'Uitrusting',
            'training' => 'Training',
            'other' => 'Anders',
        ];
    }
}
