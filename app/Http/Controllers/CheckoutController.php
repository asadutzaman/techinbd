<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Services\CartService;
use App\Support\CustomerMail;

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
        $prefill = $this->prefill();

        return view('checkout', compact('cartItems', 'subtotal', 'shipping', 'total', 'prefill'));
    }

    /**
     * What a signed-in customer's form starts with: their default shipping address, else their account
     * details. Guests start with Bangladesh selected.
     */
    private function prefill(): array
    {
        $user = Auth::user()?->load('defaultShippingAddress');
        $address = $user?->defaultShippingAddress;

        return [
            'first_name' => $address->first_name ?? Str::before(trim((string) $user?->name), ' '),
            'last_name' => $address->last_name ?? (str_contains(trim((string) $user?->name), ' ') ? Str::after(trim($user->name), ' ') : ''),
            'email' => $user?->email,
            'phone' => $address->phone ?? $user?->phone,
            'address_line_1' => $address?->address_line_1,
            'address_line_2' => $address?->address_line_2,
            'city' => $address?->city,
            'state' => $address?->state,
            'zip_code' => $address?->postal_code,
            'country' => $address?->country ?: 'Bangladesh',
        ];
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
            'payment_method' => 'required|in:' . implode(',', array_keys(Order::PAYMENT_METHODS))
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
                'variant_id' => $cartItem->variant_id,
                // The name is kept with the order, so it includes the option: "Galaxy S24 Ultra (12GB / 512GB)"
                'product_name' => $cartItem->product->name . ($cartItem->variant?->name ? ' (' . $cartItem->variant->name . ')' : ''),
                'product_price' => $cartItem->price,
                'quantity' => $cartItem->quantity,
                'size' => $cartItem->size,
                'color' => $cartItem->color,
                'total' => $cartItem->quantity * $cartItem->price
            ])->all());

            $this->cart->clear();

            return $order;
        });

        CustomerMail::send($order->customer_email, new OrderPlaced($order));

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
