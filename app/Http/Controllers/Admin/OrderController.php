<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderPlaced;
use App\Mail\OrderStatusChanged;
use App\Models\Order;
use App\Support\CustomerMail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = array_key_exists((string) $request->query('status'), Order::STATUSES) ? $request->query('status') : null;
        $search = trim((string) $request->query('q', ''));
        $failedEmails = $request->boolean('failed_emails');

        $orders = Order::query()
            ->withCount('orderItems')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($failedEmails, fn ($query) => $query->latestEmailFailed())
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('order_number', 'like', "%{$search}%")
                ->orWhere('customer_name', 'like', "%{$search}%")
                ->orWhere('customer_email', 'like', "%{$search}%")
                ->orWhere('customer_phone', 'like', "%{$search}%")))
            ->latest()
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Only the open statuses' tabs get a count, whatever is filtered: those are the short lists staff work
        // through, and counting delivered and cancelled orders would read most of the table on every visit
        $openStatuses = array_keys(Order::NEXT_STEPS);
        $statusCounts = collect(array_fill_keys($openStatuses, 0))->merge(
            Order::whereIn('status', $openStatuses)
                ->selectRaw('status, count(*) as orders_count')
                ->groupBy('status')
                ->pluck('orders_count', 'status')
                ->map(fn ($count) => (int) $count)
        );
        $failedEmailCount = Order::latestEmailFailed()->count();

        return view('admin.orders.index', compact('orders', 'statusCounts', 'status', 'search', 'failedEmails', 'failedEmailCount'));
    }

    public function show($id)
    {
        $order = Order::with([
            'orderItems.product.mainImage',
            'emails.user',
            'statusChanges.user',
            'user' => fn ($user) => $user->withCount('orders'),
        ])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Moves an order to a status: the workflow's next-step buttons and the manual correction form both post here.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'note' => 'nullable|string|max:500',
        ]);

        $order = Order::findOrFail($id);
        $admin = $request->user();

        if (! $order->changeStatus($request->status, $admin, $request->input('note'))) {
            return $this->backTo($request, $order)->with('success', "The order was already {$order->status_label}.");
        }

        // Cash on delivery: the courier collected the money on delivery
        if ($request->boolean('mark_paid') && $order->status === 'delivered' && $order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'paid']);
        }

        $message = "{$order->order_number} marked as {$order->status_label}.";
        $failed = false;
        if ($request->boolean('notify_customer') && OrderStatusChanged::covers($order->status)) {
            $failed = ! CustomerMail::send($order->customer_email, new OrderStatusChanged($order), $admin);
            $message .= $failed
                ? " The email to {$order->customer_email} failed; resend it from the order's Emails panel."
                : " We've emailed {$order->customer_email}.";
        }

        return $this->backTo($request, $order)->with($failed ? 'error' : 'success', $message);
    }

    /**
     * Sends the confirmation or the current status email again (after a failure, or on request).
     */
    public function resendEmail(Request $request, $id)
    {
        $request->validate(['kind' => ['required', Rule::in(['placed', 'status'])]]);
        $order = Order::findOrFail($id);

        if ($request->kind === 'status' && ! OrderStatusChanged::covers($order->status)) {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('error', "There's no status email for a {$order->status_label} order.");
        }

        $mail = $request->kind === 'placed' ? new OrderPlaced($order) : new OrderStatusChanged($order);
        if (CustomerMail::send($order->customer_email, $mail, $request->user())) {
            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', "Sent “{$mail->envelope()->subject}” to {$order->customer_email}.");
        }

        return redirect()->route('admin.orders.show', $order->id)
            ->with('error', "The email to {$order->customer_email} failed: " . ($order->emails()->value('error') ?? 'unknown error') . ' Check the mail settings.');
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed'
        ]);

        $order = Order::findOrFail($id);
        $order->payment_status = $request->payment_status;
        $order->save();

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Payment marked as ' . ucfirst($order->payment_status) . '.');
    }

    /**
     * Quick actions on the orders list go back to the list (with its filters); everything else to the order.
     */
    private function backTo(Request $request, Order $order)
    {
        return $request->input('from') === 'list'
            ? redirect()->back()
            : redirect()->route('admin.orders.show', $order->id);
    }
}
