<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Category;
use App\Models\ProductOptimized;

class MenuComposer
{
    public function compose(View $view)
    {
        $menuCategories = Category::where('status', true)
                                 ->orderBy('name')
                                 ->get()
                                 ->map(function ($category) {
                                     $category->products_count = ProductOptimized::where('category_id', $category->id)
                                                                               ->where('status', 1)
                                                                               ->count();
                                     return $category;
                                 });
        
        $view->with('menuCategories', $menuCategories);
    }
}