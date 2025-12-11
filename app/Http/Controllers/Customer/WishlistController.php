<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wishlist;
use App\Models\ProductOptimized;

class WishlistController extends Controller
{


    public function index()
    {
        $wishlistItems = Auth::user()->wishlistItems()
                            ->with(['product.images', 'product.brand'])
                            ->orderBy('created_at', 'desc')
                            ->get();

        return view('customer.wishlist.index', compact('wishlistItems'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products_optimized,id'
        ]);

        $userId = Auth::id();
        $productId = $request->product_id;

        // Check if already in wishlist
        $exists = Wishlist::where('user_id', $userId)
                         ->where('product_id', $productId)
                         ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Product is already in your wishlist!'
            ]);
        }

        Wishlist::create([
            'user_id' => $userId,
            'product_id' => $productId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist!',
            'wishlist_count' => Auth::user()->wishlistItems()->count()
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products_optimized,id'
        ]);

        $userId = Auth::id();
        $productId = $request->product_id;

        $wishlistItem = Wishlist::where('user_id', $userId)
                               ->where('product_id', $productId)
                               ->first();

        if ($wishlistItem) {
            $wishlistItem->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Product removed from wishlist!',
                'wishlist_count' => Auth::user()->wishlistItems()->count()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found in wishlist!'
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products_optimized,id'
        ]);

        $userId = Auth::id();
        $productId = $request->product_id;

        $wishlistItem = Wishlist::where('user_id', $userId)
                               ->where('product_id', $productId)
                               ->first();

        if ($wishlistItem) {
            $wishlistItem->delete();
            $message = 'Product removed from wishlist!';
            $inWishlist = false;
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            $message = 'Product added to wishlist!';
            $inWishlist = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'in_wishlist' => $inWishlist,
            'wishlist_count' => Auth::user()->wishlistItems()->count()
        ]);
    }

    public function moveToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products_optimized,id'
        ]);

        $userId = Auth::id();
        $productId = $request->product_id;

        // Remove from wishlist
        $wishlistItem = Wishlist::where('user_id', $userId)
                               ->where('product_id', $productId)
                               ->first();

        if (!$wishlistItem) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in wishlist!'
            ]);
        }

        // Add to cart
        $cartController = new \App\Http\Controllers\CartController();
        $cartRequest = new Request([
            'product_id' => $productId,
            'quantity' => 1
        ]);
        
        $cartResponse = $cartController->add($cartRequest);
        
        if ($cartResponse->getData()->success) {
            $wishlistItem->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Product moved to cart!',
                'wishlist_count' => Auth::user()->wishlistItems()->count()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to move product to cart!'
        ]);
    }
}