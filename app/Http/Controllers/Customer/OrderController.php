<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Services\CartService;

class OrderController extends Controller
{


    public function index(Request $request)
    {
        $query = Auth::user()->orders()->withCount('orderItems');

        // Filter by status if provided
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search by order number
        if ($request->has('search') && $request->search !== '') {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        // Ensure user can only view their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['orderItems.product.mainImage']);

        return view('customer.orders.show', compact('order'));
    }

    public function reorder(Order $order, CartService $cart)
    {
        // Ensure user can only reorder their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $skipped = 0;
        foreach ($order->orderItems()->with('product')->get() as $item) {
            // Products that were deleted or deactivated since the order can't be re-added
            if (! $item->product || ! $item->product->is_active) {
                $skipped++;
                continue;
            }

            $cart->add($item->product_id, $item->quantity, $item->size, $item->color, $item->variant_id);
        }

        $message = 'Items from order #' . $order->order_number . ' have been added to your cart!';
        if ($skipped) {
            $message .= " {$skipped} item(s) are no longer available.";
        }

        return redirect()->route('cart')->with('success', $message);
    }

    public function downloadInvoice(Order $order)
    {
        // Ensure user can only download their own invoices
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Here you would generate and return a PDF invoice
        // For now, we'll just redirect back with a message
        return redirect()->back()->with('info', 'Invoice download feature will be implemented soon.');
    }
}