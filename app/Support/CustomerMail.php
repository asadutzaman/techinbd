<?php

namespace App\Support;

use App\Mail\OrderMail;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

use function Illuminate\Support\defer;

/**
 * Emails to customers: order updates, the welcome mail and password resets.
 *
 * A failed send is reported to the log, never thrown: the order or account it's about already exists.
 * Order emails are also logged against their order (the Emails panel on the admin order page), sent or not.
 */
final class CustomerMail
{
    /**
     * Sends now and says whether it went. Used where someone is watching the result (the admin panel).
     *
     * @param  mixed  $to  an email address or anything with an email (a User)
     * @param  User|null  $by  the admin sending it, for the order's email log
     */
    public static function send(mixed $to, Mailable $mail, ?User $by = null): bool
    {
        try {
            $sent = Mail::to($to)->send($mail);
        } catch (Throwable $e) {
            report($e);
            self::log($to, $mail, $by, error: $e);

            return false;
        }

        self::log($to, $mail, $by, messageId: $sent?->getMessageId());

        return true;
    }

    /**
     * The same, once the response has gone to the browser: for pages a customer is waiting on (checkout,
     * sign-up), since Gmail takes about 6 seconds. See also SetContentLength.
     */
    public static function sendAfterResponse(mixed $to, Mailable $mail): void
    {
        defer(fn () => self::send($to, $mail));
    }

    /**
     * A notification (the password reset email), after the response.
     */
    public static function notify(object $notifiable, Notification $notification): void
    {
        defer(fn () => rescue(fn () => $notifiable->notify($notification)));
    }

    private static function log(mixed $to, Mailable $mail, ?User $by, ?string $messageId = null, ?Throwable $error = null): void
    {
        if (! $mail instanceof OrderMail) {
            return;
        }

        rescue(fn () => $mail->order->emails()->create([
            'kind' => $mail->kind(),
            'order_status' => $mail->order->status,
            'recipient' => is_string($to) ? $to : (string) ($to->email ?? ''),
            'subject' => $mail->envelope()->subject,
            'sent' => $error === null,
            'error' => $error ? Str::limit($error->getMessage(), 1000) : null,
            'message_id' => $messageId,
            'user_id' => $by?->id,
        ]));
    }
}
