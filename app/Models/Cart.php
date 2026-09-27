<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory, MassPrunable;

    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
        'size',
        'color'
    ];

    protected $casts = [
        'price' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(ProductOptimized::class, 'product_id');
    }

    /**
     * The option picked on the product page ("12GB / 512GB"), which set the price
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariantOptimized::class, 'variant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalAttribute()
    {
        return $this->quantity * $this->price;
    }

    /**
     * Guest carts are keyed by session id, so they're orphaned once the session expires.
     * Removed daily by `php artisan model:prune` (see routes/console.php).
     */
    public function prunable()
    {
        return static::whereNull('user_id')->where('updated_at', '<=', now()->subDays(30));
    }
}
