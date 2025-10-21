<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSearchIndex extends Model
{
    use HasFactory;

    protected $table = 'product_search_index';

    protected $fillable = [
        'product_id',
        'searchable_content',
        'sku',
        'name',
        'brand_name',
        'category_names',
        'attribute_values',
        'price',
        'status',
        'stock_status',
        'total_stock'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'integer',
        'total_stock' => 'integer'
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductOptimized::class, 'product_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'in_stock');
    }

    public function scopeSearch($query, $term)
    {
        return $query->whereFullText(['searchable_content', 'name', 'brand_name', 'attribute_values'], $term);
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }
}