<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\CartService;

class CartController extends Controller
{
    public function __construct(private CartService $cart)
    {
    }

    public function index()
    {
        $cartItems = $this->cart->items();

        $subtotal = $this->cart->subtotal($cartItems);
        $shipping = CartService::SHIPPING_FLAT;
        $total = $subtotal + $shipping;

        return view('cart', compact('cartItems', 'subtotal', 'shipping', 'total'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products_optimized,id',
            // An option of this product; it sets the price
            'variant_id' => ['nullable', 'integer', Rule::exists('product_variants_optimized', 'id')->where('product_id', $request->integer('product_id'))],
            'quantity' => 'required|integer|min:1',
            'size' => 'nullable|string',
            'color' => 'nullable|string'
        ]);

        $this->cart->add(
            (int) $request->product_id,
            (int) $request->quantity,
            $request->size,
            $request->color,
            $request->filled('variant_id') ? $request->integer('variant_id') : null
        );

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully!',
            'cart_count' => $this->cart->count()
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = $this->cart->find($id);
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully!',
            'cart_count' => $this->cart->count()
        ]);
    }

    public function remove($id)
    {
        $this->cart->find($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart!',
            'cart_count' => $this->cart->count()
        ]);
    }

    public function count()
    {
        return response()->json(['count' => $this->cart->count()]);
    }
}
