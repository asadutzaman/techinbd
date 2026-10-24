<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SlugRedirect;
use Illuminate\Database\Seeder;

/**
 * The storefront's categories and subcategories, in menu order. Top-level categories make the
 * category bar; subcategories open from it. Their icons come from Category::getIconAttribute().
 *
 * Safe to re-run: categories are matched by slug. The seeder keeps their place in the tree, menu
 * order and switches as listed here; names, descriptions and images edited in admin are kept.
 */
class CategorySeeder extends Seeder
{
    /**
     * slug => [name, description, featured on the home page, subcategories (same shape, without their own)]
     *
     * The featured ones make the home page's 16 category tiles (two rows of eight), in this order.
     */
    public const CATEGORIES = [
        'laptop' => ['Laptop', 'Laptops from every major brand, for study, work and gaming.', true, [
            'apple-macbook' => ['Apple MacBook', 'MacBook Air and MacBook Pro with Apple silicon.', false],
            'asus-laptop' => ['ASUS Laptop', 'Vivobook, Zenbook, ExpertBook, TUF and ROG laptops.', false],
            'hp-laptop' => ['HP Laptop', 'HP everyday, ProBook, OmniBook, Victus and OMEN laptops.', false],
            'lenovo-laptop' => ['Lenovo Laptop', 'IdeaPad, ThinkPad, LOQ and Legion laptops.', false],
            'dell-laptop' => ['Dell Laptop', 'Dell Pro, Inspiron and Latitude laptops.', false],
            'acer-laptop' => ['Acer Laptop', 'Aspire, TravelLite and Predator laptops.', false],
            'msi-laptop' => ['MSI Laptop', 'Modern, Prestige, Cyborg, Katana and Stealth laptops.', false],
            'laptop-cooler' => ['Laptop Cooler', 'Cooling pads and stands that keep laptops running cool.', false],
        ]],
        'desktop-pc' => ['Desktop PC', 'Ready-to-use desktop computers.', true, [
            'brand-pc' => ['Brand PC', 'Desktops from Dell, HP, Lenovo, Acer, MSI and Gigabyte.', false],
        ]],
        'monitor' => ['Monitor', 'Office, gaming and 4K monitors from 22 to 27 inches.', true, []],
        'pc-components' => ['PC Components', 'Graphics cards, processors, motherboards, memory, casings, power supplies and CPU coolers for building a PC.', false, [
            'graphics-card' => ['Graphics Card', 'NVIDIA GeForce and AMD Radeon graphics cards.', true],
            'processor' => ['Processor', 'Intel Core and AMD Ryzen desktop processors.', true],
            'motherboard' => ['Motherboard', 'Intel and AMD motherboards from ASUS, MSI, Gigabyte and more.', false],
            'ram' => ['RAM', 'DDR4 and DDR5 desktop memory.', true],
            'casing' => ['Casing', 'Gaming and office PC cases.', false],
            'power-supply' => ['Power Supply', 'PC power supplies from 450W to 1000W and up.', false],
            'cpu-cooler' => ['CPU Cooler', 'Air and liquid CPU coolers.', false],
        ]],
        'peripherals' => ['Peripherals', 'Keyboards, mice, headphones, mouse pads and computer accessories.', false, [
            'keyboard' => ['Keyboard', 'Office, membrane and mechanical gaming keyboards.', true],
            'mouse' => ['Mouse', 'Everyday, gaming and wireless mice.', true],
            'headphone' => ['Headphone', 'Headsets for calls, music and gaming.', true],
            'mouse-pad' => ['Mouse Pad', 'Gaming mouse pads, desk mats and wrist rests.', false],
            'pc-accessory' => ['Computer Accessory', 'USB hubs, pen drives, laptop cases and bags.', false],
            'webcam' => ['Webcam', 'Webcams for streaming, calls and classes.', false],
            'microphone' => ['Microphone', 'USB and wireless microphones for streaming, calls and recording.', false],
        ]],
        'gadget' => ['Smart Gadget', 'Smart watches, earbuds, speakers, power banks, chargers, cables, cameras and smart home gear.', false, [
            'smart-watch' => ['Smart Watch', 'Smart watches and fitness bands.', true],
            'earbuds' => ['Earbuds', 'True wireless earbuds and neckbands, with and without noise cancelling.', true],
            'earphone' => ['Earphone', 'Wired earphones with 3.5mm and USB-C plugs.', false],
            'power-bank' => ['Power Bank', 'Power banks from 5000 to 50000mAh.', true],
            'speaker' => ['Speaker', 'Portable Bluetooth speakers.', false],
            'smart-home' => ['Smart Home', 'Air fryers, kettles, scales, trimmers and other smart home and personal care gear.', false],
            'camera' => ['Camera', 'Security cameras and wireless microphones.', false],
            'charger' => ['Charger', 'Fast chargers and power adapters from Anker, Xiaomi, Samsung and more.', false],
            'cable' => ['Cable', 'USB-C, Lightning and HDMI cables.', false],
        ]],
        // Products stay in their own category and are listed here too (DemoCatalogSeeder lists the demo ones)
        'gaming' => ['Gaming', 'Gaming laptops, PCs, monitors, keyboards, mice, headsets, mouse pads and casings.', true, [
            'gaming-laptop' => ['Gaming Laptop', 'Laptops with dedicated graphics and fast screens, for playing as well as working.', false],
            'gaming-pc' => ['Gaming PC', 'Ready-to-play desktops with dedicated graphics.', false],
            'gaming-monitor' => ['Gaming Monitor', 'High refresh rate monitors for smooth, sharp games.', false],
            'gaming-keyboard' => ['Gaming Keyboard', 'Mechanical, membrane and RGB gaming keyboards.', false],
            'gaming-mouse' => ['Gaming Mouse', 'Wired and wireless gaming mice.', false],
            'gaming-headphone' => ['Gaming Headphone', 'Gaming headsets with microphones.', false],
            'gaming-mouse-pad' => ['Gaming Mouse Pad', 'Gaming mouse pads and desk mats.', false],
            'gaming-casing' => ['Gaming Casing', 'PC cases built for airflow, big graphics cards and RGB.', false],
        ]],
        'tv' => ['TV', 'Smart TVs from 32 to 55 inches.', false, [
            'smart-tv' => ['Smart TV', 'Google TV, Tizen and other smart TVs, HD to 4K Mini LED.', true],
        ]],
        'office-equipment' => ['Office Equipment', 'Equipment for offices, classrooms and meeting rooms.', false, [
            'interactive-flat-panel' => ['Interactive Flat Panel', '65 and 86-inch 4K touch panels for teaching and meetings.', false],
        ]],
        'printer' => ['Printer', 'Printers for home, office and shop counters.', false, [
            'laser-printer' => ['Laser Printer', 'Mono and colour laser printers.', true],
            'pos-printer' => ['POS Printer', 'Thermal receipt printers for shops and restaurants.', false],
        ]],
        'networking' => ['Networking', 'Wi-Fi and home networking.', false, [
            'router' => ['Router', 'Wi-Fi 5, Wi-Fi 6 and mesh routers.', true],
        ]],
    ];

