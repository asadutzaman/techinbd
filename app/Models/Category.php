<?php

namespace App\Models;

use App\Models\Concerns\BustsCatalogCache;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
        'parent_id',
        'sort_order',
        'status',
        'is_menu',
        'is_featured'
    ];

    /**
     * Words in a category's name or slug that pick its icon in <x-category-icon>, checked in
     * order ("headphones" before "phone", "laptop cooler" before "laptop"), so categories added
     * in admin get a fitting icon too.
     */
    private const ICON_KEYWORDS = [
        'fan' => '/cooler|cooling|\bfan\b/',
        'laptop' => '/laptop|notebook|macbook/',
        'headphones' => '/headphone|earphone|earbud|audio|speaker|sound/',
        'watch' => '/watch|wearable|gadget/',
        'presentation' => '/interactive|flat panel|presentation|projector|office equipment/',
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
        'keyboard' => '/keyboard|accessor|peripheral/',
        'mouse' => '/mouse/',
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
     * Categories are one level deep: a top-level category and its subcategories.
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    /**
     * The category and its active subcategories: a category's page lists the products of all of them.
     */
    public function familyIds(): array
    {
        $children = $this->relationLoaded('children')
            ? $this->children->where('status', true)->pluck('id')
            : $this->children()->where('status', true)->pluck('id');

        return [$this->id, ...$children];
    }

    /**
     * Active categories with `products_count` of their active products, in menu order.
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
     * Active top-level categories in menu order, each with its active subcategories as `children`
     * and a `products_count` covering its own and its subcategories' active products (menu, shop
     * sidebar, home tiles). One query; subcategories of an inactive category are left out.
     */
    public static function tree(): Collection
    {
        $categories = static::activeWithProductCounts();
        $byParent = $categories->whereNotNull('parent_id')->groupBy('parent_id');

        return $categories->whereNull('parent_id')->each(function (Category $category) use ($byParent) {
            $children = $byParent->get($category->id, collect())->values();
            $children->each->setRelation('children', new EloquentCollection());
            $category->setRelation('children', new EloquentCollection($children->all()));
            $category->products_count += $children->sum('products_count');
        })->values();
    }

    /**
     * Active categories for admin selects, each top-level one followed by its subcategories. A
     * subcategory of an inactive category comes last, so a product's current category is always listed.
     */
    public static function nestedList(): Collection
    {
        $categories = static::where('status', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'parent_id']);
        $children = $categories->whereNotNull('parent_id')->groupBy('parent_id');
        $nested = $categories->whereNull('parent_id')->flatMap(fn (Category $category) => [$category, ...$children->get($category->id, [])]);

        return $nested->concat($categories->diff($nested))->values();
    }

    /**
     * Get attributes for this category
     */
    public function attributes()
    {
        return $this->hasMany(AttributeOptimized::class);
    }
}
