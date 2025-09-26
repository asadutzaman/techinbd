<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attribute_id',
        'attribute_value_id',
        'value'
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
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
                return is_numeric($this->value) ? number_format($this->value, 2) : $this->value;
            case 'color':
                return '<span class="color-swatch" style="background-color: '.e($this->value).'"></span>';
            default:
                return $this->value;
        }
    }

    public function getDisplayValueAttribute()
    {
        return $this->formatted_value;
    }
}