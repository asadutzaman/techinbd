<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at', 'desc')->paginate(15);

        // Stats cover all orders, not just the current page
        $statusCounts = Order::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.orders.index', compact('orders', 'statusCounts'));
    }

    public function show($id)
    {
        $order = Order::with('orderItems.product.mainImage')->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
        ]);

        $order = Order::findOrFail($id);
        $notify = $request->boolean('notify_customer');

        if (! $order->changeStatus($request->status, $notify)) {
            return redirect()->route('admin.orders.show', $order->id)->with('success', "The order was already {$order->status_label}.");
        }

        $message = "Order marked as {$order->status_label}.";
        if ($notify && OrderStatusChanged::covers($order->status)) {
            $message .= " We've emailed {$order->customer_email}.";
        }

        return redirect()->route('admin.orders.show', $order->id)->with('success', $message);
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed'
        ]);

        $order = Order::findOrFail($id);
        $order->payment_status = $request->payment_status;
        $order->save();

        return redirect()->route('admin.orders.index')->with('success', 'Payment status updated successfully!');
    }
}
