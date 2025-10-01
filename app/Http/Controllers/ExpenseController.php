<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Services\ExpenseService;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

final class ExpenseController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ExpenseService $expenseService,
        private readonly DashboardService $dashboardService
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['category', 'project_id', 'date_from', 'date_to', 'is_billable']);
        $expenses = $this->expenseService->getExpensesForUser($request->user(), $filters);

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
            'categories' => $this->expenseService->getExpenseCategories(),
        ]);
    }

    public function create(Request $request): Response
    {
        $projects = $this->expenseService->getActiveProjectsForUser($request->user());

        return Inertia::render('Expenses/Create', [
            'projects' => $projects,
            'categories' => $this->expenseService->getExpenseCategories(),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $this->expenseService->createExpense($request->user(), $request->validated());
        
        // Clear dashboard cache
        $this->dashboardService->clearDashboardCache($request->user());

        return redirect()->route('expenses.index')
            ->with('success', 'Uitgave succesvol toegevoegd.');
    }

    public function show(Expense $expense): Response
    {
        $this->authorize('view', $expense);

        $expense->load('project');

        return Inertia::render('Expenses/Show', [
            'expense' => $expense,
        ]);
    }

    public function edit(Expense $expense): Response
    {
        $this->authorize('update', $expense);

        $projects = $this->expenseService->getActiveProjectsForUser($expense->user);

        return Inertia::render('Expenses/Edit', [
            'expense' => $expense,
            'projects' => $projects,
            'categories' => $this->expenseService->getExpenseCategories(),
        ]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);

        $this->expenseService->updateExpense($expense, $request->validated());
        
        // Clear dashboard cache
        $this->dashboardService->clearDashboardCache($request->user());

        return redirect()->route('expenses.index')
            ->with('success', 'Uitgave succesvol bijgewerkt.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);

        $this->expenseService->deleteExpense($expense);
        
        // Clear dashboard cache
        $this->dashboardService->clearDashboardCache($expense->user);

        return redirect()->route('expenses.index')
            ->with('success', 'Uitgave succesvol verwijderd.');
    }
}
