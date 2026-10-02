<?php

namespace Database\Seeders;

use App\Models\Category;
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
        'monitor' => ['Monitor', 'Office, gaming and 4K monitors from 22 to 27 inches.', true, []],
        'peripherals' => ['Peripherals', 'Keyboards, mice, headphones and mouse pads.', false, [
            'keyboard' => ['Keyboard', 'Office, membrane and mechanical gaming keyboards.', true],
            'mouse' => ['Mouse', 'Everyday, gaming and wireless mice.', true],
            'headphone' => ['Headphone', 'Headsets for calls, music and gaming.', true],
            'mouse-pad' => ['Mouse Pad', 'Gaming mouse pads, desk mats and wrist rests.', true],
        ]],
        'desktop-pc' => ['Desktop PC', 'Ready-to-use desktop computers.', false, [
            'brand-pc' => ['Brand PC', 'Desktops from Dell, HP, Lenovo, Acer, MSI and Gigabyte.', true],
        ]],
        'office-equipment' => ['Office Equipment', 'Equipment for offices, classrooms and meeting rooms.', false, [
            'interactive-flat-panel' => ['Interactive Flat Panel', '65 and 86-inch 4K touch panels for teaching and meetings.', true],
        ]],
        'networking' => ['Networking', 'Wi-Fi and home networking.', false, [
            'router' => ['Router', 'Wi-Fi 5, Wi-Fi 6 and mesh routers.', true],
        ]],
        'tv' => ['TV', 'Smart TVs from 32 to 55 inches.', false, [
            'smart-tv' => ['Smart TV', 'Google TV, Tizen and other smart TVs, HD to 4K Mini LED.', true],
        ]],
        'gadget' => ['Gadget', 'Smart watches, earbuds, earphones and power banks.', false, [
            'smart-watch' => ['Smart Watch', 'Smart watches and fitness bands.', true],
            'earbuds' => ['Earbuds', 'True wireless earbuds, with and without noise cancelling.', true],
            'earphone' => ['Earphone', 'Wired earphones with 3.5mm and USB-C plugs.', true],
            'power-bank' => ['Power Bank', 'Power banks from 10000 to 30000mAh.', true],
        ]],
        'printer' => ['Printer', 'Printers for home, office and shop counters.', false, [
            'laser-printer' => ['Laser Printer', 'Mono and colour laser printers.', true],
            'pos-printer' => ['POS Printer', 'Thermal receipt printers for shops and restaurants.', true],
        ]],
    ];

    /**
     * Categories from the earlier catalog that live on under a new slug. The row is renamed rather
     * than replaced, so its old address redirects (HasSlug records it in slug_redirects).
     */
    public const RENAMED = ['television' => 'tv', 'headphones' => 'headphone'];

    public function run(): void
    {
        foreach (self::RENAMED as $old => $new) {
            $category = Category::firstWhere('slug', $old);
            if ($category && ! Category::where('slug', $new)->exists()) {
                $category->forceFill(['slug' => $new, 'name' => $this->entry($new)[0]])->save();
            }
        }

        $position = 0;
        foreach (self::CATEGORIES as $slug => [$name, $description, $featured, $children]) {
            $parent = $this->place($slug, $name, $description, $featured, null, ++$position);

            $childPosition = 0;
            foreach ($children as $childSlug => [$childName, $childDescription, $childFeatured]) {
                $this->place($childSlug, $childName, $childDescription, $childFeatured, $parent->id, ++$childPosition);
            }
        }
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

        $category->forceFill([
            'parent_id' => $parentId,
            'sort_order' => $position,
            'is_menu' => true,
            'is_featured' => $featured,
        ])->save();

        return $category;
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
