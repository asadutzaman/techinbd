<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            // Update last login time
            Auth::user()->update(['last_login_at' => now()]);
            
            // Merge guest cart with user cart if exists
            $this->mergeGuestCart();
            
            $request->session()->regenerate();
            
            return redirect()->intended(route('home'))->with('success', 'Welcome back!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home')->with('success', 'You have been logged out successfully.');
    }

    private function mergeGuestCart()
    {
        $sessionId = session()->getId();
        $userId = Auth::id();
        
        // Get guest cart items
        $guestCartItems = \App\Models\Cart::where('session_id', $sessionId)
                                         ->whereNull('user_id')
                                         ->get();
        
        foreach ($guestCartItems as $guestItem) {
            // Check if user already has this product in cart
            $existingItem = \App\Models\Cart::where('user_id', $userId)
                                           ->where('product_id', $guestItem->product_id)
                                           ->first();
            
            if ($existingItem) {
                // Update quantity
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $guestItem->quantity
                ]);
                $guestItem->delete();
            } else {
                // Transfer to user
                $guestItem->update([
                    'user_id' => $userId,
                    'session_id' => null
                ]);
            }
        }
    }
}