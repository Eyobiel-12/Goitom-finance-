<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class InvoiceCustomFieldDefinition extends Model
{
    use HasFactory;

    protected $fillable = ['key','label','type','required'];

    protected $casts = [
        'required' => 'bool',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(InvoiceCustomFieldValue::class, 'definition_id');
    }
}


