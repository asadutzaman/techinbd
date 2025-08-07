<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 9); // Default to 9 products per page
        
        // Validate per_page parameter
        if (!in_array($perPage, [9, 15, 21])) {
            $perPage = 9;
        }
        
        $query = Product::where('status', 1);
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
                  ->orWhere('category', 'LIKE', '%' . $searchTerm . '%');
            });
            
            // Order by relevance when searching
            $query->orderByRaw("
                CASE 
                    WHEN name LIKE ? THEN 1
                    WHEN name LIKE ? THEN 2
                    WHEN category LIKE ? THEN 3
                    WHEN description LIKE ? THEN 4
                    ELSE 5
                END
            ", [
                $searchTerm . '%',  // Starts with search term
                '%' . $searchTerm . '%',  // Contains search term
                '%' . $searchTerm . '%',  // Category contains
                '%' . $searchTerm . '%'   // Description contains
            ]);
        }
        
        // Handle category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }
        
        // Handle price range filter
        if ($request->has('min_price') && !empty($request->min_price)) {
            $query->whereRaw('COALESCE(sale_price, price) >= ?', [$request->min_price]);
        }
        
        if ($request->has('max_price') && !empty($request->max_price)) {
            $query->whereRaw('COALESCE(sale_price, price) <= ?', [$request->max_price]);
        }
        
        // Handle sale filter
        if ($request->has('sale') && $request->sale == '1') {
            $query->whereNotNull('sale_price');
        }
        
        // Handle stock filter
        if ($request->has('in_stock') && $request->in_stock == '1') {
            $query->where('stock', '>', 0);
        }
        
        // Handle sorting (only if not searching, as search has its own relevance ordering)
        if (!$isSearching) {
            $sortBy = $request->get('sort', 'latest');
            switch ($sortBy) {
                case 'price_low':
                    $query->orderByRaw('COALESCE(sale_price, price) ASC');
                    break;
                case 'price_high':
                    $query->orderByRaw('COALESCE(sale_price, price) DESC');
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
        
        // Get available categories for filter
        $categories = Product::where('status', 1)
                            ->select('category')
                            ->distinct()
                            ->pluck('category')
                            ->filter()
                            ->sort()
                            ->values();
        
        return view('shop', compact('products', 'searchTerm', 'isSearching', 'suggestions', 'categories'));
    }
    
    /**
     * Get search suggestions based on search term
     */
    private function getSearchSuggestions($searchTerm)
    {
        // Get similar product names
        $suggestions = Product::where('status', 1)
            ->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('category', 'LIKE', '%' . $searchTerm . '%');
            })
            ->select('name', 'category')
            ->limit(5)
            ->get()
            ->map(function($product) {
                return [
                    'name' => $product->name,
                    'category' => $product->category
                ];
            })
            ->unique('name')
            ->take(3);
            
        return $suggestions;
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
        
        $suggestions = Product::where('status', 1)
            ->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('category', 'LIKE', '%' . $searchTerm . '%');
            })
            ->select('name', 'category', 'id')
            ->limit(8)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category,
                    'url' => route('product.detail', $product->id)
                ];
            });
            
        return response()->json($suggestions);
    }
}
