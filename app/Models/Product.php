<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'sale_price',
        'stock',
        'image',
        'category_id',
        'brand_id',
        'status',
        'featured'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'status' => 'boolean',
        'featured' => 'boolean'
    ];

    // Relationships
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    // Accessors
    public function getDisplayPriceAttribute(): string
    {
        return $this->sale_price ?? $this->price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return !is_null($this->sale_price) && $this->sale_price < $this->price;
    }

    // Custom method to get attribute value (renamed to avoid conflict)
    public function getCustomAttributeValue(string $attributeSlug)
    {
        return $this->productAttributes()
            ->whereHas('attribute', function($query) use ($attributeSlug) {
                $query->where('slug', $attributeSlug);
            })
            ->value('value');
    }

    // Get all attributes as key-value pairs
    public function getAttributesArrayAttribute(): array
    {
        return $this->productAttributes()
            ->with('attribute')
            ->get()
            ->mapWithKeys(function ($productAttribute) {
                return [$productAttribute->attribute->slug => $productAttribute->value];
            })
            ->toArray();
    }

    // Scopes
    public function scopeWithAttributes($query)
    {
        return $query->with([
            'brand:id,name,slug',
            'category:id,name,slug',
            'productAttributes.attribute:id,name,slug,type',
            'productAttributes'
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeFilterByCategory($query, $categorySlug)
    {
        return $query->whereHas('category', function($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    public function scopeFilterByBrand($query, $brandSlug)
    {
        return $query->whereHas('brand', function($q) use ($brandSlug) {
            $q->where('slug', $brandSlug);
        });
    }
}