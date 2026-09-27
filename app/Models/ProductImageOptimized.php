<?php

namespace App\Models;

use App\Models\Concerns\BustsCatalogCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\WebpImage;
use Illuminate\Support\Facades\Storage;

class ProductImageOptimized extends Model
{
    use HasFactory, BustsCatalogCache;

    protected $table = 'product_images_optimized';

    public const THUMB_WIDTH = 600;
    public const LARGE_WIDTH = 1200;

    protected $fillable = [
        'product_id',
        'variant_id',
        'url',
        'thumb_url',
        'large_url',
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

    /**
     * The small WebP copy for cards and thumbnails, falling back to the original.
     */
    public function getCardUrlAttribute()
    {
        return $this->thumb_url ? asset('storage/' . $this->thumb_url) : $this->full_url;
    }

    /**
     * The gallery-size WebP copy for the product page and its zoom, falling back to the original.
     */
    public function getGalleryUrlAttribute()
    {
        return $this->large_url ? asset('storage/' . $this->large_url) : $this->full_url;
    }

    /**
     * srcset for the product page gallery: the card copy for small screens, the large copy for the rest.
     */
    public function getGallerySrcsetAttribute(): ?string
    {
        if (! $this->thumb_url || ! $this->large_url) {
            return null;
        }

        return $this->card_url . ' ' . self::THUMB_WIDTH . 'w, ' . $this->gallery_url . ' ' . self::LARGE_WIDTH . 'w';
    }

    /**
     * Write the WebP copies of a locally stored image: THUMB_WIDTH wide for cards (thumb_url) and
     * LARGE_WIDTH wide for the product page (large_url). Neither is upscaled. A copy GD can't make
     * stays empty, and pages use the original instead.
     */
    public function generateVersions(): void
    {
        if (str_starts_with($this->url, 'http')) {
            return;
        }

        $name = pathinfo($this->url, PATHINFO_FILENAME) . '.webp';
        $thumb = WebpImage::make($this->url, 'products/thumbs/' . $name, self::THUMB_WIDTH);
        $large = WebpImage::make($this->url, 'products/large/' . $name, self::LARGE_WIDTH, quality: 82);

        $this->update([
            'thumb_url' => $thumb['path'] ?? $this->thumb_url,
            'large_url' => $large['path'] ?? $this->large_url,
        ]);
    }

    /**
     * Remove the original and its WebP copies from storage.
     */
    public function deleteFiles(): void
    {
        $disk = Storage::disk('public');
        foreach ([$this->url, $this->thumb_url, $this->large_url] as $path) {
            if ($path && ! str_starts_with($path, 'http') && $disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }
}