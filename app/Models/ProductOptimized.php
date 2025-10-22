<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ProductOptimized extends Model
{
    use HasFactory;

    protected $table = 'products_optimized';

    protected $fillable = [
        'sku',
        'uuid',
        'name',
        'slug',
        'brand_id',
        'category_id',
        'short_description',
        'description',
        'base_price',
        'cost_price',
        'currency',
        'manage_stock',
        'stock_status',
        'total_stock',
        'weight',
        'dimensions',
        'specs',
        'attributes',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'featured',
        'warranty',
        'manufacturer_part_no',
        'ean_upc'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:3',
        'manage_stock' => 'boolean',
        'status' => 'integer',
        'featured' => 'boolean',
        'total_stock' => 'integer',
        'specs' => 'array',
        'attributes' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->uuid)) {
                $product->uuid = Str::uuid();
            }
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(Str::random(8));
            }
        });
    }

    // Relationships
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories', 'product_id', 'category_id')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariantOptimized::class, 'product_id');
    }

    public function defaultVariant(): HasMany
    {
        return $this->variants()->where('is_default', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImageOptimized::class, 'product_id');
    }

    public function mainImage(): HasMany
    {
        return $this->images()->where('is_main', true);
    }

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttributeOptimized::class, 'product_id');
    }

    public function searchIndex(): HasMany
    {
        return $this->hasMany(ProductSearchIndex::class, 'product_id');
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

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('base_price', [$min, $max]);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return number_format($this->base_price, 2);
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 1;
    }

    public function getMainImageUrlAttribute()
    {
        $mainImage = $this->mainImage()->first();
        return $mainImage ? $mainImage->url : '/img/default-product.jpg';
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // Helper methods
    public function updateSearchIndex()
    {
        // Update the denormalized search index
        $searchableContent = collect([
            $this->name,
            $this->short_description,
            $this->description,
            $this->brand?->name,
            $this->category?->name,
            $this->productAttributes->pluck('value')->implode(' ')
        ])->filter()->implode(' ');

        $this->searchIndex()->updateOrCreate(
            ['product_id' => $this->id],
            [
                'searchable_content' => $searchableContent,
                'sku' => $this->sku,
                'name' => $this->name,
                'brand_name' => $this->brand?->name,
                'category_names' => $this->categories->pluck('name')->implode(', '),
                'attribute_values' => $this->productAttributes->pluck('value')->implode(' '),
                'price' => $this->base_price,
                'status' => $this->status,
                'stock_status' => $this->stock_status,
                'total_stock' => $this->total_stock,
            ]
        );
    }

    public function calculateTotalStock()
    {
        if ($this->manage_stock) {
            $variantStock = $this->variants()->sum('stock');
            $this->update(['total_stock' => $variantStock]);
        }
    }
}