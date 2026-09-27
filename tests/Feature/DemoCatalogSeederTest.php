<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductImageOptimized;
use App\Models\ProductOptimized;
use App\Models\ProductSearchIndex;
use App\Models\ProductVariantOptimized;
use Database\Seeders\DemoCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_100_tech_products_retires_the_fashion_demo_and_is_safe_to_rerun(): void
    {
        Storage::fake('public');

        // Left over from the earlier fashion demo catalog, plus a product an admin added
        $fashion = Category::factory()->create(['slug' => 'mens-fashion', 'is_menu' => true]);
        $homeGoods = Category::factory()->create(['slug' => 'home-garden', 'is_menu' => true, 'is_featured' => true]);
        $levis = Brand::factory()->create(['slug' => 'levis']);
        $oldDemo = ProductOptimized::factory()->create(['sku' => 'TIB-MEN-001', 'category_id' => $fashion->id, 'brand_id' => $levis->id]);
        $adminProduct = ProductOptimized::factory()->create(['sku' => 'ADMIN-1', 'category_id' => $homeGoods->id]);

        $this->seed(DemoCatalogSeeder::class);
        $this->seed(DemoCatalogSeeder::class);

        $this->assertSame(100, ProductOptimized::where('sku', 'like', 'TIB-%')->count());
        $this->assertSame(100, ProductSearchIndex::count());

        // A cover for every product, plus a close-up and a specs card for featured ones; each with card and gallery copies
        $featured = ProductOptimized::where('sku', 'like', 'TIB-%')->where('featured', true)->count();
        $this->assertSame(100 + 2 * $featured, ProductImageOptimized::whereNotNull('thumb_url')->whereNotNull('large_url')->count());
        $this->assertSame(0, ProductOptimized::where('sku', 'like', 'TIB-%')->whereDoesntHave('mainImage')->count());
        $macbookPro = ProductOptimized::where('name', 'Apple MacBook Pro 14" M5')->withCount('images')->sole();
        $this->assertTrue($macbookPro->featured);
        $this->assertSame(3, $macbookPro->images_count);
        $this->assertSame(1, ProductOptimized::where('sku', 'TIB-LAP-005')->withCount('images')->sole()->images_count, 'Not featured: the cover only');
        $this->assertSame(0, ProductOptimized::where('sku', 'like', 'TIB-%')
            ->where(fn ($query) => $query->whereNull('description')->orWhereNull('ean_upc')->orWhereNull('brand_id'))
            ->count());
        $this->assertSame(16, Category::where('is_featured', true)->count());
        $this->assertSame(0, ProductOptimized::where('stock_status', 'out_of_stock')->sum('total_stock'), 'Out-of-stock items carry no stock');

        // The fashion demo goes; the admin's product and its category stay, out of the menu and off the home page
        $this->assertNull(ProductOptimized::find($oldDemo->id));
        $this->assertNull(Category::find($fashion->id));
        $this->assertNull(Brand::find($levis->id));
        $this->assertNotNull(ProductOptimized::find($adminProduct->id));
        $this->assertFalse($homeGoods->fresh()->is_menu);
        $this->assertFalse($homeGoods->fresh()->is_featured);

        $phone = ProductOptimized::with(['variants', 'productAttributes', 'brand', 'category', 'mainImage'])->where('sku', 'TIB-PHN-001')->sole();
        $this->assertSame('Samsung Galaxy S24 Ultra', $phone->name);
        $this->assertSame('Smartphone', $phone->category->name);
        $this->assertCount(2, $phone->variants);
        $this->assertSame('179999.00', $phone->variants->firstWhere('is_default', true)->compare_price);
        $this->assertEqualsCanonicalizing(['12GB', '256GB', 'Gray'], $phone->productAttributes->pluck('value')->all());
        $this->assertSame($phone->variants->sum('stock'), $phone->total_stock);
        // The page lists the specs itself, so the description is prose only
        $this->assertStringContainsString('<h3>Good to know</h3>', $phone->description);
        $this->assertStringNotContainsString('<table', $phone->description);
        $this->assertSame([800, 800], array_slice(getimagesizefromstring(Storage::disk('public')->get($phone->mainImage->first()->url)), 0, 2));
        $this->assertSame(['products/demo/tib-phn-001.jpg', 'products/demo/tib-phn-001-closeup.jpg', 'products/demo/tib-phn-001-specs.jpg'],
            $phone->images()->orderBy('sort_order')->pluck('url')->all());
        $this->assertGreaterThan(0, ProductVariantOptimized::count());

        $this->get('/shop')->assertOk()->assertSee('Showing 1–24 of 101 products');
        $this->get('/shop?search=galaxy')->assertOk()->assertSee('Samsung Galaxy S24 Ultra');
        $this->get(route('product.detail', $phone))->assertOk()->assertSee('SM-S928B')->assertSee('12GB / 512GB');
    }
}
