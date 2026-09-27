<?php

namespace App\Models;

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

    /**
     * The admin workflow: from each status, the steps staff can take next (status => button label).
     * Anything else (reopening, going back) is a manual correction on the order page.
     */
    public const NEXT_STEPS = [
        'pending' => ['processing' => 'Confirm order', 'cancelled' => 'Cancel order'],
        'processing' => ['shipped' => 'Mark as shipped', 'cancelled' => 'Cancel order'],
        'shipped' => ['delivered' => 'Mark as delivered'],
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
        'total' => 'decimal:2',
        'last_email_failed' => 'boolean',
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
     * Orders whose most recent email to the customer failed (a later successful resend clears it).
     * Reads the flag OrderEmail keeps on the order, not the email log.
     */
    public function scopeLatestEmailFailed($query)
    {
        return $query->where('last_email_failed', true);
    }

    /**
     * The workflow steps available from the current status (status => button label).
     */
    public function nextSteps(): array
    {
        return self::NEXT_STEPS[$this->status] ?? [];
    }

    /**
     * Moves the order to a new status and records who did it and why in its history. Emailing the
     * customer is up to the caller (the admin screen shows whether it went). Returns false when the
     * order already had that status.
     */
    public function changeStatus(string $status, ?User $by = null, ?string $note = null): bool
    {
        if ($this->status === $status) {
            return false;
        }

        $from = $this->status;
        $this->update(['status' => $status]);
        $this->statusChanges()->create([
            'from_status' => $from,
            'to_status' => $status,
            'note' => filled($note) ? trim($note) : null,
            'user_id' => $by?->id,
        ]);

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

    /**
     * Emails sent to the customer about this order, newest first.
     */
    public function emails()
    {
        return $this->hasMany(OrderEmail::class)->latest()->orderByDesc('id');
    }

    /**
     * The order's status history, oldest first.
     */
    public function statusChanges()
    {
        return $this->hasMany(OrderStatusChange::class)->oldest()->orderBy('id');
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
