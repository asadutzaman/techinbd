<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * The storefront's 16 tech categories, in menu order. All show in the category bar and as
 * home page tiles; their icons come from Category::getIconAttribute().
 *
 * Safe to re-run: categories are matched by slug, and ones that exist are left as they are.
 */
class CategorySeeder extends Seeder
{
    public const CATEGORIES = [
        'laptop' => ['Laptop', 'Everyday, business and gaming laptops, and MacBooks.'],
        'desktop-pc' => ['Desktop PC', 'Brand PCs, mini PCs, all-in-ones and ready-built gaming PCs.'],
        'monitor' => ['Monitor', 'Office, gaming and creator monitors.'],
        'smartphone' => ['Smartphone', 'Samsung, Apple, Xiaomi and realme phones.'],
        'tablet' => ['Tablet', 'iPads, Galaxy Tabs and Android tablets for work and study.'],
        'processor' => ['Processor', 'Intel Core and AMD Ryzen desktop processors.'],
        'motherboard' => ['Motherboard', 'Intel and AMD motherboards from ASUS, MSI and Gigabyte.'],
        'graphics-card' => ['Graphics Card', 'NVIDIA GeForce and AMD Radeon graphics cards.'],
        'ram' => ['RAM', 'DDR4 and DDR5 desktop memory.'],
        'ssd' => ['SSD', 'NVMe and SATA solid state drives.'],
        'router' => ['Router', 'Wi-Fi 5 and Wi-Fi 6 routers and mesh systems.'],
        'printer' => ['Printer', 'Ink tank and laser printers for home and office.'],
        'headphones' => ['Headphones', 'Headphones, earbuds and headsets.'],
        'keyboard-mouse' => ['Keyboard & Mouse', 'Keyboards, mice and combos for work and gaming.'],
        'television' => ['Television', 'Smart TVs from 32 to 65 inches.'],
        'ups-power' => ['UPS & Power', 'UPS, power banks and chargers.'],
    ];

    public function run(): void
    {
        $position = 0;
        foreach (self::CATEGORIES as $slug => [$name, $description]) {
            Category::firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => $description,
                'sort_order' => ++$position,
                'status' => true,
                'is_menu' => true,
                'is_featured' => true,
            ]);
        }
    }
}