    /**
     * Shorter names for the category's menu: the laptop subcategories are listed by brand under Laptop,
     * and the gaming ones without "Gaming" under Gaming.
     */
    public const MENU_NAMES = [
        'apple-macbook' => 'Apple',
        'asus-laptop' => 'ASUS',
        'hp-laptop' => 'HP',
        'lenovo-laptop' => 'Lenovo',
        'dell-laptop' => 'Dell',
        'acer-laptop' => 'Acer',
        'msi-laptop' => 'MSI',
        'gaming-laptop' => 'Laptop',
        'gaming-pc' => 'PC',
        'gaming-monitor' => 'Monitor',
        'gaming-keyboard' => 'Keyboard',
        'gaming-mouse' => 'Mouse',
        'gaming-headphone' => 'Headphone',
        'gaming-mouse-pad' => 'Mouse Pad',
        'gaming-casing' => 'Casing',
    ];

    /**
     * Categories from the earlier catalog that live on under a new slug. The row is renamed rather
     * than replaced, so its old address redirects (HasSlug records it in slug_redirects).
     */
    public const RENAMED = ['television' => 'tv', 'headphones' => 'headphone'];

    /**
     * Seeded names that have since changed. A row still carrying the old name gets the new one;
     * a name chosen in admin stays.
     */
    public const PREVIOUS_NAMES = ['gadget' => 'Gadget'];

