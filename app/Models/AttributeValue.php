<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
        'display_value',
        'slug',
        'sort_order',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->display_value ?: $this->value;
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}