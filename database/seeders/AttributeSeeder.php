<?php

namespace Database\Seeders;

use App\Models\AttributeOptimized;
use App\Models\AttributeValueOptimized;
use Illuminate\Database\Seeder;

/**
 * Shop-wide product attributes, filterable in the shop sidebar (it only lists values that
 * products use). DemoCatalogSeeder links products to these by slug.
 *
 * Safe to re-run: attributes and values are matched by name.
 */
class AttributeSeeder extends Seeder
{
    private const ATTRIBUTES = [
        'Screen Size' => ['11"', '13"', '14"', '15"', '15.6"', '16"', '22"', '24"', '27"', '32"', '43"', '50"', '55"', '65"', '75"', '86"'],
        'Storage' => ['128GB', '256GB', '480GB', '500GB', '512GB', '1TB', '2TB'],
        'RAM' => ['4GB', '6GB', '8GB', '12GB', '16GB', '24GB', '32GB', '64GB'],
        'Color' => ['Black', 'White', 'Gray', 'Silver', 'Blue', 'Green', 'Pink', 'Purple', 'Red', 'Gold'],
    ];

    public function run(): void
    {
        $position = 0;
        foreach (self::ATTRIBUTES as $name => $values) {
            $attribute = AttributeOptimized::firstOrCreate(['name' => $name], [
                'type' => 'select',
                'required' => false,
                'filterable' => true,
                'sort_order' => ++$position,
                'status' => true,
            ]);

            foreach ($values as $index => $value) {
                AttributeValueOptimized::firstOrCreate(
                    ['attribute_id' => $attribute->id, 'value' => $value],
                    ['sort_order' => $index, 'status' => true]
                );
            }
        }
    }
}
