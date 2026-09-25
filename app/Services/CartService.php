<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\ProductOptimized;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * The current visitor's cart: rows keyed by user_id when logged in,
 * or by session_id (with a null user_id) for guests.
 */
class CartService
{
    public const SHIPPING_FLAT = 10.00;

    public function query(): Builder
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id());
        }

        return Cart::where('session_id', session()->getId())->whereNull('user_id');
    }

    public function items(): Collection
    {
        return $this->query()->with(['product.mainImage'])->get();
    }

    public function find($id): Cart
    {
        return $this->query()->findOrFail($id);
    }

    public function add(int $productId, int $quantity = 1, ?string $size = null, ?string $color = null): Cart
    {
        $product = ProductOptimized::active()->findOrFail($productId);

        $existing = $this->query()
            ->where('product_id', $product->id)
            ->where('size', $size)
            ->where('color', $color)
            ->first();

        if ($existing) {
            $existing->increment('quantity', $quantity);

            return $existing;
        }

        return Cart::create([
            'user_id' => Auth::id(),
            'session_id' => Auth::check() ? null : session()->getId(),
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->base_price,
            'size' => $size,
            'color' => $color,
        ]);
    }

    public function count(): int
    {
        return (int) $this->query()->sum('quantity');
    }

    public function subtotal(Collection $items): float
    {
        return (float) $items->sum(fn (Cart $item) => $item->quantity * $item->price);
    }

    public function clear(): void
    {
        $this->query()->delete();
    }

    /**
     * Move a guest's cart onto the user's cart. Call with the session id captured
     * before logging in: Auth::login() rotates the session id.
     */
    public function mergeGuestCart(string $guestSessionId, int $userId): void
    {
        $guestItems = Cart::where('session_id', $guestSessionId)->whereNull('user_id')->get();

        foreach ($guestItems as $guestItem) {
            $existing = Cart::where('user_id', $userId)
                ->where('product_id', $guestItem->product_id)
                ->where('size', $guestItem->size)
                ->where('color', $guestItem->color)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $guestItem->quantity);
                $guestItem->delete();
            } else {
                $guestItem->update(['user_id' => $userId, 'session_id' => null]);
            }
        }
    }
}
