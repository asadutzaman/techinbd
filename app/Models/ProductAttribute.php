<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attribute_id',
        'value'
    ];

    /**
     * Get the product that owns the attribute
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the attribute
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get formatted value based on attribute type
     */
    public function getFormattedValueAttribute()
    {
        switch ($this->attribute->type) {
            case 'boolean':
                return $this->value ? 'Yes' : 'No';
            case 'number':
                return is_numeric($this->value) ? number_format($this->value) : $this->value;
            default:
                return $this->value;
        }
    }
}