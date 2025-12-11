<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'last_login_at' => now(),
        ]);

        Auth::login($user);

        // Merge guest cart with new user account
        $this->mergeGuestCart();

        return redirect()->route('home')->with('success', 'Registration successful! Welcome to our store.');
    }

    private function mergeGuestCart()
    {
        $sessionId = session()->getId();
        $userId = Auth::id();
        
        // Transfer guest cart items to user
        \App\Models\Cart::where('session_id', $sessionId)
                       ->whereNull('user_id')
                       ->update([
                           'user_id' => $userId,
                           'session_id' => null
                       ]);
    }
}