<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{


    public function index()
    {
        $user = Auth::user();
        
        // Get recent orders
        $recentOrders = $user->orders()
                            ->with(['orderItems.product'])
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
        
        // Get wishlist count
        $wishlistCount = $user->wishlistItems()->count();
        
        // Get addresses count
        $addressesCount = $user->addresses()->count();
        
        return view('customer.dashboard', compact(
            'user', 
            'recentOrders', 
            'wishlistCount', 
            'addressesCount'
        ));
    }
}