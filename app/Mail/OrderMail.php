<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;

/**
 * An email to a customer about their order. App\Support\CustomerMail logs each one against the order
 * (the admin order page's Emails panel).
 */
abstract class OrderMail extends Mailable
{
    public function __construct(public Order $order)
    {
        $order->loadMissing('orderItems');
    }

    /**
     * How the order's email log files it: "placed" or "status".
     */
    abstract public function kind(): string;
}
