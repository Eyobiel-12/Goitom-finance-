<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'user_id', 'work_date', 'hours', 'rate', 'description',
    ];

    protected $casts = [
        'work_date' => 'date',
        'hours' => 'decimal:2',
        'rate' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


