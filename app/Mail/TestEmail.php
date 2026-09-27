<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent from Admin → Settings to check that the shop's email reaches an inbox.
 */
class TestEmail extends Mailable
{
    public function __construct(public string $sentBy)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: config('shop.name') . ' test email');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.test');
    }
}
