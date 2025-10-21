<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AttributeValueOptimized extends Model
{
    use HasFactory;

    protected $table = 'attribute_values_optimized';

    protected $fillable = [
        'attribute_id',
        'value',
        'display_value',
        'slug',
        'color_code',
        'numeric_value',
        'unit',
        'sort_order',
        'status'
    ];

    protected $casts = [
        'numeric_value' => 'decimal:4',
        'sort_order' => 'integer',
        'status' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($value) {
            if (empty($value->slug)) {
                $value->slug = Str::slug($value->value);
            }
            
            // Auto-detect numeric values
            if (is_null($value->numeric_value) && is_numeric($value->value)) {
                $value->numeric_value = (float) $value->value;
            }
        });
    }

    // Relationships
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(AttributeOptimized::class, 'attribute_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        return $this->display_value ?: $this->value;
    }

    public function getFormattedValueAttribute(): string
    {
        $unit = $this->unit ?: $this->attribute->unit;
        return $this->display_name . ($unit ? ' ' . $unit : '');
    }

    public function getIsColorAttribute(): bool
    {
        return !is_null($this->color_code) || $this->attribute->type === 'color';
    }

    public function getIsNumericAttribute(): bool
    {
        return !is_null($this->numeric_value);
    }

    // Mutators
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = $value;
        $this->attributes['slug'] = Str::slug($value);
        
        // Auto-detect numeric values
        if (is_numeric($value)) {
            $this->attributes['numeric_value'] = (float) $value;
        }
    }
}