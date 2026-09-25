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
        $menuCategories = $this->catalogCache->remember('active-categories', 600, fn () => Category::activeWithProductCounts());

        $view->with('menuCategories', $menuCategories);
    }
}
