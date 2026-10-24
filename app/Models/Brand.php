<?php

namespace App\Models;

use App\Models\Concerns\BustsCatalogCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory, BustsCatalogCache;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'website',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
        
        static::updating(function ($brand) {
            if ($brand->isDirty('name') && empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    /**
     * Get products for this brand
     */
    public function products()
    {
        return $this->hasMany(ProductOptimized::class);
    }

    /**
     * Get active products count
     */
    public function getActiveProductsCountAttribute()
    {
        return $this->products()->active()->count();
    }

    /**
     * Brands that are switched on. Switching a brand off hides its products from the shop
     * (ProductOptimized::active()).
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Switched-on brands with products on sale in each category, A–Z, keyed by category id: the brand
     * lists in the menu. A product listed in a second category (Gaming) counts there too.
     *
     * @return Collection<int, Collection<int, object{category_id: int, id: int, name: string}>>
     */
    public static function byCategory(): Collection
    {
        $own = ProductOptimized::active()->toBase()->select('category_id', 'brand_id');
        $listed = ProductOptimized::active()->toBase()
            ->join('product_categories', 'product_categories.product_id', '=', 'products_optimized.id')
            ->select('product_categories.category_id', 'products_optimized.brand_id');

        return DB::query()
            ->fromSub($own->unionAll($listed), 'listings')
            ->join('brands', 'brands.id', '=', 'listings.brand_id')
            ->where('brands.status', true)
            ->distinct()
            ->orderBy('brands.name')
            ->get(['listings.category_id', 'brands.id', 'brands.name'])
            ->groupBy('category_id');
    }
}