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
        'menu_name',
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
     * order ("earphone" before "phone", "laptop cooler" before "laptop", "gaming mouse" is a mouse),
     * so categories added in admin get a fitting icon too.
     */
    private const ICON_KEYWORDS = [
        'fan' => '/cooler|cooling|\bfan\b/',
        'laptop' => '/laptop|notebook|macbook/',
        'mic' => '/microphone|\bmic\b/',
        'earbuds' => '/earbud|earphone|neckband|\btws\b/',
        'speaker' => '/speaker|sound ?bar/',
        'headphones' => '/headphone|headset|audio/',
        'watch' => '/watch|wearable|gadget/',
        'presentation' => '/interactive|flat panel|presentation|projector|office equipment/',
        'monitor' => '/monitor|display/',
        'tv' => '/\btv\b|television/',
        'tablet' => '/tablet|ipad|\btab\b/',
        'smartphone' => '/phone|mobile/',
        'cpu' => '/processor|\bcpu\b|component/',
        'circuit-board' => '/motherboard|mainboard/',
        'gpu' => '/graphics|\bgpu\b|\bvga\b/',
        'memory-stick' => '/\bram\b|memory/',
        'hard-drive' => '/\bssd\b|\bhdd\b|storage|drive/',
        'router' => '/router|network|wi-?fi/',
        'receipt' => '/\bpos\b|receipt|thermal/',
        'printer' => '/printer|scanner|office/',
        'keyboard' => '/keyboard|peripheral/',
        'usb' => '/accessor|\busb\b|pen ?drive|\bhub\b/',
        'mouse-pad' => '/mouse ?pad|desk ?mat/',
        'mouse' => '/mouse/',
        'power' => '/power supply|\bpsu\b/',
        'battery-charging' => '/power ?bank|\bups\b|battery|power/',
        'plug-zap' => '/charger|adapter/',
        'cable' => '/\bcables?\b/',
        'house-plug' => '/smart home|\bhome\b|appliance/',
        'webcam' => '/webcam/',
        'camera' => '/camera|cctv|security/',
        'pc-case' => '/casing|\bcase\b|cabinet/',
        'desktop' => '/desktop|\bpc\b|computer/',
        'gamepad' => '/gaming|\bgame|console/',
    ];

    public const DEFAULT_ICON = 'package';

    /**
     * Each icon's colour on the home page's category tiles, on a pale tint of itself. Strong enough to read on
     * the tint (3:1 or more), and the default tiles' neighbours differ.
     */
    private const ICON_COLORS = [
        'laptop' => '#2563EB',
        'desktop' => '#7C3AED',
        'monitor' => '#0284C7',
        'gpu' => '#059669',
        'cpu' => '#EA580C',
        'memory-stick' => '#4F46E5',
        'keyboard' => '#475569',
        'mouse' => '#DB2777',
        'headphones' => '#9333EA',
        'watch' => '#0D9488',
        'earbuds' => '#E11D48',
        'battery-charging' => '#B45309',
        'gamepad' => '#C026D3',
        'tv' => '#DC2626',
        'printer' => '#0891B2',
        'router' => '#15803D',
        'circuit-board' => '#4D7C0F',
        'hard-drive' => '#57534E',
        'fan' => '#0369A1',
        'speaker' => '#C2410C',
        'plug-zap' => '#A16207',
        'cable' => '#A16207',
        'power' => '#B91C1C',
        'pc-case' => '#4338CA',
        'webcam' => '#BE123C',
        'camera' => '#BE123C',
        'mic' => '#A21CAF',
        'house-plug' => '#047857',
        'presentation' => '#C2410C',
        'receipt' => '#0F766E',
        'usb' => '#334155',
        'mouse-pad' => '#BE185D',
        'smartphone' => '#1D4ED8',
        'tablet' => '#0369A1',
        'package' => '#6B7280',
    ];

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
     * The colour of the category's icon on its home page tile.
     */
    public function getIconColorAttribute(): string
    {
        return self::ICON_COLORS[$this->icon] ?? self::ICON_COLORS[self::DEFAULT_ICON];
    }

    /**
     * What the menus call a subcategory under its category: the menu name when one is set ("ASUS" under
     * Laptop), else the name. Pages, breadcrumbs and tiles use the name.
     */
    public function getMenuLabelAttribute(): string
    {
        return $this->menu_name ?: $this->name;
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
     * Products from other categories listed here too (Gaming Keyboard lists gaming keyboards from Keyboard).
     */
    public function extraProducts()
    {
        return $this->belongsToMany(ProductOptimized::class, 'product_categories', 'category_id', 'product_id')->withTimestamps();
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
     * Active categories with `products_count` of their active products (their own and those listed
     * there too), in menu order.
     */
    public static function activeWithProductCounts()
    {
        return static::where('status', true)
            ->withCount([
                'products' => fn ($query) => $query->active(),
                'extraProducts' => fn ($query) => $query->active(),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->each(function (Category $category) {
                $category->products_count += $category->extra_products_count;
            });
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
