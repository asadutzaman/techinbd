<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ProductImageOptimized;
use App\Models\ProductSearchIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_images_get_a_card_thumbnail_that_is_deleted_with_the_product(): void
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
        ])->assertRedirect(route('admin.products.index'));

        $image = ProductImageOptimized::sole();
        $disk = Storage::disk('public');
        $disk->assertExists($image->url);
        $disk->assertExists($image->thumb_url);
        [$width, $height] = getimagesizefromstring($disk->get($image->thumb_url));
        $this->assertSame([ProductImageOptimized::THUMB_WIDTH, 450], [$width, $height]);

        // Cards use the thumbnail; the product was indexed for search once
        $this->get('/shop')->assertSee('storage/' . $image->thumb_url);
        $this->assertSame(1, ProductSearchIndex::count());

        $this->actingAs($admin)->delete(route('admin.products.destroy', $image->product_id));
        $disk->assertMissing($image->url);
        $disk->assertMissing($image->thumb_url);
    }
}
