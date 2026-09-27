<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A slug a product or category page used to have, so links to the old address keep working.
 * Written by App\Models\Concerns\HasSlug when a slug changes.
 */
class SlugRedirect extends Model
{
    protected $fillable = [
        'type',
        'slug',
        'target_id',
    ];
}
