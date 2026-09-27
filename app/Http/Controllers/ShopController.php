<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\ProductOptimized;
use App\Models\Category;
use App\Models\Brand;
use App\Models\AttributeOptimized;
use App\Models\ProductAttributeOptimized;
use App\Models\ProductSearchIndex;
use App\Support\CatalogCache;
use App\Support\ShopFilters;

class ShopController extends Controller
{
    /**
     * InnoDB's default FULLTEXT stopwords: required (+word) stopwords would match nothing.
     */
    private const FULLTEXT_STOPWORDS = ['about', 'are', 'com', 'for', 'from', 'how', 'that', 'the', 'this', 'was', 'what', 'when', 'where', 'who', 'will', 'with', 'und', 'www'];

    public function __construct(private CatalogCache $catalogCache)
    {
    }

    public function index(Request $request)
    {
        $filters = ShopFilters::fromRequest($request);

        $query = ProductOptimized::active();
        if ($filters->search !== null) {
            $this->applySearch($query, $filters->search);
        }
        $filters->apply($query);

        // The price range of everything that matches, for the summary line and the price inputs
        $prices = $query->clone()->toBase()->selectRaw('min(base_price) as low, max(base_price) as high')->first();

        // Only what the product cards render
        $products = $filters->orderBy($query->with('mainImage')->withMax('variants', 'compare_price'))
            ->paginate(ShopFilters::PER_PAGE)
            ->withQueryString();

        // The sidebar lists don't depend on the other filters, so they're cached with the catalog
        $categories = $this->catalogCache->remember('active-categories', 600, fn () => Category::activeWithProductCounts());
        $brands = $this->brandsFor($filters->category);
        $totalActiveProducts = $this->catalogCache->remember('active-product-count', 600, fn () => ProductOptimized::active()->count());
        $filterableAttributes = $this->getFilterableAttributes($filters->category);

        $category = $filters->category ? $categories->firstWhere('id', $filters->category) : null;
        $brand = count($filters->brands) === 1 ? $brands->firstWhere('id', $filters->brands[0]) : null;
        $heading = match (true) {
            $filters->search !== null => 'Results for “' . $filters->search . '”',
            $category !== null => $category->name,
            $brand !== null => $brand->name,
            $filters->sale => 'Deals',
            $filters->featured => 'Featured products',
            default => 'All products',
        };

        return view('shop', [
            'products' => $products,
            'filters' => $filters,
            'heading' => $heading,
            'category' => $category,
            'categories' => $categories,
            'brands' => $brands,
            'totalActiveProducts' => $totalActiveProducts,
            'filterableAttributes' => $filterableAttributes,
            'prices' => $prices,
            'chips' => $filters->chips($brands, $filterableAttributes),
        ]);
    }

    /**
     * Active brands, each with its number of active products in the category (or in the whole shop).
     */
    private function brandsFor(?int $categoryId): Collection
    {
        return $this->catalogCache->remember('shop-brands:' . ($categoryId ?? 'all'), 600, fn () => Brand::active()
            ->withCount(['products' => fn ($products) => $products->active()
                ->when($categoryId, fn ($products) => $products->where('category_id', $categoryId))])
            ->orderBy('name')
            ->get());
    }

    /**
     * Match products against the search term. On MySQL this uses the FULLTEXT index on
     * product_search_index (kept up to date by ProductOptimized::updateSearchIndex());
     * other drivers, and terms with no indexable words, fall back to LIKE.
     */
    private function applySearch(Builder $query, string $searchTerm): void
    {
        if ($query->getConnection()->getDriverName() === 'mysql') {
            $words = preg_split('/\s+/', preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower($searchTerm)), -1, PREG_SPLIT_NO_EMPTY);
            // InnoDB doesn't index words shorter than 3 characters or stopwords
            $words = array_filter($words, fn ($word) => mb_strlen($word) >= 3 && !in_array($word, self::FULLTEXT_STOPWORDS));

            if ($words) {
                $booleanQuery = implode(' ', array_map(fn ($word) => '+' . $word . '*', $words));
                $query->whereIn('id', ProductSearchIndex::select('product_id')->whereFullText(
                    ['searchable_content', 'name', 'brand_name', 'attribute_values'],
                    $booleanQuery,
                    ['mode' => 'boolean']
                ));

                return;
            }
        }

        $query->where(function ($q) use ($searchTerm) {
            $q->where('name', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('description', 'LIKE', '%' . $searchTerm . '%')
              ->orWhereHas('category', fn ($category) => $category->where('name', 'LIKE', '%' . $searchTerm . '%'))
              ->orWhereHas('brand', fn ($brand) => $brand->where('name', 'LIKE', '%' . $searchTerm . '%'));
        });
    }

    /**
     * Get filterable attributes with the values actually used by active products
     */
    private function getFilterableAttributes(?int $selectedCategory): array
    {
        return $this->catalogCache->remember('shop-filters:' . ($selectedCategory ?? 'all'), 600, function () use ($selectedCategory) {
            $attributesQuery = AttributeOptimized::active()->filterable();

            // If category is selected, get attributes for that category
            if ($selectedCategory) {
                $attributesQuery->forCategory($selectedCategory);
            }

            $attributes = $attributesQuery->orderBy('sort_order')->get();
            if ($attributes->isEmpty()) {
                return [];
            }

            // One query for the used values of every attribute
            $usedValues = ProductAttributeOptimized::whereIn('attribute_id', $attributes->pluck('id'))
                ->whereHas('product', function ($q) use ($selectedCategory) {
                    $q->active();
                    if ($selectedCategory) {
                        $q->where('category_id', $selectedCategory);
                    }
                })
                ->distinct()
                ->get(['attribute_id', 'value'])
                ->groupBy('attribute_id');

            $filterableAttributes = [];
            foreach ($attributes as $attribute) {
                // Natural order: 4GB, 8GB, 16GB rather than 16GB, 4GB, 8GB
                $values = collect($usedValues->get($attribute->id))
                    ->pluck('value')
                    ->filter()
                    ->unique()
                    ->sort(SORT_NATURAL | SORT_FLAG_CASE)
                    ->values();

                if ($values->isNotEmpty()) {
                    $filterableAttributes[] = [
                        'id' => $attribute->id,
                        'name' => $attribute->name,
                        'slug' => $attribute->slug,
                        'type' => $attribute->type,
                        'values' => $values
                    ];
                }
            }

            return $filterableAttributes;
        });
    }

    /**
     * AJAX endpoint for search suggestions
     */
    public function searchSuggestions(Request $request)
    {
        $searchTerm = trim((string) $request->get('q', ''));

        if (strlen($searchTerm) < 2) {
            return response()->json([]);
        }

        $query = ProductOptimized::active()
            ->with('category:id,name')
            ->select('id', 'name', 'category_id')
            ->limit(8);
        $this->applySearch($query, $searchTerm);

        $suggestions = $query->get()->map(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category?->name,
            'url' => route('product.detail', $product->id)
        ]);

        return response()->json($suggestions);
    }
}
