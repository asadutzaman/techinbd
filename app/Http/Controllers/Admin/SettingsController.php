<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TestEmail;
use App\Models\OrderEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * The store's settings as they are: they live in config/shop.php and .env (SHOP_*, MAIL_*), so this page
 * shows them and can check that email works, rather than offering a form that saves nothing.
 */
class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'mailer' => config('mail.default'),
            'smtpHost' => config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'),
            'from' => config('mail.from'),
            'emailsSent' => OrderEmail::where('sent', true)->where('created_at', '>=', now()->subDays(30))->count(),
            'emailsFailed' => OrderEmail::where('sent', false)->where('created_at', '>=', now()->subDays(30))->count(),
            'lastFailure' => OrderEmail::where('sent', false)->latest('id')->first(),
        ]);
    }

    /**
     * Sends a test to the shop's own address (MAIL_FROM_ADDRESS), never to an admin's login email.
     */
    public function testEmail(Request $request)
    {
        $to = config('mail.from.address');

        try {
            Mail::to($to)->send(new TestEmail($request->user()->name));
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('admin.settings.index')->with('error', "The test email to {$to} failed: {$e->getMessage()}");
        }

        return redirect()->route('admin.settings.index')->with('success', "Test email sent to {$to}. Check that inbox (and its spam folder).");
    }
}
