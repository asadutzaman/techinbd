<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One email sent (or attempted) to a customer about their order. Written by App\Support\CustomerMail.
 */
class OrderEmail extends Model
{
    protected $fillable = [
        'order_id',
        'kind',
        'order_status',
        'recipient',
        'subject',
        'sent',
        'error',
        'message_id',
        'user_id',
    ];

    protected $casts = [
        'sent' => 'boolean',
    ];

    protected static function booted(): void
    {
        // The order keeps whether its latest email failed, so the admin can count and list those orders
        // without reading the whole log. Written without touching the order's updated_at.
        static::created(function (OrderEmail $email) {
            Order::whereKey($email->order_id)->toBase()->update(['last_email_failed' => ! $email->sent]);
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The admin who sent it; null when the shop sent it (at checkout).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * "Order confirmation" or "Status: Shipped"
     */
    public function getLabelAttribute(): string
    {
        return match ($this->kind) {
            'placed' => 'Order confirmation',
            'status' => 'Status: ' . (Order::STATUSES[$this->order_status] ?? ucfirst((string) $this->order_status)),
            default => ucfirst($this->kind),
        };
    }
}
