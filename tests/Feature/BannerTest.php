<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->admin = User::factory()->create();
        $this->admin->forceFill(['is_admin' => true])->save();
    }

    public function test_admin_adds_a_banner_and_gets_desktop_and_phone_webp_versions(): void
    {
        $this->actingAs($this->admin)->post(route('admin.banners.store'), [
            'title' => 'Eid collection',
            'subtitle' => 'Panjabis from Aarong',
            'button_text' => 'Shop Eid',
            'link_url' => '/shop?sale=1',
            'placement' => 'slider',
            'show_text' => 1,
            'is_active' => 1,
            'image' => UploadedFile::fake()->image('eid.jpg', 1920, 640),
        ])->assertRedirect(route('admin.banners.index'));

        $banner = Banner::sole();
        $disk = Storage::disk('public');

        // Desktop: 3:1 at 800 and 1600 wide; phones: 2:1 cropped from the same upload
        $this->assertSame([800, 1600], array_keys($banner->images['desktop']));
        $this->assertSame([600, 1200], array_keys($banner->images['mobile']));
        $this->assertSame([1600, 533], array_slice(getimagesizefromstring($disk->get($banner->images['desktop'][1600])), 0, 2));
        $this->assertSame([1200, 600], array_slice(getimagesizefromstring($disk->get($banner->images['mobile'][1200])), 0, 2));

        $this->get('/')
            ->assertOk()
            ->assertSee('Eid collection')
            ->assertSee('Shop Eid')
            ->assertSee('href="/shop?sale=1"', false)
            ->assertSee('fetchpriority="high"', false)
            ->assertSee($banner->images['desktop'][1600]);
    }

    public function test_small_uploads_are_not_upscaled(): void
    {
        $this->actingAs($this->admin)->post(route('admin.banners.store'), [
            'title' => 'Small banner',
            'placement' => 'slider',
            'is_active' => 1,
            'image' => UploadedFile::fake()->image('small.jpg', 1000, 430),
        ]);

        $images = Banner::sole()->images;
        $this->assertSame([800, 1000], array_keys($images['desktop']));
        $this->assertCount(4, Storage::disk('public')->allFiles('banners/' . Banner::sole()->id), 'No duplicate files for repeated widths');
    }

    public function test_only_live_banners_show_in_order(): void
    {
        $this->makeBanner(['title' => 'Second banner', 'sort_order' => 2]);
        $this->makeBanner(['title' => 'First banner', 'sort_order' => 1]);
        $this->makeBanner(['title' => 'Hidden banner', 'is_active' => false]);
        $this->makeBanner(['title' => 'Future banner', 'starts_at' => now()->addDay()]);
        $this->makeBanner(['title' => 'Ended banner', 'ends_at' => now()->subHour()]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['First banner', 'Second banner'])
            ->assertDontSee('Hidden banner')
            ->assertDontSee('Future banner')
            ->assertDontSee('Ended banner')
            ->assertSee('1 / 2');
    }

    public function test_banner_with_text_in_the_image_uses_the_title_as_alt_text(): void
    {
        $this->makeBanner(['title' => 'Mega sale 50% off', 'show_text' => false]);

        $this->get('/')->assertSee('alt="Mega sale 50% off"', false)->assertDontSee('class="home-banner-caption"', false);
    }

    public function test_home_has_no_slider_without_banners(): void
    {
        $this->get('/')->assertOk()->assertDontSee('id="home-banner"', false)->assertDontSee('class="home-hero"', false);
    }

    public function test_side_banners_get_a_two_to_one_crop_and_sit_beside_the_slider(): void
    {
        $this->makeBanner(['title' => 'Main slide']);

        $this->actingAs($this->admin)->post(route('admin.banners.store'), [
            'title' => 'Cash on delivery',
            'link_url' => '/shop',
            'placement' => 'side',
            'is_active' => 1,
            'image' => UploadedFile::fake()->image('cod.jpg', 1000, 500),
            'mobile_image' => UploadedFile::fake()->image('ignored.jpg', 1200, 600),
        ])->assertRedirect(route('admin.banners.index'));

        // One 2:1 crop serves every screen, so no desktop or phone versions
        $side = Banner::where('placement', 'side')->sole();
        $this->assertSame(['side'], array_keys($side->images));
        $this->assertSame([400, 800], array_keys($side->images['side']));
        $this->assertSame([800, 400], array_slice(getimagesizefromstring(Storage::disk('public')->get($side->images['side'][800])), 0, 2));

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('home-hero-grid has-side', $html);
        $this->assertMatchesRegularExpression('#<a class="home-side-banner"\s+href="/shop"\s*>#', $html);
        $this->assertStringContainsString('alt="Cash on delivery"', $html);
        $this->assertStringContainsString($side->images['side'][800], $html);
        $this->assertStringContainsString('aria-label="1 of 1"', $html, 'Side banners are not slides');
    }

    public function test_only_the_first_two_side_banners_show(): void
    {
        $this->makeBanner(['title' => 'Main slide']);
        $this->makeBanner(['title' => 'Side one', 'placement' => 'side', 'sort_order' => 1]);
        $this->makeBanner(['title' => 'Side two', 'placement' => 'side', 'sort_order' => 2]);
        $this->makeBanner(['title' => 'Side three', 'placement' => 'side', 'sort_order' => 3]);

        $this->get('/')->assertOk()->assertSeeInOrder(['Side one', 'Side two'])->assertDontSee('Side three');
    }

    public function test_a_slider_without_side_banners_takes_the_whole_row(): void
    {
        $this->makeBanner(['title' => 'Only slide']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Only slide')
            ->assertDontSee('home-hero-grid has-side')
            ->assertDontSee('class="home-side-banner"', false);
    }

    public function test_moving_a_banner_to_the_side_recrops_it(): void
    {
        $banner = $this->makeBanner(['title' => 'Moving banner']);
        $this->assertArrayHasKey('desktop', $banner->images);

        $this->actingAs($this->admin)->put(route('admin.banners.update', $banner), [
            'title' => 'Moving banner',
            'placement' => 'side',
            'is_active' => 1,
        ])->assertRedirect(route('admin.banners.index'));

        $this->assertSame(['side'], array_keys($banner->fresh()->images));
        $files = Storage::disk('public')->allFiles('banners/' . $banner->id);
        $this->assertCount(2, $files, 'The slide versions are deleted');
        $this->assertSame([], array_filter($files, fn ($path) => ! str_contains($path, '/side-')));
    }

    public function test_schedule_is_entered_in_shop_time(): void
    {
        $this->actingAs($this->admin)->post(route('admin.banners.store'), [
            'title' => 'Pohela Boishakh',
            'placement' => 'slider',
            'is_active' => 1,
            'starts_at' => '2026-04-14T09:00',
            'image' => UploadedFile::fake()->image('b.jpg', 1920, 640),
        ]);

        // 9:00 in Dhaka (UTC+6) is 3:00 UTC
        $this->assertSame('2026-04-14 03:00:00', Banner::sole()->starts_at->utc()->format('Y-m-d H:i:s'));
    }

    public function test_links_must_be_a_site_path_or_web_address(): void
    {
        $this->actingAs($this->admin)->post(route('admin.banners.store'), [
            'title' => 'Bad link',
            'link_url' => 'javascript:alert(1)',
            'image' => UploadedFile::fake()->image('b.jpg', 1920, 640),
        ])->assertSessionHasErrors('link_url');

        $this->assertSame(0, Banner::count());
    }

    public function test_replacing_and_deleting_a_banner_cleans_up_its_files(): void
    {
        $banner = $this->makeBanner(['title' => 'Old image']);
        $oldFiles = collect($banner->images)->flatten()->push($banner->image_path)->all();

        $this->actingAs($this->admin)->get(route('admin.banners.index'))->assertOk()->assertSee('Old image')->assertSee('Live');
        $this->actingAs($this->admin)->get(route('admin.banners.edit', $banner))->assertOk()->assertSee('value="Old image"', false);

        $this->actingAs($this->admin)->put(route('admin.banners.update', $banner), [
            'title' => 'New image',
            'placement' => 'slider',
            'is_active' => 1,
            'image' => UploadedFile::fake()->image('new.jpg', 1920, 640),
        ])->assertRedirect(route('admin.banners.index'));

        foreach ($oldFiles as $path) {
            Storage::disk('public')->assertMissing($path);
        }

        $banner->refresh();
        $this->actingAs($this->admin)->delete(route('admin.banners.destroy', $banner));

        $this->assertSame(0, Banner::count());
        $this->assertSame([], Storage::disk('public')->allFiles('banners'));
    }

    public function test_starter_banners_lead_with_the_macbook_air_m5(): void
    {
        // Slides from earlier demo catalogs (fashion, then graphics cards), which the seeder retires
        $retired = $this->makeBanner(['title' => 'Panjabis, blazers and jeans']);
        $retired->forceFill(['image_path' => 'banners/originals/starter-carousel-1.jpg'])->save();
        $graphicsCards = $this->makeBanner(['title' => 'Graphics cards: GeForce RTX 5060']);
        $graphicsCards->forceFill(['image_path' => 'banners/originals/starter-graphics-cards.jpg'])->save();

        $this->seed([\Database\Seeders\CategorySeeder::class, \Database\Seeders\BannerSeeder::class]);

        $banners = Banner::active()->get();
        $slides = $banners->reject->isSide()->values();
        $sides = $banners->filter->isSide()->values();
        $this->assertCount(3, $slides);
        $this->assertCount(2, $sides);
        $this->assertStringStartsWith('MacBook Air M5', $slides[0]->title);
        $this->assertStringStartsWith('Smart gadgets', $slides[1]->title);
        $this->assertSame('/category/gadget', $slides[1]->link_url);
        $this->assertStringStartsWith('Gaming laptops', $slides[2]->title);
        $this->assertStringStartsWith('Cash on delivery', $sides[0]->title);
        $this->assertSame('/shop?sale=1', $sides[1]->link_url);
        $this->assertNull(Banner::find($retired->id));
        $this->assertNull(Banner::find($graphicsCards->id));

        $macbook = $slides[0];
        $this->assertFalse($macbook->show_text, 'The artwork has its text designed in');
        $this->assertSame('/shop?search=MacBook%20Air', $macbook->link_url);
        $this->assertNotNull($macbook->mobile_image_path, 'Phones get the purpose-made 2:1 artwork');
        $this->assertSame([1200, 600], array_slice(getimagesizefromstring(Storage::disk('public')->get($macbook->images['mobile'][1200])), 0, 2));
        $this->assertSame([800, 400], array_slice(getimagesizefromstring(Storage::disk('public')->get($sides[0]->images['side'][800])), 0, 2));

        // Text in the image is described by the alt text
        $this->get('/')
            ->assertOk()
            ->assertSee('alt="' . e($macbook->title) . '"', false)
            ->assertSeeInOrder(['/shop?search=MacBook%20Air', e($slides[1]->title), e($sides[0]->title)], false)
            ->assertSee('1 / 3')
            ->assertDontSee('Panjabis');

        // Re-running updates in place rather than adding duplicates, and leaves unchanged artwork alone
        $generated = $macbook->images;
        $this->seed(\Database\Seeders\BannerSeeder::class);
        $this->assertSame(5, Banner::count());
        $this->assertSame($generated, $macbook->fresh()->images);
    }

    public function test_customers_cannot_manage_banners(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.banners.index'))->assertForbidden();
    }

    private function makeBanner(array $attributes): Banner
    {
        $banner = new Banner(array_merge(['title' => 'Banner', 'placement' => 'slider', 'is_active' => true, 'show_text' => true], $attributes));
        $banner->image_path = UploadedFile::fake()->image('b.jpg', 1920, 640)->store('banners/originals', 'public');
        $banner->save();
        $banner->generateImages();

        return $banner;
    }
}
