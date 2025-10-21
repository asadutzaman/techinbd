<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AttributeOptimized extends Model
{
    use HasFactory;

    protected $table = 'attributes_optimized';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'type',
        'required',
        'filterable',
        'searchable',
        'comparable',
        'unit',
        'sort_order',
        'status'
    ];

    protected $casts = [
        'required' => 'boolean',
        'filterable' => 'boolean',
        'searchable' => 'boolean',
        'comparable' => 'boolean',
        'status' => 'boolean',
        'sort_order' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($attribute) {
            if (empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValueOptimized::class, 'attribute_id')->orderBy('sort_order');
    }

    public function activeValues(): HasMany
    {
        return $this->values()->where('status', true);
    }

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttributeOptimized::class, 'attribute_id');
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

    public function scopeSearchable($query)
    {
        return $query->where('searchable', true);
    }

    public function scopeComparable($query)
    {
        return $query->where('comparable', true);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('category_id');
    }

    public function scopeForCategory($query, $categoryId)
    {
        return $query->where(function($q) use ($categoryId) {
            $q->where('category_id', $categoryId)
              ->orWhereNull('category_id');
        });
    }

    // Accessors
    public function getIsGlobalAttribute()
    {
        return is_null($this->category_id);
    }

    public function getDisplayNameAttribute()
    {
        return $this->name . ($this->unit ? ' (' . $this->unit . ')' : '');
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }
}