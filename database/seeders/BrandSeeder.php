<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Apple',
                'slug' => 'apple',
                'description' => 'American multinational technology company that designs, develops, and sells consumer electronics, computer software, and online services.',
                'website' => 'https://www.apple.com',
                'status' => true
            ],
            [
                'name' => 'Samsung',
                'slug' => 'samsung',
                'description' => 'South Korean multinational conglomerate headquartered in Samsung Town, Seoul.',
                'website' => 'https://www.samsung.com',
                'status' => true
            ],
            [
                'name' => 'Sony',
                'slug' => 'sony',
                'description' => 'Japanese multinational conglomerate corporation headquartered in Kōnan, Minato, Tokyo.',
                'website' => 'https://www.sony.com',
                'status' => true
            ],
            [
                'name' => 'LG',
                'slug' => 'lg',
                'description' => 'South Korean multinational electronics company headquartered in Yeouido-dong, Seoul.',
                'website' => 'https://www.lg.com',
                'status' => true
            ],
            [
                'name' => 'Dell',
                'slug' => 'dell',
                'description' => 'American multinational computer technology company that develops, sells, repairs, and supports computers and related products and services.',
                'website' => 'https://www.dell.com',
                'status' => true
            ],
            [
                'name' => 'HP',
                'slug' => 'hp',
                'description' => 'American multinational information technology company headquartered in Palo Alto, California.',
                'website' => 'https://www.hp.com',
                'status' => true
            ],
            [
                'name' => 'Lenovo',
                'slug' => 'lenovo',
                'description' => 'Chinese multinational technology company specializing in designing, manufacturing, and marketing consumer electronics, personal computers, software, business solutions, and related services.',
                'website' => 'https://www.lenovo.com',
                'status' => true
            ],
            [
                'name' => 'ASUS',
                'slug' => 'asus',
                'description' => 'Taiwanese multinational computer and phone hardware and electronics company headquartered in Beitou District, Taipei, Taiwan.',
                'website' => 'https://www.asus.com',
                'status' => true
            ]
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(['slug' => $brand['slug']], $brand);
        }
    }
}