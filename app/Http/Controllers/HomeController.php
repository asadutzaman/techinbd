<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::where('featured', true)
                                  ->where('status', true)
                                  ->take(8)
                                  ->get();
        
        $categories = Category::where('is_featured', true)
                                  ->where('status', true)
                                  ->take(4)
                                  ->get();
        
        return view('home', compact('featuredProducts', 'categories'));
    }
}
