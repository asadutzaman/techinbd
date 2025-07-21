<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutController extends Controller
{
    public function index()
    {
        $sessionId = session()->getId();
        $cartItems = Cart::with('product')->where('session_id', $sessionId)->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty!');
        }
        
        $subtotal = $cartItems->sum(function($item) {
            return $item->quantity * $item->price;
        });
        
        $shipping = 10.00;
        $total = $subtotal + $shipping;
        
        return view('checkout', compact('cartItems', 'subtotal', 'shipping', 'total'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'country' => 'required|string|max:255',
            'payment_method' => 'required|in:paypal,directcheck,banktransfer'
        ]);

        $sessionId = session()->getId();
        $cartItems = Cart::with('product')->where('session_id', $sessionId)->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty!');
        }

        $subtotal = $cartItems->sum(function($item) {
            return $item->quantity * $item->price;
        });
        
        $shipping = 10.00;
        $total = $subtotal + $shipping;

        // Create billing address
        $billingAddress = $request->address_line_1 . ', ' . 
                         ($request->address_line_2 ? $request->address_line_2 . ', ' : '') .
                         $request->city . ', ' . $request->state . ' ' . $request->zip_code . ', ' . $request->country;

        // Create order
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'customer_name' => $request->first_name . ' ' . $request->last_name,
            'customer_email' => $request->email,
            'customer_phone' => $request->phone,
            'billing_address' => $billingAddress,
            'shipping_address' => $request->has('ship_to_different') ? $billingAddress : null,
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'total' => $total,
            'payment_method' => $request->payment_method,
            'status' => 'pending',
            'payment_status' => 'pending'
        ]);

        // Create order items
        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'product_name' => $cartItem->product->name,
                'product_price' => $cartItem->price,
                'quantity' => $cartItem->quantity,
                'size' => $cartItem->size,
                'color' => $cartItem->color,
                'total' => $cartItem->quantity * $cartItem->price
            ]);
        }

        // Clear cart
        Cart::where('session_id', $sessionId)->delete();

        return redirect()->route('order.success', $order->id)->with('success', 'Order placed successfully!');
    }

    public function success($orderId)
    {
        $order = Order::with('orderItems.product')->findOrFail($orderId);
        return view('order-success', compact('order'));
    }
}
