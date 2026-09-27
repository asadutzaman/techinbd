<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One step in an order's history: its status went from one value to another (Order::changeStatus()).
 */
class OrderStatusChange extends Model
{
    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'note',
        'user_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Who made the change; null for changes made outside the admin panel (e.g. php artisan tinker).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
