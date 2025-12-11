<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductOptimized;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = ProductOptimized::with(['brand', 'images'])
                                  ->where('featured', true)
                                  ->where('status', 1)
                                  ->take(12)
                                  ->get();
        
        // If no featured products, get any active products
        if ($featuredProducts->isEmpty()) {
            $featuredProducts = ProductOptimized::with(['brand', 'images'])
                                      ->where('status', 1)
                                      ->take(12)
                                      ->get();
        }
        
        $categories = Category::where('status', true)
                                  ->take(6)
                                  ->get()
                                  ->map(function ($category) {
                                      // Count products in this category through the product_categories table
                                      $category->products_count = \DB::table('product_categories')
                                                                    ->join('products_optimized', 'product_categories.product_id', '=', 'products_optimized.id')
                                                                    ->where('product_categories.category_id', $category->id)
                                                                    ->where('products_optimized.status', 1)
                                                                    ->count();
                                      return $category;
                                  });
        
        return view('home', compact('featuredProducts', 'categories'));
    }
}
