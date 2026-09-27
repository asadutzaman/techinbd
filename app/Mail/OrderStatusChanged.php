<?php

namespace App\Mail;

use App\Support\Money;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Str;

/**
 * Sent when an order moves on: being prepared, on its way, delivered or cancelled.
 */
class OrderStatusChanged extends OrderMail
{
    /**
     * Per status: the subject (with the order number), the heading, and the line after "Hi {name},".
     */
    private const COPY = [
        'processing' => [
            'Order %s is being prepared',
            "We're preparing your order",
            "we've confirmed your order and we're getting it ready.",
        ],
        'shipped' => [
            'Order %s is on its way',
            'Your order is on its way',
            'your order has left us and is on its way to you.',
        ],
        'delivered' => [
            'Order %s has been delivered',
            'Your order has been delivered',
            "your order has been delivered. We hope you enjoy it. If anything isn't right, just reply to this email.",
        ],
        'cancelled' => [
            'Order %s has been cancelled',
            'Your order has been cancelled',
            "your order has been cancelled. If you didn't expect this, reply to this email and we'll sort it out.",
        ],
    ];

    public function kind(): string
    {
        return 'status';
    }

    /**
     * Whether customers hear about an order moving to this status.
     */
    public static function covers(string $status): bool
    {
        return isset(self::COPY[$status]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: sprintf(self::COPY[$this->order->status][0], $this->order->order_number));
    }

    public function content(): Content
    {
        [, $heading, $line] = self::COPY[$this->order->status];

        return new Content(markdown: 'mail.orders.status', with: [
            'heading' => $heading,
            'line' => $line,
            'firstName' => Str::before(trim($this->order->customer_name), ' '),
            // Cash on delivery: the amount to have ready when the parcel arrives
            'payOnDelivery' => $this->order->status === 'shipped' && $this->order->payment_method === 'cod'
                ? Money::format($this->order->total)
                : null,
            'orderUrl' => $this->order->user_id ? route('customer.orders.show', $this->order) : null,
        ]);
    }
}
