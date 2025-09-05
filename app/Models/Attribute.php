<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'type',
        'required',
        'filterable',
        'sort_order',
        'status'
    ];

    protected $casts = [
        'required' => 'boolean',
        'filterable' => 'boolean',
        'status' => 'boolean'
    ];

    /**
     * Get the category that owns the attribute
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get attribute values
     */
    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    /**
     * Get active attribute values
     */
    public function activeAttributeValues()
    {
        return $this->hasMany(AttributeValue::class)->where('status', true)->orderBy('sort_order');
    }

    /**
     * Get product attributes
     */
    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    /**
     * Scope for active attributes
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope for filterable attributes
     */
    public function scopeFilterable($query)
    {
        return $query->where('filterable', true);
    }

    /**
     * Scope for required attributes
     */
    public function scopeRequired($query)
    {
        return $query->where('required', true);
    }
}