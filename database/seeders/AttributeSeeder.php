<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories
        $electronics = Category::where('name', 'Electronics')->first();
        $fashion = Category::where('name', 'Fashion')->first();
        $books = Category::where('name', 'Books')->first();

        if ($electronics) {
            // Electronics attributes
            $screenSize = Attribute::firstOrCreate(
                [
                    'category_id' => $electronics->id,
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
                AttributeValue::firstOrCreate(
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

            $storage = Attribute::firstOrCreate(
                [
                    'category_id' => $electronics->id,
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
                AttributeValue::firstOrCreate(
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

            $ram = Attribute::firstOrCreate(
                [
                    'category_id' => $electronics->id,
                    'name' => 'RAM'
                ],
                [
                    'type' => 'select',
                    'required' => false,
                    'filterable' => true,
                    'sort_order' => 3,
                    'status' => true
                ]
            );

            // RAM values
            $ramOptions = ['4GB', '8GB', '16GB', '32GB', '64GB'];
            foreach ($ramOptions as $index => $ram_option) {
                AttributeValue::firstOrCreate(
                    [
                        'attribute_id' => $ram->id,
                        'value' => $ram_option
                    ],
                    [
                        'sort_order' => $index,
                        'status' => true
                    ]
                );
            }

            $processor = Attribute::firstOrCreate(
                [
                    'category_id' => $electronics->id,
                    'name' => 'Processor'
                ],
                [
                    'type' => 'select',
                    'required' => false,
                    'filterable' => true,
                    'sort_order' => 4,
                    'status' => true
                ]
            );

            // Processor values
            $processors = ['Intel i3', 'Intel i5', 'Intel i7', 'Intel i9', 'AMD Ryzen 3', 'AMD Ryzen 5', 'AMD Ryzen 7', 'AMD Ryzen 9', 'Apple M1', 'Apple M2', 'Apple M3'];
            foreach ($processors as $index => $proc) {
                AttributeValue::firstOrCreate(
                    [
                        'attribute_id' => $processor->id,
                        'value' => $proc
                    ],
                    [
                        'sort_order' => $index,
                        'status' => true
                    ]
                );
            }

            $warranty = Attribute::firstOrCreate(
                [
                    'category_id' => $electronics->id,
                    'name' => 'Warranty'
                ],
                [
                    'type' => 'select',
                    'required' => false,
                    'filterable' => true,
                    'sort_order' => 5,
                    'status' => true
                ]
            );

            // Warranty values
            $warranties = ['1 Year', '2 Years', '3 Years', '5 Years'];
            foreach ($warranties as $index => $warranty_option) {
                AttributeValue::firstOrCreate(
                    [
                        'attribute_id' => $warranty->id,
                        'value' => $warranty_option
                    ],
                    [
                        'sort_order' => $index,
                        'status' => true
                    ]
                );
            }
        }

        if ($fashion) {
            // Fashion attributes
            $size = Attribute::firstOrCreate(
                [
                    'category_id' => $fashion->id,
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
                AttributeValue::firstOrCreate(
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

            $color = Attribute::firstOrCreate(
                [
                    'category_id' => $fashion->id,
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
                AttributeValue::firstOrCreate(
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

            $material = Attribute::firstOrCreate(
                [
                    'category_id' => $fashion->id,
                    'name' => 'Material'
                ],
                [
                    'type' => 'select',
                    'required' => false,
                    'filterable' => true,
                    'sort_order' => 3,
                    'status' => true
                ]
            );

            // Material values
            $materials = ['Cotton', 'Polyester', 'Wool', 'Silk', 'Linen', 'Denim', 'Leather', 'Synthetic'];
            foreach ($materials as $index => $material_option) {
                AttributeValue::firstOrCreate(
                    [
                        'attribute_id' => $material->id,
                        'value' => $material_option
                    ],
                    [
                        'sort_order' => $index,
                        'status' => true
                    ]
                );
            }
        }

        if ($books) {
            // Books attributes
            $author = Attribute::firstOrCreate(
                [
                    'category_id' => $books->id,
                    'name' => 'Author'
                ],
                [
                    'type' => 'text',
                    'required' => true,
                    'filterable' => false,
                    'sort_order' => 1,
                    'status' => true
                ]
            );

            $pages = Attribute::firstOrCreate(
                [
                    'category_id' => $books->id,
                    'name' => 'Pages'
                ],
                [
                    'type' => 'number',
                    'required' => false,
                    'filterable' => true,
                    'sort_order' => 2,
                    'status' => true
                ]
            );

            $language = Attribute::firstOrCreate(
                [
                    'category_id' => $books->id,
                    'name' => 'Language'
                ],
                [
                    'type' => 'select',
                    'required' => false,
                    'filterable' => true,
                    'sort_order' => 3,
                    'status' => true
                ]
            );

            // Language values
            $languages = ['English', 'Spanish', 'French', 'German', 'Italian', 'Portuguese', 'Chinese', 'Japanese', 'Korean'];
            foreach ($languages as $index => $language_option) {
                AttributeValue::firstOrCreate(
                    [
                        'attribute_id' => $language->id,
                        'value' => $language_option
                    ],
                    [
                        'sort_order' => $index,
                        'status' => true
                    ]
                );
            }

            $format = Attribute::firstOrCreate(
                [
                    'category_id' => $books->id,
                    'name' => 'Format'
                ],
                [
                    'type' => 'select',
                    'required' => false,
                    'filterable' => true,
                    'sort_order' => 4,
                    'status' => true
                ]
            );

            // Format values
            $formats = ['Hardcover', 'Paperback', 'eBook', 'Audiobook'];
            foreach ($formats as $index => $format_option) {
                AttributeValue::firstOrCreate(
                    [
                        'attribute_id' => $format->id,
                        'value' => $format_option
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