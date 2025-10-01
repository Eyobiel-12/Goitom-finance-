<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class InvoiceCustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_id','definition_id','value'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(InvoiceCustomFieldDefinition::class, 'definition_id');
    }
}


