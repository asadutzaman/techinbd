<?php

namespace App\Models\Concerns;

use App\Support\CatalogCache;

/**
 * Invalidate cached catalog data (menu, sidebar counts, filters) when this model changes.
 */
trait BustsCatalogCache
{
    protected static function bootBustsCatalogCache(): void
    {
        static::saved(fn () => app(CatalogCache::class)->bump());
        static::deleted(fn () => app(CatalogCache::class)->bump());
    }
}
