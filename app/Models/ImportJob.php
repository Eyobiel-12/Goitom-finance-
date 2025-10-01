<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ImportJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'filename',
        'file_path',
        'mapping',
        'status',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'errors',
        'notes',
    ];

    protected $casts = [
        'mapping' => 'array',
        'errors' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return (int) round(($this->processed_rows / $this->total_rows) * 100);
    }

    /**
     * Check if import is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if import failed
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if import is in progress
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }
}