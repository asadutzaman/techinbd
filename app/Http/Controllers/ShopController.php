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
        
        // Handle search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('description', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('category', 'LIKE', '%' . $searchTerm . '%');
            });
        }
        
        // Handle category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }
        
        // Handle sale filter
        if ($request->has('sale') && $request->sale == '1') {
            $query->whereNotNull('sale_price');
        }
        
        // Handle sorting
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
        
        $products = $query->paginate($perPage);
        
        // Append query parameters to pagination links
        $products->appends($request->query());
        
        return view('shop', compact('products'));
    }
}
