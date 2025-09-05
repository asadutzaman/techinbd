<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
        'display_value',
        'sort_order',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    /**
     * Get the attribute that owns the value
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get display value or fallback to value
     */
    public function getDisplayNameAttribute()
    {
        return $this->display_value ?: $this->value;
    }

    /**
     * Scope for active values
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}