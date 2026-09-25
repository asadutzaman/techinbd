# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

"MultiShop" e-commerce app on Laravel 12 / PHP ^8.2. Three surfaces share one codebase: a public storefront (Bootstrap 4 theme), an AdminLTE admin panel at `/admin`, and a logged-in customer area at `/customer`. `README.md` is unmodified Laravel boilerplate; the three `*_SUMMARY.md` / `CUSTOMER_FEATURES_IMPLEMENTATION.md` files are status notes from earlier work, not specs.

## Commands

```bash
composer install && cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan storage:link                   # product images live on the public disk
php artisan admin:grant you@example.com    # admin access (--revoke to remove); register the account first
php artisan serve                          # http://127.0.0.1:8000

# Tests use in-memory SQLite. This Laragon PHP doesn't enable pdo_sqlite, and `artisan test`
# runs PHPUnit in a child process that drops -d flags, so call PHPUnit directly:
php -d extension=pdo_sqlite vendor/bin/phpunit
php -d extension=pdo_sqlite vendor/bin/phpunit --filter=CheckoutTest    # one class or test
vendor/bin/pint                                                         # code style

php artisan products:reindex      # rebuild product_search_index after importing/seeding products
php artisan products:thumbnails   # backfill card thumbnails for images uploaded before thumbnails existed
```

- `.env` targets MySQL (`DB_DATABASE=rrit`); `.env.example` still says sqlite. Sessions, cache and queue use the `database` driver.
- The theme is served as static files from `public/` (`css/style.min.css`, `lib/`, `js/main.js`); the admin layout loads AdminLTE/Bootstrap/jQuery from CDNs. Only `welcome.blade.php` uses Vite, so `npm run build` isn't part of normal work.

### Production

`composer install --no-dev --optimize-autoloader`, `php artisan migrate --force`, `php artisan storage:link`, `php artisan optimize` (config/route/view/event caches; routes are all controller-based so route caching works). Set `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=warning`, and `CACHE_STORE=file` (or `redis`) so cache reads don't hit MySQL. Enable OPcache in `php.ini`. Run the scheduler cron (`* * * * * php artisan schedule:run`): it prunes guest carts older than 30 days daily (`Cart::prunable()` via `model:prune`).

## Architecture

Laravel MVC; controllers mostly query Eloquent directly. All routes are in `routes/web.php`; custom artisan commands and the schedule are closures in `routes/console.php`.

### Catalog

The catalog models are the `*Optimized` ones: `ProductOptimized` (`products_optimized`), `ProductVariantOptimized`, `ProductImageOptimized`, `AttributeOptimized`, `AttributeValueOptimized`, `ProductAttributeOptimized`, plus `ProductSearchIndex`. The original `products`/`product_variants` tables and their models were removed.

- Price is `base_price`; `status` is an integer 0/1 (`->active()` scope). A product's category is `category_id` (the `product_categories` pivot and `categories()` relation exist but are empty/unused).
- `main_image_url` reads the `mainImage` relation, so **eager load `mainImage`** wherever products are listed. It returns the WebP card thumbnail (`thumb_url`, written by `ProductImageOptimized::generateThumbnail()` on upload) when there is one, else the original, else a `public/img/product-N.jpg` placeholder. Use `$image->full_url` for the full-size image.
- Card "compare at" prices come from `withMax('variants', 'compare_price')` → `variants_max_compare_price`, not from loading variants.
- Search: `ShopController::applySearch()` uses the FULLTEXT index on `product_search_index` on MySQL and falls back to `LIKE` elsewhere (tests). That index is only as fresh as the last `ProductOptimized::updateSearchIndex()` call; the admin product controller and `OptimizedProductSeeder` call it once per product after writing attributes. New write paths (imports, other seeders) must call it or run `products:reindex`.

### Caching and query discipline

- `App\Support\CatalogCache` caches catalog-derived data (menu categories, shop sidebar counts, attribute filters) under a version number. Models using the `BustsCatalogCache` trait bump the version on save/delete, invalidating every catalog key at once. The file/database cache stores don't support tags, which is why it works this way. New cached catalog data should go through `CatalogCache::remember()`, and new catalog models should use the trait.
- `Model::preventLazyLoading()` is on outside production (`AppServiceProvider`). Lazy loads **throw in tests** and log a warning locally, so an N+1 shows up as a failing test. `tests/Feature/StorefrontTest` also asserts that home/shop/product query counts don't grow with catalog size.

### Cart, checkout, orders

- `App\Services\CartService` is the only place that knows how to find "the current cart": rows keyed by `user_id` when logged in, or by `session_id` with a null `user_id` for guests. Controllers (cart, checkout, reorder, wishlist move-to-cart) go through it; don't query `Cart` by session directly. `SHIPPING_FLAT` lives there.
- `Auth::login()`/`attempt()` rotate the session id, so login and register capture `$request->session()->getId()` **before** authenticating and pass it to `CartService::mergeGuestCart()`.
- `orders` columns are `subtotal`, `shipping_cost`, `total`, `billing_address`/`shipping_address` (single text fields). Order items snapshot `product_name`/`product_price`; `order_items.product_id` is nullable and set null when a product is deleted, so use `$item->product?->…`.
- The order confirmation page (`/order/success/{id}`) is only shown to the session that placed the order (`last_order_id`) or the owning user.

### Auth and access control

- Hand-written `Auth\LoginController`/`RegisterController` on the default `web` guard; `POST /login` is throttled to 5/min.
- Admin routes use `['auth', 'admin']`; the `admin` alias (`EnsureUserIsAdmin`, registered in `bootstrap/app.php`) checks `users.is_admin`. `is_admin` is deliberately not mass-assignable, so grant it with `php artisan admin:grant`.

### Views

- `resources/views/layouts/app.blade.php` is the storefront layout. `MenuComposer` provides `$menuCategories`, and `HeaderCountsComposer` provides `$cartCount`/`$wishlistCount` server-side. JS updates the badge with `setCartCount(response.cart_count)` from cart endpoint responses rather than re-fetching `/cart/count`.
- Pages push page-specific assets with `@push('styles')`/`@push('scripts')`; both layouts render those stacks.

### Migrations

Older migration history was hand-cleaned (see `MIGRATION_CLEANUP_SUMMARY.md`), so a long-lived DB may differ from `migrate:fresh`; some old migrations guard with `Schema::hasColumn`. `migrate:fresh --seed` on MySQL and the SQLite test DB are both verified to work end-to-end.
