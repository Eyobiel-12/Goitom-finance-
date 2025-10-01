<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

final class InvoiceService
{
    public function createInvoice(User $user, array $data): Invoice
    {
        return DB::transaction(function () use ($user, $data) {
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber($user);

            // Calculate totals
            $totals = $this->calculateTotals($data['items'], $data['tax_rate']);

            // Create invoice
            $invoice = $user->invoices()->create([
                'client_id' => $data['client_id'],
                'project_id' => $data['project_id'] ?? null,
                'invoice_number' => $invoiceNumber,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'] ?? $this->defaultDueDate($data['issue_date']),
                'subtotal' => $totals['subtotal'],
                'tax_rate' => $data['tax_rate'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['total_amount'],
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
            ]);

            // Create invoice items
            $this->createInvoiceItems($invoice, $data['items']);

            return $invoice;
        });
    }

    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            // Calculate totals
            $totals = $this->calculateTotals($data['items'], $data['tax_rate']);

            // Update invoice
            $invoice->update([
                'client_id' => $data['client_id'],
                'project_id' => $data['project_id'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'status' => $data['status'] ?? $invoice->status,
                'subtotal' => $totals['subtotal'],
                'tax_rate' => $data['tax_rate'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['total_amount'],
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
            ]);

            // Update invoice items
            $invoice->items()->delete();
            $this->createInvoiceItems($invoice, $data['items']);

            return $invoice;
        });
    }

    private function generateInvoiceNumber(User $user): string
    {
        // Zoek laatste nummer op basis van invoice_number suffix
        $prefix = (string) Config::get('billing.invoice_prefix', 'INV-');

        $last = Invoice::where('user_id', $user->id)
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $next = 1;
        if (is_string($last)) {
            $num = (int) preg_replace('/\D/', '', $last);
            $next = max(1, $num + 1);
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function defaultDueDate(string $issueDate): string
    {
        $days = (int) Config::get('billing.invoice_due_days', 14);
        return now()->parse($issueDate)->addDays($days)->toDateString();
    }

    private function calculateTotals(array $items, float $taxRate): array
    {
        $subtotal = 0;
        
        foreach ($items as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemTotal;
        }

        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ];
    }

    private function createInvoiceItems(Invoice $invoice, array $items): void
    {
        foreach ($items as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }
    }

    public function markAsPaid(Invoice $invoice, ?string $paidDate = null): Invoice
    {
        $invoice->update([
            'status' => 'paid',
            'paid_date' => $paidDate ?? now()->toDateString(),
        ]);

        return $invoice;
    }

    public function markAsOverdue(Invoice $invoice): Invoice
    {
        if ($invoice->status === 'sent' && $invoice->due_date < now()->toDateString()) {
            $invoice->update(['status' => 'overdue']);
        }

        return $invoice;
    }

    public function getOverdueInvoices(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Invoice::where('user_id', $user->id)
            ->where('status', 'sent')
            ->where('due_date', '<', now()->toDateString())
            ->with(['client', 'project'])
            ->get();
    }

    public function getInvoiceStatistics(User $user): array
    {
        return Invoice::where('user_id', $user->id)
            ->selectRaw('
                COUNT(*) as total_invoices,
                SUM(CASE WHEN status = "paid" THEN total_amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = "sent" AND due_date < CURDATE() THEN 1 ELSE 0 END) as overdue_count,
                AVG(CASE WHEN status = "paid" THEN total_amount END) as average_invoice_amount
            ')
            ->first()
            ->toArray();
    }
}
