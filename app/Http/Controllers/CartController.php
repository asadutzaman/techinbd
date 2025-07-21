<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    public function index()
    {
        $sessionId = session()->getId();
        $cartItems = Cart::with('product')->where('session_id', $sessionId)->get();
        
        $subtotal = $cartItems->sum(function($item) {
            return $item->quantity * $item->price;
        });
        
        $shipping = 10.00; // Fixed shipping cost
        $total = $subtotal + $shipping;
        
        return view('cart', compact('cartItems', 'subtotal', 'shipping', 'total'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'size' => 'nullable|string',
            'color' => 'nullable|string'
        ]);

        $product = Product::findOrFail($request->product_id);
        $sessionId = session()->getId();

        // Check if item already exists in cart
        $existingItem = Cart::where('session_id', $sessionId)
            ->where('product_id', $request->product_id)
            ->where('size', $request->size)
            ->where('color', $request->color)
            ->first();

        if ($existingItem) {
            $existingItem->quantity += $request->quantity;
            $existingItem->save();
        } else {
            Cart::create([
                'session_id' => $sessionId,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price' => $product->display_price,
                'size' => $request->size,
                'color' => $request->color
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully!'
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = Cart::where('session_id', session()->getId())->findOrFail($id);
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully!'
        ]);
    }

    public function remove($id)
    {
        $cartItem = Cart::where('session_id', session()->getId())->findOrFail($id);
        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart!'
        ]);
    }

    public function count()
    {
        $sessionId = session()->getId();
        $count = Cart::where('session_id', $sessionId)->sum('quantity');
        
        return response()->json(['count' => $count]);
    }
}
