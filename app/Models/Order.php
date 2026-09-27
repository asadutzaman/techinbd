<?php

namespace App\Models;

use App\Mail\OrderStatusChanged;
use App\Support\CustomerMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * Payment options offered at checkout, keyed by the value stored on the order.
     */
    public const PAYMENT_METHODS = [
        'cod' => 'Cash on delivery',
        'banktransfer' => 'Bank transfer',
        'paypal' => 'PayPal',
        'directcheck' => 'Direct check',
    ];

    /**
     * Order statuses, in the order an order moves through them.
     */
    public const STATUSES = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'user_id',
        'order_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'billing_address',
        'shipping_address',
        'subtotal',
        'shipping_cost',
        'total',
        'status',
        'payment_method',
        'payment_status'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? ucfirst((string) $this->payment_method);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    /**
     * Moves the order to a new status and, when $notify is on, emails the customer about it (for the
     * statuses OrderStatusChanged covers; going back to pending is a correction, not news).
     * Returns false when the order already had that status.
     */
    public function changeStatus(string $status, bool $notify = true): bool
    {
        if ($this->status === $status) {
            return false;
        }

        $this->update(['status' => $status]);

        if ($notify && OrderStatusChanged::covers($status)) {
            CustomerMail::send($this->customer_email, new OrderStatusChanged($this));
        }

        return true;
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateOrderNumber()
    {
        // order_number is unique, so retry on the rare random collision
        do {
            $number = 'ORD-' . date('Y') . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('order_number', $number)->exists());

        return $number;
    }
}
