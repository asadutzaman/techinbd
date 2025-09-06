<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    public function activeValues(): HasMany
    {
        return $this->values()->where('status', true);
    }

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeFilterable($query)
    {
        return $query->where('filterable', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('required', true);
    }
}