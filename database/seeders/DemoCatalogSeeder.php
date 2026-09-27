<?php

namespace Database\Seeders;

use App\Models\AttributeOptimized;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductAttributeOptimized;
use App\Models\ProductImageOptimized;
use App\Models\ProductOptimized;
use App\Models\ProductVariantOptimized;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 100 demo tech products across the 16 CategorySeeder categories, with specs, HTML descriptions,
 * variants, attributes, SEO fields, barcodes and a generated cover image + thumbnail each.
 *
 *   php artisan db:seed --class=DemoCatalogSeeder
 *
 * Safe to re-run: products are matched by SKU (TIB-XXX-NNN) and updated in place. Demo products,
 * categories and brands from the earlier fashion catalog are removed; anything added in admin stays.
 */
class DemoCatalogSeeder extends Seeder
{
    private const DEVICE_INTRO = 'Every %1$s we sell is brand-new and sealed in its original %2$s box, and comes with the warranty listed below.';
    private const DEVICE_NOTES = ['Brand-new, sealed-box unit', '7-day replacement for manufacturing defects', 'Cash on delivery available across Bangladesh'];
    private const PART_INTRO = 'The %1$s is a genuine %2$s part in retail packaging, covered by the warranty listed below.';
    private const PART_NOTES = ['Genuine, retail-boxed part', 'Check that it suits the rest of your build before ordering', 'Keep the box and invoice for warranty claims'];
    private const GEAR_INTRO = 'The %1$s is a genuine %2$s product, packed with everything you need to start using it straight away.';
    private const GEAR_NOTES = ['100% genuine product', '7-day replacement for manufacturing defects', 'Cash on delivery available across Bangladesh'];

