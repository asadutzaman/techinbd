<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Category;
use App\Support\CatalogCache;

class MenuComposer
{
    public function __construct(private CatalogCache $catalogCache)
    {
    }

    public function compose(View $view)
    {
        // Top-level categories with their subcategories as `children`
        $menuCategories = $this->catalogCache->remember('category-tree', 600, fn () => Category::tree());

        $view->with('menuCategories', $menuCategories);
    }
}
