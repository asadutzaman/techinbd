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
        
        $products = Product::where('status', 1)->paginate($perPage);
        
        // Append query parameters to pagination links
        $products->appends($request->query());
        
        return view('shop', compact('products'));
    }
}
