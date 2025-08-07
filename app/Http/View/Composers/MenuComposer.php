<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Category;

class MenuComposer
{
    public function compose(View $view)
    {
        $menuCategories = Category::where('is_menu', true)
                                 ->where('status', true)
                                 ->withCount('products')
                                 ->orderBy('name')
                                 ->get();
        
        $view->with('menuCategories', $menuCategories);
    }
}