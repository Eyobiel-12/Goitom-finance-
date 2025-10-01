<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id','name','interval','next_run_at','amount','vat_rate','currency','active',
    ];

    protected $casts = [
        'next_run_at' => 'date',
        'amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'active' => 'bool',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}


