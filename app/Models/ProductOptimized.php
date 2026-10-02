<?php

namespace App\Models;

use App\Models\Concerns\BustsCatalogCache;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ProductOptimized extends Model
{
    use HasFactory, BustsCatalogCache, HasSlug;

    protected $table = 'products_optimized';

    /**
     * Product pages live at /product/{slug}.
     */
    public static function slugType(): string
    {
        return 'product';
    }

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
        'attributes' => 'array',
    ];

    /**
     * Specs as label => value, in the order they were entered. They're stored as a list of
     * [label, value] pairs because MySQL sorts the keys of a JSON object.
     */
    protected function specs(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $decoded = json_decode($value ?? '', true);
                if (! is_array($decoded)) {
                    return [];
                }
                // Rows saved before specs were stored as pairs hold a label => value object
                if (! array_is_list($decoded)) {
                    return $decoded;
                }

                $specs = [];
                foreach ($decoded as $pair) {
                    if (is_array($pair) && count($pair) === 2) {
                        $specs[(string) $pair[0]] = $pair[1];
                    }
                }

                return $specs;
            },
            set: function ($value) {
                if ($value === null) {
                    return null;
                }
                $pairs = collect($value)->map(fn ($specValue, $label) => [(string) $label, $specValue])->values()->all();

                return json_encode($pairs, JSON_UNESCAPED_UNICODE);
            },
        );
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
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

    /**
     * Marked down: a variant's compare-at price is above the current price.
     */
    public function scopeOnSale($query)
    {
        return $query->whereHas('variants', fn ($variants) => $variants->whereColumn('compare_price', '>', 'products_optimized.base_price'));
    }

    /**
     * Biggest taka saving first (highest compare-at price minus the current price).
     */
    public function scopeOrderBySaving($query)
    {
        return $query->orderByRaw(
            '(select max(v.compare_price) from product_variants_optimized v where v.product_id = products_optimized.id) - products_optimized.base_price desc'
        );
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

    /**
     * Card-sized image URL (the WebP thumbnail when one exists).
     * Reads the mainImage relation: eager load it (with('mainImage')) when listing products.
     */
    public function getMainImageUrlAttribute()
    {
        $mainImage = $this->mainImage->first();
        if ($mainImage) {
            // Thumbnails are written at upload time and deleted with the original
            if ($mainImage->thumb_url) {
                return $mainImage->card_url;
            }
            // Remote URLs are used as-is; local files fall back to a placeholder if missing
            if (str_starts_with($mainImage->url, 'http') || file_exists(storage_path('app/public/' . $mainImage->url))) {
                return $mainImage->full_url;
            }
        }

        // Return a placeholder image based on product ID
        $placeholderNumber = ($this->id % 8) + 1;
        return asset('img/product-' . $placeholderNumber . '.jpg');
    }

    /**
     * Up to $count headline specs as label => value, for the shop's product cards ("Chip" => "Apple M5" …).
     */
    public function keySpecs(int $count = 4): array
    {
        return collect($this->specs ?? [])->filter(fn ($value) => filled($value))->take($count)->all();
    }

    /**
     * The same headline specs without their labels, for the top of the product page ("Apple M5", "16GB unified memory" …).
     */
    public function keyFeatures(int $count = 4): array
    {
        return array_values($this->keySpecs($count));
    }

    /**
     * The product page's specification table, as group title => [label => value]: the product's own
     * specs plus attribute values they don't already list, then general details. Empty rows and
     * groups are left out. Expects productAttributes.attribute, brand and category loaded.
     */
    public function specGroups(): array
    {
        $specs = collect($this->specs ?? [])->filter(fn ($value) => filled($value))->map(fn ($value) => (string) $value);
        $listedLabels = $specs->keys()->map(fn ($label) => Str::lower($label));
        $listedValues = Str::lower($specs->implode(' | '));

        // Filter attributes (Color, Storage …) only add a row when the specs don't already say it
        foreach ($this->productAttributes as $productAttribute) {
            $label = $productAttribute->attribute?->name;
            $value = trim((string) $productAttribute->value);
            if ($label && $value !== '' && ! $listedLabels->contains(Str::lower($label)) && ! str_contains($listedValues, Str::lower($value))) {
                $specs[$label] = trim($value . ' ' . $productAttribute->attribute->unit);
            }
        }

        $general = array_filter([
            'Brand' => $this->brand?->name,
            'Category' => $this->category?->name,
            'Model' => $this->manufacturer_part_no,
            'SKU' => $this->sku,
            'Barcode' => $this->ean_upc,
            'Weight' => $this->weight > 0 ? rtrim(rtrim((string) $this->weight, '0'), '.') . ' kg' : null,
            'Dimensions' => $this->dimensions,
            'Warranty' => $this->warranty,
        ], 'filled');

        return array_filter(['Specifications' => $specs->all(), 'General' => $general]);
    }

    // Helper methods
    public function updateSearchIndex()
    {
        // Fresh load: callers usually change brand/category/attributes just before indexing
        $this->load(['brand', 'category.parent', 'productAttributes']);

        // "Gadget, Smart Watch": searching a category also finds its subcategories' products
        $categoryNames = collect([$this->category?->parent?->name, $this->category?->name])->filter()->implode(', ');

        // Update the denormalized search index
        $searchableContent = collect([
            $this->name,
            $this->short_description,
            $this->description,
            $this->brand?->name,
            $categoryNames,
            $this->productAttributes->pluck('value')->implode(' ')
        ])->filter()->implode(' ');

        $this->searchIndex()->updateOrCreate(
            ['product_id' => $this->id],
            [
                'searchable_content' => $searchableContent,
                'sku' => $this->sku,
                'name' => $this->name,
                'brand_name' => $this->brand?->name,
                'category_names' => $categoryNames ?: null,
                'attribute_values' => $this->productAttributes->pluck('value')->implode(' '),
                'price' => $this->base_price,
                'status' => $this->status ?? 1,
                'stock_status' => $this->stock_status ?? 'in_stock',
                'total_stock' => $this->total_stock ?? 0,
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