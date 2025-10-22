<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductOptimized;
use App\Models\Brand;
use App\Models\Category;

class AdditionalFeaturedProductsSeeder extends Seeder
{
    public function run()
    {
        // Get existing brands and categories
        $samsung = Brand::where('slug', 'samsung')->first();
        $apple = Brand::where('slug', 'apple')->first();
        $sony = Brand::where('slug', 'sony')->first();
        $lg = Brand::where('slug', 'lg')->first();
        
        $smartphones = Category::where('slug', 'smartphones')->first();
        $laptops = Category::where('slug', 'laptops')->first();
        $headphones = Category::where('slug', 'headphones')->first();
        $tablets = Category::where('slug', 'tablets')->first();

        // Create additional featured products
        $additionalProducts = [
            [
                'name' => 'Samsung Galaxy Tab S9',
                'slug' => 'samsung-galaxy-tab-s9',
                'sku' => 'SAM-TAB-S9-001',
                'brand_id' => $samsung->id,
                'category_id' => $tablets->id,
                'short_description' => 'Premium Android tablet with S Pen included',
                'description' => '<p>The Galaxy Tab S9 features a stunning 11-inch display, powerful Snapdragon processor, and comes with the S Pen for productivity and creativity.</p>',
                'base_price' => 799.99,
                'cost_price' => 580.00,
                'currency' => 'USD',
                'stock_status' => 'in_stock',
                'total_stock' => 20,
                'weight' => 0.498,
                'dimensions' => '254.3 x 165.8 x 5.9 mm',
                'warranty' => '1 Year',
                'status' => 1,
                'featured' => true
            ],
            [
                'name' => 'MacBook Air M2',
                'slug' => 'macbook-air-m2',
                'sku' => 'APL-MBA-M2-001',
                'brand_id' => $apple->id,
                'category_id' => $laptops->id,
                'short_description' => 'Ultra-thin laptop with Apple M2 chip',
                'description' => '<p>The MacBook Air with M2 chip delivers incredible performance in an ultra-thin design. Perfect for work, creativity, and everything in between.</p>',
                'base_price' => 1199.99,
                'cost_price' => 900.00,
                'currency' => 'USD',
                'stock_status' => 'in_stock',
                'total_stock' => 15,
                'weight' => 1.24,
                'dimensions' => '304.1 x 215 x 11.3 mm',
                'warranty' => '1 Year',
                'status' => 1,
                'featured' => true
            ],
            [
                'name' => 'LG OLED C3 55"',
                'slug' => 'lg-oled-c3-55',
                'sku' => 'LG-OLED-C3-55-001',
                'brand_id' => $lg->id,
                'category_id' => Category::firstOrCreate(['slug' => 'tvs'], ['name' => 'TVs', 'status' => true])->id,
                'short_description' => '55-inch OLED Smart TV with 4K resolution',
                'description' => '<p>Experience perfect blacks and vibrant colors with LG OLED technology. Features webOS smart platform and gaming optimizations.</p>',
                'base_price' => 1499.99,
                'cost_price' => 1100.00,
                'currency' => 'USD',
                'stock_status' => 'in_stock',
                'total_stock' => 8,
                'weight' => 18.7,
                'dimensions' => '1228 x 706 x 45 mm',
                'warranty' => '2 Years',
                'status' => 1,
                'featured' => true
            ],
            [
                'name' => 'Samsung Galaxy Watch 6',
                'slug' => 'samsung-galaxy-watch-6',
                'sku' => 'SAM-WATCH-6-001',
                'brand_id' => $samsung->id,
                'category_id' => Category::firstOrCreate(['slug' => 'wearables'], ['name' => 'Wearables', 'status' => true])->id,
                'short_description' => 'Advanced smartwatch with health monitoring',
                'description' => '<p>Track your fitness, monitor your health, and stay connected with the Galaxy Watch 6. Features advanced sleep tracking and heart rate monitoring.</p>',
                'base_price' => 329.99,
                'cost_price' => 240.00,
                'currency' => 'USD',
                'stock_status' => 'in_stock',
                'total_stock' => 35,
                'weight' => 0.033,
                'dimensions' => '40.4 x 40.4 x 9.0 mm',
                'warranty' => '1 Year',
                'status' => 1,
                'featured' => true
            ],
            [
                'name' => 'iPad Pro 12.9" M2',
                'slug' => 'ipad-pro-12-9-m2',
                'sku' => 'APL-IPADPRO-M2-001',
                'brand_id' => $apple->id,
                'category_id' => $tablets->id,
                'short_description' => 'Professional tablet with M2 chip and Liquid Retina XDR display',
                'description' => '<p>The ultimate iPad experience with M2 chip, stunning Liquid Retina XDR display, and support for Apple Pencil and Magic Keyboard.</p>',
                'base_price' => 1099.99,
                'cost_price' => 820.00,
                'currency' => 'USD',
                'stock_status' => 'in_stock',
                'total_stock' => 12,
                'weight' => 0.682,
                'dimensions' => '280.6 x 214.9 x 6.4 mm',
                'warranty' => '1 Year',
                'status' => 1,
                'featured' => true
            ],
            [
                'name' => 'Sony PlayStation 5',
                'slug' => 'sony-playstation-5',
                'sku' => 'SNY-PS5-001',
                'brand_id' => $sony->id,
                'category_id' => Category::firstOrCreate(['slug' => 'gaming'], ['name' => 'Gaming', 'status' => true])->id,
                'short_description' => 'Next-generation gaming console with 4K gaming',
                'description' => '<p>Experience lightning-fast loading with an ultra-high speed SSD, deeper immersion with haptic feedback, and 3D Audio technology.</p>',
                'base_price' => 499.99,
                'cost_price' => 380.00,
                'currency' => 'USD',
                'stock_status' => 'in_stock',
                'total_stock' => 5,
                'weight' => 4.5,
                'dimensions' => '390 x 104 x 260 mm',
                'warranty' => '1 Year',
                'status' => 1,
                'featured' => true
            ],
            [
                'name' => 'Samsung Galaxy Buds2 Pro',
                'slug' => 'samsung-galaxy-buds2-pro',
                'sku' => 'SAM-BUDS2PRO-001',
                'brand_id' => $samsung->id,
                'category_id' => $headphones->id,
                'short_description' => 'Premium wireless earbuds with active noise cancellation',
                'description' => '<p>Immerse yourself in studio-quality sound with intelligent ANC, 360 Audio, and seamless connectivity across your Galaxy devices.</p>',
                'base_price' => 229.99,
                'cost_price' => 160.00,
                'currency' => 'USD',
                'stock_status' => 'in_stock',
                'total_stock' => 40,
                'weight' => 0.0051,
                'dimensions' => '21.0 x 19.9 x 18.7 mm',
                'warranty' => '1 Year',
                'status' => 1,
                'featured' => true
            ],
            [
                'name' => 'LG Gram 17"',
                'slug' => 'lg-gram-17',
                'sku' => 'LG-GRAM-17-001',
                'brand_id' => $lg->id,
                'category_id' => $laptops->id,
                'short_description' => 'Ultra-lightweight 17-inch laptop with all-day battery',
                'description' => '<p>The LG Gram 17 offers a large screen in an incredibly lightweight design. Perfect for productivity with exceptional battery life.</p>',
                'base_price' => 1699.99,
                'cost_price' => 1250.00,
                'currency' => 'USD',
                'stock_status' => 'in_stock',
                'total_stock' => 10,
                'weight' => 1.35,
                'dimensions' => '378.8 x 258.8 x 17.8 mm',
                'warranty' => '2 Years',
                'status' => 1,
                'featured' => true
            ],
            [
                'name' => 'Apple AirPods Pro 2',
                'slug' => 'apple-airpods-pro-2',
                'sku' => 'APL-AIRPODS-PRO2-001',
                'brand_id' => $apple->id,
                'category_id' => $headphones->id,
                'short_description' => 'Advanced wireless earbuds with adaptive transparency',
                'description' => '<p>AirPods Pro 2 feature the Apple H2 chip for smarter noise cancellation, three-dimensional sound, and longer battery life.</p>',
                'base_price' => 249.99,
                'cost_price' => 180.00,
                'currency' => 'USD',
                'stock_status' => 'in_stock',
                'total_stock' => 30,
                'weight' => 0.0056,
                'dimensions' => '30.9 x 21.8 x 24.0 mm',
                'warranty' => '1 Year',
                'status' => 1,
                'featured' => true
            ]
        ];

        foreach ($additionalProducts as $productData) {
            $product = ProductOptimized::firstOrCreate([
                'sku' => $productData['sku']
            ], $productData);

            // Add sample image
            \App\Models\ProductImageOptimized::firstOrCreate([
                'product_id' => $product->id,
                'is_main' => true
            ], [
                'url' => 'products/sample-' . ($product->id % 8 + 1) . '.jpg',
                'alt_text' => $product->name,
                'sort_order' => 0
            ]);
        }

        $this->command->info('Additional featured products created successfully!');
    }
}