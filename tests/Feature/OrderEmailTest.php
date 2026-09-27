<?php

namespace Tests\Feature;

use App\Mail\OrderPlaced;
use App\Mail\OrderStatusChanged;
use App\Models\Order;
use App\Models\ProductOptimized;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class OrderEmailTest extends TestCase
{
    use RefreshDatabase;

    private ?string $sessionId = null;

    public function test_placing_an_order_emails_a_confirmation_to_the_checkout_address(): void
    {
        Mail::fake();
        $customer = User::factory()->create(['email' => 'account@example.com']);

        $order = $this->placeOrder($customer, ['email' => 'rahim@example.com']);

        Mail::assertSent(OrderPlaced::class, fn (OrderPlaced $mail) => $mail->hasTo('rahim@example.com') && $mail->order->is($order));
        Mail::assertSentCount(1);

        $mail = new OrderPlaced($order);
        $this->assertSame("We've received your order {$order->order_number}", $mail->envelope()->subject);
        $html = $mail->render();
        $this->assertStringContainsString('Thanks for your order, Rahim', $html);
        $this->assertStringContainsString('Galaxy Buds', $html);
        $this->assertStringContainsString('৳200', $html, 'The line total');
        $this->assertStringContainsString('৳210', $html, 'The order total with delivery');
        $this->assertStringContainsString('Cash on delivery', $html);
        $this->assertStringContainsString(route('customer.orders.show', $order), $html, 'Account orders link to the order');
    }

    public function test_guest_orders_are_emailed_without_an_account_link(): void
    {
        Mail::fake();

        $order = $this->placeOrder();

        Mail::assertSent(OrderPlaced::class, fn (OrderPlaced $mail) => $mail->hasTo('rahim@example.com'));
        $this->assertStringNotContainsString('View your order', (new OrderPlaced($order))->render());
    }

    public function test_the_confirmation_page_says_where_the_email_went(): void
    {
        Mail::fake();

        $order = $this->placeOrder();

        $this->guest('get', route('order.success', $order->id))
            ->assertOk()
            ->assertSee("We've emailed a confirmation to <strong>rahim@example.com</strong>", false);
    }

    public function test_status_changes_email_the_customer(): void
    {
        Mail::fake();
        $order = $this->placeOrder(User::factory()->create());

        $this->actingAs($admin = $this->admin())
            ->put(route('admin.orders.updateStatus', $order->id), ['status' => 'shipped', 'notify_customer' => 1])
            ->assertRedirect(route('admin.orders.show', $order->id))
            ->assertSessionHas('success', "{$order->order_number} marked as Shipped. We've emailed rahim@example.com.");

        $this->assertSame('shipped', $order->fresh()->status);
        Mail::assertSent(OrderStatusChanged::class, fn (OrderStatusChanged $mail) => $mail->hasTo('rahim@example.com'));

        // Logged against the order: the confirmation (by the shop) and this update (by the admin)
        $log = $order->emails()->get();
        $this->assertSame(['status', 'placed'], $log->pluck('kind')->all());
        $this->assertTrue($log[0]->sent);
        $this->assertSame($admin->id, $log[0]->user_id);
        $this->assertSame("Order {$order->order_number} is on its way", $log[0]->subject);
        $this->assertNull($log[1]->user_id);

        $mail = new OrderStatusChanged($order->fresh());
        $this->assertSame("Order {$order->order_number} is on its way", $mail->envelope()->subject);
        $html = $mail->render();
        $this->assertStringContainsString('Your order is on its way', $html);
        // The mail's inlined CSS adds a style to <strong>
        $this->assertMatchesRegularExpression('#please have <strong[^>]*>৳210</strong> ready#u', $html, 'Cash on delivery: the amount to have ready');
    }

    public function test_each_status_has_its_own_subject(): void
    {
        $order = $this->placeOrder();

        foreach (['processing' => 'is being prepared', 'delivered' => 'has been delivered', 'cancelled' => 'has been cancelled'] as $status => $subject) {
            $order->status = $status;
            $this->assertSame("Order {$order->order_number} {$subject}", (new OrderStatusChanged($order))->envelope()->subject);
        }
        $this->assertFalse(OrderStatusChanged::covers('pending'));
    }

    public function test_no_email_for_an_unchanged_status_a_return_to_pending_or_an_unticked_box(): void
    {
        Mail::fake();
        $order = $this->placeOrder();
        $this->actingAs($this->admin());
        Mail::assertSentCount(1);

        // Same status
        $this->put(route('admin.orders.updateStatus', $order->id), ['status' => 'pending', 'notify_customer' => 1])
            ->assertSessionHas('success', 'The order was already Pending.');
        // Box unticked
        $this->put(route('admin.orders.updateStatus', $order->id), ['status' => 'processing'])
            ->assertSessionHas('success', "{$order->order_number} marked as Processing.");
        // Back to pending is a correction, not news
        $this->put(route('admin.orders.updateStatus', $order->id), ['status' => 'pending', 'notify_customer' => 1]);

        $this->assertSame('pending', $order->fresh()->status);
        Mail::assertNotSent(OrderStatusChanged::class);
        Mail::assertSentCount(1);
    }

    public function test_customers_cannot_change_order_status(): void
    {
        Mail::fake();
        $order = $this->placeOrder();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.orders.updateStatus', $order->id), ['status' => 'cancelled'])
            ->assertForbidden();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_the_checkout_redirect_says_how_long_it_is(): void
    {
        Mail::fake();
        $product = ProductOptimized::factory()->create();
        $this->guest('postJson', '/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        // Outside PHP-FPM the connection stays open while the email goes out after the response;
        // with a Content-Length the browser follows the redirect without waiting for Gmail
        $response = $this->guest('post', '/checkout', [
            'first_name' => 'Rahim', 'last_name' => 'Uddin', 'email' => 'rahim@example.com', 'phone' => '01711000001',
            'address_line_1' => 'House 12', 'city' => 'Dhaka', 'state' => 'Dhaka', 'zip_code' => '1205',
            'country' => 'Bangladesh', 'payment_method' => 'cod',
        ])->assertRedirect();

        $response->assertHeader('Content-Length', (string) strlen($response->getContent()));
    }

    public function test_an_unreachable_mail_server_does_not_stop_checkout(): void
    {
        Exceptions::fake();
        // Nothing listens on port 1, so the send fails straight away
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.timeout' => 2]);

        $order = $this->placeOrder();

        $this->assertSame(1, Order::count());
        $this->guest('get', route('order.success', $order->id))->assertOk();
        Exceptions::assertReported(TransportException::class);

        // The failure is on the order's email log, with the reason, for the admin to see and resend
        $email = $order->emails()->sole();
        $this->assertFalse($email->sent);
        $this->assertStringContainsString('Connection could not be established', $email->error);
    }

    /**
     * Two Galaxy Buds at ৳100 by cash on delivery (৳210 with delivery). Guests keep one session cookie, as a browser would.
     */
    private function placeOrder(?User $customer = null, array $form = []): Order
    {
        $product = ProductOptimized::factory()->create(['name' => 'Galaxy Buds', 'base_price' => 100]);
        if ($customer) {
            $this->actingAs($customer);
        }

        $this->guest('postJson', '/cart/add', ['product_id' => $product->id, 'quantity' => 2])->assertOk();
        $this->guest('post', '/checkout', $form + [
            'first_name' => 'Rahim',
            'last_name' => 'Uddin',
            'email' => 'rahim@example.com',
            'phone' => '01711000001',
            'address_line_1' => 'House 12, Road 5',
            'city' => 'Dhaka',
            'state' => 'Dhaka',
            'zip_code' => '1205',
            'country' => 'Bangladesh',
            'payment_method' => 'cod',
        ])->assertRedirect();

        return Order::with('orderItems')->latest('id')->firstOrFail();
    }

    private function guest(string $method, string $uri, array $data = []): TestResponse
    {
        if ($this->sessionId) {
            $this->withCookie(config('session.cookie'), $this->sessionId);
        }

        $response = $method === 'get' ? $this->get($uri) : $this->{$method}($uri, $data);
        $this->sessionId = $this->app['session']->getId();

        return $response;
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }
}
