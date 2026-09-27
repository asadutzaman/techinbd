<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Str;

/**
 * Sent when a customer creates an account.
 */
class Welcome extends Mailable
{
    public function __construct(public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome to ' . config('shop.name'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.welcome', with: [
            'firstName' => Str::before(trim($this->user->name), ' '),
        ]);
    }
}
