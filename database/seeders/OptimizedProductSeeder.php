<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductOptimized;
use App\Models\Brand;
use App\Models\Category;
use App\Models\AttributeOptimized;
use App\Models\AttributeValueOptimized;
use App\Models\ProductAttributeOptimized;

class OptimizedProductSeeder extends Seeder
{
    public function run()
    {
        // Create sample brands
        $brands = [
            ['name' => 'Samsung', 'slug' => 'samsung'],
            ['name' => 'Apple', 'slug' => 'apple'],
            ['name' => 'Sony', 'slug' => 'sony'],
            ['name' => 'LG', 'slug' => 'lg'],
        ];

        foreach ($brands as $brandData) {
            Brand::firstOrCreate(['slug' => $brandData['slug']], $brandData);
        }

        // Create sample categories
        $categories = [
            ['name' => 'Smartphones', 'slug' => 'smartphones', 'status' => true],
            ['name' => 'Laptops', 'slug' => 'laptops', 'status' => true],
            ['name' => 'Headphones', 'slug' => 'headphones', 'status' => true],
            ['name' => 'Tablets', 'slug' => 'tablets', 'status' => true],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(['slug' => $categoryData['slug']], $categoryData);
        }

        // Get created brands and categories
        $samsung = Brand::where('slug', 'samsung')->first();
        $apple = Brand::where('slug', 'apple')->first();
        $sony = Brand::where('slug', 'sony')->first();
        
        $smartphones = Category::where('slug', 'smartphones')->first();
        $laptops = Category::where('slug', 'laptops')->first();
        $headphones = Category::where('slug', 'headphones')->first();

        // Create sample attributes for smartphones
        if ($smartphones) {
            $screenSizeAttr = AttributeOptimized::firstOrCreate([
                'category_id' => $smartphones->id,
                'slug' => 'screen-size'
            ], [
                'name' => 'Screen Size',
                'type' => 'select',
                'unit' => 'inches',
                'filterable' => true,
                'status' => true
            ]);

            $storageAttr = AttributeOptimized::firstOrCreate([
                'category_id' => $smartphones->id,
                'slug' => 'storage'
            ], [
                'name' => 'Storage',
                'type' => 'select',
                'unit' => 'GB',
                'filterable' => true,
                'status' => true
            ]);

            // Create attribute values
            $screenSizes = ['5.5', '6.1', '6.7'];
            foreach ($screenSizes as $size) {
                AttributeValueOptimized::firstOrCreate([
                    'attribute_id' => $screenSizeAttr->id,
                    'value' => $size
                ], [
                    'display_value' => $size . '"',
                    'numeric_value' => (float)$size,
                    'status' => true
                ]);
            }

            $storages = ['64', '128', '256', '512'];
            foreach ($storages as $storage) {
                AttributeValueOptimized::firstOrCreate([
                    'attribute_id' => $storageAttr->id,
                    'value' => $storage
                ], [
                    'display_value' => $storage . 'GB',
                    'numeric_value' => (float)$storage,
                    'status' => true
                ]);
            }
        }

        // Create sample products
        $products = [
            [
                'name' => 'Samsung Galaxy S24',
                'slug' => 'samsung-galaxy-s24',
                'sku' => 'SAM-S24-001',
                'brand_id' => $samsung->id,
                'category_id' => $smartphones->id,
                'short_description' => 'Latest Samsung flagship smartphone with advanced AI features',
                'description' => '<p>The Samsung Galaxy S24 features a stunning 6.1-inch Dynamic AMOLED display, powerful Snapdragon processor, and advanced camera system with AI-enhanced photography.</p>',
                'base_price' => 899.99,
                'cost_price' => 650.00,
                'currency' => 'USD',
                'stock_status' => 'in_stock',
                'total_stock' => 50,
                'weight' => 0.168,
                'dimensions' => '147.0 x 70.6 x 7.6 mm',
                'warranty' => '1 Year',
                'status' => 1
            ],
            [
                'name' => 'iPhone 15 Pro',
                'slug' => 'iphone-15-pro',
                'sku' => 'APL-IP15P-001',
                'brand_id' => $apple->id,
                'category_id' => $smartphones->id,
                'short_description' => 'Apple iPhone 15 Pro with titanium design and A17 Pro chip',
                'description' => '<p>The iPhone 15 Pro features a titanium design, A17 Pro chip, and Pro camera system with 3x telephoto lens. Built for professionals and enthusiasts.</p>',
                'base_price' => 999.99,
                'cost_price' => 750.00,
                'currency' => 'USD',
                'stock_status' => 'in_stock',
                'total_stock' => 30,
                'weight' => 0.187,
                'dimensions' => '146.6 x 70.6 x 8.25 mm',
                'warranty' => '1 Year',
                'status' => 1
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'slug' => 'sony-wh-1000xm5',
                'sku' => 'SNY-WH1000XM5-001',
                'brand_id' => $sony->id,
                'category_id' => $headphones->id,
                'short_description' => 'Premium noise-canceling wireless headphones',
                'description' => '<p>Industry-leading noise cancellation with the new V1 processor. Up to 30 hours of battery life with quick charge.</p>',
                'base_price' => 399.99,
                'cost_price' => 280.00,
                'currency' => 'USD',
                'stock_status' => 'in_stock',
                'total_stock' => 25,
                'weight' => 0.250,
                'dimensions' => '254 x 192 x 80 mm',
                'warranty' => '2 Years',
                'status' => 1
            ]
        ];

        foreach ($products as $productData) {
            $product = ProductOptimized::firstOrCreate([
                'sku' => $productData['sku']
            ], $productData);

            // Add attributes for smartphones
            if ($product->category_id === $smartphones->id && isset($screenSizeAttr) && isset($storageAttr)) {
                // Add screen size attribute
                ProductAttributeOptimized::firstOrCreate([
                    'product_id' => $product->id,
                    'attribute_id' => $screenSizeAttr->id
                ], [
                    'value' => $product->name === 'Samsung Galaxy S24' ? '6.1' : '6.1',
                    'numeric_value' => 6.1
                ]);

                // Add storage attribute
                ProductAttributeOptimized::firstOrCreate([
                    'product_id' => $product->id,
                    'attribute_id' => $storageAttr->id
                ], [
                    'value' => '128',
                    'numeric_value' => 128
                ]);
            }

            // Update search index
            $product->updateSearchIndex();
        }

        $this->command->info('Sample products created successfully!');
    }
}