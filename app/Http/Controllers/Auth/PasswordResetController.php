<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * "Forgot password?": emails a reset link (AppServiceProvider sets up that email), then takes the new password.
 */
class PasswordResetController extends Controller
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        // Asked again within a minute: say so, since the email may still be on its way
        if ($status === Password::RESET_THROTTLED) {
            return back()->withInput()->withErrors(['email' => 'We just sent a link to this address. Check your inbox, or try again in a minute.']);
        }

        // The same answer whether or not there's an account, so the form can't be used to find out who shops here
        return back()->withInput()->with('status', "If there's an account for {$request->email}, we've emailed it a link to set a new password.");
    }

    public function reset(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        // Admins can't be reset from the site at all (see User::sendPasswordResetNotification)
        $status = User::where('email', $request->email)->value('is_admin')
            ? Password::INVALID_TOKEN
            : Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                    event(new PasswordReset($user));
                }
            );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'This reset link has expired or was already used. Ask for a new one below.']);
        }

        return redirect()->route('login')
            ->withInput($request->only('email'))
            ->with('status', 'Your password has been changed. Sign in with your new password.');
    }
}
