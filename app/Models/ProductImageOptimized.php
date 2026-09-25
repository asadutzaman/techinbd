<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImageOptimized extends Model
{
    use HasFactory;

    protected $table = 'product_images_optimized';

    public const THUMB_WIDTH = 600;

    protected $fillable = [
        'product_id',
        'variant_id',
        'url',
        'thumb_url',
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
     * Write a THUMB_WIDTH-wide WebP copy of a locally stored image and record it in thumb_url.
     * Leaves thumb_url empty (cards use the original) if GD can't read the file.
     */
    public function generateThumbnail(): void
    {
        $disk = Storage::disk('public');
        if (str_starts_with($this->url, 'http') || ! $disk->exists($this->url) || ! function_exists('imagewebp')) {
            return;
        }

        $source = @imagecreatefromstring($disk->get($this->url));
        if (! $source) {
            return;
        }

        imagepalettetotruecolor($source);
        $thumb = imagesx($source) > self::THUMB_WIDTH ? imagescale($source, self::THUMB_WIDTH) : $source;
        imagesavealpha($thumb, true);

        ob_start();
        imagewebp($thumb, null, 80);
        $webp = ob_get_clean();

        $thumbPath = 'products/thumbs/' . pathinfo($this->url, PATHINFO_FILENAME) . '.webp';
        $disk->put($thumbPath, $webp);
        $this->update(['thumb_url' => $thumbPath]);
    }

    /**
     * Remove the original and thumbnail files from storage.
     */
    public function deleteFiles(): void
    {
        $disk = Storage::disk('public');
        foreach ([$this->url, $this->thumb_url] as $path) {
            if ($path && ! str_starts_with($path, 'http') && $disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }
}