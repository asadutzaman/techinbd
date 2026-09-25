<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\ProductOptimized;
use App\Models\Category;
use App\Models\Brand;
use App\Models\AttributeOptimized;
use App\Models\ProductAttributeOptimized;
use App\Models\ProductSearchIndex;
use App\Support\CatalogCache;

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
        $perPage = $request->get('per_page', 9); // Default to 9 products per page

        // Validate per_page parameter
        if (!in_array($perPage, [9, 15, 21])) {
            $perPage = 9;
        }

        // Only what the product cards render
        $query = ProductOptimized::with('mainImage')
            ->withMax('variants', 'compare_price')
            ->active();
        $searchTerm = null;
        $isSearching = false;

        // Handle search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = trim($request->search);
            $isSearching = true;
            $this->applySearch($query, $searchTerm);
        }

        // Handle category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category_id', $request->category);
        }

        // Handle brand filter
        if ($request->has('brand') && !empty($request->brand)) {
            $query->where('brand_id', $request->brand);
        }

        // Handle product attributes filters
        $attributeFilters = $request->get('attributes', []);
        if (!empty($attributeFilters)) {
            foreach ($attributeFilters as $attributeId => $values) {
                if (!empty($values)) {
                    $query->whereHas('productAttributes', function($q) use ($attributeId, $values) {
                        $q->where('attribute_id', $attributeId);
                        if (is_array($values)) {
                            $q->whereIn('value', $values);
                        } else {
                            $q->where('value', $values);
                        }
                    });
                }
            }
        }

        // Handle price range filter
        if ($request->has('min_price') && !empty($request->min_price)) {
            $query->where('base_price', '>=', $request->min_price);
        }

        if ($request->has('max_price') && !empty($request->max_price)) {
            $query->where('base_price', '<=', $request->max_price);
        }

        // Handle featured filter
        if ($request->has('featured') && $request->featured == '1') {
            $query->featured();
        }

        // Handle stock filter
        if ($request->has('in_stock') && $request->in_stock == '1') {
            $query->inStock();
        }

        // Handle sorting (only if not searching, as search has its own relevance ordering)
        if (!$isSearching) {
            $sortBy = $request->get('sort', 'latest');
            switch ($sortBy) {
                case 'price_low':
                    $query->orderBy('base_price', 'ASC');
                    break;
                case 'price_high':
                    $query->orderBy('base_price', 'DESC');
                    break;
                case 'name':
                    $query->orderBy('name', 'ASC');
                    break;
                case 'latest':
                default:
                    $query->orderBy('created_at', 'DESC');
                    break;
            }
        }

        $products = $query->paginate($perPage);

        // Append query parameters to pagination links
        $products->appends($request->query());

        // Get search suggestions if searching
        $suggestions = collect();
        if ($isSearching && $products->count() < 5) {
            $suggestions = $this->getSearchSuggestions($searchTerm);
        }

        // Sidebar data doesn't depend on the current filters, so it's cached with the catalog
        $categories = $this->catalogCache->remember('active-categories', 600, fn () => Category::activeWithProductCounts());
        $brands = $this->catalogCache->remember('active-brands', 600, fn () => Brand::where('status', 1)
            ->withCount(['products' => fn ($q) => $q->where('status', 1)])
            ->orderBy('name')
            ->get());
        $totalActiveProducts = $this->catalogCache->remember('active-product-count', 600, fn () => ProductOptimized::active()->count());

        // Get filterable attributes with their values
        $filterableAttributes = $this->getFilterableAttributes($request->get('category'));

        return view('shop', compact(
            'products',
            'searchTerm',
            'isSearching',
            'suggestions',
            'categories',
            'brands',
            'totalActiveProducts',
            'filterableAttributes'
        ));
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
     * Get search suggestions based on search term
     */
    private function getSearchSuggestions($searchTerm)
    {
        $query = ProductOptimized::active()->select('name')->limit(5);
        $this->applySearch($query, $searchTerm);

        return $query->get()
            ->map(fn ($product) => ['name' => $product->name])
            ->unique('name')
            ->take(3);
    }

    /**
     * Get filterable attributes with the values actually used by active products
     */
    private function getFilterableAttributes($selectedCategory)
    {
        $selectedCategory = (int) $selectedCategory ?: null;

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
                $values = collect($usedValues->get($attribute->id))
                    ->pluck('value')
                    ->filter()
                    ->sort()
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

    /**
     * AJAX endpoint to get attributes for a specific category
     */
    public function getAttributesByCategory(Request $request)
    {
        $categoryId = $request->get('category_id');

        if (!$categoryId) {
            return response()->json([]);
        }

        return response()->json($this->getFilterableAttributes($categoryId));
    }
}
