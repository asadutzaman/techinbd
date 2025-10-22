<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductOptimized;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = ProductOptimized::with(['brand', 'category', 'mainImage'])
                                  ->featured()
                                  ->active()
                                  ->take(12)
                                  ->get();
        
        $categories = Category::where('status', true)
                                  ->take(6)
                                  ->get()
                                  ->map(function ($category) {
                                      $category->products_count = ProductOptimized::where('category_id', $category->id)
                                                                                ->where('status', 1)
                                                                                ->count();
                                      return $category;
                                  });
        
        return view('home', compact('featuredProducts', 'categories'));
    }
}
