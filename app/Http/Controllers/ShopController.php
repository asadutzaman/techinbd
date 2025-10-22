<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductOptimized;
use App\Models\Category;
use App\Models\Brand;
use App\Models\AttributeOptimized;
use App\Models\ProductAttributeOptimized;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 9); // Default to 9 products per page
        
        // Validate per_page parameter
        if (!in_array($perPage, [9, 15, 21])) {
            $perPage = 9;
        }
        
        $query = ProductOptimized::with(['category', 'brand', 'productAttributes.attribute', 'mainImage'])->active();
        $searchTerm = null;
        $isSearching = false;
        
        // Handle search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = trim($request->search);
            $isSearching = true;
            
            // Enhanced search with relevance scoring
            $query->where(function($q) use ($searchTerm) {
                // Exact name match gets highest priority
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  // Description match
                  ->orWhere('description', 'LIKE', '%' . $searchTerm . '%')
                  // Category match
                  ->orWhereHas('category', function ($query) use ($searchTerm) {
                      $query->where('name', 'LIKE', '%' . $searchTerm . '%');
                  })
                  // Brand match
                  ->orWhereHas('brand', function ($query) use ($searchTerm) {
                      $query->where('name', 'LIKE', '%' . $searchTerm . '%');
                  });
            });
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
        $suggestions = [];
        if ($isSearching && $products->count() < 5) {
            $suggestions = $this->getSearchSuggestions($searchTerm);
        }
        
        // Get filter data
        $categories = Category::where('status', 1)->orderBy('name')->get();
        $brands = Brand::where('status', 1)->orderBy('name')->get();
        
        // Get filterable attributes with their values
        $filterableAttributes = $this->getFilterableAttributes($request);
        
        return view('shop', compact(
            'products', 
            'searchTerm', 
            'isSearching', 
            'suggestions', 
            'categories', 
            'brands',
            'filterableAttributes'
        ));
    }
    
    /**
     * Get search suggestions based on search term
     */
    private function getSearchSuggestions($searchTerm)
    {
        // Get similar product names
        $suggestions = ProductOptimized::active()
            ->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhereHas('category', function ($query) use ($searchTerm) {
                      $query->where('name', 'LIKE', '%' . $searchTerm . '%');
                  });
            })
            ->select('name')
            ->limit(5)
            ->get()
            ->map(function($product) {
                return [
                    'name' => $product->name
                ];
            })
            ->unique('name')
            ->take(3);
            
        return $suggestions;
    }
    
    /**
     * Get filterable attributes with their available values
     */
    private function getFilterableAttributes(Request $request)
    {
        $selectedCategory = $request->get('category');
        
        // Get attributes that are filterable
        $attributesQuery = AttributeOptimized::active()
            ->filterable()
            ->with(['activeValues']);
        
        // If category is selected, get attributes for that category
        if ($selectedCategory) {
            $attributesQuery->forCategory($selectedCategory);
        }
        
        $attributes = $attributesQuery->orderBy('sort_order')->get();
        
        // For each attribute, get the actual values used in products
        $filterableAttributes = [];
        foreach ($attributes as $attribute) {
            $usedValues = ProductAttributeOptimized::where('attribute_id', $attribute->id)
                ->whereHas('product', function($q) use ($request, $selectedCategory) {
                    $q->active();
                    if ($selectedCategory) {
                        $q->where('category_id', $selectedCategory);
                    }
                })
                ->select('value')
                ->distinct()
                ->pluck('value')
                ->filter()
                ->sort()
                ->values();
            
            if ($usedValues->count() > 0) {
                $filterableAttributes[] = [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'slug' => $attribute->slug,
                    'type' => $attribute->type,
                    'values' => $usedValues
                ];
            }
        }
        
        return $filterableAttributes;
    }

    /**
     * AJAX endpoint for search suggestions
     */
    public function searchSuggestions(Request $request)
    {
        $searchTerm = $request->get('q', '');
        
        if (strlen($searchTerm) < 2) {
            return response()->json([]);
        }
        
        $suggestions = ProductOptimized::active()
            ->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhereHas('category', function ($query) use ($searchTerm) {
                      $query->where('name', 'LIKE', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('brand', function ($query) use ($searchTerm) {
                      $query->where('name', 'LIKE', '%' . $searchTerm . '%');
                  });
            })
            ->select('name', 'id')
            ->limit(8)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'url' => route('product.detail', $product->id)
                ];
            });
            
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
        
        // Create a mock request with the category to reuse the existing method
        $mockRequest = new Request(['category' => $categoryId]);
        $attributes = $this->getFilterableAttributes($mockRequest);
        
        return response()->json($attributes);
    }
}
