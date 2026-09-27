<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

use function Illuminate\Support\defer;

/**
 * Emails to customers: order updates, the welcome mail and password resets.
 *
 * They're sent once the response has gone to the browser, so a page doesn't wait on the mail server (Gmail
 * takes about 6 seconds; see also SetContentLength), and a failed send is logged instead of reaching the
 * customer: the order or account it's about already exists.
 */
final class CustomerMail
{
    /**
     * @param  mixed  $to  an email address or anything with an email (a User)
     */
    public static function send(mixed $to, Mailable $mail): void
    {
        defer(fn () => rescue(fn () => Mail::to($to)->send($mail)));
    }

    /**
     * The same for a notification, such as the password reset email.
     */
    public static function notify(object $notifiable, Notification $notification): void
    {
        defer(fn () => rescue(fn () => $notifiable->notify($notification)));
    }
}
