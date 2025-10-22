<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImageOptimized extends Model
{
    use HasFactory;

    protected $table = 'product_images_optimized';

    protected $fillable = [
        'product_id',
        'variant_id',
        'url',
        'alt_text',
        'sort_order',
        'is_main'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_main' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($image) {
            // If this image is being set as main, unset other main images for the same product/variant
            if ($image->is_main) {
                if ($image->product_id) {
                    static::where('product_id', $image->product_id)
                          ->where('is_main', true)
                          ->update(['is_main' => false]);
                }
                if ($image->variant_id) {
                    static::where('variant_id', $image->variant_id)
                          ->where('is_main', true)
                          ->update(['is_main' => false]);
                }
            }
        });

        static::updating(function ($image) {
            // If this image is being set as main, unset other main images for the same product/variant
            if ($image->is_main && $image->isDirty('is_main')) {
                if ($image->product_id) {
                    static::where('product_id', $image->product_id)
                          ->where('id', '!=', $image->id)
                          ->where('is_main', true)
                          ->update(['is_main' => false]);
                }
                if ($image->variant_id) {
                    static::where('variant_id', $image->variant_id)
                          ->where('id', '!=', $image->id)
                          ->where('is_main', true)
                          ->update(['is_main' => false]);
                }
            }
        });
    }

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductOptimized::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariantOptimized::class, 'variant_id');
    }

    // Scopes
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Accessors
    public function getFullUrlAttribute()
    {
        if (str_starts_with($this->url, 'http')) {
            return $this->url;
        }
        return asset('storage/' . $this->url);
    }
}