<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Men\'s Fashion T-Shirt',
                'description' => 'Comfortable cotton t-shirt perfect for casual wear. Available in multiple colors and sizes.',
                'price' => 29.99,
                'sale_price' => 24.99,
                'stock' => 50,
                'image' => 'product-1.jpg',
                'category' => 'men',
                'status' => true
            ],
            [
                'name' => 'Women\'s Summer Dress',
                'description' => 'Elegant summer dress made from breathable fabric. Perfect for any occasion.',
                'price' => 59.99,
                'sale_price' => 49.99,
                'stock' => 30,
                'image' => 'product-2.jpg',
                'category' => 'women',
                'status' => true
            ],
            [
                'name' => 'Kids Casual Wear',
                'description' => 'Comfortable and durable clothing for active kids. Machine washable.',
                'price' => 19.99,
                'sale_price' => null,
                'stock' => 40,
                'image' => 'product-3.jpg',
                'category' => 'kids',
                'status' => true
            ],
            [
                'name' => 'Premium Jeans',
                'description' => 'High-quality denim jeans with perfect fit. Available in various sizes.',
                'price' => 79.99,
                'sale_price' => 69.99,
                'stock' => 25,
                'image' => 'product-4.jpg',
                'category' => 'men',
                'status' => true
            ],
            [
                'name' => 'Sports Sneakers',
                'description' => 'Comfortable sports shoes perfect for running and gym activities.',
                'price' => 89.99,
                'sale_price' => null,
                'stock' => 35,
                'image' => 'product-5.jpg',
                'category' => 'shoes',
                'status' => true
            ],
            [
                'name' => 'Elegant Blouse',
                'description' => 'Professional blouse perfect for office wear. Wrinkle-resistant fabric.',
                'price' => 45.99,
                'sale_price' => 39.99,
                'stock' => 20,
                'image' => 'product-6.jpg',
                'category' => 'women',
                'status' => true
            ],
            [
                'name' => 'Casual Jacket',
                'description' => 'Stylish jacket suitable for all seasons. Water-resistant material.',
                'price' => 99.99,
                'sale_price' => 84.99,
                'stock' => 15,
                'image' => 'product-7.jpg',
                'category' => 'men',
                'status' => true
            ],
            [
                'name' => 'Designer Handbag',
                'description' => 'Luxury handbag made from genuine leather. Multiple compartments.',
                'price' => 149.99,
                'sale_price' => null,
                'stock' => 10,
                'image' => 'product-8.jpg',
                'category' => 'accessories',
                'status' => true
            ],
            [
                'name' => 'Winter Coat',
                'description' => 'Warm winter coat with insulation. Perfect for cold weather.',
                'price' => 129.99,
                'sale_price' => 109.99,
                'stock' => 18,
                'image' => 'product-9.jpg',
                'category' => 'women',
                'status' => true
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}