<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeOptimized extends Model
{
    use HasFactory;

    protected $table = 'product_attributes_optimized';

    protected $fillable = [
        'product_id',
        'attribute_id',
        'attribute_value_id',
        'value',
        'numeric_value'
    ];

    protected $casts = [
        'numeric_value' => 'decimal:4'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($productAttribute) {
            // Auto-detect numeric values
            if (is_null($productAttribute->numeric_value) && is_numeric($productAttribute->value)) {
                $productAttribute->numeric_value = (float) $productAttribute->value;
            }
        });

        static::saved(function ($productAttribute) {
            // Update product search index when attributes change
            $productAttribute->product->updateSearchIndex();
        });
    }

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductOptimized::class, 'product_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(AttributeOptimized::class, 'attribute_id');
    }

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValueOptimized::class, 'attribute_value_id');
    }

    // Accessors
    public function getFormattedValueAttribute()
    {
        // Format based on attribute type
        switch ($this->attribute->type ?? 'text') {
            case 'boolean':
            case 'checkbox':
                return (bool)$this->value ? 'Yes' : 'No';
            case 'number':
                $unit = $this->attribute->unit;
                return is_numeric($this->value) 
                    ? number_format($this->value, 2) . ($unit ? ' ' . $unit : '')
                    : $this->value;
            case 'color':
                return '<span class="color-swatch" style="background-color: '.e($this->value).'"></span> ' . $this->value;
            default:
                return $this->value;
        }
    }

    public function getDisplayValueAttribute()
    {
        // Use predefined value display name if available
        if ($this->attributeValue) {
            return $this->attributeValue->display_name;
        }
        
        return $this->formatted_value;
    }

    public function getIsNumericAttribute(): bool
    {
        return !is_null($this->numeric_value);
    }

    // Mutators
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = $value;
        
        // Auto-detect numeric values
        if (is_numeric($value)) {
            $this->attributes['numeric_value'] = (float) $value;
        }
    }
}