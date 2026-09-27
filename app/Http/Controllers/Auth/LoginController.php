<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\CartService;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request, CartService $cart)
    {
        // No length rule here: accounts made under older rules must still be able to sign in
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        // Auth::attempt() rotates the session id, so capture the guest cart's id first
        $guestSessionId = $request->session()->getId();

        if (Auth::attempt($credentials, $remember)) {
            // Update last login time
            Auth::user()->update(['last_login_at' => now()]);

            // Merge guest cart with user cart if exists
            $cart->mergeGuestCart($guestSessionId, Auth::id());

            $request->session()->regenerate();
            
            $home = Auth::user()->is_admin ? route('admin.dashboard') : route('home');

            return redirect()->intended($home)->with('success', 'Welcome back!');
        }

        return back()->withErrors([
            'email' => "That email and password don't match an account. Check them, or reset your password.",
        ])->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home')->with('success', 'You have been logged out successfully.');
    }
}