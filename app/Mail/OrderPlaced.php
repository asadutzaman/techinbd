<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Str;

/**
 * Sent to the checkout email once an order is placed.
 */
class OrderPlaced extends OrderMail
{
    public function kind(): string
    {
        return 'placed';
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "We've received your order {$this->order->order_number}");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.orders.placed', with: [
            'firstName' => Str::before(trim($this->order->customer_name), ' '),
            // Guests' confirmation page only opens in the browser that placed the order, so they get no link
            'orderUrl' => $this->order->user_id ? route('customer.orders.show', $this->order) : null,
        ]);
    }
}
