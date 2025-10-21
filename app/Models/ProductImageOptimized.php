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