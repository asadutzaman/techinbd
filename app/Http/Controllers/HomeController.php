<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\ProductOptimized;
use App\Support\CatalogCache;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    /** Cards per product row */
    private const ROW_SIZE = 12;

    /** Two rows of eight on computers */
    private const CATEGORY_TILES = 16;

    /** Best Sellers waits for this many products with sales, so two orders don't make a "best sellers" row */
    private const MIN_BEST_SELLERS = 4;

    public function index(CatalogCache $catalogCache)
    {
        // Everything on the page comes from the catalog, so it's cached until the catalog changes
        $home = $catalogCache->remember('home', 600, fn () => $this->homeData());

        // Banner schedules are checked on every request, so campaigns start and end on time
        $live = $home['banners']->filter->isLive();
        $home['sliderBanners'] = $live->reject->isSide()->values();
        $home['sideBanners'] = $live->filter->isSide()->take(Banner::SIDE_SLOTS)->values();
        unset($home['banners']);

        return view('home', $home);
    }

    private function homeData(): array
    {
        return [
            'banners' => Banner::active()->get(),
            'categories' => $this->categories(),
            'deals' => $this->cards()->onSale()->orderBySaving()->limit(self::ROW_SIZE)->get(),
            'featured' => $this->cards()->featured()->latest()->orderByDesc('id')->limit(self::ROW_SIZE)->get(),
            'latest' => $this->cards()->latest()->orderByDesc('id')->limit(self::ROW_SIZE)->get(),
            'bestSellers' => $this->bestSellers(),
            'productCount' => ProductOptimized::active()->count(),
        ];
    }

    /**
     * Active products with what a card shows: the main image and the compare-at price.
     */
    private function cards()
    {
        return ProductOptimized::active()->with('mainImage')->withMax('variants', 'compare_price');
    }

    /**
     * Categories with active products, limited to the featured ones when any are marked.
     */
    private function categories(): Collection
    {
        $categories = Category::activeWithProductCounts()->filter(fn ($category) => $category->products_count > 0);
        $featured = $categories->where('is_featured', true);

        return ($featured->isNotEmpty() ? $featured : $categories)->take(self::CATEGORY_TILES)->values();
    }

    /**
     * Most units ordered, not counting cancelled orders. Refreshes with the home cache, as orders don't change the catalog.
     */
    private function bestSellers(): Collection
    {
        $ranking = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->whereNotNull('order_items.product_id')
            ->groupBy('order_items.product_id')
            ->orderByRaw('sum(order_items.quantity) desc')
            ->orderBy('order_items.product_id')
            ->limit(self::ROW_SIZE)
            ->pluck('order_items.product_id');

        if ($ranking->count() < self::MIN_BEST_SELLERS) {
            return collect();
        }

        $products = $this->cards()
            ->whereIn('id', $ranking)
            ->get()
            ->sortBy(fn ($product) => $ranking->search($product->id))
            ->values();

        return $products->count() >= self::MIN_BEST_SELLERS ? $products : collect();
    }
}
