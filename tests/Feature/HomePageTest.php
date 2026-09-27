<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductOptimized;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->assertStringContainsString('href="' . route('shop', ['category' => $laptops->id]) . '"', $tiles);
        $this->assertStringContainsString('Laptop', $tiles);
        $this->assertStringNotContainsString('Cables', $tiles, 'Only featured categories when some are featured');
        $this->assertStringNotContainsString('Empty Shelf', $tiles, 'Categories without products are left out');
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

        $this->assertMatchesRegularExpression('#category=' . $laptops->id . '">\s*<svg class="category-icon"#', $tiles);
        $this->assertStringContainsString('<img src="' . asset('img/camera-tile.png') . '"', $tiles);
    }

    public function test_category_icons_follow_the_category_name(): void
    {
        $icons = [
            'Laptop' => 'laptop', 'Desktop PC' => 'desktop', 'Monitor' => 'monitor', 'Smartphone' => 'smartphone',
            'Tablet' => 'tablet', 'Processor' => 'cpu', 'Motherboard' => 'circuit-board', 'Graphics Card' => 'gpu',
            'RAM' => 'memory-stick', 'SSD' => 'hard-drive', 'Router' => 'router', 'Printer' => 'printer',
            'Headphones' => 'headphones', 'Keyboard & Mouse' => 'keyboard', 'Television' => 'tv', 'UPS & Power' => 'battery-charging',
            'Kitchen' => 'package',
        ];

        foreach ($icons as $name => $icon) {
            $this->assertSame($icon, (new Category(['name' => $name]))->icon, $name);
        }
    }

    public function test_deals_list_marked_down_products_biggest_saving_first(): void
    {
        ProductOptimized::factory()->onSale(1200)->create(['name' => 'Small Saving Mouse', 'base_price' => 1000]);
        ProductOptimized::factory()->onSale(6000)->create(['name' => 'Big Saving Monitor', 'base_price' => 5000]);
        ProductOptimized::factory()->create(['name' => 'Full Price Cable', 'base_price' => 300]);

        $deals = Str::betweenFirst($this->get('/')->assertOk()->getContent(), 'id="deals-rail"', '</ul>');

        $this->assertMatchesRegularExpression('/Big Saving Monitor.*Small Saving Mouse/s', $deals);
        $this->assertStringNotContainsString('Full Price Cable', $deals);
        $this->assertMatchesRegularExpression('#Save\s*<span class="price">.*1,000</span>#', $deals);
        $this->assertStringContainsString('<span class="sr-only">Was </span>', $deals);
        $this->assertStringContainsString('sf-card is-deal', $deals);
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

    public function test_out_of_stock_products_cannot_be_added_to_cart(): void
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
