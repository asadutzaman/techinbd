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
                'price' => 29.99,
                'sale_price' => 24.99,
                'stock' => 50,
                'image' => 'product-1.jpg',
                'category_name' => 'Men',
                'status' => true,
                'featured' => true
            ],
            [
                'name' => 'Women\'s Summer Dress',
                'description' => 'Elegant summer dress made from breathable fabric. Perfect for any occasion.',
                'price' => 59.99,
                'sale_price' => 49.99,
                'stock' => 30,
                'image' => 'product-2.jpg',
                'category_name' => 'Women',
                'status' => true,
                'featured' => true
            ],
            [
                'name' => 'Kids Casual Wear',
                'description' => 'Comfortable and durable clothing for active kids. Machine washable.',
                'price' => 19.99,
                'sale_price' => null,
                'stock' => 40,
                'image' => 'product-3.jpg',
                'category_name' => 'Kids',
                'status' => true,
                'featured' => false
            ],
            [
                'name' => 'Premium Jeans',
                'description' => 'High-quality denim jeans with perfect fit. Available in various sizes.',
                'price' => 79.99,
                'sale_price' => 69.99,
                'stock' => 25,
                'image' => 'product-4.jpg',
                'category_name' => 'Men',
                'status' => true,
                'featured' => true
            ],
            [
                'name' => 'Sports Sneakers',
                'description' => 'Comfortable sports shoes perfect for running and gym activities.',
                'price' => 89.99,
                'sale_price' => null,
                'stock' => 35,
                'image' => 'product-5.jpg',
                'category_name' => 'Shoes',
                'status' => true,
                'featured' => false
            ],
            [
                'name' => 'Elegant Blouse',
                'description' => 'Professional blouse perfect for office wear. Wrinkle-resistant fabric.',
                'price' => 45.99,
                'sale_price' => 39.99,
                'stock' => 20,
                'image' => 'product-6.jpg',
                'category_name' => 'Women',
                'status' => true,
                'featured' => true
            ],
            [
                'name' => 'Casual Jacket',
                'description' => 'Stylish jacket suitable for all seasons. Water-resistant material.',
                'price' => 99.99,
                'sale_price' => 84.99,
                'stock' => 15,
                'image' => 'product-7.jpg',
                'category_name' => 'Men',
                'status' => true,
                'featured' => false
            ],
            [
                'name' => 'Designer Handbag',
                'description' => 'Luxury handbag made from genuine leather. Multiple compartments.',
                'price' => 149.99,
                'sale_price' => null,
                'stock' => 10,
                'image' => 'product-8.jpg',
                'category_name' => 'Accessories',
                'status' => true,
                'featured' => true
            ],
            [
                'name' => 'Winter Coat',
                'description' => 'Warm winter coat with insulation. Perfect for cold weather.',
                'price' => 129.99,
                'sale_price' => 109.99,
                'stock' => 18,
                'image' => 'product-9.jpg',
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