<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'status',
        'is_menu',
        'is_featured'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_menu' => 'boolean',
        'is_featured' => 'boolean'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get attributes for this category
     */
    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }
}
