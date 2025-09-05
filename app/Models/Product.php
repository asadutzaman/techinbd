<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
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

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    public function getDisplayPriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    public function getIsOnSaleAttribute()
    {
        return !is_null($this->sale_price);
    }

    /**
     * Get the brand that owns the product
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the category that owns the product
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get product attributes
     */
    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    /**
     * Get attributes with values for this product
     */
    public function attributesWithValues()
    {
        return $this->productAttributes()->with('attribute');
    }

    /**
     * Get specific attribute value
     */
    public function getAttributeValue($attributeName)
    {
        $productAttribute = $this->productAttributes()
            ->whereHas('attribute', function($query) use ($attributeName) {
                $query->where('name', $attributeName);
            })
            ->first();
            
        return $productAttribute ? $productAttribute->value : null;
    }
}
