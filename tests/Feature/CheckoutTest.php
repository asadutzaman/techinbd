<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\ProductOptimized;
use App\Models\ProductVariantOptimized;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private ?string $sessionId = null;

    public function test_guest_can_add_to_cart_and_check_out(): void
    {
        $product = ProductOptimized::factory()->create(['base_price' => 100]);

        $this->guest('postJson', '/cart/add', ['product_id' => $product->id, 'quantity' => 2])
            ->assertOk()
            ->assertJson(['success' => true, 'cart_count' => 2]);

        $this->assertSame('100.00', Cart::first()->price, 'Cart stores the product base price');

        $response = $this->guest('post', '/checkout', $this->checkoutForm());

        $order = Order::with('orderItems')->sole();
        $response->assertRedirect(route('order.success', $order->id));
        $this->assertNull($order->user_id);
        $this->assertSame('200.00', $order->subtotal);
        $this->assertSame('210.00', $order->total);
        $this->assertCount(1, $order->orderItems);
        $this->assertSame(0, Cart::count(), 'Cart is cleared after checkout');

        $this->guest('get', route('order.success', $order->id))->assertOk()->assertSee($order->order_number);
    }

    public function test_order_confirmation_is_private(): void
    {
        $order = $this->placeGuestOrder();

        // A different visitor (fresh session) can't read someone else's order
        $this->defaultCookies = [];
        $this->flushSession();
        $this->get(route('order.success', $order->id))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('order.success', $order->id))->assertForbidden();
    }

    public function test_logged_in_user_checks_out_their_own_cart(): void
    {
        $user = User::factory()->create();
        $product = ProductOptimized::factory()->create(['base_price' => 50]);

        $this->actingAs($user)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertOk();
        $this->actingAs($user)->get('/checkout')->assertOk()->assertSee($product->name);
        $this->actingAs($user)->post('/checkout', $this->checkoutForm());

        $order = Order::sole();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('60.00', $order->total);

        $this->actingAs($user)->get('/customer/orders')->assertOk()->assertSee($order->order_number)->assertSeeText('৳Tk 60');
        $this->actingAs($user)->get(route('customer.orders.show', $order))->assertOk()->assertSee($product->name);
        $this->actingAs($user)->get('/customer/dashboard')->assertOk()->assertSee($order->order_number);
    }

    public function test_login_merges_the_guest_cart(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);
        $product = ProductOptimized::factory()->create();
        Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => $product->base_price]);

        $this->guest('postJson', '/cart/add', ['product_id' => $product->id, 'quantity' => 2])->assertOk();
        $this->guest('post', '/login', ['email' => $user->email, 'password' => 'secret123'])->assertRedirect();

        $this->assertSame(3, (int) Cart::where('user_id', $user->id)->sum('quantity'));
        $this->assertSame(0, Cart::whereNull('user_id')->count());
    }

    public function test_customer_can_pay_cash_on_delivery(): void
    {
        $product = ProductOptimized::factory()->create();

        $this->guest('postJson', '/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $this->guest('get', '/checkout')->assertOk()->assertSee('Cash on delivery');
        $this->guest('post', '/checkout', ['payment_method' => 'cod'] + $this->checkoutForm());

        $order = Order::sole();
        $this->assertSame('cod', $order->payment_method);
        $this->guest('get', route('order.success', $order->id))->assertOk()->assertSee('Cash on delivery');
    }

    public function test_unknown_payment_methods_are_rejected(): void
    {
        $product = ProductOptimized::factory()->create();

        $this->guest('postJson', '/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $this->guest('post', '/checkout', ['payment_method' => 'bitcoin'] + $this->checkoutForm())
            ->assertSessionHasErrors('payment_method');
        $this->assertSame(0, Order::count());
    }

    public function test_inactive_products_cannot_be_added(): void
    {
        $product = ProductOptimized::factory()->inactive()->create();

        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertNotFound();
        $this->assertSame(0, Cart::count());
    }

    public function test_customer_can_reorder(): void
    {
        $user = User::factory()->create();
        $product = ProductOptimized::factory()->create();
        $this->actingAs($user)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        $this->actingAs($user)->post('/checkout', $this->checkoutForm());

        $this->actingAs($user)->post(route('customer.orders.reorder', Order::sole()))->assertRedirect(route('cart'));
        $this->assertSame(2, (int) Cart::where('user_id', $user->id)->sum('quantity'));
    }

    public function test_an_option_is_charged_at_its_own_price_and_kept_with_the_order(): void
    {
        $user = User::factory()->create();
        [$product, $small, $large] = $this->productWithOptions();

        foreach ([$large, $small, $large] as $option) {
            $this->actingAs($user)->postJson('/cart/add', ['product_id' => $product->id, 'variant_id' => $option->id, 'quantity' => 1])->assertOk();
        }

        // Each option is its own cart line, at its own price
        $lines = Cart::where('user_id', $user->id)->orderBy('price')->get()
            ->map(fn ($line) => [$line->variant_id, $line->quantity, $line->price])->all();
        $this->assertSame([[$small->id, 1, '1000.00'], [$large->id, 2, '1500.00']], $lines);
        $this->actingAs($user)->get('/cart')->assertOk()->assertSee('Option: 512GB');
        $this->actingAs($user)->get('/checkout')->assertOk()->assertSee('(512GB)');

        $this->actingAs($user)->post('/checkout', $this->checkoutForm());

        $order = Order::with('orderItems')->sole();
        $this->assertSame('4000.00', $order->subtotal);
        $item = $order->orderItems->firstWhere('variant_id', $large->id);
        $this->assertSame('Galaxy Test (512GB)', $item->product_name);
        $this->assertSame('1500.00', $item->product_price);

        // Reordering brings the option back, at its price
        $this->actingAs($user)->post(route('customer.orders.reorder', $order));
        $this->assertSame('1500.00', Cart::where('user_id', $user->id)->where('variant_id', $large->id)->sole()->price);
    }

    public function test_an_option_of_another_product_is_rejected(): void
    {
        [$product] = $this->productWithOptions();
        [, , $otherOption] = $this->productWithOptions('Other Phone');

        $this->postJson('/cart/add', ['product_id' => $product->id, 'variant_id' => $otherOption->id, 'quantity' => 1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('variant_id');
        $this->assertSame(0, Cart::count());
    }

    public function test_login_keeps_the_guest_options_apart(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);
        [$product, $small, $large] = $this->productWithOptions();
        Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'variant_id' => $small->id, 'quantity' => 1, 'price' => 1000]);

        $this->guest('postJson', '/cart/add', ['product_id' => $product->id, 'variant_id' => $large->id, 'quantity' => 1])->assertOk();
        $this->guest('post', '/login', ['email' => $user->email, 'password' => 'secret123'])->assertRedirect();

        $this->assertSame(2, Cart::where('user_id', $user->id)->count());
        $this->assertSame('1500.00', Cart::where('user_id', $user->id)->where('variant_id', $large->id)->sole()->price);
    }

    public function test_signed_in_customers_start_checkout_with_their_saved_details(): void
    {
        $user = User::factory()->create(['name' => 'Rahim Uddin', 'email' => 'rahim@example.com', 'phone' => '01711000001']);
        CustomerAddress::create([
            'user_id' => $user->id, 'type' => 'shipping', 'is_default' => true,
            'first_name' => 'Rahim', 'last_name' => 'Uddin', 'phone' => '01711000001',
            'address_line_1' => 'House 12, Road 5', 'address_line_2' => 'Dhanmondi',
            'city' => 'Dhaka', 'state' => 'Dhaka', 'postal_code' => '1205', 'country' => 'Bangladesh',
        ]);
        $product = ProductOptimized::factory()->create(['base_price' => 100]);
        $this->actingAs($user)->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $html = $this->actingAs($user)->get('/checkout')->assertOk()->getContent();

        foreach (['first_name' => 'Rahim', 'last_name' => 'Uddin', 'email' => 'rahim@example.com', 'phone' => '01711000001',
            'address_line_1' => 'House 12, Road 5', 'city' => 'Dhaka', 'state' => 'Dhaka', 'zip_code' => '1205'] as $field => $value) {
            $this->assertStringContainsString('name="' . $field . '" value="' . e($value) . '"', $html, "{$field} is filled in");
        }
        $this->assertStringContainsString('<option value="Bangladesh" selected>', $html);
    }

    public function test_guests_check_out_in_bangladesh_with_taka_prices(): void
    {
        $product = ProductOptimized::factory()->create(['base_price' => 100]);
        $this->guest('postJson', '/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->guest('get', '/checkout')->assertOk();

        $response->assertSee('<option value="Bangladesh" selected>', false)
            ->assertSee('name="email" value=""', false)
            ->assertSeeText('৳Tk 110')
            ->assertDontSee('name="create_account"', false)
            ->assertDontSee('name="ship_to_different"', false);
    }

    /**
     * A product at ৳1,000 with two options: "256GB" at the product price and "512GB" at ৳1,500.
     */
    private function productWithOptions(string $name = 'Galaxy Test'): array
    {
        $product = ProductOptimized::factory()->create(['name' => $name, 'base_price' => 1000]);
        $options = [];
        foreach (['256GB' => null, '512GB' => 1500] as $optionName => $price) {
            $option = new ProductVariantOptimized(['name' => $optionName, 'price' => $price, 'stock' => 5, 'is_default' => $price === null]);
            $option->product()->associate($product);
            $option->save();
            $options[] = $option;
        }

        return [$product, ...$options];
    }

    private function placeGuestOrder(): Order
    {
        $product = ProductOptimized::factory()->create();
        $this->guest('postJson', '/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $this->guest('post', '/checkout', $this->checkoutForm());

        return Order::sole();
    }

    /**
     * Guest carts are keyed by session id, so keep sending the same session cookie like a browser would.
     */
    private function guest(string $method, string $uri, array $data = []): TestResponse
    {
        if ($this->sessionId) {
            $this->withCookie(config('session.cookie'), $this->sessionId);
        }

        $response = $method === 'get' ? $this->get($uri) : $this->{$method}($uri, $data);
        $this->sessionId = $this->app['session']->getId();

        return $response;
    }

    private function checkoutForm(): array
    {
        return [
            'first_name' => 'Rahim',
            'last_name' => 'Uddin',
            'email' => 'rahim@example.com',
            'phone' => '01700000000',
            'address_line_1' => '12 Road',
            'city' => 'Dhaka',
            'state' => 'Dhaka',
            'zip_code' => '1207',
            'country' => 'Bangladesh',
            'payment_method' => 'banktransfer',
        ];
    }
}
