<?php

namespace App\Models\Concerns;

use App\Models\SlugRedirect;
use Illuminate\Support\Str;

/**
 * For models whose pages are addressed by slug (/product/{slug}, /category/{slug}).
 *
 * The slug is made from the name when none is given, and a number keeps it unique (usb-c-cable,
 * usb-c-cable-2). It doesn't follow later renames, so addresses stay put. When it is changed on purpose,
 * the old slug goes into slug_redirects and still leads to the page.
 */
trait HasSlug
{
    /**
     * Which pages the slugs address ("product", "category"): redirects are kept per type.
     */
    abstract public static function slugType(): string;

    public static function bootHasSlug(): void
    {
        static::saving(function (self $model) {
            if (! $model->exists || blank($model->slug) || $model->isDirty('slug')) {
                $model->slug = $model->uniqueSlug(filled($model->slug) ? $model->slug : (string) $model->name);
            }
        });

        static::saved(function (self $model) {
            if ($model->wasChanged('slug') && filled($old = $model->getOriginal('slug'))) {
                SlugRedirect::updateOrCreate(['type' => static::slugType(), 'slug' => $old], ['target_id' => $model->getKey()]);
            }
            // A slug in use is the page's own address, not a redirect to another one
            if ($model->wasRecentlyCreated || $model->wasChanged('slug')) {
                SlugRedirect::where('type', static::slugType())->where('slug', $model->slug)->delete();
            }
        });

        static::deleted(function (self $model) {
            SlugRedirect::where('type', static::slugType())->where('target_id', $model->getKey())->delete();
        });
    }

    /**
     * The model a slug belonged to before it changed, if any.
     */
    public static function findByOldSlug(string $slug): ?static
    {
        $id = SlugRedirect::where('type', static::slugType())->where('slug', $slug)->value('target_id');

        return $id ? static::find($id) : null;
    }

    /**
     * Longest slug made from a name, leaving room for a "-2" within the column.
     */
    protected static function slugMaxLength(): int
    {
        return 190;
    }

    private function uniqueSlug(string $source): string
    {
        $base = trim(Str::limit(Str::slug($source), static::slugMaxLength(), ''), '-') ?: static::slugType();
        // An all-digit slug would read as an old /product/{id} address
        if (ctype_digit($base)) {
            $base = static::slugType() . '-' . $base;
        }

        $slug = $base;
        for ($n = 2; static::where('slug', $slug)->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))->exists(); $n++) {
            $slug = "{$base}-{$n}";
        }

        return $slug;
    }
}
