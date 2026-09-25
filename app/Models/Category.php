<?php

namespace App\Models;

use App\Models\Concerns\BustsCatalogCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, BustsCatalogCache;

    protected $fillable = [
        'name',
        'description',
        'image',
        'status',
        'is_menu',
        'is_featured'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_menu' => 'boolean',
        'is_featured' => 'boolean'
    ];

    public function products()
    {
        return $this->hasMany(ProductOptimized::class);
    }

    /**
     * Active categories with `products_count` of their active products (menu, shop sidebar, home).
     */
    public static function activeWithProductCounts()
    {
        return static::where('status', true)
            ->withCount(['products' => fn ($query) => $query->where('status', 1)])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get attributes for this category
     */
    public function attributes()
    {
        return $this->hasMany(AttributeOptimized::class);
    }
}
