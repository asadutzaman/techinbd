<?php

namespace Tests\Feature;

use App\Mail\OrderPlaced;
use App\Mail\OrderStatusChanged;
use App\Models\Order;
use App\Models\OrderEmail;
use App\Models\ProductOptimized;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_order_list_searches_and_filters_by_status(): void
    {
        $rahim = $this->order(['customer_name' => 'Rahim Uddin', 'customer_email' => 'rahim@example.com']);
        $nusrat = $this->order(['customer_name' => 'Nusrat Jahan', 'customer_email' => 'nusrat@example.com', 'status' => 'shipped']);
        $this->order(['customer_name' => 'Karim Hasan', 'customer_email' => 'karim@example.com', 'status' => 'shipped']);

        $this->actingAs($this->admin());

        $this->assertSame([$rahim->order_number], $this->listed('/admin/orders?q=rahim@'));
        $this->assertSame([$nusrat->order_number], $this->listed('/admin/orders?q=' . $nusrat->order_number));
        $this->assertCount(2, $this->listed('/admin/orders?status=shipped'));

        // The open statuses' tabs are counted, in whole numbers; All, Delivered and Cancelled aren't
        $this->order(['customer_name' => 'Farhana Akter', 'customer_email' => 'farhana@example.com', 'status' => 'delivered']);
        $html = $this->get('/admin/orders?status=shipped')->getContent();
        $this->assertMatchesRegularExpression('#Shipped\s*<span class="badge badge-light ml-1">2</span>#', $html);
        $this->assertMatchesRegularExpression('#Pending\s*<span class="badge badge-secondary ml-1">1</span>#', $html);
        $this->assertMatchesRegularExpression('#Processing\s*<span class="badge badge-secondary ml-1">0</span>#', $html);
        $this->assertMatchesRegularExpression('#All\s*</a>#', $html);
        $this->assertMatchesRegularExpression('#Delivered\s*</a>#', $html);
        $this->assertStringNotContainsString('2.00', $html);
    }

    public function test_order_lists_are_indexed(): void
    {
        // Without these, every admin page scans the whole orders table for the pending badge, and lists sort all of it
        $this->assertTrue(Schema::hasIndex('orders', ['status', 'created_at']));
        $this->assertTrue(Schema::hasIndex('orders', ['created_at']));
        $this->assertTrue(Schema::hasIndex('orders', ['last_email_failed']));
    }

    public function test_the_list_offers_the_next_step_and_it_emails_the_customer(): void
    {
        Mail::fake();
        $order = $this->order();
        $this->actingAs($this->admin());

        $row = Str::betweenFirst($this->get('/admin/orders')->getContent(), $order->order_number, '</tr>');
        $this->assertStringContainsString('>Confirm order</button>', $row);
        $this->assertStringNotContainsString('Cancel order', $row, 'Cancelling is done from the order page');

        $this->from('/admin/orders?status=pending')
            ->put(route('admin.orders.updateStatus', $order->id), ['status' => 'processing', 'notify_customer' => 1, 'from' => 'list'])
            ->assertRedirect('/admin/orders?status=pending');

        $this->assertSame('processing', $order->fresh()->status);
        Mail::assertSent(OrderStatusChanged::class);
    }

    public function test_the_order_page_shows_the_steps_the_order_can_take(): void
    {
        $this->actingAs($this->admin());

        $pending = $this->get(route('admin.orders.show', $this->order()->id))->getContent();
        $this->assertStringContainsString('value="processing" class="btn btn-primary"', $pending);
        $this->assertStringContainsString('value="cancelled"', $pending);
        $this->assertStringContainsString('Confirm order', $pending);

        $shipped = $this->get(route('admin.orders.show', $this->order(['status' => 'shipped', 'payment_method' => 'cod'])->id))->getContent();
        $this->assertStringContainsString('Mark as delivered', $shipped);
        $this->assertStringContainsString('name="mark_paid"', $shipped, 'Cash on delivery can be marked paid on delivery');

        $delivered = $this->get(route('admin.orders.show', $this->order(['status' => 'delivered'])->id))->getContent();
        $this->assertStringContainsString('Delivered: nothing left to do.', $delivered);
        $this->assertStringNotContainsString('name="mark_paid"', $delivered);
    }

    public function test_each_step_is_recorded_with_who_did_it_and_their_note(): void
    {
        Mail::fake();
        $admin = $this->admin(['name' => 'Sadia (admin)']);
        $order = $this->order(['payment_method' => 'cod']);
        $this->actingAs($admin);

        $this->put(route('admin.orders.updateStatus', $order->id), ['status' => 'processing', 'notify_customer' => 1]);
        $this->put(route('admin.orders.updateStatus', $order->id), ['status' => 'shipped', 'notify_customer' => 1, 'note' => 'Courier: Pathao, tracking 12345']);
        $this->put(route('admin.orders.updateStatus', $order->id), ['status' => 'delivered', 'notify_customer' => 1, 'mark_paid' => 1]);

        $order->refresh();
        $this->assertSame(['delivered', 'paid'], [$order->status, $order->payment_status]);
        $this->assertSame(
            [['pending', 'processing'], ['processing', 'shipped'], ['shipped', 'delivered']],
            $order->statusChanges->map(fn ($change) => [$change->from_status, $change->to_status])->all()
        );
        $this->assertSame('Courier: Pathao, tracking 12345', $order->statusChanges[1]->note);
        $this->assertSame($admin->id, $order->statusChanges[2]->user_id);

        $html = $this->get(route('admin.orders.show', $order->id))->getContent();
        $this->assertStringContainsString('Processing → Shipped</strong>', $html);
        $this->assertStringContainsString('“Courier: Pathao, tracking 12345”', $html);
        $this->assertSame(3, substr_count($html, 'by Sadia (admin)'));
        $this->assertSame(3, $order->emails()->where('sent', true)->count());
    }

    public function test_the_email_log_shows_what_went_and_what_failed(): void
    {
        $order = $this->order();
        $admin = $this->admin(['name' => 'Asad']);
        OrderEmail::create(['order_id' => $order->id, 'kind' => 'placed', 'order_status' => 'pending', 'recipient' => 'rahim@example.com',
            'subject' => "We've received your order {$order->order_number}", 'sent' => true]);
        OrderEmail::create(['order_id' => $order->id, 'kind' => 'status', 'order_status' => 'processing', 'recipient' => 'rahim@example.com',
            'subject' => "Order {$order->order_number} is being prepared", 'sent' => false, 'error' => 'Failed to authenticate on SMTP server', 'user_id' => $admin->id]);

        $html = $this->actingAs($admin)->get(route('admin.orders.show', $order->id))->getContent();
        $emails = Str::betweenFirst($html, 'id="emails"', '</table>');

        $this->assertStringContainsString('Order confirmation', $emails);
        $this->assertStringContainsString('Shop (automatic)', $emails);
        $this->assertStringContainsString('Status: Processing', $emails);
        $this->assertStringContainsString('Failed to authenticate on SMTP server', $emails);
        $this->assertStringContainsString('>Asad</td>', $emails);

        // The list flags orders whose latest email failed
        $this->assertStringContainsString('email failed', $this->get('/admin/orders')->getContent());
        $this->assertSame([$order->order_number], $this->listed('/admin/orders?failed_emails=1'));
    }

    public function test_the_failed_flag_follows_the_latest_email(): void
    {
        $order = $this->order();
        $updatedAt = $order->updated_at->toDateTimeString();
        $this->travel(5)->minutes();
        $log = fn (bool $sent) => OrderEmail::create(['order_id' => $order->id, 'kind' => 'status', 'order_status' => 'pending',
            'recipient' => 'rahim@example.com', 'subject' => "Order {$order->order_number}", 'sent' => $sent]);

        $log(false);
        $this->assertTrue($order->fresh()->last_email_failed);
        $this->assertSame(1, Order::latestEmailFailed()->count());

        // Sent again, and this time it went
        $log(true);
        $this->assertFalse($order->fresh()->last_email_failed);
        $this->assertSame(0, Order::latestEmailFailed()->count());
        $this->assertSame($updatedAt, $order->fresh()->updated_at->toDateTimeString(), 'Logging an email is not a change to the order');
    }

    public function test_an_email_can_be_sent_again(): void
    {
        Mail::fake();
        $order = $this->order(['status' => 'shipped']);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.orders.resendEmail', $order->id), ['kind' => 'placed'])
            ->assertRedirect(route('admin.orders.show', $order->id))
            ->assertSessionHas('success', "Sent “We've received your order {$order->order_number}” to rahim@example.com.");
        $this->post(route('admin.orders.resendEmail', $order->id), ['kind' => 'status'])->assertSessionHas('success');

        Mail::assertSent(OrderPlaced::class);
        Mail::assertSent(OrderStatusChanged::class);
        $this->assertSame(['status', 'placed'], $order->emails()->pluck('kind')->all());
        $this->assertSame([$admin->id, $admin->id], $order->emails()->pluck('user_id')->all());

        // A pending order has no status email to send
        $pending = $this->order();
        $this->post(route('admin.orders.resendEmail', $pending->id), ['kind' => 'status'])
            ->assertSessionHas('error', "There's no status email for a Pending order.");
    }

    public function test_a_failed_email_is_reported_to_the_admin_and_logged(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.timeout' => 2]);
        $order = $this->order();

        $this->actingAs($this->admin())
            ->put(route('admin.orders.updateStatus', $order->id), ['status' => 'processing', 'notify_customer' => 1])
            ->assertSessionHas('error', "{$order->order_number} marked as Processing. The email to rahim@example.com failed; resend it from the order's Emails panel.");

        $this->assertSame('processing', $order->fresh()->status, 'The status change stands');
        $this->assertFalse($order->emails()->sole()->sent);
        $this->assertTrue($order->fresh()->last_email_failed);
    }

    public function test_customers_cannot_use_the_workflow(): void
    {
        $order = $this->order();
        $this->actingAs(User::factory()->create());

        $this->get('/admin/orders')->assertForbidden();
        $this->post(route('admin.orders.resendEmail', $order->id), ['kind' => 'placed'])->assertForbidden();
    }

    private function order(array $attributes = []): Order
    {
        $product = ProductOptimized::factory()->create(['base_price' => 100]);
        $order = Order::create($attributes + [
            'order_number' => Order::generateOrderNumber(),
            'customer_name' => 'Rahim Uddin',
            'customer_email' => 'rahim@example.com',
            'customer_phone' => '01711000001',
            'billing_address' => 'House 12, Road 5, Dhaka 1205, Bangladesh',
            'shipping_address' => 'House 12, Road 5, Dhaka 1205, Bangladesh',
            'subtotal' => 200,
            'shipping_cost' => 10,
            'total' => 210,
            'payment_method' => 'cod',
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
        $order->orderItems()->create(['product_id' => $product->id, 'product_name' => $product->name, 'product_price' => 100, 'quantity' => 2, 'total' => 200]);

        return $order;
    }

    private function admin(array $attributes = []): User
    {
        $admin = User::factory()->create($attributes);
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }

    /**
     * Order numbers on an orders list page, in order.
     */
    private function listed(string $uri): array
    {
        preg_match_all('#class="font-weight-bold">(ORD-\d{4}-\d{5})</a>#', $this->get($uri)->assertOk()->getContent(), $matches);

        return $matches[1];
    }
}
