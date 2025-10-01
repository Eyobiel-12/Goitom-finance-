<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\RecurringInvoice;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;

final class GenerateRecurringInvoices extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'invoices:generate-recurring';

    /**
     * The console command description.
     */
    protected $description = 'Generate recurring invoices and optionally send them';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating recurring invoices...');

        $recurringInvoices = RecurringInvoice::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('last_generated')
                    ->orWhere('next_due', '<=', now()->toDateString());
            })
            ->get();

        if ($recurringInvoices->isEmpty()) {
            $this->info('No recurring invoices to generate.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($recurringInvoices->count());
        $generated = 0;
        $sent = 0;

        foreach ($recurringInvoices as $recurringInvoice) {
            if ($recurringInvoice->shouldGenerateToday()) {
                try {
                    $invoice = $recurringInvoice->generateInvoice();
                    $generated++;

                    $this->line("\nGenerated invoice {$invoice->invoice_number} for {$recurringInvoice->client->name}");

                    // Auto-send if enabled
                    if ($recurringInvoice->auto_send && $recurringInvoice->client->email) {
                        $invoice->load(['client', 'project', 'items', 'user']);
                        
                        Mail::to($recurringInvoice->client->email)
                            ->send(new InvoiceMail($invoice, "Automatische factuur voor {$recurringInvoice->template_name}"));
                        
                        $sent++;
                        $this->line("Sent invoice {$invoice->invoice_number} to {$recurringInvoice->client->email}");
                    }

                } catch (\Exception $e) {
                    $this->error("Failed to generate invoice for {$recurringInvoice->template_name}: " . $e->getMessage());
                }
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        
        $this->info("Generated {$generated} invoices, sent {$sent} emails.");

        return Command::SUCCESS;
    }
}