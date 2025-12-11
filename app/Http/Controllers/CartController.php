<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductOptimized;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = $this->getCartItems();
        
        $subtotal = $cartItems->sum(function($item) {
            return $item->quantity * $item->price;
        });
        
        $shipping = 10.00; // Fixed shipping cost
        $total = $subtotal + $shipping;
        
        return view('cart', compact('cartItems', 'subtotal', 'shipping', 'total'));
    }

    private function getCartItems()
    {
        if (Auth::check()) {
            return Cart::with(['product' => function($query) {
                $query->with(['images', 'brand']);
            }])->where('user_id', Auth::id())->get();
        } else {
            $sessionId = session()->getId();
            return Cart::with(['product' => function($query) {
                $query->with(['images', 'brand']);
            }])->where('session_id', $sessionId)->whereNull('user_id')->get();
        }
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products_optimized,id',
            'quantity' => 'required|integer|min:1',
            'size' => 'nullable|string',
            'color' => 'nullable|string'
        ]);

        $product = ProductOptimized::findOrFail($request->product_id);
        
        // Determine cart identification
        $cartQuery = Cart::where('product_id', $request->product_id)
                        ->where('size', $request->size)
                        ->where('color', $request->color);

        if (Auth::check()) {
            $cartQuery->where('user_id', Auth::id());
            $cartData = [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price' => $product->price,
                'size' => $request->size,
                'color' => $request->color
            ];
        } else {
            $sessionId = session()->getId();
            $cartQuery->where('session_id', $sessionId)->whereNull('user_id');
            $cartData = [
                'session_id' => $sessionId,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price' => $product->price,
                'size' => $request->size,
                'color' => $request->color
            ];
        }

        $existingItem = $cartQuery->first();

        if ($existingItem) {
            $existingItem->quantity += $request->quantity;
            $existingItem->save();
        } else {
            Cart::create($cartData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully!',
            'cart_count' => $this->getCartCount()
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = $this->findCartItem($id);
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully!',
            'cart_count' => $this->getCartCount()
        ]);
    }

    public function remove($id)
    {
        $cartItem = $this->findCartItem($id);
        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart!',
            'cart_count' => $this->getCartCount()
        ]);
    }

    public function count()
    {
        return response()->json(['count' => $this->getCartCount()]);
    }

    private function findCartItem($id)
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id())->findOrFail($id);
        } else {
            $sessionId = session()->getId();
            return Cart::where('session_id', $sessionId)->whereNull('user_id')->findOrFail($id);
        }
    }

    private function getCartCount()
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id())->sum('quantity');
        } else {
            $sessionId = session()->getId();
            return Cart::where('session_id', $sessionId)->whereNull('user_id')->sum('quantity');
        }
    }
}
