<?php

namespace Tests\Feature;

use App\Mail\TestEmail;
use App\Models\AttributeOptimized;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderEmail;
use App\Models\ProductImageOptimized;
use App\Models\ProductOptimized;
use App\Models\ProductVariantOptimized;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_dashboard_shows_real_figures(): void
    {
        $this->order(['status' => 'pending', 'total' => 1000]);
        $this->order(['status' => 'pending', 'total' => 500]);
        $this->order(['status' => 'processing', 'total' => 250]);
        $this->order(['status' => 'cancelled', 'total' => 9999]);
        ProductOptimized::factory()->create(['name' => 'Last Laptop', 'manage_stock' => true, 'total_stock' => 1]);

        $html = $this->actingAs($this->admin())->get('/admin')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<h3>2</h3>\s*<p>Orders to confirm</p>#', $html);
        $this->assertMatchesRegularExpression('#<h3>1</h3>\s*<p>Order to ship</p>#', $html);
        $this->assertMatchesRegularExpression('#Tk </span>1,750</span>\s*</h3>\s*<p>Sales this month · 3 orders</p>#', $html, 'Sales this month leave out the cancelled order');
        $this->assertStringContainsString('Last Laptop', $html);
        $this->assertStringContainsString('1 left', $html);
        $this->assertStringNotContainsString('Product Name 1', $html, 'No placeholder rows');
        $this->assertStringNotContainsString('$110', $html);
        // The sidebar shows the orders waiting
        $this->assertStringContainsString('title="2 waiting to be confirmed">2</span>', $html);
    }

    public function test_the_dashboard_warns_about_failed_emails(): void
    {
        $order = $this->order();
        OrderEmail::create(['order_id' => $order->id, 'kind' => 'placed', 'recipient' => 'a@example.com', 'subject' => 'x', 'sent' => false, 'error' => 'boom']);

        $this->actingAs($this->admin())->get('/admin')
            ->assertSee('1 order with a failed customer email')
            ->assertSee(route('admin.orders.index', ['failed_emails' => 1]), false);
    }

    public function test_long_lists_use_bootstrap_pagination(): void
    {
        ProductOptimized::factory()->count(20)->create();

        $html = $this->actingAs($this->admin())->get('/admin/products')->assertOk()->getContent();

        $this->assertStringContainsString('<ul class="pagination">', $html);
        $this->assertStringNotContainsString('<svg class="w-5 h-5"', $html, 'Not the Tailwind arrows that filled the page');
    }

    public function test_the_product_page_shows_specs_sales_and_the_live_link(): void
    {
        $product = ProductOptimized::factory()->create([
            'name' => 'Zen Laptop', 'base_price' => 50000, 'cost_price' => 40000,
            'specs' => ['Processor' => 'Ryzen 5', 'Memory' => '16GB'],
        ]);
        $variant = new ProductVariantOptimized(['name' => 'Standard', 'compare_price' => 60000, 'stock' => 5, 'is_default' => true]);
        $variant->product()->associate($product);
        $variant->save();
        $order = $this->order(['status' => 'delivered']);
        $order->orderItems()->create(['product_id' => $product->id, 'product_name' => 'Zen Laptop', 'product_price' => 50000, 'quantity' => 2, 'total' => 100000]);
        $cancelled = $this->order(['status' => 'cancelled']);
        $cancelled->orderItems()->create(['product_id' => $product->id, 'product_name' => 'Zen Laptop', 'product_price' => 50000, 'quantity' => 7, 'total' => 350000]);

        $html = $this->actingAs($this->admin())->get(route('admin.products.show', $product->id))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#Processor</th>\s*<td>\s*Ryzen 5\s*<span class="badge[^"]*"[^>]*>key feature</span>#', $html);
        $this->assertStringContainsString('http://localhost/product/' . $product->id, $html, 'The search preview uses APP_URL');
        $this->assertStringContainsString('Save <span class="price">', $html);
        $this->assertStringContainsString('(17%)', $html, 'Saving as a share of the was price');
        $this->assertStringContainsString('(20%)', $html, 'Margin over cost');
        $this->assertMatchesRegularExpression('#<div class="h4 mb-0">2</div>\s*<small class="text-muted">sold</small>#', $html, 'Cancelled orders are left out');
        $this->assertStringContainsString('href="' . route('product.detail', $product->id) . '"', $html);
        $this->assertStringContainsString($order->order_number, $html);
    }

    public function test_the_product_form_saves_specs_in_order_and_the_was_price(): void
    {
        $category = Category::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Zen Phone',
            'category_id' => $category->id,
            'base_price' => 30000,
            'compare_price' => 35000,
            'stock_status' => 'in_stock',
            'total_stock' => 4,
            'manage_stock' => 1,
            'status' => 1,
            'specs' => [
                ['label' => 'Display', 'value' => '6.7" AMOLED'],
                ['label' => '', 'value' => ''],
                ['label' => 'Chipset', 'value' => 'Snapdragon 7'],
            ],
        ])->assertSessionHasNoErrors();

        $product = ProductOptimized::with('variants')->where('name', 'Zen Phone')->sole();
        $this->assertSame(['Display' => '6.7" AMOLED', 'Chipset' => 'Snapdragon 7'], $product->specs);
        $this->assertSame('35000.00', $product->variants->sole()->compare_price);
        $this->assertSame(4, $product->fresh()->total_stock);

        // Edit: reorder the specs, end the sale, untick Featured
        $product->update(['featured' => true]);
        $this->put(route('admin.products.update', $product->id), [
            'name' => 'Zen Phone',
            'category_id' => $category->id,
            'base_price' => 30000,
            'compare_price' => '',
            'stock_status' => 'in_stock',
            'total_stock' => 9,
            'manage_stock' => 1,
            'status' => 1,
            'specs' => [['label' => 'Chipset', 'value' => 'Snapdragon 7'], ['label' => 'Display', 'value' => '6.7" AMOLED']],
        ])->assertRedirect(route('admin.products.show', $product->id));

        $product = $product->fresh('variants');
        $this->assertSame(['Chipset', 'Display'], array_keys($product->specs));
        $this->assertNull($product->variants->sole()->compare_price);
        $this->assertFalse($product->featured);
        $this->assertSame(9, $product->total_stock, 'Ending the sale keeps the stock typed in the form');
    }

    public function test_two_products_can_have_the_same_name(): void
    {
        $category = Category::factory()->create();
        $this->actingAs($this->admin());
        $cable = ['name' => 'USB-C Cable', 'category_id' => $category->id, 'base_price' => 450, 'stock_status' => 'in_stock', 'status' => 1];

        $this->post(route('admin.products.store'), $cable)->assertSessionHasNoErrors();
        // Typing the same slug is fine too: no URL uses it
        $this->post(route('admin.products.store'), $cable + ['slug' => 'usb-c-cable'])->assertSessionHasNoErrors();

        $this->assertSame(['usb-c-cable', 'usb-c-cable'], ProductOptimized::where('name', 'USB-C Cable')->pluck('slug')->all());
    }

    public function test_a_was_price_below_the_price_is_refused(): void
    {
        $product = ProductOptimized::factory()->create(['base_price' => 30000]);

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product->id), [
                'name' => $product->name, 'category_id' => $product->category_id, 'base_price' => 30000,
                'compare_price' => 25000, 'stock_status' => 'in_stock', 'status' => 1,
            ])
            ->assertSessionHasErrors('compare_price');
    }

    public function test_a_store_wide_attribute_cannot_block_saving(): void
    {
        $category = Category::factory()->create();
        // An optional attribute first: the check must go on past it
        AttributeOptimized::create(['name' => 'Color', 'type' => 'select', 'required' => false, 'status' => true, 'sort_order' => 1]);
        AttributeOptimized::create(['name' => 'Size', 'type' => 'select', 'required' => true, 'status' => true, 'sort_order' => 2]);
        $scoped = AttributeOptimized::create(['name' => 'Screen', 'type' => 'select', 'required' => true, 'status' => true, 'category_id' => $category->id, 'sort_order' => 3]);

        $attributes = collect($this->actingAs($this->admin())->getJson("/admin/products/categories/{$category->id}/attributes")->json('attributes'))->keyBy('name');

        $this->assertFalse($attributes['Color']['required']);
        $this->assertFalse($attributes['Size']['required']);
        $this->assertTrue($attributes['Screen']['required']);
        $this->assertSame($scoped->id, $attributes['Screen']['id']);
    }

    public function test_product_images_can_be_deleted(): void
    {
        Storage::fake('public');
        $product = ProductOptimized::factory()->create();
        Storage::disk('public')->put('products/a.jpg', UploadedFile::fake()->image('a.jpg', 100, 100)->get());
        $image = ProductImageOptimized::create(['product_id' => $product->id, 'url' => 'products/a.jpg', 'is_main' => true]);

        $this->actingAs($this->admin())
            ->deleteJson("/admin/products/images/{$image->id}")
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertModelMissing($image);
    }

    public function test_settings_show_the_real_configuration_and_send_a_test_email(): void
    {
        Mail::fake();
        config(['mail.from.address' => 'shop@example.com']);
        $admin = $this->admin(['name' => 'Asad', 'email' => 'admin@example.com']);

        $this->actingAs($admin)->get('/admin/settings')->assertOk()
            ->assertSee('shop@example.com')
            ->assertSee('php artisan admin:password')
            ->assertDontSee('info@multishop.com');

        $this->post('/admin/settings/test-email')
            ->assertRedirect('/admin/settings')
            ->assertSessionHas('success', 'Test email sent to shop@example.com. Check that inbox (and its spam folder).');
        Mail::assertSent(TestEmail::class, fn (TestEmail $mail) => $mail->hasTo('shop@example.com') && ! $mail->hasTo('admin@example.com'));
    }

    public function test_admin_accounts_cannot_be_reset_by_email(): void
    {
        Notification::fake();
        $admin = $this->admin(['email' => 'boss@example.com', 'password' => 'the-old-one']);

        $this->from('/forgot-password')->post('/forgot-password', ['email' => 'boss@example.com'])
            ->assertSessionHas('status', "If there's an account for boss@example.com, we've emailed it a link to set a new password.");
        Notification::assertNothingSent();

        // Even with a token, the reset form won't change an admin's password
        $token = app('auth.password.broker')->createToken($admin);
        $this->post('/reset-password', ['token' => $token, 'email' => 'boss@example.com', 'password' => 'new-password', 'password_confirmation' => 'new-password'])
            ->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('the-old-one', $admin->fresh()->password));

        // The server command does it
        $this->artisan('admin:password', ['email' => 'boss@example.com', '--password' => 'brand-new-pass'])->assertSuccessful();
        $this->assertTrue(Hash::check('brand-new-pass', $admin->fresh()->password));
        $this->artisan('admin:password', ['email' => 'boss@example.com', '--password' => 'short'])->assertFailed();
    }

    private function order(array $attributes = []): Order
    {
        return Order::create($attributes + [
            'order_number' => Order::generateOrderNumber(),
            'customer_name' => 'Rahim Uddin',
            'customer_email' => 'rahim@example.com',
            'customer_phone' => '01711000001',
            'billing_address' => 'Dhaka',
            'shipping_address' => 'Dhaka',
            'subtotal' => 200,
            'shipping_cost' => 10,
            'total' => 210,
            'payment_method' => 'cod',
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    private function admin(array $attributes = []): User
    {
        $admin = User::factory()->create($attributes);
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
