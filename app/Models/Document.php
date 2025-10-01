<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'documentable_type','documentable_id','disk','path','original_name','size','mime_type','meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}