    /**
     * Top-level categories whose subcategories moved under another. Once one is empty it's deleted
     * and its address leads to the other; a product added to it in admin keeps it, out of the menu.
     */
    public const MERGED = ['charger-cable' => 'gadget'];

    public function run(): void
    {
        foreach (self::RENAMED as $old => $new) {
            $category = Category::firstWhere('slug', $old);
            if ($category && ! Category::where('slug', $new)->exists()) {
                $category->forceFill(['slug' => $new, 'name' => $this->entry($new)[0]])->save();
            }
        }

        foreach (self::PREVIOUS_NAMES as $slug => $previousName) {
            Category::where('slug', $slug)->where('name', $previousName)->first()?->update(['name' => $this->entry($slug)[0]]);
        }

        $position = 0;
        foreach (self::CATEGORIES as $slug => [$name, $description, $featured, $children]) {
            $parent = $this->place($slug, $name, $description, $featured, null, ++$position);

            $childPosition = 0;
            foreach ($children as $childSlug => [$childName, $childDescription, $childFeatured]) {
                $this->place($childSlug, $childName, $childDescription, $childFeatured, $parent->id, ++$childPosition);
            }
        }

        $this->retireMerged();
    }

    /**
     * Every slug the tree uses, top-level and subcategories.
     */
    public static function slugs(): array
    {
        $slugs = [];
        foreach (self::CATEGORIES as $slug => [, , , $children]) {
            $slugs = [...$slugs, $slug, ...array_keys($children)];
        }

        return $slugs;
    }

    private function place(string $slug, string $name, string $description, bool $featured, ?int $parentId, int $position): Category
    {
        $category = Category::firstOrNew(['slug' => $slug]);
        if (! $category->exists) {
            $category->fill(['name' => $name, 'description' => $description, 'status' => true]);
        }
        if (blank($category->menu_name) && isset(self::MENU_NAMES[$slug])) {
            $category->menu_name = self::MENU_NAMES[$slug];
        }

        $category->forceFill([
            'parent_id' => $parentId,
            'sort_order' => $position,
            'is_menu' => true,
            'is_featured' => $featured,
        ])->save();

        return $category;
    }

    private function retireMerged(): void
    {
        foreach (self::MERGED as $slug => $into) {
            $category = Category::withCount(['products', 'children'])->firstWhere('slug', $slug);
            if ($category && ($category->products_count || $category->children_count)) {
                $category->update(['is_menu' => false, 'is_featured' => false]);

                continue;
            }

            $category?->delete();
            SlugRedirect::updateOrCreate(
                ['type' => Category::slugType(), 'slug' => $slug],
                ['target_id' => Category::where('slug', $into)->value('id')]
            );
        }
    }

    private function entry(string $slug): array
    {
        foreach (self::CATEGORIES as $topSlug => $entry) {
            if ($topSlug === $slug) {
                return $entry;
            }
            if (isset($entry[3][$slug])) {
                return $entry[3][$slug];
            }
        }

        throw new \InvalidArgumentException("No category {$slug}");
    }
}
