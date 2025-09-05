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
                'description' => 'Stylish clothing and accessories for men',
                'image' => 'cat-1.jpg',
                'status' => true
            ],
            [
                'name' => 'Women\'s Fashion',
                'description' => 'Trendy clothing and accessories for women',
                'image' => 'cat-2.jpg',
                'status' => true
            ],
            [
                'name' => 'Kids Fashion',
                'description' => 'Comfortable and stylish clothing for children',
                'image' => 'cat-3.jpg',
                'status' => true
            ],
            [
                'name' => 'Electronics',
                'description' => 'Latest gadgets and electronic devices',
                'image' => 'cat-4.jpg',
                'status' => true
            ],
            [
                'name' => 'Sports & Outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'image' => 'cat-1.jpg',
                'status' => true
            ],
            [
                'name' => 'Accessories',
                'description' => 'Fashion accessories and jewelry',
                'image' => 'cat-2.jpg',
                'status' => true
            ],
            [
                'name' => 'Shoes',
                'description' => 'Footwear for all occasions',
                'image' => 'cat-3.jpg',
                'status' => true
            ],
            [
                'name' => 'Home & Garden',
                'description' => 'Home decor and garden supplies',
                'image' => 'cat-4.jpg',
                'status' => true
            ]
        ];

        foreach ($categories as $category) {
            \App\Models\Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
