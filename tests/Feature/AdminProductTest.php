<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ProductImageOptimized;
use App\Models\ProductOptimized;
use App\Models\ProductSearchIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_images_get_card_and_gallery_copies_that_are_deleted_with_the_product(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $category = Category::factory()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Phone X',
            'category_id' => $category->id,
            'base_price' => 500,
            'stock_status' => 'in_stock',
            'status' => 1,
            'images' => [UploadedFile::fake()->image('phone.jpg', 1600, 1200)],
        ])->assertRedirect(route('admin.products.show', ProductOptimized::sole()->id));

        $image = ProductImageOptimized::sole();
        $disk = Storage::disk('public');
        $disk->assertExists($image->url);
        $disk->assertExists($image->thumb_url);
        [$width, $height] = getimagesizefromstring($disk->get($image->thumb_url));
        $this->assertSame([ProductImageOptimized::THUMB_WIDTH, 450], [$width, $height]);
        $disk->assertExists($image->large_url);
        [$width, $height] = getimagesizefromstring($disk->get($image->large_url));
        $this->assertSame([ProductImageOptimized::LARGE_WIDTH, 900], [$width, $height]);

        // Cards use the thumbnail and the product page the gallery copy; the product was indexed for search once
        $this->get('/shop')->assertSee('storage/' . $image->thumb_url);
        $this->get(route('product.detail', $image->product_id))->assertSee('storage/' . $image->large_url)->assertDontSee('storage/' . $image->url);
        $this->assertSame(1, ProductSearchIndex::count());

        $this->actingAs($admin)->delete(route('admin.products.destroy', $image->product_id));
        $disk->assertMissing($image->url);
        $disk->assertMissing($image->thumb_url);
        $disk->assertMissing($image->large_url);
    }

    public function test_the_thumbnails_command_fills_in_missing_gallery_copies(): void
    {
        Storage::fake('public');
        $product = ProductOptimized::factory()->create();
        Storage::disk('public')->put('products/old.jpg', UploadedFile::fake()->image('old.jpg', 1400, 1400)->get());
        $image = ProductImageOptimized::create(['product_id' => $product->id, 'url' => 'products/old.jpg', 'is_main' => true]);

        $this->artisan('products:thumbnails')->assertSuccessful();

        $image->refresh();
        Storage::disk('public')->assertExists([$image->thumb_url, $image->large_url]);
        $this->assertSame(ProductImageOptimized::LARGE_WIDTH, getimagesizefromstring(Storage::disk('public')->get($image->large_url))[0]);
    }
}
