<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

final class OtpAttempt extends Model
{
    use HasFactory, Prunable;

    protected $fillable = [
        'email',
        'type',
        'ip_address',
        'user_agent',
        'success',
        'reason',
    ];

    protected $casts = [
        'success' => 'boolean',
    ];

    /**
     * Prune records older dan 90 dagen.
     */
    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(90));
    }
}