    /**
     * Per category: SKU prefix, default warranty, variant JSON key, description intro and "good to know" notes.
     */
    private const CATEGORIES = [
        'laptop' => ['prefix' => 'LAP', 'warranty' => '2 Years Official Warranty', 'variant_key' => 'configuration', 'intro' => self::DEVICE_INTRO, 'notes' => self::DEVICE_NOTES],
        'desktop-pc' => ['prefix' => 'DPC', 'warranty' => '3 Years Warranty', 'variant_key' => 'configuration', 'intro' => self::DEVICE_INTRO, 'notes' => self::DEVICE_NOTES],
        'monitor' => ['prefix' => 'MON', 'warranty' => '3 Years Official Warranty', 'variant_key' => 'option', 'intro' => self::DEVICE_INTRO, 'notes' => self::DEVICE_NOTES],
        'smartphone' => ['prefix' => 'PHN', 'warranty' => '1 Year Official Warranty', 'variant_key' => 'configuration', 'intro' => self::DEVICE_INTRO, 'notes' => self::DEVICE_NOTES],
        'tablet' => ['prefix' => 'TAB', 'warranty' => '1 Year Official Warranty', 'variant_key' => 'configuration', 'intro' => self::DEVICE_INTRO, 'notes' => self::DEVICE_NOTES],
        'processor' => ['prefix' => 'CPU', 'warranty' => '3 Years Warranty', 'variant_key' => 'option', 'intro' => self::PART_INTRO, 'notes' => self::PART_NOTES],
        'motherboard' => ['prefix' => 'MBD', 'warranty' => '3 Years Warranty', 'variant_key' => 'option', 'intro' => self::PART_INTRO, 'notes' => self::PART_NOTES],
        'graphics-card' => ['prefix' => 'GPU', 'warranty' => '3 Years Warranty', 'variant_key' => 'option', 'intro' => self::PART_INTRO, 'notes' => self::PART_NOTES],
        'ram' => ['prefix' => 'RAM', 'warranty' => 'Lifetime Warranty', 'variant_key' => 'option', 'intro' => self::PART_INTRO, 'notes' => self::PART_NOTES],
        'ssd' => ['prefix' => 'SSD', 'warranty' => '3 Years Warranty', 'variant_key' => 'option', 'intro' => self::PART_INTRO, 'notes' => self::PART_NOTES],
        'router' => ['prefix' => 'RTR', 'warranty' => '2 Years Warranty', 'variant_key' => 'option', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
        'printer' => ['prefix' => 'PRN', 'warranty' => '1 Year Warranty', 'variant_key' => 'option',
            'intro' => 'The %1$s comes with starter ink or toner from %2$s in the box, so you can print as soon as it is set up.', 'notes' => self::GEAR_NOTES],
        'headphones' => ['prefix' => 'HPH', 'warranty' => '1 Year Warranty', 'variant_key' => 'color', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
        'keyboard-mouse' => ['prefix' => 'KBM', 'warranty' => '1 Year Warranty', 'variant_key' => 'option', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
        'television' => ['prefix' => 'TVS', 'warranty' => '2 Years Official Warranty', 'variant_key' => 'option', 'intro' => self::DEVICE_INTRO, 'notes' => self::DEVICE_NOTES],
        'ups-power' => ['prefix' => 'UPS', 'warranty' => '1 Year Warranty', 'variant_key' => 'option', 'intro' => self::GEAR_INTRO, 'notes' => self::GEAR_NOTES],
    ];

    private const BRANDS = [
        'apple' => ['Apple', 'iPhone, iPad, Mac and AirPods.'],
        'samsung' => ['Samsung', 'Galaxy phones and tablets, monitors, TVs, SSDs and chargers.'],
        'xiaomi' => ['Xiaomi', 'Smartphones, tablets, TVs, routers and power banks.'],
        'realme' => ['realme', 'Smartphones with big batteries and fast charging.'],
        'asus' => ['ASUS', 'Laptops, motherboards, graphics cards, monitors and mini PCs.'],
        'lenovo' => ['Lenovo', 'IdeaPad, LOQ and IdeaCentre computers.'],
        'hp' => ['HP', 'Laptops, desktops and printers.'],
        'dell' => ['Dell', 'Laptops, OptiPlex desktops and monitors.'],
        'acer' => ['Acer', 'Aspire laptops.'],
        'msi' => ['MSI', 'Motherboards, graphics cards and monitors.'],
        'gigabyte' => ['Gigabyte', 'Motherboards, graphics cards and monitors.'],
        'intel' => ['Intel', 'Core and Core Ultra processors.'],
        'amd' => ['AMD', 'Ryzen processors and Radeon graphics.'],
        'zotac' => ['ZOTAC', 'GeForce graphics cards.'],
        'corsair' => ['Corsair', 'Memory and gaming gear.'],
        'kingston' => ['Kingston', 'Memory and SSDs.'],
        'adata' => ['ADATA', 'XPG memory and storage.'],
        'western-digital' => ['Western Digital', 'WD_BLACK and WD Blue SSDs.'],
        'tp-link' => ['TP-Link', 'Wi-Fi routers and mesh systems.'],
        'tenda' => ['Tenda', 'Affordable home networking.'],
        'canon' => ['Canon', 'Ink tank and laser printers.'],
        'epson' => ['Epson', 'EcoTank ink tank printers.'],
        'sony' => ['Sony', 'Headphones and BRAVIA TVs.'],
        'jbl' => ['JBL', 'Headphones and earbuds.'],
        'edifier' => ['Edifier', 'Headphones and speakers.'],
        'logitech' => ['Logitech', 'Mice, keyboards and headsets.'],
        'a4tech' => ['A4Tech', 'Keyboards and mice.'],
        'lg' => ['LG', 'Monitors and TVs.'],
        'walton' => ['Walton', 'Bangladeshi electronics maker.'],
        'anker' => ['Anker', 'Chargers and power banks.'],
        'apc' => ['APC', 'UPS for computers and networking.'],
    ];

    /** From the earlier fashion and home-goods demo catalog; removed once nothing uses them */
    private const RETIRED_CATEGORIES = ['mens-fashion', 'womens-fashion', 'kids-fashion', 'electronics', 'sports-outdoors', 'accessories', 'shoes', 'home-garden'];

    private const RETIRED_BRANDS = ['levis', 'zara', 'hm', 'uniqlo', 'aarong', 'yellow', 'nike', 'adidas', 'puma', 'bata', 'apex', 'yonex', 'sg', 'decathlon', 'casio', 'fossil', 'ray-ban', 'philips', 'miyako', 'ikea', 'vision'];

    /**
     * name, brand, price (BDT), was (compare-at price), hl (one-line highlight), specs,
     * attrs (attribute slug => value), variants (list, or name => price), f (featured),
     * mpn, weight (kg), dim, warranty, status (out_of_stock|preorder), vkey (variant JSON key)
     */
    private const CATALOG = [
        'laptop' => [
            ['name' => 'Apple MacBook Air 13" M5', 'brand' => 'apple', 'price' => 154999, 'f' => true,
                'hl' => "Apple's thin and light laptop, now with the M5 chip: silent, fanless and good for a full day's work.",
                'specs' => ['Display' => '13.6" Liquid Retina', 'Chip' => 'Apple M5', 'Memory' => '16GB unified memory', 'Storage' => '256GB SSD', 'Security' => 'Touch ID', 'Ports' => '2x Thunderbolt / USB 4, MagSafe 3, headphone jack'],
                'attrs' => ['screen-size' => '13"', 'ram' => '16GB', 'storage' => '256GB', 'color' => 'Silver'],
                'variants' => ['16GB / 256GB' => 154999, '16GB / 512GB' => 179999, '24GB / 512GB' => 199999],
                'weight' => 1.24, 'dim' => '30.41 x 21.5 x 1.13 cm', 'warranty' => '1 Year Official Warranty'],
            ['name' => 'Apple MacBook Pro 14" M5', 'brand' => 'apple', 'price' => 229999, 'f' => true,
                'hl' => 'Pro performance for developers and creators, with the M5 chip and a Liquid Retina XDR display.',
                'specs' => ['Display' => '14.2" Liquid Retina XDR, 120Hz ProMotion', 'Chip' => 'Apple M5', 'Memory' => '16GB unified memory', 'Storage' => '512GB SSD', 'Ports' => '3x Thunderbolt 4, HDMI, SDXC, MagSafe 3'],
                'attrs' => ['screen-size' => '14"', 'ram' => '16GB', 'storage' => '512GB', 'color' => 'Black'],
                'weight' => 1.55, 'dim' => '31.26 x 22.12 x 1.55 cm', 'warranty' => '1 Year Official Warranty'],
            ['name' => 'ASUS TUF Gaming F15', 'brand' => 'asus', 'price' => 119999, 'was' => 129999, 'f' => true,
                'hl' => 'A military-grade tough gaming laptop with RTX 4050 graphics and a 144Hz display.',
                'specs' => ['Display' => '15.6" FHD, 144Hz', 'Processor' => 'Intel Core i5-12500H', 'Graphics' => 'NVIDIA GeForce RTX 4050 6GB', 'Memory' => '16GB DDR4', 'Storage' => '512GB NVMe SSD'],
                'attrs' => ['screen-size' => '15.6"', 'ram' => '16GB', 'storage' => '512GB', 'color' => 'Gray'],
                'mpn' => 'FX507ZU4', 'weight' => 2.2, 'dim' => '35.4 x 25.1 x 2.49 cm'],
            ['name' => 'Lenovo LOQ 15IRX9', 'brand' => 'lenovo', 'price' => 164000, 'was' => 172000, 'f' => true,
                'hl' => 'A gaming laptop with a Core i7 HX processor and RTX 4060 for high settings at 1080p.',
                'specs' => ['Display' => '15.6" FHD IPS, 144Hz', 'Processor' => 'Intel Core i7-13650HX', 'Graphics' => 'NVIDIA GeForce RTX 4060 8GB', 'Memory' => '16GB DDR5', 'Storage' => '1TB NVMe SSD'],
                'attrs' => ['screen-size' => '15.6"', 'ram' => '16GB', 'storage' => '1TB', 'color' => 'Gray'],
                'mpn' => '83DV', 'weight' => 2.4, 'dim' => '35.96 x 26.4 x 2.39 cm'],
            ['name' => 'HP Victus 15 Gaming Laptop', 'brand' => 'hp', 'price' => 109500,
                'hl' => 'A 144Hz gaming laptop with a Ryzen 5 and RTX 4050, at an entry-level price.',
                'specs' => ['Display' => '15.6" FHD IPS, 144Hz', 'Processor' => 'AMD Ryzen 5 8645HS', 'Graphics' => 'NVIDIA GeForce RTX 4050 6GB', 'Memory' => '16GB DDR5', 'Storage' => '512GB NVMe SSD'],
                'attrs' => ['screen-size' => '15.6"', 'ram' => '16GB', 'storage' => '512GB', 'color' => 'Blue'],
                'mpn' => '15-fb2xxx', 'weight' => 2.29, 'dim' => '35.7 x 25.5 x 2.35 cm'],
            ['name' => 'Lenovo IdeaPad Slim 3 15IRH8', 'brand' => 'lenovo', 'price' => 72500,
                'hl' => 'Slim and light, with a Core i5 H-series processor and 16GB of memory.',
                'specs' => ['Display' => '15.6" FHD IPS', 'Processor' => 'Intel Core i5-13420H', 'Memory' => '16GB LPDDR5', 'Storage' => '512GB NVMe SSD', 'OS' => 'Windows 11 Home'],
                'attrs' => ['screen-size' => '15.6"', 'ram' => '16GB', 'storage' => '512GB', 'color' => 'Gray'],
                'mpn' => '83EM', 'weight' => 1.62, 'dim' => '35.9 x 23.6 x 1.79 cm'],
            ['name' => 'ASUS Vivobook 15 X1504VA', 'brand' => 'asus', 'price' => 62500,
                'hl' => 'A roomy 15.6-inch laptop for study and office work, with a 13th Gen Intel Core i5.',
                'specs' => ['Display' => '15.6" FHD IPS', 'Processor' => 'Intel Core i5-1335U', 'Memory' => '8GB DDR4', 'Storage' => '512GB NVMe SSD', 'OS' => 'Windows 11 Home'],
                'attrs' => ['screen-size' => '15.6"', 'ram' => '8GB', 'storage' => '512GB', 'color' => 'Silver'],
                'mpn' => 'X1504VA', 'weight' => 1.7, 'dim' => '36 x 23.3 x 1.8 cm'],
            ['name' => 'Dell Inspiron 15 3530', 'brand' => 'dell', 'price' => 69999, 'was' => 74999,
                'hl' => 'A dependable 15.6-inch laptop for study and office work, with a 120Hz display.',
                'specs' => ['Display' => '15.6" FHD, 120Hz', 'Processor' => 'Intel Core i5-1335U', 'Memory' => '8GB DDR4', 'Storage' => '512GB NVMe SSD', 'OS' => 'Windows 11 Home'],
                'attrs' => ['screen-size' => '15.6"', 'ram' => '8GB', 'storage' => '512GB', 'color' => 'Black'],
                'mpn' => 'Inspiron 3530', 'weight' => 1.65, 'dim' => '35.8 x 23.5 x 1.9 cm'],
            ['name' => 'HP 15 Laptop Core i3', 'brand' => 'hp', 'price' => 52500, 'was' => 55500,
                'hl' => 'An affordable 15.6-inch HP for everyday browsing, classes and office apps.',
                'specs' => ['Display' => '15.6" FHD', 'Processor' => 'Intel Core i3-1315U', 'Memory' => '8GB DDR4', 'Storage' => '512GB NVMe SSD', 'OS' => 'Windows 11 Home'],
                'attrs' => ['screen-size' => '15.6"', 'ram' => '8GB', 'storage' => '512GB', 'color' => 'Silver'],
                'mpn' => '15-fd0xxx', 'weight' => 1.59, 'dim' => '35.9 x 24.2 x 1.86 cm'],
            ['name' => 'Acer Aspire Lite 15 Ryzen 5', 'brand' => 'acer', 'price' => 49900, 'status' => 'out_of_stock',
                'hl' => 'A budget-friendly 15.6-inch laptop with a six-core AMD Ryzen 5 processor.',
                'specs' => ['Display' => '15.6" FHD IPS', 'Processor' => 'AMD Ryzen 5 5625U', 'Memory' => '8GB DDR4', 'Storage' => '512GB NVMe SSD', 'OS' => 'Windows 11 Home'],
                'attrs' => ['screen-size' => '15.6"', 'ram' => '8GB', 'storage' => '512GB', 'color' => 'Silver'],
                'mpn' => 'AL15-41', 'weight' => 1.7, 'dim' => '36.2 x 23.8 x 1.8 cm'],
        ],

        'desktop-pc' => [
            ['name' => 'Apple Mac mini M4', 'brand' => 'apple', 'price' => 74999, 'f' => true,
                'hl' => "Apple's smallest Mac, with the M4 chip, 16GB of memory and USB-C ports on the front.",
                'specs' => ['Chip' => 'Apple M4, 10-core CPU, 10-core GPU', 'Memory' => '16GB unified memory', 'Storage' => '256GB SSD', 'Ports' => '3x Thunderbolt 4, HDMI, Gigabit Ethernet, 2x front USB-C', 'Size' => '12.7 x 12.7 x 5 cm'],
                'attrs' => ['ram' => '16GB', 'storage' => '256GB', 'color' => 'Silver'],
                'variants' => ['16GB / 256GB' => 74999, '16GB / 512GB' => 94999],
                'mpn' => 'MU9D3', 'weight' => 0.67, 'dim' => '12.7 x 12.7 x 5 cm', 'warranty' => '1 Year Official Warranty'],
            ['name' => 'AMD Ryzen 5 7600 RTX 4060 Gaming PC', 'brand' => 'amd', 'price' => 139000, 'was' => 146000, 'f' => true,
                'hl' => 'A ready-built 1080p gaming PC with a Ryzen 5 7600, RTX 4060 and 16GB of DDR5.',
                'specs' => ['Processor' => 'AMD Ryzen 5 7600', 'Motherboard' => 'Gigabyte B650M GAMING X AX', 'Graphics' => 'NVIDIA GeForce RTX 4060 8GB', 'Memory' => '16GB DDR5 5600MHz', 'Storage' => '1TB NVMe SSD', 'Power supply' => '650W 80 Plus Bronze'],
                'attrs' => ['ram' => '16GB', 'storage' => '1TB'],
                'weight' => 9.5, 'dim' => '41 x 21 x 44 cm', 'warranty' => '3 Years Warranty (parts)'],
            ['name' => 'HP Pro Tower 280 G9', 'brand' => 'hp', 'price' => 64500,
                'hl' => 'A compact business tower with a 12th Gen Core i5, ready for office work out of the box.',
                'specs' => ['Processor' => 'Intel Core i5-12500', 'Memory' => '8GB DDR4', 'Storage' => '512GB NVMe SSD', 'Graphics' => 'Intel UHD Graphics 770', 'OS' => 'FreeDOS'],
                'attrs' => ['ram' => '8GB', 'storage' => '512GB'],
                'mpn' => '280 G9', 'weight' => 4.2, 'dim' => '15.5 x 29.6 x 33.7 cm'],
            ['name' => 'Dell OptiPlex 7010 Tower', 'brand' => 'dell', 'price' => 84500, 'was' => 89000,
                'hl' => 'A dependable office tower with a 13th Gen Core i5 and room to upgrade.',
                'specs' => ['Processor' => 'Intel Core i5-13500', 'Memory' => '8GB DDR4', 'Storage' => '512GB NVMe SSD', 'Graphics' => 'Intel UHD Graphics 770', 'OS' => 'Ubuntu Linux'],
                'attrs' => ['ram' => '8GB', 'storage' => '512GB'],
                'mpn' => 'OptiPlex 7010', 'weight' => 5.6, 'dim' => '32.4 x 15.4 x 29.3 cm'],
            ['name' => 'Lenovo IdeaCentre AIO 3 24"', 'brand' => 'lenovo', 'price' => 88500, 'was' => 92000,
                'hl' => "An all-in-one PC with a 24-inch Full HD screen, so there's no tower to find space for.",
                'specs' => ['Display' => '23.8" FHD IPS', 'Processor' => 'Intel Core i5-13420H', 'Memory' => '8GB DDR4', 'Storage' => '512GB NVMe SSD', 'In the box' => 'Keyboard and mouse'],
                'attrs' => ['screen-size' => '24"', 'ram' => '8GB', 'storage' => '512GB', 'color' => 'Black'],
                'mpn' => 'F0HN', 'weight' => 5.9, 'dim' => '54.1 x 41.6 x 17.8 cm'],
            ['name' => 'ASUS NUC 14 Pro Mini PC', 'brand' => 'asus', 'price' => 64000,
                'hl' => 'A palm-sized PC with an Intel Core Ultra 5 that can mount behind a monitor.',
                'specs' => ['Processor' => 'Intel Core Ultra 5 125H', 'Memory' => '16GB DDR5', 'Storage' => '512GB NVMe SSD', 'Ports' => 'Thunderbolt 4, 2x HDMI, 2.5G Ethernet', 'Wireless' => 'Wi-Fi 6E, Bluetooth 5.3'],
                'attrs' => ['ram' => '16GB', 'storage' => '512GB'],
                'mpn' => 'RNUC14RVHU5', 'weight' => 0.6, 'dim' => '11.7 x 11.2 x 5.4 cm'],
        ],

        'monitor' => [
            ['name' => 'Samsung Odyssey G5 27" QHD Gaming Monitor', 'brand' => 'samsung', 'price' => 32500, 'was' => 35500, 'f' => true,
                'hl' => 'A curved 27-inch QHD gaming monitor with a fast 165Hz refresh rate and HDR10.',
                'specs' => ['Screen' => '27" QHD (2560 x 1440) VA, 1000R curve', 'Refresh rate' => '165Hz', 'Response time' => '1ms (MPRT)', 'HDR' => 'HDR10', 'Ports' => 'HDMI, DisplayPort'],
                'attrs' => ['screen-size' => '27"', 'color' => 'Black'],
                'mpn' => 'LS27CG552', 'weight' => 5.8, 'dim' => '61.5 x 46.4 x 25.6 cm'],
            ['name' => 'ASUS TUF Gaming VG27AQ3A 27" QHD Monitor', 'brand' => 'asus', 'price' => 33500,
                'hl' => 'A fast 27-inch QHD IPS monitor at 180Hz for sharp, smooth gaming.',
                'specs' => ['Screen' => '27" QHD (2560 x 1440) Fast IPS', 'Refresh rate' => '180Hz', 'Response time' => '1ms (GtG)', 'Sync' => 'G-SYNC Compatible, FreeSync Premium', 'Ports' => '2x HDMI, DisplayPort'],
                'attrs' => ['screen-size' => '27"', 'color' => 'Black'],
                'mpn' => 'VG27AQ3A', 'weight' => 5.8, 'dim' => '61.3 x 46.1 x 21.1 cm'],
            ['name' => 'LG UltraGear 27GS65F 27" 180Hz Monitor', 'brand' => 'lg', 'price' => 23900,
                'hl' => 'An esports-ready 27-inch IPS monitor with 180Hz and 1ms response.',
                'specs' => ['Screen' => '27" FHD (1920 x 1080) IPS', 'Refresh rate' => '180Hz', 'Response time' => '1ms (GtG)', 'HDR' => 'HDR10', 'Ports' => '2x HDMI, DisplayPort'],
                'attrs' => ['screen-size' => '27"', 'color' => 'Black'],
                'mpn' => '27GS65F-B', 'weight' => 4.3, 'dim' => '61.3 x 45.6 x 19.6 cm'],
            ['name' => 'Gigabyte G27F 2 27" 170Hz Monitor', 'brand' => 'gigabyte', 'price' => 21500,
                'hl' => 'A 27-inch 170Hz IPS gaming monitor with 1ms response and wide colour.',
                'specs' => ['Screen' => '27" FHD (1920 x 1080) SS IPS', 'Refresh rate' => '170Hz', 'Response time' => '1ms (MPRT)', 'Colour' => '95% DCI-P3', 'Ports' => '2x HDMI, DisplayPort, USB'],
                'attrs' => ['screen-size' => '27"', 'color' => 'Black'],
                'mpn' => 'G27F 2', 'weight' => 5.4, 'dim' => '61.2 x 46.1 x 19.8 cm'],
            ['name' => 'Dell 24" Full HD Monitor SE2425H', 'brand' => 'dell', 'price' => 13800,
                'hl' => 'A comfortable everyday monitor with thin bezels and ComfortView eye care.',
                'specs' => ['Screen' => '23.8" FHD (1920 x 1080) VA', 'Refresh rate' => '75Hz', 'Response time' => '5ms', 'Ports' => 'HDMI, VGA', 'Eye care' => 'ComfortView (low blue light)'],
                'attrs' => ['screen-size' => '24"', 'color' => 'Black'],
                'mpn' => 'SE2425H', 'weight' => 2.9, 'dim' => '54 x 41.7 x 18.1 cm'],
            ['name' => 'LG 24MR400 24" IPS Monitor', 'brand' => 'lg', 'price' => 11900,
                'hl' => 'A slim 24-inch IPS monitor with a 100Hz refresh rate for smooth everyday work.',
                'specs' => ['Screen' => '23.8" FHD (1920 x 1080) IPS', 'Refresh rate' => '100Hz', 'Response time' => '5ms (GtG)', 'Ports' => 'HDMI, VGA', 'Features' => 'AMD FreeSync, Reader Mode'],
                'attrs' => ['screen-size' => '24"', 'color' => 'Black'],
                'mpn' => '24MR400-B', 'weight' => 3.1, 'dim' => '53.9 x 41.7 x 18.6 cm'],
            ['name' => 'MSI PRO MP243L 24" Office Monitor', 'brand' => 'msi', 'price' => 11200, 'was' => 12500,
                'hl' => 'An affordable 24-inch office monitor with a 100Hz IPS panel and eye-care features.',
                'specs' => ['Screen' => '23.8" FHD (1920 x 1080) IPS', 'Refresh rate' => '100Hz', 'Response time' => '4ms (GtG)', 'Eye care' => 'Less Blue Light PRO, Anti-Flicker', 'Ports' => 'HDMI, VGA'],
                'attrs' => ['screen-size' => '24"', 'color' => 'Black'],
                'mpn' => 'PRO MP243L', 'weight' => 2.6, 'dim' => '54 x 41.5 x 17.5 cm'],
            ['name' => 'Samsung ViewFinity S8 27" 4K Monitor', 'brand' => 'samsung', 'price' => 47500, 'status' => 'preorder',
                'hl' => 'A 27-inch 4K monitor with HDR10 and a height-adjustable stand for detailed creative work.',
                'specs' => ['Screen' => '27" 4K UHD (3840 x 2160) IPS', 'Refresh rate' => '60Hz', 'HDR' => 'HDR10', 'Colour' => '99% sRGB', 'Stand' => 'Height, tilt and pivot adjustable'],
                'attrs' => ['screen-size' => '27"', 'color' => 'Black'],
                'mpn' => 'LS27B800PX', 'weight' => 6.7, 'dim' => '61.4 x 54 x 20 cm'],
        ],

        'smartphone' => [
            ['name' => 'Samsung Galaxy S24 Ultra', 'brand' => 'samsung', 'price' => 164999, 'was' => 179999, 'f' => true,
                'hl' => "Samsung's flagship with a built-in S Pen, 200MP camera and a titanium frame.",
                'specs' => ['Display' => '6.8" QHD+ Dynamic AMOLED 2X, 120Hz', 'Processor' => 'Snapdragon 8 Gen 3 for Galaxy', 'RAM' => '12GB', 'Rear camera' => '200MP + 50MP + 12MP + 10MP', 'Battery' => '5000mAh, 45W fast charging', 'Software' => 'Android 14, One UI 6.1'],
                'attrs' => ['ram' => '12GB', 'storage' => '256GB', 'color' => 'Gray'], 'variants' => ['12GB / 256GB' => 164999, '12GB / 512GB' => 184999],
                'mpn' => 'SM-S928B', 'weight' => 0.233, 'dim' => '162.3 x 79 x 8.6 mm'],
            ['name' => 'Samsung Galaxy S25', 'brand' => 'samsung', 'price' => 114999, 'f' => true,
                'hl' => "Samsung's compact flagship with the Snapdragon 8 Elite for Galaxy and Galaxy AI.",
                'specs' => ['Display' => '6.2" FHD+ Dynamic AMOLED 2X, 120Hz', 'Processor' => 'Snapdragon 8 Elite for Galaxy', 'RAM' => '12GB', 'Rear camera' => '50MP + 12MP + 10MP', 'Battery' => '4000mAh, 25W fast charging', 'Software' => 'Android 15, One UI 7'],
                'attrs' => ['ram' => '12GB', 'storage' => '256GB', 'color' => 'Blue'], 'variants' => ['12GB / 256GB' => 114999, '12GB / 512GB' => 129999],
                'mpn' => 'SM-S931B', 'weight' => 0.162, 'dim' => '146.9 x 70.5 x 7.2 mm'],
            ['name' => 'Samsung Galaxy A56 5G', 'brand' => 'samsung', 'price' => 54999, 'was' => 57999,
                'hl' => 'A premium mid-ranger with an aluminium frame, IP67 rating and 45W charging.',
                'specs' => ['Display' => '6.7" FHD+ Super AMOLED, 120Hz', 'Processor' => 'Exynos 1580', 'RAM' => '8GB', 'Rear camera' => '50MP + 12MP + 5MP', 'Battery' => '5000mAh, 45W fast charging', 'Protection' => 'IP67, Gorilla Glass Victus+'],
                'attrs' => ['ram' => '8GB', 'storage' => '128GB', 'color' => 'Gray'], 'variants' => ['8GB / 128GB' => 54999, '8GB / 256GB' => 59999],
                'mpn' => 'SM-A566E', 'weight' => 0.198, 'dim' => '162.2 x 77.5 x 7.4 mm'],
            ['name' => 'Samsung Galaxy A16 5G', 'brand' => 'samsung', 'price' => 19999,
                'hl' => 'An everyday Samsung with a big Super AMOLED display and long battery life.',
                'specs' => ['Display' => '6.7" FHD+ Super AMOLED, 90Hz', 'Processor' => 'Exynos 1330', 'RAM' => '6GB', 'Rear camera' => '50MP + 5MP + 2MP', 'Battery' => '5000mAh, 25W fast charging'],
                'attrs' => ['ram' => '6GB', 'storage' => '128GB', 'color' => 'Black'],
                'mpn' => 'SM-A166P', 'weight' => 0.192, 'dim' => '164.4 x 77.9 x 7.9 mm'],
            ['name' => 'Apple iPhone 16', 'brand' => 'apple', 'price' => 134999, 'f' => true,
                'hl' => 'The A18 chip, a Camera Control button and a 48MP Fusion camera in the standard iPhone.',
                'specs' => ['Display' => '6.1" Super Retina XDR OLED', 'Chip' => 'A18', 'Rear camera' => '48MP Fusion + 12MP ultra wide', 'Controls' => 'Camera Control, Action button', 'Connector' => 'USB-C'],
                'attrs' => ['storage' => '128GB', 'color' => 'Black'], 'variants' => ['128GB' => 134999, '256GB' => 149999], 'vkey' => 'storage',
                'mpn' => 'MYE73', 'weight' => 0.17, 'dim' => '147.6 x 71.6 x 7.8 mm'],
            ['name' => 'Apple iPhone 16 Pro Max', 'brand' => 'apple', 'price' => 219999,
                'hl' => "Apple's biggest iPhone, with the A18 Pro, a 5x telephoto camera and a titanium design.",
                'specs' => ['Display' => '6.9" Super Retina XDR, ProMotion 120Hz', 'Chip' => 'A18 Pro', 'Rear camera' => '48MP Fusion + 48MP ultra wide + 12MP 5x telephoto', 'Build' => 'Titanium frame, Camera Control', 'Connector' => 'USB-C (USB 3)'],
                'attrs' => ['storage' => '256GB', 'color' => 'Gray'], 'variants' => ['256GB' => 219999, '512GB' => 249999, '1TB' => 279999], 'vkey' => 'storage',
                'mpn' => 'MYWV3', 'weight' => 0.227, 'dim' => '163 x 77.6 x 8.25 mm'],
            ['name' => 'Apple iPhone 15', 'brand' => 'apple', 'price' => 109999, 'was' => 119999,
                'hl' => 'Dynamic Island, a 48MP main camera and USB-C in the standard iPhone.',
                'specs' => ['Display' => '6.1" Super Retina XDR OLED', 'Chip' => 'A16 Bionic', 'Rear camera' => '48MP main + 12MP ultra wide', 'Connector' => 'USB-C'],
                'attrs' => ['storage' => '128GB', 'color' => 'Pink'], 'variants' => ['128GB' => 109999, '256GB' => 124999], 'vkey' => 'storage',
                'mpn' => 'MTP13', 'weight' => 0.171, 'dim' => '147.6 x 71.6 x 7.8 mm'],
            ['name' => 'Xiaomi Redmi Note 14 Pro 5G', 'brand' => 'xiaomi', 'price' => 36999,
                'hl' => 'A 200MP camera, IP68 protection and 45W charging at a mid-range price.',
                'specs' => ['Display' => '6.67" 1.5K AMOLED, 120Hz', 'Processor' => 'MediaTek Dimensity 7300-Ultra', 'RAM' => '8GB', 'Rear camera' => '200MP + 8MP + 2MP', 'Battery' => '5110mAh, 45W turbo charging', 'Protection' => 'IP68'],
                'attrs' => ['ram' => '8GB', 'storage' => '256GB', 'color' => 'Purple'],
                'mpn' => '24115RA8EG', 'weight' => 0.19, 'dim' => '162.3 x 74.4 x 8.2 mm'],
            ['name' => 'Xiaomi Redmi 14C', 'brand' => 'xiaomi', 'price' => 14999,
                'hl' => 'A big 6.88-inch 120Hz screen and a 50MP camera for everyday use.',
                'specs' => ['Display' => '6.88" HD+ LCD, 120Hz', 'Processor' => 'MediaTek Helio G81-Ultra', 'RAM' => '6GB', 'Rear camera' => '50MP', 'Battery' => '5160mAh, 18W charging'],
                'attrs' => ['ram' => '6GB', 'storage' => '128GB', 'color' => 'Blue'],
                'mpn' => '2409BRN2CG', 'weight' => 0.207, 'dim' => '171.9 x 77.8 x 8.2 mm'],
            ['name' => 'realme C75', 'brand' => 'realme', 'price' => 18999,
                'hl' => 'A tough budget phone with a 6000mAh battery, 45W charging and IP69 protection.',
                'specs' => ['Display' => '6.72" FHD+ IPS LCD, 90Hz', 'Processor' => 'MediaTek Helio G92 Max', 'RAM' => '8GB', 'Rear camera' => '50MP', 'Battery' => '6000mAh, 45W fast charging', 'Protection' => 'IP69'],
                'attrs' => ['ram' => '8GB', 'storage' => '128GB', 'color' => 'Gold'],
                'mpn' => 'RMX3941', 'weight' => 0.196, 'dim' => '165.7 x 76.2 x 7.99 mm'],
        ],

        'tablet' => [
            ['name' => 'Apple iPad 11" (A16)', 'brand' => 'apple', 'price' => 52999, 'f' => true,
                'hl' => 'The everyday iPad, now with the A16 chip and 128GB of storage to start.',
                'specs' => ['Display' => '11" Liquid Retina', 'Chip' => 'A16', 'Storage' => '128GB', 'Cameras' => '12MP front (landscape) and back', 'Connector' => 'USB-C'],
                'attrs' => ['screen-size' => '11"', 'storage' => '128GB', 'color' => 'Blue'], 'variants' => ['Wi-Fi, 128GB' => 52999, 'Wi-Fi, 256GB' => 64999],
                'mpn' => 'MD3Y4', 'weight' => 0.477, 'dim' => '248.6 x 179.5 x 7 mm'],
            ['name' => 'Apple iPad Air 11" M3', 'brand' => 'apple', 'price' => 84999,
                'hl' => 'The M3 chip in a thin, light iPad Air that works with Apple Pencil Pro.',
                'specs' => ['Display' => '11" Liquid Retina', 'Chip' => 'Apple M3', 'Storage' => '128GB', 'Accessories' => 'Apple Pencil Pro and Magic Keyboard support', 'Connector' => 'USB-C'],
                'attrs' => ['screen-size' => '11"', 'storage' => '128GB', 'color' => 'Purple'],
                'mpn' => 'MC9W4', 'weight' => 0.46, 'dim' => '247.6 x 178.5 x 6.1 mm'],
            ['name' => 'Samsung Galaxy Tab S10 FE', 'brand' => 'samsung', 'price' => 57999, 'was' => 62999,
                'hl' => 'An IP68 tablet with the S Pen in the box, for notes, drawing and streaming.',
                'specs' => ['Display' => '10.9" LCD, 90Hz', 'Processor' => 'Exynos 1580', 'RAM' => '8GB', 'Battery' => '8000mAh, 45W fast charging', 'Extras' => 'S Pen included, IP68'],
                'attrs' => ['screen-size' => '11"', 'storage' => '128GB', 'color' => 'Gray'],
                'mpn' => 'SM-X520', 'weight' => 0.497, 'dim' => '254.3 x 165.8 x 6 mm'],
            ['name' => 'Xiaomi Pad 7', 'brand' => 'xiaomi', 'price' => 45999,
                'hl' => 'A sharp 3.2K 144Hz display and a Snapdragon 7+ Gen 3 in a slim metal tablet.',
                'specs' => ['Display' => '11.2" 3.2K LCD, 144Hz', 'Processor' => 'Snapdragon 7+ Gen 3', 'RAM' => '8GB', 'Battery' => '8850mAh, 45W fast charging', 'Audio' => 'Quad speakers, Dolby Atmos'],
                'attrs' => ['screen-size' => '11"', 'storage' => '256GB', 'color' => 'Gray'],
                'mpn' => '2410CRP4CG', 'weight' => 0.5, 'dim' => '251.2 x 173.4 x 6.2 mm'],
            ['name' => 'Samsung Galaxy Tab A9+', 'brand' => 'samsung', 'price' => 24999,
                'hl' => 'A family tablet with an 11-inch 90Hz screen and quad speakers.',
                'specs' => ['Display' => '11" TFT LCD, 90Hz', 'Processor' => 'Snapdragon 695', 'RAM' => '4GB', 'Storage' => '64GB, microSD up to 1TB', 'Audio' => 'Quad speakers, Dolby Atmos'],
                'attrs' => ['screen-size' => '11"', 'ram' => '4GB', 'color' => 'Gray'],
                'mpn' => 'SM-X210', 'weight' => 0.48, 'dim' => '257.1 x 168.7 x 6.9 mm'],
        ],

        'processor' => [
            ['name' => 'AMD Ryzen 5 7600 Processor', 'brand' => 'amd', 'price' => 21500, 'was' => 23000, 'f' => true,
                'hl' => 'Six Zen 4 cores on the AM5 platform, with a Wraith Stealth cooler in the box.',
                'specs' => ['Cores / threads' => '6 / 12', 'Max boost' => '5.1GHz', 'Cache' => '38MB (L2 + L3)', 'Socket' => 'AM5', 'Graphics' => 'AMD Radeon Graphics', 'Cooler' => 'AMD Wraith Stealth included'],
                'attrs' => [], 'mpn' => '100-100001015BOX', 'weight' => 0.35, 'dim' => '11.5 x 11.5 x 7 cm'],
            ['name' => 'AMD Ryzen 7 7800X3D Processor', 'brand' => 'amd', 'price' => 47500, 'f' => true,
                'hl' => 'One of the fastest gaming processors available, with AMD 3D V-Cache.',
                'specs' => ['Cores / threads' => '8 / 16', 'Max boost' => '5.0GHz', 'Cache' => '104MB (L2 + L3) with 3D V-Cache', 'Socket' => 'AM5', 'TDP' => '120W', 'Cooler' => 'Not included'],
                'attrs' => [], 'mpn' => '100-100000910WOF', 'weight' => 0.1],
            ['name' => 'AMD Ryzen 7 9700X Processor', 'brand' => 'amd', 'price' => 38500, 'status' => 'preorder',
                'hl' => 'Eight Zen 5 cores at a 65W TDP for fast, cool-running builds.',
                'specs' => ['Cores / threads' => '8 / 16', 'Max boost' => '5.5GHz', 'Cache' => '40MB (L2 + L3)', 'Socket' => 'AM5', 'TDP' => '65W', 'Cooler' => 'Not included'],
                'attrs' => [], 'mpn' => '100-100001404WOF', 'weight' => 0.1],
            ['name' => 'Intel Core i5-14400F Processor', 'brand' => 'intel', 'price' => 19500, 'was' => 21000,
                'hl' => 'Ten cores for gaming and multitasking; pair it with a graphics card, as it has no integrated graphics.',
                'specs' => ['Cores / threads' => '10 (6P + 4E) / 16', 'Max boost' => '4.7GHz', 'Cache' => '20MB Intel Smart Cache', 'Socket' => 'LGA 1700', 'Graphics' => 'None (needs a graphics card)', 'Cooler' => 'Intel Laminar RM1 included'],
                'attrs' => [], 'mpn' => 'BX8071514400F', 'weight' => 0.35, 'dim' => '12 x 10 x 7 cm'],
            ['name' => 'Intel Core i7-14700K Processor', 'brand' => 'intel', 'price' => 43500,
                'hl' => '20 cores and boost clocks up to 5.6GHz for gaming, streaming and creative work.',
                'specs' => ['Cores / threads' => '20 (8P + 12E) / 28', 'Max boost' => '5.6GHz', 'Cache' => '33MB Intel Smart Cache', 'Socket' => 'LGA 1700', 'Graphics' => 'Intel UHD Graphics 770', 'Cooler' => 'Not included'],
                'attrs' => [], 'mpn' => 'BX8071514700K', 'weight' => 0.1],
            ['name' => 'Intel Core Ultra 5 245K Processor', 'brand' => 'intel', 'price' => 32000,
                'hl' => "Intel's newer desktop chip for LGA 1851 boards, with an NPU for AI features.",
                'specs' => ['Cores / threads' => '14 (6P + 8E) / 14', 'Max boost' => '5.2GHz', 'Cache' => '24MB Intel Smart Cache', 'Socket' => 'LGA 1851', 'Graphics' => 'Intel Graphics', 'Cooler' => 'Not included'],
                'attrs' => [], 'mpn' => 'BX80768245K', 'weight' => 0.1],
        ],

        'motherboard' => [
            ['name' => 'MSI MAG B650 TOMAHAWK WIFI Motherboard', 'brand' => 'msi', 'price' => 29500, 'f' => true,
                'hl' => 'A full-size AM5 board with strong power delivery for Ryzen 7 and Ryzen 9.',
                'specs' => ['Socket' => 'AM5', 'Chipset' => 'AMD B650', 'Form factor' => 'ATX', 'Memory' => '4x DDR5', 'Storage' => '3x M.2 NVMe, 6x SATA', 'Networking' => 'Wi-Fi 6E, 2.5G LAN'],
                'attrs' => [], 'mpn' => 'MAG B650 TOMAHAWK WIFI', 'weight' => 1.4, 'dim' => '30.5 x 24.4 cm'],
            ['name' => 'Gigabyte B650M GAMING X AX Motherboard', 'brand' => 'gigabyte', 'price' => 19500,
                'hl' => 'A micro-ATX AM5 board for Ryzen 7000 and 9000, with Wi-Fi 6E and 2.5G LAN.',
                'specs' => ['Socket' => 'AM5', 'Chipset' => 'AMD B650', 'Form factor' => 'Micro-ATX', 'Memory' => '4x DDR5', 'Storage' => '2x M.2 NVMe, 4x SATA', 'Networking' => 'Wi-Fi 6E, 2.5G LAN'],
                'attrs' => [], 'mpn' => 'B650M GAMING X AX', 'weight' => 1.1, 'dim' => '24.4 x 24.4 cm'],
            ['name' => 'ASUS TUF GAMING B760M-PLUS WIFI Motherboard', 'brand' => 'asus', 'price' => 21500, 'was' => 23000,
                'hl' => 'A durable micro-ATX board for Intel 12th to 14th Gen, with Wi-Fi 6 and DDR5.',
                'specs' => ['Socket' => 'LGA 1700', 'Chipset' => 'Intel B760', 'Form factor' => 'Micro-ATX', 'Memory' => '4x DDR5', 'Storage' => '2x M.2 NVMe, 4x SATA', 'Networking' => 'Wi-Fi 6, 2.5G LAN'],
                'attrs' => [], 'mpn' => 'TUF GAMING B760M-PLUS WIFI', 'weight' => 1.1, 'dim' => '24.4 x 24.4 cm'],
            ['name' => 'MSI PRO B760M-E DDR4 Motherboard', 'brand' => 'msi', 'price' => 12500,
                'hl' => 'An affordable micro-ATX board for 12th to 14th Gen Intel processors and DDR4 memory.',
                'specs' => ['Socket' => 'LGA 1700', 'Chipset' => 'Intel B760', 'Form factor' => 'Micro-ATX', 'Memory' => '2x DDR4, up to 64GB', 'Storage' => '1x M.2 NVMe, 4x SATA', 'Networking' => '1G LAN'],
                'attrs' => [], 'mpn' => 'PRO B760M-E DDR4', 'weight' => 0.8, 'dim' => '24.4 x 21.5 cm'],
            ['name' => 'ASUS PRIME H610M-E D4 Motherboard', 'brand' => 'asus', 'price' => 9800,
                'hl' => "A budget micro-ATX board for office and home builds on Intel's LGA 1700.",
                'specs' => ['Socket' => 'LGA 1700', 'Chipset' => 'Intel H610', 'Form factor' => 'Micro-ATX', 'Memory' => '2x DDR4', 'Storage' => '1x M.2 NVMe, 4x SATA', 'Networking' => '1G LAN'],
                'attrs' => [], 'mpn' => 'PRIME H610M-E D4', 'weight' => 0.8, 'dim' => '23.4 x 20 cm'],
        ],

        'graphics-card' => [
            ['name' => 'Gigabyte GeForce RTX 5070 Ti GAMING OC 16G', 'brand' => 'gigabyte', 'price' => 125000, 'was' => 132000, 'f' => true,
                'hl' => 'A triple-fan RTX 5070 Ti with 16GB of GDDR7 for high-refresh 1440p and 4K gaming.',
                'specs' => ['GPU' => 'NVIDIA GeForce RTX 5070 Ti', 'Memory' => '16GB GDDR7', 'Cooling' => 'WINDFORCE triple fan', 'Outputs' => '3x DisplayPort, HDMI', 'Power' => '1x 16-pin, 750W PSU recommended'],
                'attrs' => [], 'mpn' => 'GV-N507TGAMING OC-16GD', 'weight' => 1.6, 'dim' => '34 x 13.5 x 5.5 cm'],
            ['name' => 'MSI GeForce RTX 5070 12G VENTUS 2X OC', 'brand' => 'msi', 'price' => 78000, 'f' => true,
                'hl' => 'RTX 5070 performance with DLSS 4 and 12GB of GDDR7 for 1440p gaming.',
                'specs' => ['GPU' => 'NVIDIA GeForce RTX 5070', 'Memory' => '12GB GDDR7', 'Cooling' => 'Dual fan', 'Outputs' => '3x DisplayPort, HDMI', 'Power' => '1x 16-pin, 650W PSU recommended'],
                'attrs' => [], 'mpn' => 'RTX 5070 12G VENTUS 2X OC', 'weight' => 1.0, 'dim' => '24.2 x 12.4 x 5 cm'],
            ['name' => 'ZOTAC GAMING GeForce RTX 5060 Twin Edge', 'brand' => 'zotac', 'price' => 42500, 'was' => 45000,
                'hl' => 'A compact RTX 50 series card with DLSS 4 for smooth 1080p gaming.',
                'specs' => ['GPU' => 'NVIDIA GeForce RTX 5060', 'Memory' => '8GB GDDR7', 'Cooling' => 'Dual fan, 2 slots', 'Outputs' => '3x DisplayPort, HDMI', 'Power' => '1x 8-pin, 550W PSU recommended'],
                'attrs' => [], 'mpn' => 'ZT-B50600E-10M', 'weight' => 0.7, 'dim' => '22.1 x 12 x 4 cm'],
            ['name' => 'ASUS TUF Gaming GeForce RTX 5080 16GB', 'brand' => 'asus', 'price' => 175000, 'status' => 'preorder',
                'hl' => "Top-tier RTX 5080 performance in ASUS's durable TUF design.",
                'specs' => ['GPU' => 'NVIDIA GeForce RTX 5080', 'Memory' => '16GB GDDR7', 'Cooling' => 'Triple axial-tech fans', 'Outputs' => '3x DisplayPort, 2x HDMI', 'Power' => '1x 16-pin, 850W PSU recommended'],
                'attrs' => [], 'mpn' => 'TUF-RTX5080-16G-GAMING', 'weight' => 1.9, 'dim' => '34.8 x 14.6 x 6.5 cm'],
            ['name' => 'Gigabyte Radeon RX 9060 XT GAMING OC 16G', 'brand' => 'gigabyte', 'price' => 52000,
                'hl' => "AMD's RDNA 4 card with 16GB of memory and FSR 4 upscaling.",
                'specs' => ['GPU' => 'AMD Radeon RX 9060 XT', 'Memory' => '16GB GDDR6', 'Cooling' => 'WINDFORCE triple fan', 'Outputs' => '2x DisplayPort, 2x HDMI', 'Power' => '1x 8-pin'],
                'attrs' => [], 'mpn' => 'GV-R9060XTGAMING OC-16GD', 'weight' => 1.0, 'dim' => '28.1 x 11.7 x 4.1 cm'],
            ['name' => 'ZOTAC GAMING GeForce RTX 4060 8GB Twin Edge', 'brand' => 'zotac', 'price' => 36500, 'was' => 39500,
                'hl' => 'Previous-generation RTX 4060 value for 1080p gaming with DLSS 3.',
                'specs' => ['GPU' => 'NVIDIA GeForce RTX 4060', 'Memory' => '8GB GDDR6', 'Cooling' => 'Dual fan, 2 slots', 'Outputs' => '3x DisplayPort, HDMI', 'Power' => '1x 8-pin, 500W PSU recommended'],
                'attrs' => [], 'mpn' => 'ZT-D40600E-10M', 'weight' => 0.7, 'dim' => '22.1 x 12 x 4 cm'],
        ],

        'ram' => [
            ['name' => 'Corsair Vengeance RGB 32GB (2 x 16GB) DDR5 6000MHz', 'brand' => 'corsair', 'price' => 13500, 'f' => true,
                'hl' => 'A 32GB DDR5 kit with RGB lighting, tuned for new AMD and Intel builds.',
                'specs' => ['Capacity' => '32GB (2 x 16GB)', 'Type' => 'DDR5', 'Speed' => '6000MHz', 'Latency' => 'CL36', 'Lighting' => 'RGB, iCUE compatible'],
                'attrs' => ['ram' => '32GB'], 'mpn' => 'CMH32GX5M2E6000C36', 'weight' => 0.1],
            ['name' => 'Kingston FURY Beast 16GB DDR5 5600MHz', 'brand' => 'kingston', 'price' => 6200, 'was' => 6800,
                'hl' => 'Fast DDR5 memory with Intel XMP 3.0 and AMD EXPO profiles.',
                'specs' => ['Capacity' => '16GB (1 x 16GB)', 'Type' => 'DDR5', 'Speed' => '5600MHz', 'Latency' => 'CL36', 'Profiles' => 'Intel XMP 3.0, AMD EXPO'],
                'attrs' => ['ram' => '16GB'], 'mpn' => 'KF556C36BBE-16', 'weight' => 0.05],
            ['name' => 'ADATA XPG Lancer RGB 16GB DDR5 6000MHz', 'brand' => 'adata', 'price' => 6800,
                'hl' => 'DDR5-6000 memory with RGB lighting and an aluminium heat spreader.',
                'specs' => ['Capacity' => '16GB (1 x 16GB)', 'Type' => 'DDR5', 'Speed' => '6000MHz', 'Latency' => 'CL30', 'Lighting' => 'RGB'],
                'attrs' => ['ram' => '16GB'], 'mpn' => 'AX5U6000C3016G-CLARBK', 'weight' => 0.05],
            ['name' => 'Corsair Vengeance LPX 16GB DDR4 3200MHz', 'brand' => 'corsair', 'price' => 4200,
                'hl' => 'Low-profile DDR4 memory that fits under large CPU coolers.',
                'specs' => ['Capacity' => '16GB (1 x 16GB)', 'Type' => 'DDR4', 'Speed' => '3200MHz', 'Latency' => 'CL16', 'Voltage' => '1.35V'],
                'attrs' => ['ram' => '16GB'], 'mpn' => 'CMK16GX4M1E3200C16', 'weight' => 0.05],
            ['name' => 'Kingston FURY Beast 8GB DDR4 3200MHz', 'brand' => 'kingston', 'price' => 2300,
                'hl' => 'An easy 8GB upgrade for DDR4 desktops.',
                'specs' => ['Capacity' => '8GB (1 x 8GB)', 'Type' => 'DDR4', 'Speed' => '3200MHz', 'Latency' => 'CL16', 'Profile' => 'Intel XMP'],
                'attrs' => ['ram' => '8GB'], 'mpn' => 'KF432C16BB/8', 'weight' => 0.04],
        ],

        'ssd' => [
            ['name' => 'Samsung 990 PRO 1TB NVMe SSD', 'brand' => 'samsung', 'price' => 12500, 'was' => 13900, 'f' => true,
                'hl' => 'One of the fastest PCIe 4.0 SSDs, with reads up to 7,450MB/s.',
                'specs' => ['Capacity' => '1TB', 'Interface' => 'PCIe 4.0 x4, NVMe 2.0', 'Read' => '7,450MB/s', 'Write' => '6,900MB/s', 'Form factor' => 'M.2 2280'],
                'attrs' => ['storage' => '1TB'], 'mpn' => 'MZ-V9P1T0BW', 'weight' => 0.03, 'warranty' => '5 Years Warranty'],
            ['name' => 'WD_BLACK SN850X 1TB NVMe SSD', 'brand' => 'western-digital', 'price' => 11800,
                'hl' => 'A gaming SSD with reads up to 7,300MB/s and a Game Mode for faster loads.',
                'specs' => ['Capacity' => '1TB', 'Interface' => 'PCIe 4.0 x4, NVMe', 'Read' => '7,300MB/s', 'Write' => '6,300MB/s', 'Form factor' => 'M.2 2280'],
                'attrs' => ['storage' => '1TB'], 'mpn' => 'WDS100T2X0E', 'weight' => 0.03, 'warranty' => '5 Years Warranty'],
            ['name' => 'Kingston NV3 1TB NVMe SSD', 'brand' => 'kingston', 'price' => 7200,
                'hl' => 'Good-value PCIe 4.0 storage with reads up to 6,000MB/s.',
                'specs' => ['Capacity' => '1TB', 'Interface' => 'PCIe 4.0 x4, NVMe', 'Read' => '6,000MB/s', 'Write' => '4,000MB/s', 'Form factor' => 'M.2 2280'],
                'attrs' => ['storage' => '1TB'], 'mpn' => 'SNV3S/1000G', 'weight' => 0.03],
            ['name' => 'WD Blue SN580 500GB NVMe SSD', 'brand' => 'western-digital', 'price' => 5200, 'was' => 5800,
                'hl' => 'An affordable PCIe 4.0 NVMe drive for fast boot and load times.',
                'specs' => ['Capacity' => '500GB', 'Interface' => 'PCIe 4.0 x4, NVMe', 'Read' => '4,000MB/s', 'Write' => '3,600MB/s', 'Form factor' => 'M.2 2280'],
                'attrs' => ['storage' => '500GB'], 'mpn' => 'WDS500G3B0E', 'weight' => 0.03, 'warranty' => '5 Years Warranty'],
            ['name' => 'Samsung 870 EVO 500GB SATA SSD', 'brand' => 'samsung', 'price' => 6200,
                'hl' => 'A reliable 2.5-inch SATA SSD that brings new life to older laptops and PCs.',
                'specs' => ['Capacity' => '500GB', 'Interface' => 'SATA 6Gb/s', 'Read' => '560MB/s', 'Write' => '530MB/s', 'Form factor' => '2.5 inch'],
                'attrs' => ['storage' => '500GB'], 'mpn' => 'MZ-77E500BW', 'weight' => 0.05, 'warranty' => '5 Years Warranty'],
            ['name' => 'Kingston A400 480GB SATA SSD', 'brand' => 'kingston', 'price' => 3300,
                'hl' => "An entry-level SATA SSD that's far quicker than a hard drive.",
                'specs' => ['Capacity' => '480GB', 'Interface' => 'SATA 6Gb/s', 'Read' => '500MB/s', 'Write' => '450MB/s', 'Form factor' => '2.5 inch'],
                'attrs' => ['storage' => '480GB'], 'mpn' => 'SA400S37/480G', 'weight' => 0.04],
        ],

        'router' => [
            ['name' => 'TP-Link Archer AX23 AX1800 Wi-Fi 6 Router', 'brand' => 'tp-link', 'price' => 5600, 'was' => 6200, 'f' => true,
                'hl' => 'Affordable Wi-Fi 6, with faster speeds and better handling of many devices at once.',
                'specs' => ['Wi-Fi' => 'Wi-Fi 6 (802.11ax), AX1800 dual band', 'Ports' => '4x Gigabit LAN, 1x Gigabit WAN', 'Antennas' => '4 external', 'Features' => 'OFDMA, MU-MIMO, EasyMesh'],
                'attrs' => ['color' => 'Black'], 'mpn' => 'Archer AX23', 'weight' => 0.4],
            ['name' => 'TP-Link Deco X20 Mesh Wi-Fi 6 (2-Pack)', 'brand' => 'tp-link', 'price' => 14500,
                'hl' => 'Whole-home Wi-Fi 6 mesh: two units that work as one seamless network.',
                'specs' => ['Wi-Fi' => 'Wi-Fi 6 (802.11ax), AX1800 dual band', 'Units' => '2', 'Ports' => '2x Gigabit per unit', 'Features' => 'Seamless roaming, set up in the Deco app'],
                'attrs' => ['color' => 'White'], 'mpn' => 'Deco X20 (2-pack)', 'weight' => 0.8],
            ['name' => 'Xiaomi Router AX3000T', 'brand' => 'xiaomi', 'price' => 4500,
                'hl' => 'Wi-Fi 6 AX3000 speeds with 160MHz channels, at a small price.',
                'specs' => ['Wi-Fi' => 'Wi-Fi 6, AX3000 dual band', 'Ports' => '3x Gigabit LAN, 1x Gigabit WAN', 'Antennas' => '4 external', 'Features' => 'Mesh networking, NFC connect'],
                'attrs' => ['color' => 'White'], 'mpn' => 'RD03', 'weight' => 0.35],
            ['name' => 'TP-Link Archer C6 AC1200 Router', 'brand' => 'tp-link', 'price' => 3300,
                'hl' => 'A dual-band Wi-Fi 5 router with MU-MIMO for busy homes.',
                'specs' => ['Wi-Fi' => 'Wi-Fi 5 (802.11ac), AC1200 dual band', 'Ports' => '4x Gigabit LAN, 1x Gigabit WAN', 'Antennas' => '4 external + 1 internal', 'Features' => 'MU-MIMO, beamforming'],
                'attrs' => ['color' => 'White'], 'mpn' => 'Archer C6', 'weight' => 0.4],
            ['name' => 'Tenda AC10 AC1200 Router', 'brand' => 'tenda', 'price' => 2900,
                'hl' => 'A budget dual-band gigabit router with four antennas.',
                'specs' => ['Wi-Fi' => 'Wi-Fi 5, AC1200 dual band', 'Ports' => '3x Gigabit LAN, 1x Gigabit WAN', 'Antennas' => '4 external', 'Features' => 'MU-MIMO, beamforming'],
                'attrs' => ['color' => 'Black'], 'mpn' => 'AC10', 'weight' => 0.35],
        ],

        'printer' => [
            ['name' => 'Epson EcoTank L3250 Wi-Fi Ink Tank Printer', 'brand' => 'epson', 'price' => 17500, 'f' => true,
                'hl' => 'An all-in-one ink tank printer with Wi-Fi and a very low cost per page.',
                'specs' => ['Functions' => 'Print, scan, copy', 'Printing' => 'Colour ink tank', 'Connectivity' => 'Wi-Fi, Wi-Fi Direct, USB', 'Ink in the box' => 'Up to 4,500 black / 7,500 colour pages'],
                'attrs' => ['color' => 'Black'], 'mpn' => 'C11CJ67503', 'weight' => 3.9, 'dim' => '37.5 x 34.7 x 17.9 cm'],
            ['name' => 'Canon PIXMA G3010 Ink Tank Printer', 'brand' => 'canon', 'price' => 16900, 'was' => 18200,
                'hl' => 'Refillable ink tanks for high-volume colour printing, with wireless printing from your phone.',
                'specs' => ['Functions' => 'Print, scan, copy', 'Printing' => 'Colour ink tank', 'Connectivity' => 'Wi-Fi, USB', 'Ink in the box' => 'Up to 6,000 black / 7,000 colour pages'],
                'attrs' => ['color' => 'Black'], 'mpn' => 'G3010', 'weight' => 6.3, 'dim' => '44.5 x 33 x 16.3 cm'],
            ['name' => 'HP LaserJet M111w Wireless Printer', 'brand' => 'hp', 'price' => 14500,
                'hl' => 'A compact wireless mono laser printer for home offices.',
                'specs' => ['Functions' => 'Print', 'Printing' => 'Mono laser, up to 20ppm', 'Connectivity' => 'Wi-Fi, USB', 'Setup' => 'HP Smart app'],
                'attrs' => ['color' => 'White'], 'mpn' => '7MD68A', 'weight' => 3.7, 'dim' => '34.6 x 18.9 x 15.9 cm'],
            ['name' => 'Canon imageCLASS LBP6030 Laser Printer', 'brand' => 'canon', 'price' => 12800,
                'hl' => 'A small, quiet mono laser printer for everyday documents.',
                'specs' => ['Functions' => 'Print', 'Printing' => 'Mono laser, up to 18ppm', 'Connectivity' => 'USB', 'Toner' => 'Cartridge 325'],
                'attrs' => ['color' => 'Black'], 'mpn' => 'LBP6030', 'weight' => 5.0, 'dim' => '36.4 x 24.9 x 19.9 cm'],
        ],

        'headphones' => [
            ['name' => 'Sony WH-1000XM5 Noise Cancelling Headphones', 'brand' => 'sony', 'price' => 42999, 'was' => 47999, 'f' => true,
                'hl' => 'Industry-leading noise cancellation with up to 30 hours of battery life.',
                'specs' => ['Type' => 'Over-ear, wireless', 'Noise cancelling' => 'Dual processors, 8 microphones', 'Battery' => 'Up to 30 hours with ANC on', 'Charging' => 'USB-C, 3 min charge = 3 hours playback', 'Connectivity' => 'Bluetooth 5.2, multipoint'],
                'attrs' => ['color' => 'Black'], 'variants' => ['Black', 'Midnight Blue'],
                'mpn' => 'WH1000XM5', 'weight' => 0.25, 'dim' => '27 x 21 x 9 cm (case)'],
            ['name' => 'Apple AirPods Pro (2nd Generation, USB-C)', 'brand' => 'apple', 'price' => 32999,
                'hl' => 'Active noise cancellation, Adaptive Audio and personalised spatial audio in a USB-C case.',
                'specs' => ['Type' => 'In-ear, true wireless', 'Chip' => 'Apple H2', 'Noise cancelling' => 'Active Noise Cancellation, Transparency mode', 'Battery' => 'Up to 6 hours (30 hours with case)', 'Case' => 'USB-C MagSafe case, IP54'],
                'attrs' => ['color' => 'White'], 'mpn' => 'MTJV3', 'weight' => 0.051, 'dim' => '4.5 x 6.1 x 2.1 cm (case)', 'warranty' => '1 Year Official Warranty'],
            ['name' => 'Samsung Galaxy Buds3 Pro', 'brand' => 'samsung', 'price' => 23999, 'was' => 26999,
                'hl' => 'Earbuds with adaptive noise cancelling, 24-bit Hi-Fi sound and Blade Lights.',
                'specs' => ['Type' => 'In-ear, true wireless', 'Noise cancelling' => 'Adaptive ANC', 'Battery' => 'Up to 6 hours (26 hours with case)', 'Water resistance' => 'IP57', 'Connectivity' => 'Bluetooth 5.4'],
                'attrs' => ['color' => 'Silver'], 'mpn' => 'SM-R630', 'weight' => 0.05, 'warranty' => '1 Year Official Warranty'],
            ['name' => 'Edifier W820NB Plus ANC Headphones', 'brand' => 'edifier', 'price' => 6900,
                'hl' => 'Hybrid noise cancelling headphones with Hi-Res wireless audio and up to 49 hours of battery.',
                'specs' => ['Type' => 'Over-ear, wireless', 'Noise cancelling' => 'Hybrid ANC', 'Battery' => 'Up to 49 hours', 'Codec' => 'LDAC (Hi-Res Audio Wireless)', 'Connectivity' => 'Bluetooth 5.2'],
                'attrs' => ['color' => 'Black'], 'mpn' => 'W820NB Plus', 'weight' => 0.26],
            ['name' => 'Logitech G435 LIGHTSPEED Wireless Gaming Headset', 'brand' => 'logitech', 'price' => 7500,
                'hl' => 'An ultra-light wireless gaming headset with LIGHTSPEED and Bluetooth.',
                'specs' => ['Type' => 'Over-ear gaming headset', 'Connectivity' => 'LIGHTSPEED wireless, Bluetooth', 'Battery' => 'Up to 18 hours', 'Weight' => '165 g'],
                'attrs' => ['color' => 'Black'], 'mpn' => '981-001049', 'weight' => 0.17],
            ['name' => 'JBL Wave Beam True Wireless Earbuds', 'brand' => 'jbl', 'price' => 5900, 'was' => 6500,
                'hl' => 'Compact earbuds with JBL Deep Bass sound and a water- and dust-resistant design.',
                'specs' => ['Type' => 'In-ear, true wireless', 'Battery' => 'Up to 32 hours with case', 'Water resistance' => 'IP54 (earbuds)', 'Microphones' => '4 mics for calls'],
                'attrs' => ['color' => 'Black'], 'mpn' => 'JBLWBEAMBLK', 'weight' => 0.05],
            ['name' => 'JBL Tune 520BT Wireless Headphones', 'brand' => 'jbl', 'price' => 5200,
                'hl' => 'Lightweight on-ear headphones with JBL Pure Bass sound and up to 57 hours of battery.',
                'specs' => ['Type' => 'On-ear, wireless', 'Battery' => 'Up to 57 hours', 'Charging' => 'USB-C, 5 min charge = 3 hours playback', 'Connectivity' => 'Bluetooth 5.3, multipoint'],
                'attrs' => ['color' => 'Black'], 'variants' => ['Black', 'Blue', 'White'],
                'mpn' => 'JBLT520BTBLK', 'weight' => 0.16],
        ],

        'keyboard-mouse' => [
            ['name' => 'Logitech MX Master 3S Wireless Mouse', 'brand' => 'logitech', 'price' => 11500, 'f' => true,
                'hl' => 'A precise, quiet wireless mouse with an 8K DPI sensor and MagSpeed scrolling.',
                'specs' => ['Sensor' => '8,000 DPI, tracks on glass', 'Buttons' => '7, programmable', 'Connectivity' => 'Bluetooth, Logi Bolt receiver', 'Battery' => 'Up to 70 days, USB-C charging'],
                'attrs' => ['color' => 'Gray'], 'mpn' => '910-006561', 'weight' => 0.14],
            ['name' => 'Logitech G502 X Gaming Mouse', 'brand' => 'logitech', 'price' => 7200,
                'hl' => 'The G502 reimagined, with LIGHTFORCE hybrid switches and the HERO 25K sensor.',
                'specs' => ['Sensor' => 'HERO 25K, 100 to 25,600 DPI', 'Switches' => 'LIGHTFORCE hybrid optical-mechanical', 'Buttons' => '13 programmable', 'Connection' => 'Wired USB'],
                'attrs' => ['color' => 'Black'], 'mpn' => '910-006140', 'weight' => 0.09],
            ['name' => 'Logitech K380 Multi-Device Bluetooth Keyboard', 'brand' => 'logitech', 'price' => 3900,
                'hl' => 'A compact Bluetooth keyboard that switches between three devices at a tap.',
                'specs' => ['Layout' => 'Compact, 79 keys', 'Connectivity' => 'Bluetooth, 3 devices', 'Battery' => 'Up to 24 months (2x AAA)', 'Works with' => 'Windows, macOS, iPadOS, Android'],
                'attrs' => ['color' => 'Gray'], 'mpn' => '920-007596', 'weight' => 0.42],
            ['name' => 'Logitech MK270 Wireless Keyboard and Mouse Combo', 'brand' => 'logitech', 'price' => 2900, 'was' => 3200,
                'hl' => 'A reliable wireless keyboard and mouse set that shares one tiny USB receiver.',
                'specs' => ['Includes' => 'Full-size keyboard and mouse', 'Connectivity' => '2.4GHz USB receiver', 'Battery' => 'Keyboard up to 24 months, mouse up to 12 months', 'Keys' => '8 hotkeys'],
                'attrs' => ['color' => 'Black'], 'mpn' => '920-004536', 'weight' => 0.6],
            ['name' => 'A4Tech FStyler FG1010 Wireless Combo', 'brand' => 'a4tech', 'price' => 2600,
                'hl' => 'A wireless keyboard and mouse combo with a comfortable, quiet design.',
                'specs' => ['Includes' => 'Keyboard and mouse', 'Connectivity' => '2.4GHz USB receiver', 'Mouse' => '2,000 DPI', 'Keys' => '12 multimedia keys'],
                'attrs' => ['color' => 'White'], 'mpn' => 'FG1010', 'weight' => 0.65],
            ['name' => 'Logitech G213 Prodigy RGB Gaming Keyboard', 'brand' => 'logitech', 'price' => 5500, 'status' => 'out_of_stock',
                'hl' => 'A spill-resistant gaming keyboard with RGB lighting and dedicated media controls.',
                'specs' => ['Switches' => 'Mech-Dome', 'Lighting' => 'LIGHTSYNC RGB, 5 zones', 'Durability' => 'Spill-resistant', 'Connection' => 'Wired USB'],
                'attrs' => ['color' => 'Black'], 'mpn' => '920-008093', 'weight' => 1.0],
        ],

        'television' => [
            ['name' => 'Samsung 55" Crystal UHD 4K Smart TV', 'brand' => 'samsung', 'price' => 79999, 'was' => 89999, 'f' => true,
                'hl' => 'A crystal-clear 4K picture with Tizen smart apps and a slim design.',
                'specs' => ['Screen' => '55" 4K UHD (3840 x 2160)', 'Processor' => 'Crystal Processor 4K', 'HDR' => 'HDR10+', 'Smart OS' => 'Tizen', 'Connectivity' => '3x HDMI, 2x USB, Wi-Fi, Bluetooth'],
                'attrs' => ['screen-size' => '55"', 'color' => 'Black'],
                'mpn' => 'UA55DU7700', 'weight' => 15.4, 'dim' => '123 x 70.6 x 6 cm'],
            ['name' => 'Sony BRAVIA 3 65" 4K Google TV', 'brand' => 'sony', 'price' => 159999,
                'hl' => 'Big-screen 4K with Google TV, the 4K HDR Processor X1 and Dolby Vision.',
                'specs' => ['Screen' => '65" 4K UHD LED', 'Processor' => '4K HDR Processor X1', 'Smart OS' => 'Google TV', 'HDR' => 'Dolby Vision, HDR10', 'Audio' => 'Dolby Atmos'],
                'attrs' => ['screen-size' => '65"', 'color' => 'Black'],
                'mpn' => 'K-65S30', 'weight' => 17.9, 'dim' => '145.5 x 83.9 x 7.8 cm'],
            ['name' => 'LG 43" UHD 4K Smart TV UR7550', 'brand' => 'lg', 'price' => 49999,
                'hl' => 'An affordable 4K LG TV with webOS, ThinQ AI and Magic Remote support.',
                'specs' => ['Screen' => '43" 4K UHD', 'Processor' => 'α5 AI Processor 4K Gen6', 'Smart OS' => 'webOS 23', 'HDR' => 'HDR10, HLG', 'Connectivity' => '3x HDMI, Wi-Fi, Bluetooth'],
                'attrs' => ['screen-size' => '43"', 'color' => 'Black'],
                'mpn' => '43UR7550PSC', 'weight' => 8.2, 'dim' => '97.4 x 57.3 x 8.6 cm'],
            ['name' => 'Xiaomi TV A 43" 4K Google TV', 'brand' => 'xiaomi', 'price' => 36999, 'was' => 39999,
                'hl' => 'A 43-inch 4K Google TV with a slim, nearly bezel-less design.',
                'specs' => ['Screen' => '43" 4K UHD', 'Smart OS' => 'Google TV', 'Audio' => '2x 10W, Dolby Audio', 'Connectivity' => '3x HDMI, 2x USB, Wi-Fi, Bluetooth'],
                'attrs' => ['screen-size' => '43"', 'color' => 'Black'],
                'weight' => 7.1, 'dim' => '95.9 x 56.3 x 8.5 cm'],
            ['name' => 'Walton 32" HD Smart Android TV', 'brand' => 'walton', 'price' => 21999, 'was' => 23999,
                'hl' => 'A made-in-Bangladesh smart TV with Android, built-in Wi-Fi and streaming apps.',
                'specs' => ['Screen' => '32" HD (1366 x 768)', 'Smart OS' => 'Android 11', 'Audio' => '2x 8W speakers', 'Connectivity' => '2x HDMI, 2x USB, Wi-Fi'],
                'attrs' => ['screen-size' => '32"', 'color' => 'Black'],
                'mpn' => 'W32D210EG1', 'weight' => 4.1, 'dim' => '72.5 x 43.3 x 8.2 cm', 'warranty' => '3 Years Panel Warranty'],
        ],

        'ups-power' => [
            ['name' => 'APC Back-UPS 650VA', 'brand' => 'apc', 'price' => 7800,
                'hl' => 'Keeps a desktop PC and router running through short power cuts, with voltage regulation.',
                'specs' => ['Capacity' => '650VA / 360W', 'Outlets' => '4, universal', 'Regulation' => 'Automatic Voltage Regulation (AVR)', 'Battery' => 'Sealed lead-acid, user-replaceable'],
                'attrs' => [], 'mpn' => 'BX650LI-MS', 'weight' => 5.0, 'dim' => '28.5 x 10 x 18 cm', 'warranty' => '2 Years Warranty'],
            ['name' => 'APC Back-UPS 1100VA', 'brand' => 'apc', 'price' => 13500, 'was' => 14500,
                'hl' => 'More backup capacity for a gaming PC and monitor, with voltage regulation.',
                'specs' => ['Capacity' => '1100VA / 550W', 'Outlets' => '4, universal', 'Regulation' => 'Automatic Voltage Regulation (AVR)', 'Battery' => 'Sealed lead-acid, user-replaceable'],
                'attrs' => [], 'mpn' => 'BX1100LI-MS', 'weight' => 7.4, 'dim' => '31.5 x 13 x 21.5 cm', 'warranty' => '2 Years Warranty'],
            ['name' => 'Anker PowerCore 20000mAh Power Bank', 'brand' => 'anker', 'price' => 4290, 'f' => true,
                'hl' => 'A high-capacity power bank that charges most phones four to five times.',
                'specs' => ['Capacity' => '20000 mAh', 'Output' => '20W USB-C PD + USB-A', 'Input' => 'USB-C', 'Weight' => '356 g'],
                'attrs' => ['color' => 'Black'], 'weight' => 0.36, 'warranty' => '18 Months Warranty'],
            ['name' => 'Anker 65W GaN USB-C Charger', 'brand' => 'anker', 'price' => 4990,
                'hl' => 'A compact GaN charger that powers a laptop, tablet and phone at once.',
                'specs' => ['Output' => '65W max', 'Ports' => '2x USB-C, 1x USB-A', 'Technology' => 'GaN II', 'Plug' => 'Foldable'],
                'attrs' => ['color' => 'White'], 'weight' => 0.13, 'warranty' => '18 Months Warranty'],
            ['name' => 'Xiaomi 20000mAh 22.5W Power Bank', 'brand' => 'xiaomi', 'price' => 2600,
                'hl' => 'A 20000mAh power bank with 22.5W fast charging and three ports.',
                'specs' => ['Capacity' => '20000 mAh', 'Output' => '22.5W max', 'Ports' => '2x USB-A, 1x USB-C (in and out)', 'Input' => 'USB-C, 18W'],
                'attrs' => ['color' => 'Black'], 'mpn' => 'PB2022ZM', 'weight' => 0.43],
            ['name' => 'Samsung 25W USB-C Fast Charger', 'brand' => 'samsung', 'price' => 1990,
                'hl' => 'The official Samsung Super Fast Charging adapter for Galaxy phones.',
                'specs' => ['Output' => '25W', 'Port' => 'USB-C', 'Protocol' => 'USB PD 3.0 PPS', 'Cable' => 'USB-C to USB-C included'],
                'attrs' => ['color' => 'Black'], 'mpn' => 'EP-TA800', 'weight' => 0.1],
        ],
    ];

    /** @var array{bold: ?string, regular: ?string} */
    private array $fonts = ['bold' => null, 'regular' => null];

    public function run(): void
    {
        $this->call([CategorySeeder::class, AttributeSeeder::class]);

        $this->fonts = ['bold' => $this->findFont(bold: true), 'regular' => $this->findFont(bold: false)];
        $this->retireOldDemoData();

        $brands = $this->seedBrands();
        $categories = Category::whereIn('slug', array_keys(self::CATALOG))->get()->keyBy('slug');
        $attributes = AttributeOptimized::with('values')
            ->whereIn('slug', ['screen-size', 'storage', 'ram', 'color'])
            ->get()
            ->keyBy('slug');

        $number = 0;
        foreach (self::CATALOG as $categorySlug => $items) {
            $settings = self::CATEGORIES[$categorySlug];
            $category = $categories[$categorySlug];

            foreach ($items as $position => $item) {
                $number++;
                $sku = sprintf('TIB-%s-%03d', $settings['prefix'], $position + 1);
                $this->seedProduct($item, $sku, $number, $category, $settings, $brands[$item['brand']], $attributes);
            }

            $this->command?->info(sprintf('  %-18s %d products', $category->name, count($items)));
        }

        $this->command?->info("Seeded {$number} products.");
    }

    /**
     * SKUs this catalog uses, so older demo products (same TIB- scheme) can be told apart.
     */
    private function skus(): array
    {
        $skus = [];
        foreach (self::CATALOG as $categorySlug => $items) {
            foreach (array_keys($items) as $position) {
                $skus[] = sprintf('TIB-%s-%03d', self::CATEGORIES[$categorySlug]['prefix'], $position + 1);
            }
        }

        return $skus;
    }

    /**
     * Remove demo data this catalog no longer has: products first, then the categories and brands
     * nothing else uses.
     */
    private function retireOldDemoData(): void
    {
        $retired = ProductOptimized::where('sku', 'like', 'TIB-%')
            ->whereNotIn('sku', $this->skus())
            ->with('images')
            ->get();

        foreach ($retired as $product) {
            $product->images->each->deleteFiles();
            // Variants, images, attributes, search index and cart rows go with it; order items keep their snapshot
            $product->delete();
        }

        // A category an admin-made product still uses stays, but leaves the menu and the home page
        Category::whereIn('slug', self::RETIRED_CATEGORIES)->withCount('products')->get()
            ->each(fn (Category $category) => $category->products_count
                ? $category->update(['is_menu' => false, 'is_featured' => false])
                : $category->delete());

        Brand::whereIn('slug', self::RETIRED_BRANDS)->doesntHave('products')->get()->each->delete();

        if ($retired->isNotEmpty()) {
            $this->command?->info("Removed {$retired->count()} products from the earlier demo catalog.");
        }
    }

    private function seedBrands(): array
    {
        $brands = [];
        foreach (self::BRANDS as $slug => [$name, $description]) {
            $brands[$slug] = Brand::firstOrCreate(['slug' => $slug], ['name' => $name, 'description' => $description, 'status' => true]);
        }

        return $brands;
    }

    private function seedProduct(array $item, string $sku, int $number, Category $category, array $settings, Brand $brand, $attributes): void
    {
        $status = $item['status'] ?? 'in_stock';
        $stock = $status === 'in_stock' ? 8 + crc32($sku) % 60 : 0;
        $warranty = $item['warranty'] ?? $settings['warranty'];

        $product = ProductOptimized::updateOrCreate(['sku' => $sku], [
            'name' => $item['name'],
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'short_description' => $item['hl'],
            'description' => $this->description($item, $brand->name, $settings),
            'base_price' => $item['price'],
            'cost_price' => round($item['price'] * 0.82),
            'currency' => 'BDT',
            'manage_stock' => true,
            'stock_status' => $status,
            'total_stock' => $stock,
            'weight' => $item['weight'] ?? null,
            'dimensions' => $item['dim'] ?? null,
            'specs' => $item['specs'],
            'attributes' => $item['attrs'],
            'meta_title' => $item['name'] . ' Price in Bangladesh | ' . config('shop.name'),
            'meta_description' => $item['hl'],
            'meta_keywords' => implode(', ', array_merge([$brand->name, $category->name], array_values($item['attrs']))),
            'status' => 1,
            'featured' => ! empty($item['f']),
            'warranty' => $warranty,
            'manufacturer_part_no' => $item['mpn'] ?? null,
            'ean_upc' => $this->ean13($number),
            // Listed over the past six weeks, so "Latest Products" mixes categories as in a real shop
            'created_at' => now()->subMinutes(crc32($sku) % (60 * 24 * 42)),
        ]);

        $this->seedAttributes($product, $item['attrs'], $attributes);
        $this->seedVariants($product, $item, $settings, $sku, $stock);
        $this->seedImages($product, $item, $sku, $brand->name, $category->slug);

        $product->updateSearchIndex();
    }

    private function seedAttributes(ProductOptimized $product, array $attrs, $attributes): void
    {
        $product->productAttributes()->delete();

        foreach ($attrs as $slug => $value) {
            $attribute = $attributes->get($slug);
            if (! $attribute) {
                continue;
            }

            ProductAttributeOptimized::create([
                'product_id' => $product->id,
                'attribute_id' => $attribute->id,
                'attribute_value_id' => $attribute->values->firstWhere('value', $value)?->id,
                'value' => $value,
            ]);
        }
    }

    private function seedVariants(ProductOptimized $product, array $item, array $settings, string $sku, int $stock): void
    {
        $product->variants()->delete();

        $options = $item['variants'] ?? [];
        // A single "Standard" variant carries the compare-at price for products without options
        if (! $options && isset($item['was'])) {
            $options = ['Standard'];
        }
        if (! $options) {
            return;
        }

        // Accept a plain list (same price) or name => price
        $options = array_is_list($options) ? array_fill_keys($options, null) : $options;
        $key = $item['vkey'] ?? $settings['variant_key'];
        $position = 0;

        foreach ($options as $name => $price) {
            $position++;
            $variant = new ProductVariantOptimized([
                'sku' => $sku . '-' . strtoupper(Str::slug($name)),
                'name' => $name,
                'price' => $price,
                'compare_price' => $position === 1 ? ($item['was'] ?? null) : null,
                'stock' => $stock ? max(1, intdiv($stock, count($options)) + $position % 3) : 0,
                'manage_stock' => true,
                'is_default' => $position === 1,
                'attributes' => [$key => $name],
            ]);
            // The saved hook recalculates the product's total stock through this relation
            $variant->product()->associate($product);
            $variant->save();
        }
    }

    /**
     * The cover image, plus a close-up and a key-specs card for featured products, so their pages
     * show a gallery. Each is only made when its file isn't there yet, so re-running tops up
     * missing images and leaves images added in admin alone.
     */
    private function seedImages(ProductOptimized $product, array $item, string $sku, string $brandName, string $categorySlug): void
    {
        // The cover names the model under the brand: "Samsung Galaxy S24 Ultra" → SAMSUNG / Galaxy S24 Ultra
        $model = Str::startsWith($item['name'], $brandName . ' ') ? Str::after($item['name'], $brandName . ' ') : $item['name'];
        $images = ['' => [$item['name'], fn () => $this->renderCover($categorySlug, $brandName, $model)]];
        if (! empty($item['f'])) {
            $images['-closeup'] = [$item['name'] . ', close-up', fn () => $this->renderCloseUp($categorySlug)];
            $images['-specs'] = [$item['name'] . ' key specifications', fn () => $this->renderSpecsCard($brandName, $item['name'], $item['specs'])];
        }

        $position = 0;
        foreach ($images as $suffix => [$alt, $render]) {
            $path = 'products/demo/' . strtolower($sku) . $suffix . '.jpg';
            if (! $product->images()->where('url', $path)->exists()) {
                Storage::disk('public')->put($path, $render());
                $image = ProductImageOptimized::create([
                    'product_id' => $product->id,
                    'url' => $path,
                    'alt_text' => $alt,
                    'sort_order' => $position,
                    // The cover leads, unless a main image was already chosen
                    'is_main' => $suffix === '' && ! $product->images()->where('is_main', true)->exists(),
                ]);
                $image->generateVersions();
            }
            $position++;
        }
    }

    /**
     * Prose for the Description section; the page lists the specs itself (ProductOptimized::specGroups()).
     */
    private function description(array $item, string $brandName, array $settings): string
    {
        $html = '<p>' . e($item['hl']) . '</p>';
        $html .= '<p>' . e(sprintf($settings['intro'], $item['name'], $brandName)) . '</p>';
        $html .= '<h3>Good to know</h3><ul>';
        foreach ($settings['notes'] as $note) {
            $html .= '<li>' . e($note) . '</li>';
        }

        return $html . '</ul>';
    }

    /**
     * EAN-13 using Bangladesh's GS1 prefix (941) and a valid check digit.
     */
    private function ean13(int $number): string
    {
        $digits = '941' . str_pad((string) $number, 9, '0', STR_PAD_LEFT);
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $digits[$i] * ($i % 2 ? 3 : 1);
        }

        return $digits . ((10 - $sum % 10) % 10);
    }

    /**
     * An 800x800 JPEG cover in the style of a product shot: the category's device drawing on white
     * (database/seeders/demo-art, drawn from the storefront's category icons) over the brand and model.
     */
    private function renderCover(string $categorySlug, string $brand, string $model): string
    {
        $image = $this->art($categorySlug);
        $ink = imagecolorallocate($image, 17, 24, 39);
        $muted = imagecolorallocate($image, 75, 85, 99);

        if ($this->fonts['bold'] && $this->fonts['regular']) {
            $this->centredText($image, 38, 628, $ink, $this->fonts['bold'], mb_strtoupper($brand));
            [$fontSize, $lines] = $this->fitText($model, 660, [30, 27, 24], 2, $this->fonts['regular']);
            $y = 690;
            foreach ($lines as $line) {
                $this->centredText($image, $fontSize, $y, $muted, $this->fonts['regular'], $line);
                $y += (int) ($fontSize * 1.5);
            }
        } else {
            // No TrueType font on this machine: fall back to GD's built-in font
            imagestring($image, 5, 60, 610, strtoupper($brand), $ink);
            imagestring($image, 4, 60, 650, $model, $muted);
        }

        return $this->jpeg($image);
    }

    /**
     * A detail shot: the middle of the category drawing, enlarged to fill the frame.
     */
    private function renderCloseUp(string $categorySlug): string
    {
        $closeUp = imagecreatetruecolor(800, 800);
        imagecopyresampled($closeUp, $this->art($categorySlug), 0, 0, 170, 90, 800, 800, 460, 460);

        return $this->jpeg($closeUp);
    }

    /**
     * A white card with the brand, the name and up to five specs, like the spec sheets shops add to galleries.
     */
    private function renderSpecsCard(string $brand, string $name, array $specs): string
    {
        $image = imagecreatetruecolor(800, 800);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
        $ink = imagecolorallocate($image, 17, 24, 39);
        $muted = imagecolorallocate($image, 107, 114, 128);
        $rule = imagecolorallocate($image, 229, 231, 235);
        $accent = imagecolorallocate($image, 37, 99, 235);

        if (! $this->fonts['bold'] || ! $this->fonts['regular']) {
            imagestring($image, 5, 64, 80, strtoupper($brand) . ' ' . $name, $ink);
            $y = 140;
            foreach (array_slice($specs, 0, 5, true) as $label => $value) {
                imagestring($image, 4, 64, $y, $label . ': ' . $value, $muted);
                $y += 40;
            }

            return $this->jpeg($image);
        }

        imagettftext($image, 20, 0, 64, 104, $accent, $this->fonts['bold'], mb_strtoupper($brand));
        [$fontSize, $lines] = $this->fitText($name, 672, [34, 30, 26], 2, $this->fonts['bold']);
        $y = 164;
        foreach ($lines as $line) {
            imagettftext($image, $fontSize, 0, 64, $y, $ink, $this->fonts['bold'], $line);
            $y += (int) ($fontSize * 1.45);
        }
        imagefilledrectangle($image, 64, $y - 12, 136, $y - 7, $accent);

        $y += 44;
        foreach (array_slice($specs, 0, 5, true) as $label => $value) {
            imagettftext($image, 16, 0, 64, $y, $muted, $this->fonts['regular'], mb_strtoupper($label));
            [$valueSize, $valueLines] = $this->fitText($value, 672, [23, 21, 19], 1, $this->fonts['bold']);
            imagettftext($image, $valueSize, 0, 64, $y + 36, $ink, $this->fonts['bold'], $valueLines[0]);
            imagefilledrectangle($image, 64, $y + 58, 736, $y + 58, $rule);
            $y += 92;
        }

        return $this->jpeg($image);
    }

    /**
     * The category's drawing (database/seeders/demo-art), or a blank white square without one.
     */
    private function art(string $categorySlug)
    {
        $path = __DIR__ . '/demo-art/' . $categorySlug . '.jpg';
        if (is_file($path)) {
            return imagecreatefromjpeg($path);
        }

        $image = imagecreatetruecolor(800, 800);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));

        return $image;
    }

