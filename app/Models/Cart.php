<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalAttribute()
    {
        return $this->quantity * $this->price;
    }
}
