<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Starter banners for the home page: three slides (MacBook Air M5, gadgets, gaming laptops) and
 * two side banners (cash on delivery, deals). All are original artwork with the text designed in
 * (public/img/banners), so the title is what screen readers announce.
 * Replace or reorder them in Admin → Home Banners.
 *
 * Safe to re-run: banners are matched by their image, and changed artwork is picked up.
 * Slides from earlier demo catalogs (fashion, graphics cards) are removed.
 *
 *   php artisan db:seed --class=BannerSeeder
 */
class BannerSeeder extends Seeder
{
    private const BANNERS = [
        [
            'photo' => 'banners/macbook-air-m5.jpg',
            'mobile_photo' => 'banners/macbook-air-m5-mobile.jpg',
            'link' => ['search' => 'MacBook Air'],
            'title' => 'MacBook Air M5, now available. Powered by the Apple M5 chip, with official warranty and cash on delivery.',
        ],
        [
            'photo' => 'banners/gadgets.jpg',
            'mobile_photo' => 'banners/gadgets-mobile.jpg',
            'link' => ['category' => 'gadget'],
            'title' => 'Smart gadgets: smart watches, earbuds and power banks from Samsung, Amazfit, Anker and more.',
        ],
        [
            'photo' => 'banners/gaming-laptops.jpg',
            'mobile_photo' => 'banners/gaming-laptops-mobile.jpg',
            'link' => ['category' => 'laptop'],
            'title' => 'Gaming laptops: play at 144Hz with RTX gaming laptops from ASUS, HP, Lenovo, MSI and Acer.',
        ],
        [
            'photo' => 'banners/cash-on-delivery.jpg',
            'placement' => 'side',
            'link' => [],
            'title' => 'Cash on delivery: pay when your order arrives, anywhere in Bangladesh.',
        ],
        [
            'photo' => 'banners/deals.jpg',
            'placement' => 'side',
            'link' => ['sale' => 1],
            'title' => 'Deals: marked-down tech. See all deals.',
        ],
    ];

    /** Starter slides from earlier demo catalogs: the fashion slides, then graphics cards */
    private const RETIRED = ['carousel-1.jpg', 'carousel-2.jpg', 'carousel-3.jpg', 'graphics-cards.jpg'];

    public function run(): void
    {
        Banner::whereIn('image_path', array_map(fn ($photo) => $this->storagePath($photo), self::RETIRED))
            ->get()
            ->each(function (Banner $banner) {
                $banner->deleteFiles();
                $banner->delete();
            });

        foreach (self::BANNERS as $position => $data) {
            $imagePath = $this->storagePath($data['photo']);
            $banner = Banner::firstOrNew(['image_path' => $imagePath]);
            $banner->image_path = $imagePath;

            // Copy the artwork in; remake the WebP versions when it's new or has changed
            $changed = $this->syncImage($data['photo']);
            if (isset($data['mobile_photo'])) {
                $changed = $this->syncImage($data['mobile_photo']) || $changed;
                $banner->mobile_image_path = $this->storagePath($data['mobile_photo']);
            }

            $banner->fill([
                'title' => $data['title'],
                'subtitle' => null,
                'button_text' => null,
                'link_url' => $this->link($data['link']),
                'placement' => $data['placement'] ?? 'slider',
                'show_text' => false,
                'sort_order' => $position,
                'is_active' => true,
            ]);
            // Slides and side banners are cropped to different shapes
            $changed = $changed || $banner->isDirty('placement');
            $banner->save();

            if ($changed || empty($banner->images)) {
                $banner->generateImages();
            }
        }

        $this->command?->info('Seeded ' . count(self::BANNERS) . ' starter banners.');
    }

    private function storagePath(string $publicImage): string
    {
        return 'banners/originals/starter-' . basename($publicImage);
    }

    /**
     * Copy an image from public/img into storage. True when the stored copy was missing or different.
     */
    private function syncImage(string $publicImage): bool
    {
        $disk = Storage::disk('public');
        $path = $this->storagePath($publicImage);
        $contents = file_get_contents(public_path('img/' . $publicImage));

        if ($disk->exists($path) && md5($disk->get($path)) === md5($contents)) {
            return false;
        }

        $disk->put($path, $contents);

        return true;
    }

    /**
     * A relative shop link, so it works on any domain.
     */
    private function link(array $link): string
    {
        if (isset($link['category'])) {
            $category = Category::where('slug', $link['category'])->first();

            return $category ? route('shop.category', $category, false) : route('shop', [], false);
        }

        return route('shop', $link, false);
    }
}
