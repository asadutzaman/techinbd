<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\Welcome;
use App\Models\User;
use App\Services\CartService;
use App\Support\CustomerMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request, CartService $cart)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.unique' => 'An account with this email already exists. Sign in instead, or reset your password.',
        ]);

        // The password is hashed by the model's cast
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
            'last_login_at' => now(),
        ]);

        // Auth::login() rotates the session id, so capture the guest cart's id first
        $guestSessionId = $request->session()->getId();

        Auth::login($user);

        $cart->mergeGuestCart($guestSessionId, $user->id);

        CustomerMail::send($user, new Welcome($user));

        return redirect()->intended(route('home'))
            ->with('success', 'Welcome to ' . config('shop.name') . ', ' . Str::before(trim($user->name), ' ') . '! Your account is ready.');
    }
}
