<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\AttributeOptimized;
use App\Models\AttributeValueOptimized;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories
        $electronics = Category::where('name', 'Electronics')->first();
        $mensFashion = Category::where('name', 'Men\'s Fashion')->first();
        $womensFashion = Category::where('name', 'Women\'s Fashion')->first();

        if ($electronics) {
            // Electronics attributes
            $screenSize = AttributeOptimized::firstOrCreate(
                [
                    'name' => 'Screen Size'
                ],
                [
                    'type' => 'select',
                    'required' => false,
                    'filterable' => true,
                    'sort_order' => 1,
                    'status' => true
                ]
            );

            // Screen size values
            $screenSizes = ['13"', '14"', '15.6"', '17"', '21"', '24"', '27"', '32"', '43"', '55"', '65"', '75"'];
            foreach ($screenSizes as $index => $size) {
                AttributeValueOptimized::firstOrCreate(
                    [
                        'attribute_id' => $screenSize->id,
                        'value' => $size
                    ],
                    [
                        'sort_order' => $index,
                        'status' => true
                    ]
                );
            }

            $storage = AttributeOptimized::firstOrCreate(
                [
                    'name' => 'Storage'
                ],
                [
                    'type' => 'select',
                    'required' => false,
                    'filterable' => true,
                    'sort_order' => 2,
                    'status' => true
                ]
            );

            // Storage values
            $storageOptions = ['128GB', '256GB', '512GB', '1TB', '2TB', '4TB', '8TB'];
            foreach ($storageOptions as $index => $storage_option) {
                AttributeValueOptimized::firstOrCreate(
                    [
                        'attribute_id' => $storage->id,
                        'value' => $storage_option
                    ],
                    [
                        'sort_order' => $index,
                        'status' => true
                    ]
                );
            }
        }

        if ($mensFashion) {
            // Men's Fashion attributes
            $size = AttributeOptimized::firstOrCreate(
                [
                    'name' => 'Size'
                ],
                [
                    'type' => 'select',
                    'required' => true,
                    'filterable' => true,
                    'sort_order' => 1,
                    'status' => true
                ]
            );

            // Size values
            $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
            foreach ($sizes as $index => $size_option) {
                AttributeValueOptimized::firstOrCreate(
                    [
                        'attribute_id' => $size->id,
                        'value' => $size_option
                    ],
                    [
                        'sort_order' => $index,
                        'status' => true
                    ]
                );
            }

            $color = AttributeOptimized::firstOrCreate(
                [
                    'name' => 'Color'
                ],
                [
                    'type' => 'select',
                    'required' => false,
                    'filterable' => true,
                    'sort_order' => 2,
                    'status' => true
                ]
            );

            // Color values
            $colors = ['Black', 'White', 'Red', 'Blue', 'Green', 'Yellow', 'Pink', 'Purple', 'Orange', 'Gray', 'Brown'];
            foreach ($colors as $index => $color_option) {
                AttributeValueOptimized::firstOrCreate(
                    [
                        'attribute_id' => $color->id,
                        'value' => $color_option
                    ],
                    [
                        'sort_order' => $index,
                        'status' => true
                    ]
                );
            }
        }
    }
}