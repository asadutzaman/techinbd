<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $categories = Category::pluck('id', 'name')->all();

        $products = [
            [
                'name' => 'Men\'s Fashion T-Shirt',
                'description' => 'Comfortable cotton t-shirt perfect for casual wear. Available in multiple colors and sizes.',
                'base_price' => 29.99,
                'cost_price' => 24.99,
                'total_stock' => 50,
                'category_name' => 'Men',
                'status' => true,
                'featured' => true
            ],
            [
                'name' => 'Women\'s Summer Dress',
                'description' => 'Elegant summer dress made from breathable fabric. Perfect for any occasion.',
                'base_price' => 59.99,
                'cost_price' => 49.99,
                'total_stock' => 30,
                'category_name' => 'Women',
                'status' => true,
                'featured' => true
            ],
            [
                'name' => 'Kids Casual Wear',
                'description' => 'Comfortable and durable clothing for active kids. Machine washable.',
                'base_price' => 19.99,
                'cost_price' => null,
                'total_stock' => 40,
                'category_name' => 'Kids',
                'status' => true,
                'featured' => false
            ],
            [
                'name' => 'Premium Jeans',
                'description' => 'High-quality denim jeans with perfect fit. Available in various sizes.',
                'base_price' => 79.99,
                'cost_price' => 69.99,
                'total_stock' => 25,
                'category_name' => 'Men',
                'status' => true,
                'featured' => true
            ],
            [
                'name' => 'Sports Sneakers',
                'description' => 'Comfortable sports shoes perfect for running and gym activities.',
                'base_price' => 89.99,
                'cost_price' => null,
                'total_stock' => 35,
                'category_name' => 'Shoes',
                'status' => true,
                'featured' => false
            ],
            [
                'name' => 'Elegant Blouse',
                'description' => 'Professional blouse perfect for office wear. Wrinkle-resistant fabric.',
                'base_price' => 45.99,
                'cost_price' => 39.99,
                'total_stock' => 20,
                '' => 'product-6.jpg',
                'category_name' => 'Women',
                'status' => true,
                'featured' => true
            ],
            [
                'name' => 'Casual Jacket',
                'description' => 'Stylish jacket suitable for all seasons. Water-resistant material.',
                'base_price' => 99.99,
                'cost_price' => 84.99,
                'total_stock' => 15,
                'category_name' => 'Men',
                'status' => true,
                'featured' => false
            ],
            [
                'name' => 'Designer Handbag',
                'description' => 'Luxury handbag made from genuine leather. Multiple compartments.',
                'base_price' => 149.99,
                'cost_price' => null,
                'total_stock' => 10,
                'category_name' => 'Accessories',
                'status' => true,
                'featured' => true
            ],
            [
                'name' => 'Winter Coat',
                'description' => 'Warm winter coat with insulation. Perfect for cold weather.',
                'base_price' => 129.99,
                'cost_price' => 109.99,
                'total_stock' => 18,
                'category_name' => 'Women',
                'status' => true,
                'featured' => false
            ]
        ];

        foreach ($products as $productData) {
            $categoryId = $categories[$productData['category_name']] ?? null;
            unset($productData['category_name']);
            $productData['category_id'] = $categoryId;
            Product::create($productData);
        }
    }
}