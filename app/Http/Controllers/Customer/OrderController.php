<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class OrderController extends Controller
{


    public function index(Request $request)
    {
        $query = Auth::user()->orders()->with(['orderItems.product']);

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

        $order->load(['orderItems.product']);

        return view('customer.orders.show', compact('order'));
    }

    public function reorder(Order $order)
    {
        // Ensure user can only reorder their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $cartController = new \App\Http\Controllers\CartController();

        foreach ($order->orderItems as $item) {
            $request = new Request([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity
            ]);
            
            $cartController->add($request);
        }

        return redirect()->route('cart')->with('success', 'Items from order #' . $order->order_number . ' have been added to your cart!');
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