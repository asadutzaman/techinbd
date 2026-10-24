<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductOptimized;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_pages_render(): void
    {
        $product = ProductOptimized::factory()->featured()->create();
        ProductOptimized::factory()->create(['category_id' => $product->category_id]);

        $this->get('/')->assertOk()->assertSee($product->name);
        $this->get('/shop')->assertOk()->assertSee($product->name);
        $this->get(route('product.detail', $product))->assertOk()->assertSee($product->name);
        $this->get(route('shop.category', $product->category))->assertOk()->assertSee($product->name);
        $this->get('/cart')->assertOk();
        $this->get('/contact')->assertOk();
    }

    public function test_inactive_products_are_hidden(): void
    {
        $hidden = ProductOptimized::factory()->inactive()->create();

        $this->get('/shop')->assertOk()->assertDontSee($hidden->name);
        $this->get(route('product.detail', $hidden))->assertNotFound();
        $this->get('/product/' . $hidden->id)->assertNotFound();
    }

    public function test_switching_a_brand_off_hides_its_products_until_it_is_switched_on(): void
    {
        $user = User::factory()->create();
        $keyboards = Category::factory()->create(['name' => 'Keyboards', 'is_menu' => true]);
        $fantech = Brand::factory()->create(['name' => 'Fantech']);
        $hidden = ProductOptimized::factory()->featured()->onSale(2500)->create([
            'name' => 'MK853 Mechanical Keyboard', 'base_price' => 2000, 'category_id' => $keyboards->id, 'brand_id' => $fantech->id,
        ]);
        $other = ProductOptimized::factory()->featured()->create(['name' => 'K120 Office Keyboard', 'category_id' => $keyboards->id]);
        $this->actingAs($user)->post(route('cart.add'), ['product_id' => $hidden->id, 'quantity' => 2])->assertOk();

        $fantech->update(['status' => false]);

        $this->get('/')->assertOk()->assertSee($other->name)->assertDontSee($hidden->name);
        $this->get('/shop')->assertOk()->assertSee($other->name)->assertDontSee('MK853')->assertDontSee('Fantech');
        $this->assertMatchesRegularExpression('/Keyboards.*?shop-count">1</s', $this->get('/shop')->getContent());
        $this->get(route('shop.category', $keyboards))->assertOk()->assertDontSee($hidden->name);
        $this->get('/shop?search=mk853')->assertOk()->assertDontSee($hidden->name);
        $this->getJson('/search/suggestions?q=mk853')->assertOk()->assertExactJson([]);
        $this->get(route('product.detail', $hidden))->assertNotFound();
        $this->get('/product/' . $hidden->id)->assertNotFound();
        $this->postJson(route('cart.add'), ['product_id' => $hidden->id, 'quantity' => 1])->assertNotFound();

        // The line already in the cart drops out of the cart, its badge and checkout
        $this->get('/cart')->assertOk()->assertDontSee($hidden->name);
        $this->assertSame(3, preg_match_all('#class="sf-count cart-count"\s+hidden\s*>0</span>#', $this->get('/')->getContent()));
        $this->get('/checkout')->assertRedirect(route('cart'));

        $fantech->update(['status' => true]);

        $this->get('/shop')->assertSee($hidden->name)->assertSee('Fantech');
        $this->get(route('product.detail', $hidden))->assertOk();
        $this->get('/cart')->assertSee($hidden->name);
    }

    public function test_shop_sidebar_counts_active_catalog_products(): void
    {
        $category = Category::factory()->create(['name' => 'Laptops']);
        $brand = Brand::factory()->create(['name' => 'Acme']);
        ProductOptimized::factory()->count(3)->create(['category_id' => $category->id, 'brand_id' => $brand->id]);
        ProductOptimized::factory()->inactive()->create(['category_id' => $category->id, 'brand_id' => $brand->id]);

        $html = $this->get('/shop')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/Laptops.*?shop-count">3</s', $html);
        $this->assertMatchesRegularExpression('/Acme.*?shop-count">3</s', $html);
    }

    public function test_search_and_suggestions(): void
    {
        $brand = Brand::factory()->create(['name' => 'Zentrix']);
        $match = ProductOptimized::factory()->create(['name' => 'Gaming Laptop', 'brand_id' => $brand->id]);
        $other = ProductOptimized::factory()->create(['name' => 'Coffee Mug']);

        $this->get('/shop?search=laptop')->assertOk()->assertSee($match->name)->assertDontSee($other->name);
        $this->get('/shop?search=zentrix')->assertOk()->assertSee($match->name);

        $this->getJson('/search/suggestions?q=gaming')
            ->assertOk()
            ->assertJsonPath('0.name', 'Gaming Laptop')
            ->assertJsonPath('0.category', $match->category->name)
            ->assertJsonPath('0.url', url('/product/gaming-laptop'));
    }

    public function test_shop_can_show_only_price_drops(): void
    {
        ProductOptimized::factory()->onSale(150)->create(['name' => 'Discounted Drill', 'base_price' => 100]);
        ProductOptimized::factory()->create(['name' => 'Regular Hammer']);

        $html = $this->get('/shop?sale=1')
            ->assertOk()
            ->assertSee('Discounted Drill')
            ->assertDontSee('Regular Hammer')
            ->getContent();
        $this->assertMatchesRegularExpression('#class="shop-summary">\s*1 product\s#', $html);
    }

    public function test_menu_links_filter_the_shop_by_category(): void
    {
        $laptop = ProductOptimized::factory()->create();
        $mug = ProductOptimized::factory()->create();

        $this->get('/')->assertSee('href="' . url('/category/' . $laptop->category->slug) . '"', false);
        $this->get(route('shop.category', $laptop->category))
            ->assertSee($laptop->name)
            ->assertDontSee($mug->name);
    }

    public function test_cached_catalog_data_updates_when_products_change(): void
    {
        $category = Category::factory()->create(['name' => 'Monitors']);
        ProductOptimized::factory()->create(['category_id' => $category->id]);

        $this->assertMatchesRegularExpression('/Monitors.*?shop-count">1</s', $this->get('/shop')->getContent());

        ProductOptimized::factory()->create(['category_id' => $category->id]);

        $this->assertMatchesRegularExpression('/Monitors.*?shop-count">2</s', $this->get('/shop')->getContent());
    }

    public function test_query_count_does_not_grow_with_catalog_size(): void
    {
        $small = $this->queryCountsFor(3);
        $large = $this->queryCountsFor(12);

        foreach ($small as $page => $count) {
            $this->assertSame($count, $large[$page], "Query count for {$page} grew with catalog size ({$count} -> {$large[$page]})");
        }
    }

    /**
     * Build a catalog of $n categories/brands/products and count the queries per page.
     */
    private function queryCountsFor(int $n): array
    {
        $this->refreshTestDatabaseState();

        $categories = Category::factory()->count($n)->create();
        $brands = Brand::factory()->count($n)->create();
        foreach (range(0, $n - 1) as $i) {
            ProductOptimized::factory()->featured()->create([
                'category_id' => $categories[0]->id,
                'brand_id' => $brands[$i]->id,
            ]);
        }
        $product = ProductOptimized::first();

        $counts = [];
        $pages = ['home' => '/', 'shop' => '/shop', 'category' => route('shop.category', $categories[0]), 'product' => route('product.detail', $product)];
        foreach ($pages as $page => $uri) {
            cache()->flush();
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get($uri)->assertOk();
            $counts[$page] = count(DB::getQueryLog());
            DB::disableQueryLog();
        }

        return $counts;
    }

    private function refreshTestDatabaseState(): void
    {
        foreach (['product_search_index', 'product_attributes_optimized', 'product_images_optimized', 'product_variants_optimized', 'products_optimized', 'brands', 'categories'] as $table) {
            DB::table($table)->delete();
        }
    }
}
