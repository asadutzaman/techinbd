<?php

namespace App\Models;

use App\Models\Concerns\BustsCatalogCache;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, BustsCatalogCache, HasSlug;

    /**
     * Category pages live at /category/{slug}.
     */
    public static function slugType(): string
    {
        return 'category';
    }

    /**
     * The slug column is 100 characters.
     */
    protected static function slugMaxLength(): int
    {
        return 90;
    }

    protected $fillable = [
        'name',
        'description',
        'image',
        'status',
        'is_menu',
        'is_featured'
    ];

    /**
     * Words in a category's name or slug that pick its icon in <x-category-icon>, checked in
     * order ("headphones" before "phone"), so categories added in admin get a fitting icon too.
     */
    private const ICON_KEYWORDS = [
        'laptop' => '/laptop|notebook|macbook/',
        'headphones' => '/headphone|earphone|earbud|audio|speaker|sound/',
        'monitor' => '/monitor|display/',
        'tv' => '/\btv\b|television/',
        'tablet' => '/tablet|ipad|\btab\b/',
        'smartphone' => '/phone|mobile/',
        'cpu' => '/processor|\bcpu\b/',
        'circuit-board' => '/motherboard|mainboard/',
        'gpu' => '/graphics|\bgpu\b|\bvga\b/',
        'memory-stick' => '/\bram\b|memory/',
        'hard-drive' => '/\bssd\b|\bhdd\b|storage|drive/',
        'router' => '/router|network|wi-?fi/',
        'printer' => '/printer|scanner|office/',
        'keyboard' => '/keyboard|mouse|accessor/',
        'battery-charging' => '/\bups\b|power|battery|charger/',
        'camera' => '/camera|cctv|security/',
        'gamepad' => '/gaming|\bgame|console/',
        'desktop' => '/desktop|\bpc\b|computer|casing/',
    ];

    public const DEFAULT_ICON = 'package';

    protected $casts = [
        'status' => 'boolean',
        'is_menu' => 'boolean',
        'is_featured' => 'boolean'
    ];

    public function getIconAttribute(): string
    {
        $words = strtolower($this->name . ' ' . str_replace('-', ' ', (string) $this->slug));

        foreach (self::ICON_KEYWORDS as $icon => $pattern) {
            if (preg_match($pattern, $words)) {
                return $icon;
            }
        }

        return self::DEFAULT_ICON;
    }

    /**
     * An image uploaded in admin (stored in public/img) replaces the icon on the home page tile.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('img/' . $this->image) : null;
    }

    public function products()
    {
        return $this->hasMany(ProductOptimized::class);
    }

    /**
     * Active categories with `products_count` of their active products, in menu order (menu, shop sidebar).
     */
    public static function activeWithProductCounts()
    {
        return static::where('status', true)
            ->withCount(['products' => fn ($query) => $query->where('status', 1)])
            ->orderBy('sort_order')
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
