<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductOptimized;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_with_an_empty_catalog(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<h1 class="sr-only">' . config('shop.name') . '</h1>', false)
            ->assertDontSee('id="categories-title"', false)
            ->assertDontSee('id="deals-title"', false);
    }

    public function test_the_earlier_home_sections_are_gone(): void
    {
        ProductOptimized::factory()->onSale(2000)->create(['base_price' => 1500]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('brand-wall')
            ->assertDontSee('price-board')
            ->assertDontSee('Phones to panjabis')
            ->assertDontSee('Anek+Latin');
    }

    public function test_category_tiles_link_to_the_shop_and_prefer_featured_categories(): void
    {
        $laptops = Category::factory()->create(['name' => 'Laptop', 'is_featured' => true]);
        $cables = Category::factory()->create(['name' => 'Cables', 'is_featured' => false]);
        Category::factory()->create(['name' => 'Empty Shelf', 'is_featured' => true]);
        ProductOptimized::factory()->create(['category_id' => $laptops->id]);
        ProductOptimized::factory()->create(['category_id' => $cables->id]);

        $tiles = $this->categoryTiles();

        $this->assertStringContainsString('href="' . route('shop.category', $laptops) . '"', $tiles);
        $this->assertStringContainsString('Laptop', $tiles);
        $this->assertStringNotContainsString('Cables', $tiles, 'Only featured categories when some are featured');
        $this->assertStringNotContainsString('Empty Shelf', $tiles, 'Categories without products are left out');
    }

    public function test_featured_subcategories_get_tiles_and_a_category_counts_its_subcategories(): void
    {
        $gadgets = Category::factory()->create(['name' => 'Gadget', 'is_featured' => true, 'sort_order' => 1]);
        $watches = Category::factory()->create(['name' => 'Smart Watch', 'parent_id' => $gadgets->id, 'is_featured' => true]);
        $bands = Category::factory()->create(['name' => 'Fitness Band', 'parent_id' => $gadgets->id, 'is_featured' => false]);
        $printers = Category::factory()->create(['name' => 'Printer', 'is_featured' => false, 'sort_order' => 2]);
        $laser = Category::factory()->create(['name' => 'Laser Printer', 'parent_id' => $printers->id, 'is_featured' => true]);
        // Gadget has no products of its own
        ProductOptimized::factory()->create(['category_id' => $watches->id]);
        ProductOptimized::factory()->create(['category_id' => $bands->id]);
        ProductOptimized::factory()->create(['category_id' => $laser->id]);

        $tiles = $this->categoryTiles();

        $this->assertMatchesRegularExpression('#Gadget.*Smart Watch.*Laser Printer#s', $tiles, 'Each category, then its subcategories');
        $this->assertStringContainsString('href="' . route('shop.category', $laser) . '"', $tiles);
        $this->assertStringNotContainsString('Fitness Band', $tiles);
        $this->assertStringNotContainsString('>Printer<', $tiles);
    }

    public function test_all_categories_with_products_show_when_none_are_featured(): void
    {
        $cables = Category::factory()->create(['name' => 'Cables']);
        $mice = Category::factory()->create(['name' => 'Mice']);
        ProductOptimized::factory()->create(['category_id' => $cables->id]);
        ProductOptimized::factory()->create(['category_id' => $mice->id]);

        $tiles = $this->categoryTiles();

        $this->assertStringContainsString('Cables', $tiles);
        $this->assertStringContainsString('Mice', $tiles);
    }

    public function test_category_tiles_show_an_icon_unless_an_image_was_uploaded(): void
    {
        $laptops = Category::factory()->create(['name' => 'Gaming Laptop']);
        $photo = Category::factory()->create(['name' => 'Cameras', 'image' => 'camera-tile.png']);
        ProductOptimized::factory()->create(['category_id' => $laptops->id]);
        ProductOptimized::factory()->create(['category_id' => $photo->id]);

        $tiles = $this->categoryTiles();

        $this->assertMatchesRegularExpression('#' . preg_quote(route('shop.category', $laptops), '#') . '" style="--tile-color: \#2563EB">\s*<span class="home-category-icon"><svg class="category-icon"#', $tiles);
        $this->assertStringContainsString('<img src="' . asset('img/camera-tile.png') . '"', $tiles);
    }

    public function test_category_icons_follow_the_category_name(): void
    {
        $icons = [
            'Laptop' => 'laptop', 'Apple MacBook' => 'laptop', 'Laptop Cooler' => 'fan', 'Gaming Laptop' => 'laptop',
            'Desktop PC' => 'desktop', 'Brand PC' => 'desktop', 'Gaming PC' => 'desktop', 'Monitor' => 'monitor', 'Gaming Monitor' => 'monitor',
            'PC Components' => 'cpu', 'Graphics Card' => 'gpu', 'Processor' => 'cpu', 'Motherboard' => 'circuit-board', 'RAM' => 'memory-stick',
            'Casing' => 'pc-case', 'Gaming Casing' => 'pc-case', 'Power Supply' => 'power', 'CPU Cooler' => 'fan', 'SSD' => 'hard-drive',
            'Peripherals' => 'keyboard', 'Keyboard' => 'keyboard', 'Gaming Keyboard' => 'keyboard', 'Mouse' => 'mouse', 'Gaming Mouse' => 'mouse',
            'Mouse Pad' => 'mouse-pad', 'Gaming Mouse Pad' => 'mouse-pad', 'Headphone' => 'headphones', 'Gaming Headphone' => 'headphones',
            'Headphones' => 'headphones', 'Computer Accessory' => 'usb', 'Webcam' => 'webcam', 'Microphone' => 'mic',
            'Smart Gadget' => 'watch', 'Gadget' => 'watch', 'Smart Watch' => 'watch', 'Earbuds' => 'earbuds', 'Earphone' => 'earbuds',
            'Power Bank' => 'battery-charging', 'Speaker' => 'speaker', 'Smart Home' => 'house-plug', 'Camera' => 'camera',
            'Charger' => 'plug-zap', 'Cable' => 'cable', 'Gaming' => 'gamepad', 'TV' => 'tv', 'Smart TV' => 'tv', 'Television' => 'tv',
            'Office Equipment' => 'presentation', 'Interactive Flat Panel' => 'presentation', 'Printer' => 'printer',
            'Laser Printer' => 'printer', 'POS Printer' => 'receipt', 'Networking' => 'router', 'Router' => 'router',
            'Smartphone' => 'smartphone', 'Tablet' => 'tablet', 'Keyboard & Mouse' => 'keyboard', 'UPS & Power' => 'battery-charging',
            'Kitchen' => 'package',
        ];

        $fallback = Blade::render('<x-category-icon name="package" />');
        foreach ($icons as $name => $icon) {
            $category = new Category(['name' => $name]);
            $this->assertSame($icon, $category->icon, $name);
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/', $category->icon_color, $name);
            if ($icon !== 'package') {
                $this->assertNotSame($fallback, Blade::render('<x-category-icon :name="$icon" />', ['icon' => $icon]), "{$icon} has a drawing");
            }
        }
    }

    public function test_top_deals_list_marked_down_products_biggest_saving_first(): void
    {
        ProductOptimized::factory()->onSale(1200)->create(['name' => 'Small Saving Mouse', 'base_price' => 1000]);
        ProductOptimized::factory()->onSale(6000)->create(['name' => 'Big Saving Monitor', 'base_price' => 5000]);
        ProductOptimized::factory()->create(['name' => 'Full Price Cable', 'base_price' => 300]);

        $html = $this->get('/')->assertOk()->getContent();
        $deals = Str::betweenFirst($html, 'id="deals-rail"', '</ul>');

        // The panel: the TOP DEALS tile is the heading, with a button to every deal
        $this->assertMatchesRegularExpression('#<h2 class="home-top-deals-tile" id="deals-title">.*Top.*Deals</span></h2>#', $html);
        $this->assertStringContainsString('<a class="home-top-deals-all" href="' . route('shop', ['sale' => 1]) . '">See all Top Deals</a>', $html);

        $this->assertMatchesRegularExpression('/Big Saving Monitor.*Small Saving Mouse/s', $deals);
        $this->assertStringNotContainsString('Full Price Cable', $deals);
        $this->assertMatchesRegularExpression('#<span class="sf-card-save-pill">Save <span class="price">.*1,000</span>#', $deals);
        $this->assertStringContainsString('<p class="sf-card-label">Top Deal</p>', $deals);
        $this->assertMatchesRegularExpression('#<p class="sf-card-old"><s><span class="sr-only">Was </span><span class="price">.*6,000</span>#', $deals);
        $this->assertStringContainsString('class="sf-card is-deal is-rail"', $deals);
    }

    public function test_top_deals_are_a_new_dozen_each_day(): void
    {
        foreach (range(1, 30) as $i) {
            ProductOptimized::factory()->onSale(1000 + $i * 10)->create(['name' => "Deal {$i}", 'base_price' => 1000]);
        }

        $this->travelTo(Carbon::parse('2026-10-24 09:00', 'Asia/Dhaka'));
        $friday = $this->dealNumbers();
        $this->travelTo(Carbon::parse('2026-10-24 23:50', 'Asia/Dhaka'));
        $this->assertSame($friday, $this->dealNumbers(), 'The same dozen all day');
        $this->travelTo(Carbon::parse('2026-10-25 00:10', 'Asia/Dhaka'));
        $saturday = $this->dealNumbers();

        $this->assertCount(12, $friday);
        $this->assertCount(12, $saturday);
        $this->assertNotEquals($friday, $saturday, 'A new dozen after midnight in the shop');
        $this->assertSame(collect($saturday)->sortDesc()->values()->all(), $saturday, 'Biggest saving first');
        $this->get('/')->assertSee('See all Top Deals')->assertDontSee('countdown');
    }

    public function test_top_deals_take_two_at_most_from_a_category_while_others_are_on_sale(): void
    {
        $tvs = Category::factory()->create(['name' => 'Smart TV']);
        foreach (range(1, 14) as $i) {
            ProductOptimized::factory()->onSale(50000 + $i * 1000)->create(['name' => "Deal TV {$i}", 'base_price' => 40000, 'category_id' => $tvs->id]);
        }
        foreach (range(1, 10) as $i) {
            ProductOptimized::factory()->onSale(1500)->create(['name' => "Deal Gadget {$i}", 'base_price' => 1000]);
        }

        $rail = Str::betweenFirst($this->get('/')->assertOk()->getContent(), 'id="deals-rail"', '</ul>');

        $this->assertSame(2, substr_count($rail, '>Deal TV '), 'Two TVs, then the ten gadgets');
        $this->assertSame(10, substr_count($rail, '>Deal Gadget '));

        // With nothing else on sale, more of one category fill the row (models saved one by one, which refreshes the home cache)
        ProductOptimized::where('name', 'like', 'Deal Gadget%')->get()->each->update(['status' => 0]);
        $this->assertSame(12, substr_count(Str::betweenFirst($this->get('/')->assertOk()->getContent(), 'id="deals-rail"', '</ul>'), '>Deal TV '));
    }

    public function test_trending_deals_rank_a_category_by_recent_sales_then_saving(): void
    {
        $laptops = Category::factory()->create(['name' => 'Laptop', 'is_menu' => true, 'sort_order' => 1]);
        $gaming = Category::factory()->create(['name' => 'Gaming', 'is_menu' => true, 'sort_order' => 2]);
        $gamingLaptops = Category::factory()->create(['name' => 'Gaming Laptop', 'is_menu' => true, 'parent_id' => $gaming->id]);
        $printers = Category::factory()->create(['name' => 'Printer', 'is_menu' => true, 'sort_order' => 3]);
        $laptopDeals = collect(range(1, 9))->map(fn ($i) => ProductOptimized::factory()->onSale(1000 + $i * 100)->create([
            'name' => "Laptop Deal {$i}", 'base_price' => 1000, 'category_id' => $laptops->id,
        ]));
        foreach (range(1, 5) as $i) {
            ProductOptimized::factory()->onSale(1500)->create(['name' => "Gaming Deal {$i}", 'base_price' => 1000, 'category_id' => $gamingLaptops->id]);
        }
        foreach (range(1, 3) as $i) {
            ProductOptimized::factory()->onSale(2000)->create(['name' => "Printer Deal {$i}", 'base_price' => 1000, 'category_id' => $printers->id]);
        }
        // Gaming lists three of the laptops too
        $laptopDeals->take(3)->each(fn ($deal) => $deal->extraCategories()->attach($gamingLaptops));
        // Sold this month, the smallest saving leads; a cancelled order and one from two months ago don't count
        $this->sell($laptopDeals[0], 3);
        $this->sell($laptopDeals[1], 50, 'cancelled');
        $this->travelTo(now()->subDays(60));
        $this->sell($laptopDeals[2], 20);
        $this->travelBack();

        $html = $this->get('/')->assertOk()->getContent();
        $tabs = Str::betweenFirst($html, 'role="tablist"', '</div>');

        $this->assertMatchesRegularExpression('#role="tab" id="trending-tab-' . $laptops->slug . '" aria-controls="trending-' . $laptops->slug . '"\s+aria-selected="true" tabindex="0"#', $tabs);
        $this->assertMatchesRegularExpression('#id="trending-tab-' . $gaming->slug . '".*aria-selected="false" tabindex="-1"#s', $tabs);
        $this->assertStringNotContainsString('Printer', $tabs, 'Three deals are too few for a tab');
        $this->assertMatchesRegularExpression('#<a class="sf-panel-more" href="' . preg_quote(route('shop.category', ['category' => $laptops, 'sale' => 1]), '#') . '" data-tabs-more>#', $html);
        $this->assertMatchesRegularExpression('#<div role="tabpanel" id="trending-' . $gaming->slug . '" aria-labelledby="trending-tab-' . $gaming->slug . '"\s+hidden\s*>#', $html);

        $this->assertSame(['Laptop Deal 1', 'Laptop Deal 9', 'Laptop Deal 8', 'Laptop Deal 7', 'Laptop Deal 6', 'Laptop Deal 5', 'Laptop Deal 4', 'Laptop Deal 3', 'Laptop Deal 2'],
            $this->trendingNames($html, $laptops));
        $this->assertSame(['Laptop Deal 1', 'Gaming Deal 5', 'Gaming Deal 4', 'Gaming Deal 3', 'Gaming Deal 2', 'Gaming Deal 1', 'Laptop Deal 3', 'Laptop Deal 2'],
            $this->trendingNames($html, $gaming), 'Listed laptops rank in Gaming too');
        $this->assertSame(['#1', '#2', '#3'], array_slice($this->trendingRanks($html, $laptops), 0, 3));
    }

    public function test_trending_deals_show_six_tabs_at_most_in_menu_order(): void
    {
        foreach (range(1, 7) as $position) {
            $category = Category::factory()->create(['name' => "Aisle {$position}", 'is_menu' => true, 'sort_order' => $position]);
            ProductOptimized::factory()->count(8)->onSale(1500)->create(['base_price' => 1000, 'category_id' => $category->id]);
        }

        $tabs = Str::betweenFirst($this->get('/')->assertOk()->getContent(), 'role="tablist"', '</div>');

        preg_match_all('#role="tab"[^>]*>([^<]+)</button>#', $tabs, $names);
        $this->assertSame(['Aisle 1', 'Aisle 2', 'Aisle 3', 'Aisle 4', 'Aisle 5', 'Aisle 6'], $names[1]);
    }

    public function test_home_cards_open_the_product_and_save_it_to_the_wishlist_without_buttons(): void
    {
        $keyboard = ProductOptimized::factory()->featured()->create(['name' => 'Office Keyboard']);
        ProductOptimized::factory()->featured()->create(['name' => 'Coming Soon Mouse', 'stock_status' => 'preorder']);

        // Guests are asked to sign in
        $featured = Str::betweenFirst($this->get('/')->assertOk()->getContent(), 'id="featured-rail"', '</ul>');
        $this->assertStringContainsString('<a class="sf-card-wish" href="' . route('login') . '" aria-label="Sign in to save Office Keyboard to your wishlist">', $featured);
        $this->assertStringContainsString('<p class="sf-card-status">Pre-order</p>', $featured);
        $this->assertStringNotContainsString('sf-card-actions', $featured);
        $this->assertStringNotContainsString('sf-card-save-pill', $featured, 'Nothing is marked down');

        // A signed-in customer's heart says whether it's saved, and toggles through the wishlist
        $customer = User::factory()->create();
        $customer->wishlistItems()->create(['product_id' => $keyboard->id]);
        $featured = Str::betweenFirst($this->actingAs($customer)->get('/')->assertOk()->getContent(), 'id="featured-rail"', '</ul>');
        $this->assertMatchesRegularExpression('#data-product-id="' . $keyboard->id . '"\s+aria-pressed="true" aria-label="Save Office Keyboard to your wishlist">\s*<i class="fas fa-heart"#', $featured);
        $this->assertMatchesRegularExpression('#data-wishlist-url="' . preg_quote(route('customer.wishlist.toggle'), '#') . '"#', $featured);

        $this->postJson(route('customer.wishlist.toggle'), ['product_id' => $keyboard->id])
            ->assertOk()
            ->assertJson(['in_wishlist' => false, 'wishlist_count' => 0]);
    }

    public function test_featured_and_latest_rows(): void
    {
        ProductOptimized::factory()->featured()->create(['name' => 'Picked Keyboard', 'created_at' => now()->subDays(3)]);
        ProductOptimized::factory()->create(['name' => 'Older Router', 'created_at' => now()->subDays(2)]);
        ProductOptimized::factory()->create(['name' => 'Newest Tablet', 'created_at' => now()->subDay()]);

        $html = $this->get('/')->assertOk()->getContent();
        $featured = Str::betweenFirst($html, 'id="featured-rail"', '</ul>');
        $latest = Str::betweenFirst($html, 'id="latest-rail"', '</ul>');

        $this->assertStringContainsString('Picked Keyboard', $featured);
        $this->assertStringNotContainsString('Older Router', $featured);
        $this->assertMatchesRegularExpression('/Newest Tablet.*Older Router.*Picked Keyboard/s', $latest);
        $this->assertStringContainsString('href="' . route('shop', ['featured' => 1]) . '"', $html);
    }

    public function test_best_sellers_wait_for_four_products_with_sales(): void
    {
        $products = ProductOptimized::factory()->count(4)->create();
        $this->sell($products[0], 5);
        $this->sell($products[1], 3);
        $this->sell($products[2], 1);

        $this->get('/')->assertOk()->assertDontSee('id="best-sellers-title"', false);
    }

    public function test_best_sellers_are_ordered_by_units_sold_without_cancelled_orders(): void
    {
        [$top, $second, $third, $fourth, $cancelled] = ProductOptimized::factory()->count(5)->create()->all();
        $this->sell($top, 2);
        $this->sell($top, 7);
        $this->sell($second, 6);
        $this->sell($third, 4);
        $this->sell($fourth, 1);
        $this->sell($cancelled, 50, 'cancelled');

        $row = Str::betweenFirst($this->get('/')->assertOk()->getContent(), 'id="best-sellers-rail"', '</ul>');

        $this->assertMatchesRegularExpression(
            '/' . preg_quote(e($top->name), '/') . '.*' . preg_quote(e($second->name), '/') . '.*' . preg_quote(e($third->name), '/') . '.*' . preg_quote(e($fourth->name), '/') . '/s',
            $row
        );
        $this->assertStringNotContainsString(e($cancelled->name), $row);
    }

    public function test_out_of_stock_products_say_so_and_the_home_page_has_no_add_to_cart(): void
    {
        ProductOptimized::factory()->featured()->create(['stock_status' => 'out_of_stock']);

        $this->get('/')->assertOk()->assertSee('Out of stock')->assertDontSee('Add to cart');
    }

    public function test_home_updates_when_a_price_changes(): void
    {
        $product = ProductOptimized::factory()->onSale(2000)->create(['name' => 'Rice Cooker', 'base_price' => 1800]);
        $this->get('/')->assertSee('1,800');

        $product->update(['base_price' => 1650]);

        $this->get('/')->assertSee('1,650')->assertDontSee('1,800');
    }

    /**
     * The numbers of the "Deal N" cards in the Deals of the Day rail, in order.
     */
    private function dealNumbers(): array
    {
        $rail = Str::betweenFirst($this->get('/')->assertOk()->getContent(), 'id="deals-rail"', '</ul>');
        preg_match_all('#class="sf-card-title"><a href="[^"]*">Deal (\d+)</a>#', $rail, $matches);

        return array_map('intval', $matches[1]);
    }

    /**
     * The product names in a Trending deals tab's row, in rank order.
     */
    private function trendingNames(string $html, Category $category): array
    {
        preg_match_all('#class="sf-card-title"><a href="[^"]*">([^<]*)</a>#', Str::betweenFirst($html, 'id="trending-' . $category->slug . '-rail"', '</ul>'), $matches);

        return $matches[1];
    }

    private function trendingRanks(string $html, Category $category): array
    {
        preg_match_all('#<span class="sf-card-rank">(\#\d+)</span>#', Str::betweenFirst($html, 'id="trending-' . $category->slug . '-rail"', '</ul>'), $matches);

        return $matches[1];
    }

    private function categoryTiles(): string
    {
        return Str::betweenFirst($this->get('/')->assertOk()->getContent(), 'class="home-categories"', '</ul>');
    }

    private function sell(ProductOptimized $product, int $quantity, string $status = 'delivered'): void
    {
        $order = Order::forceCreate([
            'order_number' => 'ORD-' . Str::upper(Str::random(8)),
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '01700000000',
            'billing_address' => 'Dhaka',
            'subtotal' => $product->base_price * $quantity,
            'total' => $product->base_price * $quantity,
            'status' => $status,
        ]);

        OrderItem::forceCreate([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->base_price,
            'quantity' => $quantity,
            'total' => $product->base_price * $quantity,
        ]);
    }
}
