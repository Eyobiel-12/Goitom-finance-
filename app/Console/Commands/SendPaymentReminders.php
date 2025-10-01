<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceReminderMail;
use Carbon\Carbon;

final class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'invoices:send-reminders {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     */
    protected $description = 'Send automatic payment reminders for overdue invoices';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('DRY RUN MODE - No emails will be sent');
        }

        $this->info('Checking for invoices that need payment reminders...');

        $today = Carbon::today();
        
        // Find invoices that are overdue and haven't been paid
        $overdueInvoices = Invoice::where('status', '!=', 'paid')
            ->where('due_date', '<', $today)
            ->whereHas('client', function ($query) {
                $query->whereNotNull('email');
            })
            ->with(['client', 'user', 'items'])
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('No overdue invoices found.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($overdueInvoices->count());
        $sent = 0;
        $skipped = 0;

        foreach ($overdueInvoices as $invoice) {
            $daysOverdue = $today->diffInDays($invoice->due_date);
            
            // Skip if invoice is too new (less than 1 day overdue)
            if ($daysOverdue < 1) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // Determine reminder frequency based on how overdue
            $shouldSend = $this->shouldSendReminder($daysOverdue);
            
            if (!$shouldSend) {
                $skipped++;
                $bar->advance();
                continue;
            }

            try {
                if (!$isDryRun) {
                    Mail::to($invoice->client->email)
                        ->send(new InvoiceReminderMail($invoice, $this->getReminderMessage($daysOverdue)));
                }
                
                $sent++;
                $this->line("\n" . ($isDryRun ? 'Would send' : 'Sent') . " reminder for invoice {$invoice->invoice_number} ({$daysOverdue} days overdue) to {$invoice->client->email}");
                
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for invoice {$invoice->invoice_number}: " . $e->getMessage());
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        
        $this->info(($isDryRun ? 'Would send' : 'Sent') . " {$sent} reminders, skipped {$skipped} invoices.");

        return Command::SUCCESS;
    }

    /**
     * Determine if a reminder should be sent based on days overdue
     */
    private function shouldSendReminder(int $daysOverdue): bool
    {
        // Send reminders on: 1, 7, 14, 30, 60, 90 days overdue
        return in_array($daysOverdue, [1, 7, 14, 30, 60, 90]);
    }

    /**
     * Get appropriate reminder message based on days overdue
     */
    private function getReminderMessage(int $daysOverdue): ?string
    {
        return match (true) {
            $daysOverdue === 1 => "Vriendelijke herinnering: uw factuur is vandaag vervallen.",
            $daysOverdue <= 7 => "Uw factuur is {$daysOverdue} dagen achterstallig. Gelieve zo spoedig mogelijk te betalen.",
            $daysOverdue <= 30 => "Uw factuur is {$daysOverdue} dagen achterstallig. Dit kan gevolgen hebben voor onze samenwerking.",
            $daysOverdue <= 60 => "URGENT: Uw factuur is {$daysOverdue} dagen achterstallig. Neem direct contact op.",
            default => "KRITIEK: Uw factuur is {$daysOverdue} dagen achterstallig. Juridische stappen worden overwogen.",
        };
    }
}