    private function jpeg($image): string
    {
        ob_start();
        imagejpeg($image, null, 86);

        return ob_get_clean();
    }

    private function centredText($image, int $fontSize, int $baseline, int $color, string $font, string $text): void
    {
        $box = imagettfbbox($fontSize, 0, $font, $text);
        $x = (int) ((imagesx($image) - ($box[2] - $box[0])) / 2);
        imagettftext($image, $fontSize, 0, $x, $baseline, $color, $font, $text);
    }

    /**
     * Largest font size (from the given steps) at which the text wraps into $maxLines; at the
     * smallest size, lines past the limit are dropped and the last one ends with an ellipsis.
     */
    private function fitText(string $text, int $maxWidth, array $sizes, int $maxLines, string $font): array
    {
        foreach ($sizes as $fontSize) {
            $lines = [];
            $line = '';
            foreach (explode(' ', $text) as $word) {
                $candidate = trim($line . ' ' . $word);
                $box = imagettfbbox($fontSize, 0, $font, $candidate);
                if ($line !== '' && $box[2] - $box[0] > $maxWidth) {
                    $lines[] = $line;
                    $line = $word;
                } else {
                    $line = $candidate;
                }
            }
            $lines[] = $line;

            if (count($lines) <= $maxLines) {
                return [$fontSize, $lines];
            }
        }

        $lines = array_slice($lines, 0, $maxLines);
        $lines[$maxLines - 1] .= '…';

        return [$fontSize, $lines];
    }

    private function findFont(bool $bold): ?string
    {
        if (! function_exists('imagettftext')) {
            return null;
        }

        $candidates = $bold
            ? ['C:/Windows/Fonts/segoeuib.ttf', 'C:/Windows/Fonts/arialbd.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf', '/System/Library/Fonts/Supplemental/Arial Bold.ttf']
            : ['C:/Windows/Fonts/segoeui.ttf', 'C:/Windows/Fonts/arial.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf', '/usr/share/fonts/dejavu/DejaVuSans.ttf', '/System/Library/Fonts/Supplemental/Arial.ttf'];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
