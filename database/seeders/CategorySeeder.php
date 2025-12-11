<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Men\'s Fashion',
                'slug' => 'mens-fashion',
                'description' => 'Stylish clothing and accessories for men',
                'image' => 'cat-1.jpg',
                'status' => true,
                'is_menu' => true,
                'is_featured' => true
            ],
            [
                'name' => 'Women\'s Fashion',
                'slug' => 'womens-fashion',
                'description' => 'Trendy clothing and accessories for women',
                'image' => 'cat-2.jpg',
                'status' => true,
                'is_menu' => true,
                'is_featured' => true
            ],
            [
                'name' => 'Kids Fashion',
                'slug' => 'kids-fashion',
                'description' => 'Comfortable and stylish clothing for children',
                'image' => 'cat-3.jpg',
                'status' => true,
                'is_menu' => true,
                'is_featured' => false
            ],
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Latest gadgets and electronic devices',
                'image' => 'cat-4.jpg',
                'status' => true,
                'is_menu' => true,
                'is_featured' => true
            ],
            [
                'name' => 'Sports & Outdoors',
                'slug' => 'sports-outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'image' => 'cat-1.jpg',
                'status' => true,
                'is_menu' => true,
                'is_featured' => false
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Fashion accessories and jewelry',
                'image' => 'cat-2.jpg',
                'status' => true,
                'is_menu' => true,
                'is_featured' => false
            ],
            [
                'name' => 'Shoes',
                'slug' => 'shoes',
                'description' => 'Footwear for all occasions',
                'image' => 'cat-3.jpg',
                'status' => true,
                'is_menu' => true,
                'is_featured' => true
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'description' => 'Home decor and garden supplies',
                'image' => 'cat-4.jpg',
                'status' => true,
                'is_menu' => false,
                'is_featured' => false
            ]
        ];

        foreach ($categories as $category) {
            \App\Models\Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
