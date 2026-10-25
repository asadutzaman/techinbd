<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\ProductOptimized;
use App\Support\CatalogCache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /** Cards per product row */
    private const ROW_SIZE = 12;

    /** Two rows of eight on computers */
    private const CATEGORY_TILES = 16;

    /** Best Sellers waits for this many products with sales, so two orders don't make a "best sellers" row */
    private const MIN_BEST_SELLERS = 4;

    /** Top Deals are a dozen of this many biggest savings, a different dozen each day */
    private const DEAL_POOL = 96;

    private const DEALS_PER_CATEGORY = 2;

    /** Trending deals: up to this many category tabs, each ranking this many deals */
    private const TRENDING_TABS = 6;

    private const TRENDING_SIZE = 10;

    /** A category needs this many deals for a tab, so its row fills */
    private const TRENDING_MIN = 8;

    /** "Trending" counts the units ordered over this many days */
    private const TRENDING_DAYS = 30;

    public function index(CatalogCache $catalogCache)
    {
        // Everything on the page comes from the catalog, so it's cached until the catalog changes. The day is in
        // the key: Top Deals change at midnight in the shop's time zone.
        $day = now(config('shop.timezone'))->toDateString();
        $home = $catalogCache->remember('home:' . $day, 600, fn () => $this->homeData($day));

        // Banner schedules are checked on every request, so campaigns start and end on time
        $live = $home['banners']->filter->isLive();
        $home['sliderBanners'] = $live->reject->isSide()->values();
        $side = $live->filter->isSide()->values();
        $home['sideBanners'] = $side->take($side->count() >= Banner::SIDE_SLOTS ? Banner::SIDE_SLOTS : Banner::STACKED_SIDE_SLOTS);
        unset($home['banners']);

        // The cards' hearts: what's in the signed-in customer's wishlist
        $home['wishlistIds'] = Auth::check() ? Auth::user()->wishlistItems()->pluck('product_id')->all() : [];

        return view('home', $home);
    }

    private function homeData(string $day): array
    {
        $tree = Category::tree();

        return [
            'banners' => Banner::active()->get(),
            'categories' => $this->categories($tree),
            'deals' => $this->topDeals($day),
            'trending' => $this->trendingDeals($tree),
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
     * A dozen of the biggest savings: a different dozen each day and the same all day, shown biggest saving first.
     * Nothing about the deals themselves changes at midnight, so there's no countdown.
     */
    private function topDeals(string $day): Collection
    {
        $pool = ProductOptimized::active()->onSale()->orderBySaving()->orderByDesc('id')->limit(self::DEAL_POOL)->get(['id', 'category_id'])
            ->sortBy(fn ($product) => crc32($day . ':' . $product->id))
            ->values();

        // At most two from a category, so the biggest-ticket kind (TVs) doesn't fill the row; the rest only fill gaps
        $perCategory = [];
        $varied = $pool->filter(function ($product) use (&$perCategory) {
            $perCategory[$product->category_id] = ($perCategory[$product->category_id] ?? 0) + 1;

            return $perCategory[$product->category_id] <= self::DEALS_PER_CATEGORY;
        });
        $picks = $varied->concat($pool)->unique('id')->take(self::ROW_SIZE)->pluck('id');

        return $this->cards()->whereIn('id', $picks)->orderBySaving()->orderByDesc('id')->get();
    }

    /**
     * Trending deals: a tab per menu category with enough deals, in menu order. Each ranks the category's deals (its
     * subcategories', and those listed in it) by units ordered lately, then by the biggest saving.
     *
     * @return Collection<int, array{category: Category, products: Collection<int, ProductOptimized>}>
     */
    private function trendingDeals(Collection $tree): Collection
    {
        // Units ordered over the past month, leaving out cancelled orders
        $sold = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.created_at', '>=', now()->subDays(self::TRENDING_DAYS))
            ->whereNotNull('order_items.product_id')
            ->groupBy('order_items.product_id')
            ->selectRaw('order_items.product_id, sum(order_items.quantity) as units')
            ->pluck('units', 'product_id');

        // Every deal with its saving, and the other categories each is listed in (Gaming)
        $deals = ProductOptimized::active()->onSale()->select('id', 'category_id', 'base_price')->withMax('variants', 'compare_price')->get();
        $listedIn = DB::table('product_categories')
            ->whereIn('product_id', ProductOptimized::active()->onSale()->select('products_optimized.id'))
            ->get(['product_id', 'category_id'])
            ->groupBy('product_id');
        $saving = fn ($deal) => (float) $deal->variants_max_compare_price - (float) $deal->base_price;

        $tabs = $tree->filter(fn (Category $category) => $category->is_menu)
            ->map(function (Category $category) use ($deals, $listedIn, $sold, $saving) {
                $family = $category->familyIds();
                $ranked = $deals
                    ->filter(fn ($deal) => in_array($deal->category_id, $family)
                        || $listedIn->get($deal->id, collect())->contains(fn ($listing) => in_array($listing->category_id, $family)))
                    ->sortBy([
                        fn ($a, $b) => ($sold[$b->id] ?? 0) <=> ($sold[$a->id] ?? 0),
                        fn ($a, $b) => $saving($b) <=> $saving($a),
                        fn ($a, $b) => $b->id <=> $a->id,
                    ]);

                return ['category' => $category, 'ids' => $ranked->take(self::TRENDING_SIZE)->pluck('id'), 'count' => $ranked->count()];
            })
            ->filter(fn (array $tab) => $tab['count'] >= self::TRENDING_MIN)
            ->take(self::TRENDING_TABS)
            ->values();

        // One query for every tab's cards
        $cards = $this->cards()->whereIn('id', $tabs->pluck('ids')->flatten()->unique()->values())->get()->keyBy('id');

        return $tabs->map(fn (array $tab) => [
            'category' => $tab['category'],
            'products' => $tab['ids']->map(fn ($id) => $cards[$id])->values(),
        ]);
    }

    /**
     * The featured categories and subcategories with active products, in menu order (each category
     * followed by its subcategories); the top-level categories when none are featured.
     */
    private function categories(Collection $tree): Collection
    {
        $tree = $tree->filter(fn ($category) => $category->products_count > 0);
        $featured = $tree->flatMap(fn ($category) => [$category, ...$category->children])
            ->filter(fn ($category) => $category->is_featured && $category->products_count > 0);

        return ($featured->isNotEmpty() ? $featured : $tree)->take(self::CATEGORY_TILES)->values();
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
