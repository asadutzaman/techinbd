<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductVariantOptimized extends Model
{
    use HasFactory;

    protected $table = 'product_variants_optimized';

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'barcode',
        'price',
        'compare_price',
        'stock',
        'manage_stock',
        'is_default',
        'attributes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'stock' => 'integer',
        'manage_stock' => 'boolean',
        'is_default' => 'boolean',
        'attributes' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($variant) {
            if (empty($variant->sku)) {
                $variant->sku = 'VAR-' . strtoupper(Str::random(8));
            }
        });

        static::saved(function ($variant) {
            // Update parent product's total stock
            $variant->product->calculateTotalStock();
        });
    }

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductOptimized::class, 'product_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImageOptimized::class, 'variant_id');
    }

    public function mainImage(): HasMany
    {
        return $this->images()->where('is_main', true);
    }

    // Scopes
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Accessors
    public function getEffectivePriceAttribute()
    {
        return $this->price ?? $this->product->base_price;
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->effective_price, 2);
    }

    public function getIsInStockAttribute()
    {
        return $this->stock > 0;
    }

    public function getDisplayNameAttribute()
    {
        return $this->name ?? $this->product->name;
    }
}