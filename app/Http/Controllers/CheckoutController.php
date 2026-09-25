<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Services\CartService;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart)
    {
    }

    public function index()
    {
        $cartItems = $this->cart->items();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty!');
        }

        $subtotal = $this->cart->subtotal($cartItems);
        $shipping = CartService::SHIPPING_FLAT;
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

        $cartItems = $this->cart->items();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty!');
        }

        $subtotal = $this->cart->subtotal($cartItems);
        $shipping = CartService::SHIPPING_FLAT;

        $billingAddress = $request->address_line_1 . ', ' .
                         ($request->address_line_2 ? $request->address_line_2 . ', ' : '') .
                         $request->city . ', ' . $request->state . ' ' . $request->zip_code . ', ' . $request->country;

        $order = DB::transaction(function () use ($request, $cartItems, $subtotal, $shipping, $billingAddress) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => Order::generateOrderNumber(),
                'customer_name' => $request->first_name . ' ' . $request->last_name,
                'customer_email' => $request->email,
                'customer_phone' => $request->phone,
                'billing_address' => $billingAddress,
                // The checkout form has no separate shipping fields
                'shipping_address' => $billingAddress,
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'total' => $subtotal + $shipping,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'payment_status' => 'pending'
            ]);

            $order->orderItems()->createMany($cartItems->map(fn ($cartItem) => [
                'product_id' => $cartItem->product_id,
                'product_name' => $cartItem->product->name,
                'product_price' => $cartItem->price,
                'quantity' => $cartItem->quantity,
                'size' => $cartItem->size,
                'color' => $cartItem->color,
                'total' => $cartItem->quantity * $cartItem->price
            ])->all());

            $this->cart->clear();

            return $order;
        });

        // Lets a guest see their own confirmation page, and only that one
        $request->session()->put('last_order_id', $order->id);

        return redirect()->route('order.success', $order->id)->with('success', 'Order placed successfully!');
    }

    public function success(Request $request, $orderId)
    {
        $order = Order::with('orderItems')->findOrFail($orderId);

        $placedThisSession = (int) $request->session()->get('last_order_id') === $order->id;
        $ownsOrder = Auth::check() && $order->user_id === Auth::id();
        abort_unless($placedThisSession || $ownsOrder, 403);

        return view('order-success', compact('order'));
    }
}
