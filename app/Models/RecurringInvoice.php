<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

final class RecurringInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'project_id',
        'template_name',
        'invoice_data',
        'frequency',
        'day_of_month',
        'day_of_week',
        'is_active',
        'start_date',
        'end_date',
        'last_generated',
        'next_due',
        'auto_send',
        'send_days_before',
    ];

    protected $casts = [
        'invoice_data' => 'array',
        'is_active' => 'boolean',
        'auto_send' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'last_generated' => 'date',
        'next_due' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Calculate the next due date based on frequency
     */
    public function calculateNextDue(): Carbon
    {
        $baseDate = $this->last_generated ?: $this->start_date;
        
        return match ($this->frequency) {
            'weekly' => $baseDate->addWeek(),
            'monthly' => $baseDate->addMonth(),
            'quarterly' => $baseDate->addMonths(3),
            'yearly' => $baseDate->addYear(),
        };
    }

    /**
     * Check if this recurring invoice should be generated today
     */
    public function shouldGenerateToday(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $today = Carbon::today();
        
        // Check if we're past the end date
        if ($this->end_date && $today->gt($this->end_date)) {
            return false;
        }

        // Check if we haven't generated yet
        if (!$this->last_generated) {
            return $today->gte($this->start_date);
        }

        // Check if it's time for the next generation
        $nextDue = $this->calculateNextDue();
        return $today->gte($nextDue);
    }

    /**
     * Generate a new invoice from this template
     */
    public function generateInvoice(): Invoice
    {
        $invoiceData = $this->invoice_data;
        $invoiceData['client_id'] = $this->client_id;
        $invoiceData['project_id'] = $this->project_id;
        $invoiceData['issue_date'] = Carbon::today()->toDateString();
        $invoiceData['due_date'] = Carbon::today()->addDays(config('billing.invoice_due_days', 14))->toDateString();
        $invoiceData['status'] = 'draft';

        $invoice = Invoice::create($invoiceData);
        
        // Update recurring invoice
        $this->update([
            'last_generated' => Carbon::today(),
            'next_due' => $this->calculateNextDue(),
        ]);

        return $invoice;
    }
}