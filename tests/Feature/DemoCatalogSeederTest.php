<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductImageOptimized;
use App\Models\ProductOptimized;
use App\Models\ProductSearchIndex;
use App\Models\ProductVariantOptimized;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DemoCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_startech_catalog_into_subcategories_retires_earlier_demos_and_is_safe_to_rerun(): void
    {
        Storage::fake('public');
        $catalog = require database_path('seeders/catalog/products.php');
        $productCount = array_sum(array_map('count', $catalog['products']));
        $photoCount = count(glob(database_path('seeders/catalog/images/*.jpg')));

        // Left over from earlier demo catalogs, plus a product an admin added
        $fashion = Category::factory()->create(['slug' => 'mens-fashion', 'is_menu' => true]);
        $phones = Category::factory()->create(['slug' => 'smartphone', 'is_menu' => true, 'is_featured' => true]);
        $television = Category::factory()->create(['slug' => 'television', 'name' => 'Television', 'is_menu' => true]);
        $monitors = Category::factory()->create(['slug' => 'monitor', 'name' => 'Monitor']);
        $levis = Brand::factory()->create(['slug' => 'levis']);
        $zotac = Brand::factory()->create(['slug' => 'zotac']);
        $oldDemo = ProductOptimized::factory()->create(['sku' => 'TIB-MEN-001', 'category_id' => $fashion->id, 'brand_id' => $levis->id]);
        $oldPhone = ProductOptimized::factory()->create(['sku' => 'TIB-PHN-001', 'category_id' => $phones->id, 'brand_id' => $zotac->id]);
        $adminProduct = ProductOptimized::factory()->create(['sku' => 'ADMIN-1', 'category_id' => $phones->id]);
        // A drawn cover from the previous catalog on a SKU this one reuses
        $reused = ProductOptimized::factory()->create(['sku' => 'TIB-MON-001', 'category_id' => $monitors->id]);
        Storage::disk('public')->put('products/demo/tib-mon-001.jpg', 'cover');
        $oldCover = ProductImageOptimized::create(['product_id' => $reused->id, 'url' => 'products/demo/tib-mon-001.jpg', 'is_main' => true, 'sort_order' => 0]);

        $this->seed(DemoCatalogSeeder::class);
        $this->seed(DemoCatalogSeeder::class);

        $this->assertSame($productCount, ProductOptimized::where('sku', 'like', 'TIB-%')->count());
        $this->assertGreaterThanOrEqual(130, $productCount);
        $this->assertSame($productCount, ProductSearchIndex::count());

        // Every photo, with its card and gallery copies; every product has one leading
        $this->assertSame($photoCount, ProductImageOptimized::whereNotNull('thumb_url')->whereNotNull('large_url')->count());
        $this->assertSame(0, ProductOptimized::where('sku', 'like', 'TIB-%')->whereDoesntHave('mainImage')->count());
        $this->assertSame(0, ProductOptimized::where('sku', 'like', 'TIB-%')
            ->where(fn ($query) => $query->whereNull('description')->orWhereNull('ean_upc')->orWhereNull('brand_id')->orWhereNull('warranty'))
            ->count());
        $this->assertSame(0, ProductOptimized::where('stock_status', 'out_of_stock')->sum('total_stock'), 'Out-of-stock items carry no stock');

        // The tree: 9 categories, products in the subcategories (and Monitor, which has none)
        $this->assertSame(9, Category::whereNull('parent_id')->where('is_menu', true)->count());
        $this->assertSame(count(CategorySeeder::slugs()), Category::whereIn('slug', CategorySeeder::slugs())->count());
        $this->assertSame(16, Category::where('is_featured', true)->count());
        $this->assertSame(['Laptop', 'Monitor', 'Peripherals', 'Desktop PC', 'Office Equipment', 'Networking', 'TV', 'Gadget', 'Printer'],
            Category::whereNull('parent_id')->where('is_menu', true)->orderBy('sort_order')->pluck('name')->all());
        $this->assertSame(0, ProductOptimized::where('sku', 'like', 'TIB-%')
            ->whereHas('category', fn ($category) => $category->whereNull('parent_id')->where('slug', '!=', 'monitor'))
            ->count(), 'Products sit in subcategories');

        // Renamed in place, so the old address redirects
        $this->assertSame('tv', $television->fresh()->slug);
        $this->assertSame('TV', $television->fresh()->name);
        $this->get('/category/television')->assertRedirect('/category/tv');

        // Earlier demo data goes; the admin's product and its category stay, out of the menu and off the home page
        $this->assertNull(ProductOptimized::find($oldDemo->id));
        $this->assertNull(ProductOptimized::find($oldPhone->id));
        $this->assertNull(Category::find($fashion->id));
        $this->assertNull(Brand::find($levis->id));
        $this->assertNull(Brand::find($zotac->id));
        $this->assertNotNull(ProductOptimized::find($adminProduct->id));
        $this->assertFalse($phones->fresh()->is_menu);
        $this->assertFalse($phones->fresh()->is_featured);

        // The reused SKU loses the old cover and leads with its photo
        $this->assertNull(ProductImageOptimized::find($oldCover->id));
        Storage::disk('public')->assertMissing('products/demo/tib-mon-001.jpg');
        $this->assertSame('products/demo/tib-mon-001-1.jpg', $reused->fresh()->mainImage()->value('url'));

        $zenbook = ProductOptimized::with(['variants', 'productAttributes', 'brand', 'category.parent', 'mainImage'])->where('sku', 'TIB-LAS-004')->sole();
        $this->assertStringStartsWith('ASUS Zenbook 14 OLED', $zenbook->name);
        $this->assertSame('ASUS Laptop', $zenbook->category->name);
        $this->assertSame('Laptop', $zenbook->category->parent->name);
        $this->assertSame('ASUS', $zenbook->brand->name);
        $this->assertTrue($zenbook->featured);
        $this->assertEqualsCanonicalizing(['14"', '16GB', '512GB', 'Silver'], $zenbook->productAttributes->pluck('value')->all());
        $this->assertSame('Processor', array_key_first($zenbook->specs));
        // The page lists the specs itself, so the description is prose only
        $this->assertStringContainsString('<h3>Good to know</h3>', $zenbook->description);
        $this->assertStringNotContainsString('<table', $zenbook->description);
        $photos = $zenbook->images()->orderBy('sort_order')->pluck('url')->all();
        $this->assertSame('products/demo/tib-las-004-1.jpg', $photos[0]);
        $this->assertCount(count(glob(database_path('seeders/catalog/images/tib-las-004-*.jpg'))), $photos);
        $this->assertLessThanOrEqual(800, max(array_slice(getimagesizefromstring(Storage::disk('public')->get($photos[0])), 0, 2)));

        // A crossed-out StarTech price becomes the "was" price
        $deal = ProductOptimized::with('variants')->where('sku', 'TIB-LLN-002')->sole();
        $this->assertSame('95000.00', $deal->variants->firstWhere('is_default', true)->compare_price);
        $this->assertSame($deal->variants->sum('stock'), $deal->total_stock);
        $this->assertGreaterThan(0, ProductVariantOptimized::count());

        $this->get('/shop')->assertOk()->assertSee('Showing 1–24 of ' . ($productCount + 1) . ' products');
        $this->get('/shop?search=zenbook')->assertOk()->assertSee('ASUS Zenbook 14 OLED');
        $this->get(route('product.detail', $zenbook))->assertOk()->assertSee('UX3405CA-QL219W')
            ->assertSee('href="' . route('shop.category', $zenbook->category->parent) . '"', false);
        // A category's page lists its subcategories' products
        $laptopFamily = array_sum(array_map(fn ($slug) => count($catalog['products'][$slug]), array_keys(CategorySeeder::CATEGORIES['laptop'][3])));
        $this->get('/category/laptop')->assertOk()->assertSee('Showing 1–24 of ' . $laptopFamily . ' products');
        $this->get('/category/gadget')->assertOk()->assertSee('Amazfit Bip Max');
    }
}
