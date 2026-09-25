<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductOptimized;
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

        $this->actingAs($user)->get('/customer/orders')->assertOk()->assertSee($order->order_number)->assertSee('60.00');
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
