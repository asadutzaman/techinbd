<?php

namespace App\Http\Controllers;

use App\Models\ProductOptimized;
use App\Models\Category;
use App\Support\CatalogCache;

class HomeController extends Controller
{
    public function index(CatalogCache $catalogCache)
    {
        $featuredProducts = ProductOptimized::with('mainImage')
                                  ->active()
                                  ->featured()
                                  ->take(12)
                                  ->get();

        // If no featured products, get any active products
        if ($featuredProducts->isEmpty()) {
            $featuredProducts = ProductOptimized::with('mainImage')
                                      ->active()
                                      ->take(12)
                                      ->get();
        }

        $categories = $catalogCache->remember('active-categories', 600, fn () => Category::activeWithProductCounts())
                                   ->take(6);

        return view('home', compact('featuredProducts', 'categories'));
    }
}
