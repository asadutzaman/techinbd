<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Starter banners for the home page: three slides (MacBook Air M5, gadgets, gaming laptops) and
 * two side banners (cash on delivery, deals), in public/img/banners. The slides show photos of
 * catalog products on white; all have their text designed in, so the title is what screen readers
 * announce and should say what the image says.
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
            'title' => 'MacBook Air M5, now available: the Apple M5 chip in 13-inch and 15-inch sizes. Official warranty, cash on delivery.',
        ],
        [
            'photo' => 'banners/gadgets.jpg',
            'mobile_photo' => 'banners/gadgets-mobile.jpg',
            'link' => ['category' => 'gadget'],
            'title' => 'Smart gadgets: smart watches, earbuds and power banks from Amazfit, Samsung, Anker and more.',
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
            if (isset($data['mobile_photo'])) {
                $banner->mobile_image_path = $this->storagePath($data['mobile_photo']);
            }

            // Artwork that's new or has changed. It's copied in after the banner saves, so a failed
            // save can't leave new artwork in storage that a re-run would take for unchanged.
            $stale = array_filter([$data['photo'], $data['mobile_photo'] ?? null], fn ($photo) => $photo && $this->differs($photo));

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
            $changed = $stale || $banner->isDirty('placement');
            $banner->save();

            foreach ($stale as $photo) {
                Storage::disk('public')->put($this->storagePath($photo), file_get_contents(public_path('img/' . $photo)));
            }
            // Remake the WebP versions when the artwork or its shape changed
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
     * True when the stored copy of an image from public/img is missing or different.
     */
    private function differs(string $publicImage): bool
    {
        $disk = Storage::disk('public');
        $path = $this->storagePath($publicImage);

        return ! $disk->exists($path) || md5($disk->get($path)) !== md5_file(public_path('img/' . $publicImage));
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
