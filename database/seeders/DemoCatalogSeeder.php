<?php

namespace Database\Seeders;

use App\Models\AttributeOptimized;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductAttributeOptimized;
use App\Models\ProductImageOptimized;
use App\Models\ProductOptimized;
use App\Models\ProductVariantOptimized;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The demo catalog: about 140 real products from startech.com.bd across the CategorySeeder
 * subcategories, with prices, specifications, attributes and photos. The data and the photos are
 * in database/seeders/catalog (products.php, images/); the highlights there are our own.
 *
 *   php artisan db:seed --class=DemoCatalogSeeder
 *
 * Safe to re-run: products are matched by SKU (TIB-XXX-NNN) and updated in place. Demo products,
 * categories and brands from earlier demo catalogs are removed; anything added in admin stays.
 */
class DemoCatalogSeeder extends Seeder
{
    private const DATA = __DIR__ . '/catalog/products.php';

    private const IMAGES = __DIR__ . '/catalog/images';

    private const DEVICE_INTRO = 'Every %1$s we sell is brand-new and sealed in its original %2$s box, and comes with the warranty listed below.';
    private const DEVICE_NOTES = ['Brand-new, sealed-box unit', '7-day replacement for manufacturing defects', 'Cash on delivery available across Bangladesh'];
    private const GEAR_INTRO = 'The %1$s is a genuine %2$s product, packed with everything you need to start using it straight away.';
    private const GEAR_NOTES = ['100% genuine product', '7-day replacement for manufacturing defects', 'Cash on delivery available across Bangladesh'];
    private const INSTALL_INTRO = 'The %1$s is a genuine %2$s product, delivered sealed in its original box and covered by the warranty listed below.';
    private const INSTALL_NOTES = ['Brand-new, sealed-box unit', '7-day replacement for manufacturing defects', 'Keep the box and invoice for warranty claims'];

    private const LAPTOP = ['warranty' => '2 Years Warranty', 'variant_key' => 'configuration', 'intro' => self::DEVICE_INTRO, 'notes' => self::DEVICE_NOTES];

