<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Cache for data derived from the catalog (menu, sidebar counts, shop filters).
 *
 * Keys carry a version number and bump() moves to a new version, which invalidates
 * every catalog key at once without cache tags (the file/database stores don't support
 * tags). Catalog models call bump() on save/delete via the BustsCatalogCache trait.
 * Bound as a scoped singleton so the version is read from the cache once per request.
 */
class CatalogCache
{
    private ?int $version = null;

    public function remember(string $key, int $seconds, Closure $callback): mixed
    {
        return Cache::remember("catalog:{$this->version()}:{$key}", $seconds, $callback);
    }

    public function bump(): void
    {
        $this->version = $this->version() + 1;
        Cache::forever('catalog:version', $this->version);
    }

    private function version(): int
    {
        return $this->version ??= (int) Cache::get('catalog:version', 1);
    }
}
