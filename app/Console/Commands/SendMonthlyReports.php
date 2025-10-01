<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Expense;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\MonthlyReportMail;
use Carbon\Carbon;

final class SendMonthlyReports extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reports:send-monthly {--month= : Specific month (Y-m format)} {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     */
    protected $description = 'Send monthly financial reports to all users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $monthInput = $this->option('month');
        
        if ($isDryRun) {
            $this->info('DRY RUN MODE - No emails will be sent');
        }

        // Determine which month to report on
        $reportMonth = $monthInput ? Carbon::createFromFormat('Y-m', $monthInput) : Carbon::now()->subMonth();
        
        $this->info("Generating monthly reports for {$reportMonth->format('F Y')}...");

        $users = User::all();
        $bar = $this->output->createProgressBar($users->count());
        $sent = 0;
        $skipped = 0;

        foreach ($users as $user) {
            try {
                $reportData = $this->generateReportData($user, $reportMonth);
                
                // Skip if no activity in the month
                if ($reportData['invoices_count'] === 0 && $reportData['expenses_count'] === 0) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if (!$isDryRun) {
                    Mail::to($user->email)
                        ->send(new MonthlyReportMail($user, $reportData, $reportMonth));
                }
                
                $sent++;
                $this->line("\n" . ($isDryRun ? 'Would send' : 'Sent') . " monthly report to {$user->email}");
                
            } catch (\Exception $e) {
                $this->error("Failed to send report to {$user->email}: " . $e->getMessage());
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        
        $this->info(($isDryRun ? 'Would send' : 'Sent') . " {$sent} reports, skipped {$skipped} users with no activity.");

        return Command::SUCCESS;
    }

    /**
     * Generate report data for a user
     */
    private function generateReportData(User $user, Carbon $reportMonth): array
    {
        $startOfMonth = $reportMonth->copy()->startOfMonth();
        $endOfMonth = $reportMonth->copy()->endOfMonth();

        // Get invoices for the month
        $invoices = Invoice::where('user_id', $user->id)
            ->whereBetween('issue_date', [$startOfMonth, $endOfMonth])
            ->with('client')
            ->get();

        // Get expenses for the month
        $expenses = Expense::where('user_id', $user->id)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->get();

        // Calculate totals
        $totalIncome = $invoices->where('status', 'paid')->sum('total_amount');
        $totalExpenses = $expenses->sum('amount');
        $netProfit = $totalIncome - $totalExpenses;

        // Count overdue invoices
        $overdueInvoices = Invoice::where('user_id', $user->id)
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', Carbon::today())
            ->count();

        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'invoices_count' => $invoices->count(),
            'expenses_count' => $expenses->count(),
            'overdue_invoices' => $overdueInvoices,
            'invoices' => $invoices->map(function ($invoice) {
                return [
                    'invoice_number' => $invoice->invoice_number,
                    'client_name' => $invoice->client->name,
                    'total_amount' => $invoice->total_amount,
                    'issue_date' => $invoice->issue_date->format('d-m-Y'),
                    'status' => $invoice->status,
                ];
            })->toArray(),
            'expenses' => $expenses->take(10)->map(function ($expense) {
                return [
                    'description' => $expense->description,
                    'vendor' => $expense->vendor,
                    'amount' => $expense->amount,
                    'category' => $expense->category,
                ];
            })->toArray(),
        ];
    }
}