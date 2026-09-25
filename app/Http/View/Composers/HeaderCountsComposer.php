<?php

namespace App\Http\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\CartService;

/**
 * Cart and wishlist badge counts for the storefront header, rendered server-side
 * so pages don't need an extra /cart/count request on load.
 */
class HeaderCountsComposer
{
    public function __construct(private CartService $cart)
    {
    }

    public function compose(View $view)
    {
        $view->with([
            'cartCount' => $this->cart->count(),
            'wishlistCount' => Auth::check() ? Auth::user()->wishlistItems()->count() : 0,
        ]);
    }
}
