<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The shop page's filters and sort, read from the query string (the category comes from the address,
 * /category/{slug}). Applies them to a product query and builds shop URLs with one of them changed (the
 * category links and the removable filter chips).
 *
 * The search and the category are what a page is about (its heading); the rest are filters, which
 * "Clear all" removes.
 */
final class ShopFilters
{
    public const PER_PAGE = 24;

    public const SORTS = [
        'latest' => 'Newest',
        'price_low' => 'Price: low to high',
        'price_high' => 'Price: high to low',
        'saving' => 'Biggest saving',
        'name' => 'Name: A to Z',
    ];

    /**
     * @param  int[]  $brands  any of them matches
     * @param  array<int, string[]>  $attributes  attribute id => values; any value matches, every attribute must
     */
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?Category $category = null,
        public readonly array $brands = [],
        public readonly array $attributes = [],
        public readonly ?int $minPrice = null,
        public readonly ?int $maxPrice = null,
        public readonly bool $inStock = false,
        public readonly bool $sale = false,
        public readonly bool $featured = false,
        public readonly ?string $sort = null,
    ) {
    }

    /**
     * Anything malformed in the query string is ignored rather than rejected: these URLs get shared and edited.
     */
    public static function fromRequest(Request $request, ?Category $category = null): self
    {
        $search = Str::limit(self::text($request->input('search')), 100, '');

        $brands = collect(Arr::wrap($request->input('brand')))
            ->map(fn ($brand) => self::id($brand))
            ->filter()->unique()->sort()->values()->all();

        $attributes = [];
        $requested = $request->input('attributes');
        foreach (is_array($requested) ? $requested : [] as $attributeId => $values) {
            $values = collect(Arr::wrap($values))
                ->map(fn ($value) => self::text($value))
                ->filter(fn ($value) => $value !== '')->unique()->values()->all();
            if (self::id($attributeId) && $values) {
                $attributes[(int) $attributeId] = $values;
            }
        }
        ksort($attributes);

        $minPrice = self::price($request->input('min_price'));
        $maxPrice = self::price($request->input('max_price'));
        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $sale = $request->boolean('sale');
        $sort = self::text($request->input('sort'));
        // The default order for the page leaves the URL
        if (! array_key_exists($sort, self::SORTS) || $sort === ($sale ? 'saving' : 'latest')) {
            $sort = null;
        }

        return new self(
            search: $search !== '' ? $search : null,
            category: $category,
            brands: $brands,
            attributes: $attributes,
            minPrice: $minPrice,
            maxPrice: $maxPrice,
            inStock: $request->boolean('in_stock'),
            sale: $sale,
            featured: $request->boolean('featured'),
            sort: $sort,
        );
    }

    /**
     * Everything except the search, which the controller applies (it knows the search index).
     */
    public function apply(Builder $query): Builder
    {
        $query
            ->when($this->category, fn (Builder $query) => $query->where('category_id', $this->category->id))
            ->when($this->brands, fn (Builder $query) => $query->whereIn('brand_id', $this->brands))
            ->when($this->minPrice !== null, fn (Builder $query) => $query->where('base_price', '>=', $this->minPrice))
            ->when($this->maxPrice !== null, fn (Builder $query) => $query->where('base_price', '<=', $this->maxPrice))
            ->when($this->inStock, fn (Builder $query) => $query->inStock())
            ->when($this->sale, fn (Builder $query) => $query->onSale())
            ->when($this->featured, fn (Builder $query) => $query->featured());

        foreach ($this->attributes as $attributeId => $values) {
            $query->whereHas('productAttributes', fn (Builder $attribute) => $attribute
                ->where('attribute_id', $attributeId)
                ->whereIn('value', $values));
        }

        return $query;
    }

    /**
     * Deals open with the biggest savings, everything else with the newest products.
     */
    public function sortKey(): string
    {
        return $this->sort ?? ($this->sale ? 'saving' : 'latest');
    }

    public function orderBy(Builder $query): Builder
    {
        match ($this->sortKey()) {
            'price_low' => $query->orderBy('base_price'),
            'price_high' => $query->orderByDesc('base_price'),
            'saving' => $query->orderBySaving(),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        // Products that tie (seeded in the same second, same price) keep one order from page to page
        return $query->orderByDesc($query->qualifyColumn('id'));
    }

    /**
     * Whether any filter (not the search or category) is on.
     */
    public function filtered(): bool
    {
        return $this->withoutFilters() != $this;
    }

    /**
     * The same page (search, category and sort) with every filter off.
     */
    public function withoutFilters(): self
    {
        return new self(search: $this->search, category: $this->category, sort: $this->sort);
    }

    /**
     * A copy with some values changed, named like the constructor's parameters.
     */
    public function with(array $changes): self
    {
        return new self(...array_merge(get_object_vars($this), $changes));
    }

    public function url(array $changes = []): string
    {
        $filters = $this->with($changes);

        return $filters->category
            ? route('shop.category', ['category' => $filters->category] + $filters->query())
            : route('shop', $filters->query());
    }

    /**
     * The page's address without the query string (the filter form submits to it).
     */
    public function path(): string
    {
        return $this->category ? route('shop.category', $this->category) : route('shop');
    }

    /**
     * The query string for these filters, leaving out anything unset. The category is in the path.
     */
    public function query(): array
    {
        return array_filter([
            'search' => $this->search,
            'brand' => count($this->brands) === 1 ? $this->brands[0] : ($this->brands ?: null),
            'attributes' => $this->attributes ?: null,
            'min_price' => $this->minPrice,
            'max_price' => $this->maxPrice,
            'in_stock' => $this->inStock ? 1 : null,
            'sale' => $this->sale ? 1 : null,
            'featured' => $this->featured ? 1 : null,
            'sort' => $this->sort,
        ], fn ($value) => $value !== null);
    }

    /**
     * The filters that are on, as removable chips: each is a label and the URL without that one filter.
     *
     * @param  Collection  $brands  brands (id, name) to name the chosen ones
     * @param  array  $attributes  the filterable attributes (id, name) to name the chosen values
     * @return array<int, array{label: string, url: string}>
     */
    public function chips(Collection $brands, array $attributes): array
    {
        $chips = [];

        foreach ($this->brands as $brandId) {
            $chips[] = [
                'label' => $brands->firstWhere('id', $brandId)?->name ?? 'Brand',
                'url' => $this->url(['brands' => array_values(array_diff($this->brands, [$brandId]))]),
            ];
        }

        if ($this->minPrice !== null || $this->maxPrice !== null) {
            $chips[] = ['label' => $this->priceLabel(), 'url' => $this->url(['minPrice' => null, 'maxPrice' => null])];
        }

        foreach (['inStock' => 'In stock', 'sale' => 'On sale', 'featured' => 'Featured'] as $flag => $label) {
            if ($this->{$flag}) {
                $chips[] = ['label' => $label, 'url' => $this->url([$flag => false])];
            }
        }

        $attributeNames = collect($attributes)->pluck('name', 'id');
        foreach ($this->attributes as $attributeId => $values) {
            foreach ($values as $value) {
                $remaining = $this->attributes;
                $remaining[$attributeId] = array_values(array_diff($values, [$value]));
                $chips[] = [
                    'label' => ($attributeNames[$attributeId] ?? 'Option') . ': ' . $value,
                    'url' => $this->url(['attributes' => array_filter($remaining)]),
                ];
            }
        }

        return $chips;
    }

    private function priceLabel(): string
    {
        $taka = fn (int $amount) => '৳' . number_format($amount);

        return match (true) {
            $this->minPrice === null => 'Up to ' . $taka($this->maxPrice),
            $this->maxPrice === null => 'From ' . $taka($this->minPrice),
            default => $taka($this->minPrice) . ' – ' . $taka($this->maxPrice),
        };
    }

    private static function text(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }

    private static function id(mixed $value): ?int
    {
        $value = self::text($value);

        return ctype_digit($value) && (int) $value > 0 ? (int) $value : null;
    }

    /**
     * Whole taka; "50,000" and "৳50000" work too.
     */
    private static function price(mixed $value): ?int
    {
        $value = str_replace([',', '৳', ' '], '', self::text($value));

        return is_numeric($value) && $value > 0 ? (int) floor((float) $value) : null;
    }
}