    /**
     * Per subcategory: SKU prefix, default warranty (when a product lists none), variant JSON key,
     * description intro and "good to know" notes.
     */
    private const CATEGORIES = [
        'apple-macbook' => ['prefix' => 'MBK', 'warranty' => '1 Year Warranty'] + self::LAPTOP,
        'asus-laptop' => ['prefix' => 'LAS'] + self::LAPTOP,
        'hp-laptop' => ['prefix' => 'LHP'] + self::LAPTOP,
        'lenovo-laptop' => ['prefix' => 'LLN'] + self::LAPTOP,
        'dell-laptop' => ['prefix' => 'LDL'] + self::LAPTOP,
        'acer-laptop' => ['prefix' => 'LAC'] + self::LAPTOP,
        'msi-laptop' => ['prefix' => 'LMS'] + self::LAPTOP,
        'laptop-cooler' => ['prefix' => 'LCL', 'warranty' => '7 Days Replacement', 'variant_key' => 'option', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
        'monitor' => ['prefix' => 'MON', 'warranty' => '3 Years Warranty', 'variant_key' => 'option', 'intro' => self::DEVICE_INTRO, 'notes' => self::DEVICE_NOTES],
        'keyboard' => ['prefix' => 'KBD', 'warranty' => '1 Year Warranty', 'variant_key' => 'option', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
        'mouse' => ['prefix' => 'MOU', 'warranty' => '1 Year Warranty', 'variant_key' => 'option', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
        'headphone' => ['prefix' => 'HPH', 'warranty' => '1 Year Warranty', 'variant_key' => 'color', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
        'mouse-pad' => ['prefix' => 'MPD', 'warranty' => '7 Days Replacement', 'variant_key' => 'option', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
        'brand-pc' => ['prefix' => 'BPC', 'warranty' => '3 Years Warranty', 'variant_key' => 'configuration', 'intro' => self::DEVICE_INTRO, 'notes' => self::DEVICE_NOTES],
        'interactive-flat-panel' => ['prefix' => 'IFP', 'warranty' => '3 Years Warranty', 'variant_key' => 'option', 'intro' => self::INSTALL_INTRO, 'notes' => self::INSTALL_NOTES],
        'router' => ['prefix' => 'RTR', 'warranty' => '1 Year Warranty', 'variant_key' => 'option', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
        'smart-tv' => ['prefix' => 'STV', 'warranty' => '3 Years Warranty', 'variant_key' => 'option', 'intro' => self::DEVICE_INTRO, 'notes' => self::DEVICE_NOTES],
        'smart-watch' => ['prefix' => 'SWT', 'warranty' => '6 Months Warranty', 'variant_key' => 'color', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
        'earbuds' => ['prefix' => 'EBD', 'warranty' => '1 Year Warranty', 'variant_key' => 'color', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
        'earphone' => ['prefix' => 'EPH', 'warranty' => '6 Months Warranty', 'variant_key' => 'color', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
        'power-bank' => ['prefix' => 'PBK', 'warranty' => '6 Months Warranty', 'variant_key' => 'option', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
        'laser-printer' => ['prefix' => 'LPR', 'warranty' => '1 Year Warranty', 'variant_key' => 'option', 'intro' => self::DEVICE_INTRO, 'notes' => self::DEVICE_NOTES],
        'pos-printer' => ['prefix' => 'POS', 'warranty' => '1 Year Warranty', 'variant_key' => 'option', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
    ];

    /** From earlier demo catalogs (fashion, then the 16 flat tech categories); removed once nothing uses them */
    private const RETIRED_CATEGORIES = [
        'mens-fashion', 'womens-fashion', 'kids-fashion', 'electronics', 'sports-outdoors', 'accessories', 'shoes', 'home-garden',
        'smartphone', 'tablet', 'processor', 'motherboard', 'graphics-card', 'ram', 'ssd', 'keyboard-mouse', 'ups-power',
    ];

    private const RETIRED_BRANDS = [
        'levis', 'zara', 'hm', 'uniqlo', 'aarong', 'yellow', 'nike', 'adidas', 'puma', 'bata', 'apex', 'yonex', 'sg', 'decathlon', 'casio', 'fossil', 'ray-ban', 'philips', 'miyako', 'ikea', 'vision',
        'intel', 'amd', 'zotac', 'kingston', 'adata', 'western-digital', 'walton', 'apc',
    ];

    /** @var array{brands: array, products: array} */
    private array $data;

    public function run(): void
    {
        $this->call([CategorySeeder::class, AttributeSeeder::class]);

        $this->data = require self::DATA;
        $this->retireOldDemoData();

        $brands = $this->seedBrands();
        $categories = Category::with('parent')->whereIn('slug', array_keys($this->data['products']))->get()->keyBy('slug');
        $attributes = AttributeOptimized::with('values')
            ->whereIn('slug', ['screen-size', 'storage', 'ram', 'color'])
            ->get()
            ->keyBy('slug');

        $number = 0;
        foreach ($this->data['products'] as $categorySlug => $items) {
            $settings = self::CATEGORIES[$categorySlug];
            $category = $categories[$categorySlug];

            foreach ($items as $position => $item) {
                $number++;
                $sku = sprintf('TIB-%s-%03d', $settings['prefix'], $position + 1);
                $this->seedProduct($item, $sku, $number, $category, $settings, $brands[$item['brand']], $attributes);
            }

            $this->command?->info(sprintf('  %-24s %d products', $category->name, count($items)));
        }

        $this->command?->info("Seeded {$number} products.");
    }

    /**
     * SKUs this catalog uses, so older demo products (same TIB- scheme) can be told apart.
     */
    private function skus(): array
    {
        $skus = [];
        foreach ($this->data['products'] as $categorySlug => $items) {
            foreach (array_keys($items) as $position) {
                $skus[] = sprintf('TIB-%s-%03d', self::CATEGORIES[$categorySlug]['prefix'], $position + 1);
            }
        }

        return $skus;
    }

    /**
     * Remove demo data this catalog no longer has: products first, then the categories and brands
     * nothing else uses.
     */
    private function retireOldDemoData(): void
    {
        $retired = ProductOptimized::where('sku', 'like', 'TIB-%')
            ->whereNotIn('sku', $this->skus())
            ->with('images')
            ->get();

        foreach ($retired as $product) {
            $product->images->each->deleteFiles();
            // Variants, images, attributes, search index and cart rows go with it; order items keep their snapshot
            $product->delete();
        }

        // A category an admin-made product still uses stays, but leaves the menu and the home page
        Category::whereIn('slug', self::RETIRED_CATEGORIES)->withCount(['products', 'children'])->get()
            ->each(fn (Category $category) => $category->products_count || $category->children_count
                ? $category->update(['is_menu' => false, 'is_featured' => false])
                : $category->delete());

        Brand::whereIn('slug', self::RETIRED_BRANDS)->doesntHave('products')->get()->each->delete();

        if ($retired->isNotEmpty()) {
            $this->command?->info("Removed {$retired->count()} products from the earlier demo catalog.");
        }
    }

    private function seedBrands(): array
    {
        $brands = [];
        foreach ($this->data['brands'] as $slug => [$name, $description]) {
            $brands[$slug] = Brand::firstOrCreate(['slug' => $slug], ['name' => $name, 'description' => $description, 'status' => true]);
        }

        return $brands;
    }

    private function seedProduct(array $item, string $sku, int $number, Category $category, array $settings, Brand $brand, $attributes): void
    {
        $status = $item['status'] ?? 'in_stock';
        $stock = $status === 'in_stock' ? 8 + crc32($sku) % 60 : 0;
        $warranty = $item['warranty'] ?? $settings['warranty'];
        $categoryNames = array_filter([$category->parent?->name, $category->name]);

        $product = ProductOptimized::updateOrCreate(['sku' => $sku], [
            'name' => $item['name'],
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'short_description' => $item['hl'],
            'description' => $this->description($item, $brand->name, $settings),
            'base_price' => $item['price'],
            'cost_price' => round($item['price'] * 0.88),
            'currency' => 'BDT',
            'manage_stock' => true,
            'stock_status' => $status,
            'total_stock' => $stock,
            'weight' => $item['weight'] ?? null,
            'dimensions' => $item['dim'] ?? null,
            'specs' => $item['specs'],
            'attributes' => $item['attrs'],
            'meta_title' => $item['name'] . ' Price in Bangladesh | ' . config('shop.name'),
            'meta_description' => $item['hl'],
            'meta_keywords' => implode(', ', array_unique(array_merge([$brand->name], $categoryNames, array_values($item['attrs'])))),
            'status' => 1,
            'featured' => ! empty($item['f']),
            'warranty' => $warranty,
            'manufacturer_part_no' => $item['mpn'] ?? null,
            'ean_upc' => $this->ean13($number),
            // Listed over the past six weeks, so "Latest Products" mixes categories as in a real shop
            'created_at' => now()->subMinutes(crc32($sku) % (60 * 24 * 42)),
        ]);

        $this->seedAttributes($product, $item['attrs'], $attributes);
        $this->seedVariants($product, $item, $settings, $sku, $stock);
        $this->seedImages($product, $sku, $item['name']);

        $product->updateSearchIndex();
    }

    private function seedAttributes(ProductOptimized $product, array $attrs, $attributes): void
    {
        $product->productAttributes()->delete();

        foreach ($attrs as $slug => $value) {
            $attribute = $attributes->get($slug);
            if (! $attribute) {
                continue;
            }

            ProductAttributeOptimized::create([
                'product_id' => $product->id,
                'attribute_id' => $attribute->id,
                'attribute_value_id' => $attribute->values->firstWhere('value', $value)?->id,
                'value' => $value,
            ]);
        }
    }

    private function seedVariants(ProductOptimized $product, array $item, array $settings, string $sku, int $stock): void
    {
        $product->variants()->delete();

        $options = $item['variants'] ?? [];
        // A single "Standard" variant carries the compare-at price for products without options
        if (! $options && isset($item['was'])) {
            $options = ['Standard'];
        }
        if (! $options) {
            return;
        }

        // Accept a plain list (same price) or name => price
        $options = array_is_list($options) ? array_fill_keys($options, null) : $options;
        $key = $item['vkey'] ?? $settings['variant_key'];
        $position = 0;

        foreach ($options as $name => $price) {
            $position++;
            $variant = new ProductVariantOptimized([
                'sku' => $sku . '-' . strtoupper(Str::slug($name)),
                'name' => $name,
                'price' => $price,
                'compare_price' => $position === 1 ? ($item['was'] ?? null) : null,
                'stock' => $stock ? max(1, intdiv($stock, count($options)) + $position % 3) : 0,
                'manage_stock' => true,
                'is_default' => $position === 1,
                'attributes' => [$key => $name],
            ]);
            // The saved hook recalculates the product's total stock through this relation
            $variant->product()->associate($product);
            $variant->save();
        }
    }

    /**
     * The product's photos (catalog/images/{sku}-1.jpg, -2.jpg …), copied to the public disk with
     * their WebP card and gallery versions. Photos already there are left alone, demo images the
     * product no longer has (such as an earlier catalog's drawn covers) are removed, and images
     * added in admin stay. The first photo leads unless a main image was already chosen.
     */
    private function seedImages(ProductOptimized $product, string $sku, string $name): void
    {
        $photos = [];
        for ($n = 1; is_file($source = self::IMAGES . '/' . strtolower($sku) . "-{$n}.jpg"); $n++) {
            $photos[$n] = ['products/demo/' . strtolower($sku) . "-{$n}.jpg", $source];
        }

        $product->images()
            ->where('url', 'like', 'products/demo/%')
            ->whereNotIn('url', array_column($photos, 0))
            ->get()
            ->each(function (ProductImageOptimized $image) {
                $image->deleteFiles();
                $image->delete();
            });

        $hasMain = $product->images()->where('is_main', true)->exists();
        foreach ($photos as $n => [$path, $source]) {
            if ($product->images()->where('url', $path)->exists()) {
                continue;
            }

            Storage::disk('public')->put($path, file_get_contents($source));
            $image = ProductImageOptimized::create([
                'product_id' => $product->id,
                'url' => $path,
                'alt_text' => $n === 1 ? $name : "{$name}, photo {$n}",
                'sort_order' => $n - 1,
                'is_main' => $n === 1 && ! $hasMain,
            ]);
            $image->generateVersions();
        }
    }

    /**
     * Prose for the Description section; the page lists the specs itself (ProductOptimized::specGroups()).
     */
    private function description(array $item, string $brandName, array $settings): string
    {
        $html = '<p>' . e($item['hl']) . '</p>';
        $html .= '<p>' . e(sprintf($settings['intro'], $item['name'], $brandName)) . '</p>';
        $html .= '<h3>Good to know</h3><ul>';
        foreach ($settings['notes'] as $note) {
            $html .= '<li>' . e($note) . '</li>';
        }

        return $html . '</ul>';
    }

    /**
     * EAN-13 using Bangladesh's GS1 prefix (941) and a valid check digit.
     */
    private function ean13(int $number): string
    {
        $digits = '941' . str_pad((string) $number, 9, '0', STR_PAD_LEFT);
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $digits[$i] * ($i % 2 ? 3 : 1);
        }

        return $digits . ((10 - $sum % 10) % 10);
    }
}
