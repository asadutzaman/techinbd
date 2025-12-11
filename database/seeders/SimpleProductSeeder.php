<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductOptimized;
use App\Models\ProductImageOptimized;
use App\Models\Category;
use App\Models\Brand;

class SimpleProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories and brands
        $mensFashion = Category::where('name', 'Men\'s Fashion')->first();
        $womensFashion = Category::where('name', 'Women\'s Fashion')->first();
        $electronics = Category::where('name', 'Electronics')->first();
        
        $apple = Brand::where('name', 'Apple')->first();
        $samsung = Brand::where('name', 'Samsung')->first();

        $products = [
            [
                'name' => 'Men\'s Cotton T-Shirt',
                'slug' => 'mens-cotton-t-shirt',
                'description' => 'Comfortable cotton t-shirt perfect for casual wear. Available in multiple colors and sizes.',
                'base_price' => 29.99,
                'cost_price' => 15.00,
                'sku' => 'MTS001',
                'total_stock' => 50,
                'weight' => 0.2,
                'status' => 1,
                'featured' => true,
                'brand_id' => null,
                'meta_title' => 'Men\'s Cotton T-Shirt - Comfortable & Stylish',
                'meta_description' => 'Shop our comfortable men\'s cotton t-shirt. Perfect for casual wear.',
            ],
            [
                'name' => 'Women\'s Summer Dress',
                'slug' => 'womens-summer-dress',
                'description' => 'Elegant summer dress made from lightweight fabric. Perfect for warm weather.',
                'base_price' => 79.99,
                'cost_price' => 40.00,
                'sku' => 'WSD001',
                'total_stock' => 30,
                'weight' => 0.3,
                'status' => 1,
                'featured' => true,
                'brand_id' => null,
                'meta_title' => 'Women\'s Summer Dress - Elegant & Comfortable',
                'meta_description' => 'Shop our elegant women\'s summer dress. Perfect for warm weather.',
            ],
            [
                'name' => 'iPhone 15 Pro',
                'slug' => 'iphone-15-pro',
                'description' => 'Latest iPhone with advanced camera system and powerful A17 Pro chip.',
                'base_price' => 999.99,
                'cost_price' => 600.00,
                'sku' => 'IP15P001',
                'total_stock' => 25,
                'weight' => 0.5,
                'status' => 1,
                'featured' => true,
                'brand_id' => $apple ? $apple->id : null,
                'meta_title' => 'iPhone 15 Pro - Advanced Camera & A17 Pro Chip',
                'meta_description' => 'Shop the latest iPhone 15 Pro with advanced features.',
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'slug' => 'samsung-galaxy-s24',
                'description' => 'Powerful Android smartphone with excellent camera and long battery life.',
                'base_price' => 899.99,
                'cost_price' => 500.00,
                'sku' => 'SGS24001',
                'total_stock' => 35,
                'weight' => 0.4,
                'status' => 1,
                'featured' => true,
                'brand_id' => $samsung ? $samsung->id : null,
                'meta_title' => 'Samsung Galaxy S24 - Powerful Android Smartphone',
                'meta_description' => 'Shop the Samsung Galaxy S24 with excellent camera and battery.',
            ],
            [
                'name' => 'Men\'s Denim Jeans',
                'slug' => 'mens-denim-jeans',
                'description' => 'Classic denim jeans with comfortable fit. Available in various sizes.',
                'base_price' => 59.99,
                'cost_price' => 30.00,
                'sku' => 'MDJ001',
                'total_stock' => 40,
                'weight' => 0.8,
                'status' => 1,
                'featured' => false,
                'brand_id' => null,
                'meta_title' => 'Men\'s Denim Jeans - Classic & Comfortable',
                'meta_description' => 'Shop our classic men\'s denim jeans with comfortable fit.',
            ],
            [
                'name' => 'Women\'s Sneakers',
                'slug' => 'womens-sneakers',
                'description' => 'Comfortable and stylish sneakers perfect for everyday wear.',
                'base_price' => 89.99,
                'cost_price' => 45.00,
                'sku' => 'WSN001',
                'total_stock' => 60,
                'weight' => 0.6,
                'status' => 1,
                'featured' => false,
                'brand_id' => null,
                'meta_title' => 'Women\'s Sneakers - Comfortable & Stylish',
                'meta_description' => 'Shop our comfortable women\'s sneakers for everyday wear.',
            ]
        ];

        foreach ($products as $productData) {
            $product = ProductOptimized::create($productData);
            
            // Add sample images
            $imageNames = ['product-1.jpg', 'product-2.jpg', 'product-3.jpg'];
            foreach ($imageNames as $index => $imageName) {
                ProductImageOptimized::create([
                    'product_id' => $product->id,
                    'url' => 'img/' . $imageName,
                    'alt_text' => $product->name . ' - Image ' . ($index + 1),
                    'is_main' => $index === 0,
                    'sort_order' => $index
                ]);
            }
            
            // Associate with categories
            if ($product->name === 'Men\'s Cotton T-Shirt' || $product->name === 'Men\'s Denim Jeans') {
                if ($mensFashion) {
                    \DB::table('product_categories')->insert([
                        'product_id' => $product->id,
                        'category_id' => $mensFashion->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            } elseif ($product->name === 'Women\'s Summer Dress' || $product->name === 'Women\'s Sneakers') {
                if ($womensFashion) {
                    \DB::table('product_categories')->insert([
                        'product_id' => $product->id,
                        'category_id' => $womensFashion->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            } elseif ($product->name === 'iPhone 15 Pro' || $product->name === 'Samsung Galaxy S24') {
                if ($electronics) {
                    \DB::table('product_categories')->insert([
                        'product_id' => $product->id,
                        'category_id' => $electronics->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        echo "Created " . count($products) . " sample products with images and categories.\n";
    }
}