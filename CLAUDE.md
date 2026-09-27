# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

"MultiShop" e-commerce app on Laravel 12 / PHP ^8.2. Three surfaces share one codebase: a public storefront (Bootstrap 4 theme), an AdminLTE admin panel at `/admin`, and a logged-in customer area at `/customer`. `README.md` is unmodified Laravel boilerplate; the three `*_SUMMARY.md` / `CUSTOMER_FEATURES_IMPLEMENTATION.md` files are status notes from earlier work, not specs.

## Commands

```bash
composer install && cp .env.example .env && php artisan key:generate
php artisan migrate --seed                 # the demo tech store: 16 categories, 100 products, home banners
php artisan storage:link                   # product images live on the public disk
php artisan admin:grant you@example.com    # admin access (--revoke to remove); register the account first
php artisan admin:password you@example.com # set an admin's password; admins can't reset theirs by email
php artisan db:seed --class=CustomerSeeder # 5 demo customers, password 12345678, at plus-addresses of MAIL_FROM_ADDRESS
php artisan serve --port=3000              # http://127.0.0.1:3000, the port the Cloudflare Tunnel forwards to

# Tests use in-memory SQLite. This Laragon PHP doesn't enable pdo_sqlite, and `artisan test`
# runs PHPUnit in a child process that drops -d flags, so call PHPUnit directly:
php -d extension=pdo_sqlite vendor/bin/phpunit
php -d extension=pdo_sqlite vendor/bin/phpunit --filter=CheckoutTest    # one class or test
vendor/bin/pint                                                         # code style

php artisan products:reindex      # rebuild product_search_index after importing/seeding products
php artisan products:thumbnails   # backfill the card (600px) and gallery (1200px) WebP copies of product images missing either
php artisan cache:prune-expired   # delete expired cache entries, which the database/file stores keep otherwise (runs daily)
```

- `.env` targets MySQL (`DB_DATABASE=rrit`); `.env.example` still says sqlite. Sessions, cache and queue use the `database` driver.
- The theme is served as static files from `public/` (`css/style.min.css`, `lib/`, `js/main.js`); the admin layout loads AdminLTE/Bootstrap/jQuery from CDNs. Only `welcome.blade.php` uses Vite, so `npm run build` isn't part of normal work.
- The site is also public at https://inv.naxovisoft.com, through a Cloudflare Tunnel (the `Cloudflared` Windows service) to the dev server on port 3000. `bootstrap/app.php` trusts loopback proxies for `X-Forwarded-For`/`X-Forwarded-Proto` only (a trusted forwarded host could poison reset links), so generated URLs are https:// there. Without it, browsers block `srcset` images and AJAX calls as mixed content, because Cloudflare's HTTPS rewrites skip `srcset`. Check storefront changes on the public URL too, not just localhost.

### Production

`composer install --no-dev --optimize-autoloader`, `php artisan migrate --force`, `php artisan storage:link`, `php artisan optimize` (config/route/view/event caches; routes are all controller-based so route caching works). Set `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=warning`, and `CACHE_STORE=file` (or `redis`) so cache reads don't hit MySQL. Enable OPcache in `php.ini`. Run the scheduler cron (`* * * * * php artisan schedule:run`): it prunes guest carts older than 30 days daily (`Cart::prunable()` via `model:prune`) and expired cache entries (`cache:prune-expired`).

## Architecture

Laravel MVC; controllers mostly query Eloquent directly. All routes are in `routes/web.php`; custom artisan commands and the schedule are closures in `routes/console.php`.

### Catalog

The catalog models are the `*Optimized` ones: `ProductOptimized` (`products_optimized`), `ProductVariantOptimized`, `ProductImageOptimized`, `AttributeOptimized`, `AttributeValueOptimized`, `ProductAttributeOptimized`, plus `ProductSearchIndex`. The original `products`/`product_variants` tables and their models were removed.

- Price is `base_price`; `status` is an integer 0/1 (`->active()` scope). A product's category is `category_id` (the `product_categories` pivot and `categories()` relation exist but are empty/unused). `slug` is made from the name and isn't unique; no URL uses it (product links use the id), so products can share a name.
- `main_image_url` reads the `mainImage` relation, so **eager load `mainImage`** wherever products are listed. It returns the WebP card thumbnail when there is one, else the original, else a `public/img/product-N.jpg` placeholder.
- Each product image has WebP copies written by `ProductImageOptimized::generateVersions()` on upload: `thumb_url` (600px, cards, `card_url`) and `large_url` (1200px, never upscaled, the product page gallery, `gallery_url`/`gallery_srcset`). `deleteFiles()` removes all three files. Use `$image->full_url` only when the original is really needed.
- `specs` is label → value in entry order. It's stored as a JSON list of `[label, value]` pairs by an accessor/mutator, because MySQL sorts JSON object keys; older rows holding an object still read.
- Card "compare at" prices come from `withMax('variants', 'compare_price')` → `variants_max_compare_price`, not from loading variants.
- Search: `ShopController::applySearch()` uses the FULLTEXT index on `product_search_index` on MySQL and falls back to `LIKE` elsewhere (tests). That index is only as fresh as the last `ProductOptimized::updateSearchIndex()` call; the admin product controller and `DemoCatalogSeeder` call it once per product after writing attributes. New write paths (imports, other seeders) must call it or run `products:reindex`.

### Caching and query discipline

- `App\Support\CatalogCache` caches catalog-derived data (menu categories, shop sidebar counts, attribute filters) under a version number. Models using the `BustsCatalogCache` trait bump the version on save/delete, invalidating every catalog key at once. The file/database cache stores don't support tags, which is why it works this way. Those stores only delete an expired entry when the same key is read again, and an old version's keys never are, so the daily `cache:prune-expired` clears them. New cached catalog data should go through `CatalogCache::remember()`, and new catalog models should use the trait.
- `Model::preventLazyLoading()` is on outside production (`AppServiceProvider`). Lazy loads **throw in tests** and log a warning locally, so an N+1 shows up as a failing test. `tests/Feature/StorefrontTest` also asserts that home/shop/product query counts don't grow with catalog size.

### Cart, checkout, orders

- `App\Services\CartService` is the only place that knows how to find "the current cart": rows keyed by `user_id` when logged in, or by `session_id` with a null `user_id` for guests. Controllers (cart, checkout, reorder, wishlist move-to-cart) go through it; don't query `Cart` by session directly. `SHIPPING_FLAT` lives there.
- Options: `carts.variant_id` and `order_items.variant_id` (nullable, set null when the variant is deleted). `CartService::add(..., $variantId)` prices the line at the variant's `price` (falling back to `base_price`), and each option is its own cart line, also when merging a guest cart. `CartController::add` only accepts a `variant_id` that belongs to the product. Checkout snapshots "Name (option)" into `product_name`, and reorder passes the option back.
- `Auth::login()`/`attempt()` rotate the session id, so login and register capture `$request->session()->getId()` **before** authenticating and pass it to `CartService::mergeGuestCart()`.
- `orders` columns are `subtotal`, `shipping_cost`, `total`, `billing_address`/`shipping_address` (single text fields). Order items snapshot `product_name`/`product_price`; `order_items.product_id` is nullable and set null when a product is deleted, so use `$item->product?->…`.
- The order confirmation page (`/order/success/{id}`) is only shown to the session that placed the order (`last_order_id`) or the owning user.
- Order statuses live in `Order::STATUSES`, and `Order::NEXT_STEPS` gives the admin's buttons for each one (pending → confirm or cancel, processing → ship or cancel, shipped → deliver). `$order->changeStatus($status, $by, $note)` saves only a real change and records an `OrderStatusChange` (from, to, the admin, an optional note) for the order's history. It sends no email; `Admin\OrderController` emails the customer itself.
- Checkout pre-fills signed-in customers from their default shipping address (`CheckoutController::prefill()`); the shop delivers within Bangladesh only.

### Email

- `.env` sends through Gmail SMTP (app password; `.env` is gitignored, never put it anywhere else). `APP_URL` is the public site: the mail header and password reset links use it. Tests pin `APP_URL`/`MAIL_MAILER=array` in `phpunit.xml`.
- Customer emails go through `App\Support\CustomerMail`, which `report()`s a failure instead of throwing:
  - `sendAfterResponse()` and `notify()` send after the response with `defer()`, for customer requests (checkout, registration, password reset), where a failure is never shown. Gmail takes ~6s per email. `SetContentLength` middleware lets Apache/mod_php and multi-worker servers release the page first; on Windows `artisan serve` handles one request at a time, so the next page still waits there.
  - `send()` sends right away and returns whether it worked, for admin actions, so the flash message can say whether the customer was emailed.
- Every order email is logged in `order_emails`: kind, order status, recipient, subject, sent or the error, the SMTP message id, and the admin who sent it. The admin order page lists them with "Send again". Creating an `OrderEmail` sets `orders.last_email_failed` from its result (without touching the order's `updated_at`), and `Order::latestEmailFailed()` reads that flag rather than the log (the dashboard warning, the orders list filter and row flag).
- Mailables in `app/Mail` with markdown templates in `resources/views/mail`: `OrderPlaced` (checkout) and `OrderStatusChanged` extend `OrderMail`, whose `kind()` is the log's kind; `Welcome` (registration); `TestEmail` (Admin → Settings, sent to `MAIL_FROM_ADDRESS`). `resources/views/vendor/mail/html/header.blade.php` overrides only the header (the two-tone wordmark). The password reset email is set up with `ResetPassword::toMailUsing()` in `AppServiceProvider` and builds its link on `APP_URL`.
- `App\Support\Money::format()` gives plain-text taka for emails; `<x-price>` uses `Money::amount()`.

### Auth and access control

- Hand-written `Auth\LoginController`/`RegisterController`/`PasswordResetController` on the default `web` guard, behind `guest` middleware. `POST /login` and `POST /forgot-password` are throttled to 5/min. New passwords need 8 characters; login doesn't check length, so older short passwords still work. The forgot-password form answers the same whether or not the email has an account.
- The account pages (`auth/*`) share form styles in `storefront.css` (`.sf-auth*`, `.sf-field*`, `.sf-input`, `.sf-password`), and `storefront.js` handles the Show/Hide password buttons.
- Admin routes use `['auth', 'admin']`; the `admin` alias (`EnsureUserIsAdmin`, registered in `bootstrap/app.php`) checks `users.is_admin`. `is_admin` is deliberately not mass-assignable, so grant it with `php artisan admin:grant`.
- Admin accounts can't reset their password by email, so taking over an admin's mailbox doesn't open the admin panel: `User::sendPasswordResetNotification()` skips them and `PasswordResetController::update()` refuses them. Set it on the server with `php artisan admin:password`.

### Admin panel

- AdminLTE 3 / Bootstrap 4 from CDNs, in `resources/views/admin/layouts/app.blade.php`. It carries the `csrf-token` meta tag the AJAX image buttons read. `AppServiceProvider` sets `Paginator::useBootstrapFour()`, because Laravel's default Tailwind pager renders giant arrows under Bootstrap; the shop page passes `'custom-pagination'` for its own pager. A view composer there feeds the sidebar's pending-orders badge.
- Dashboard (`AdminController`): this month's sales and orders (cancelled left out), orders to confirm and to ship, a 14-day sales chart by day in shop time, the latest orders, products running low (`manage_stock` and 3 or fewer left), and a warning when an order's latest email failed.
- Orders (`Admin\OrderController`):
  - The list has status tabs, a search (order number, name, email, phone) and next-step buttons that email the customer and return to the list (`from=list`). Only the open statuses' tabs (the `Order::NEXT_STEPS` keys) show counts; counting delivered and cancelled orders would read most of the table on every visit. The `orders` indexes `(status, created_at)` and `(created_at)` serve the sidebar's pending count and the newest-first lists.
  - The order page has a progress stepper and the next step: a note for the history, "Email the customer", and "Cash collected" for a cash-on-delivery order being delivered, which marks it paid. It also shows the items, the email log, the history, customer and payment, and a manual status change for corrections.
- Product form (`ProductOptimizedController`, `admin/products/create|edit`):
  - `_specs.blade.php` edits the specs as ordered rows (the first 4 are the key features); `_prices.blade.php` holds the price, was price and cost.
  - The was price is `compare_price` on the default variant (a "Standard" one is created when there's none). It's saved with `updateQuietly()` so the variant's stock recount doesn't overwrite the stock typed in the form.
  - Switches use `$request->boolean()`, because an unticked checkbox isn't submitted.
  - The form only marks category-specific attributes as required (`getCategoryAttributes()`); the server doesn't check them. A required store-wide attribute would otherwise block every product.
- Settings are read-only (`config/shop.php`, `SHOP_*` and `MAIL_*` env). The page can send a test email to the shop's own address.

### Views

- The storefront's look follows techlandbd.com's layout in a light theme. `resources/views/layouts/app.blade.php` is the shared chrome:
  - a sticky white header (logo, search, Deals, compare/wishlist/cart, account) with a category bar on desktops;
  - below 1200px: a phone menu drawer, a search toggle and a fixed bottom bar (the body gets bottom padding for it);
  - a light footer.

  Its styles and design tokens (`--sf-*`, Noto Sans) are in `public/css/storefront.css`, along with shared pieces: the breadcrumb strip (`.sf-crumbs`), product cards and the pager (`.sf-pager`, from `custom-pagination.blade.php`). Both files are plain static files, cache-busted with `filemtime`. Inner pages keep the theme's own content styles, including the yellow `btn-primary`.
- `public/js/storefront.js` handles:
  - **Drawers:** the phone menu and the shop filters. A `[data-drawer]` opens from a `[data-drawer-open]` button whose `aria-controls` names it, and closes from `[data-drawer-close]` or Esc. It traps Tab, locks page scroll and returns focus. `data-drawer-until="992"` makes it a drawer only below that width (1200 by default).
  - the phone search toggle and the product rail buttons;
  - **Product card buttons:** Add to cart posts to the button's `data-cart-url`; compare calls the layout's `addToCompare()`. Pages don't add their own card handlers.
- `MenuComposer` provides `$menuCategories`; the category bar and the drawer show the `is_menu` ones with products, in `sort_order`. `HeaderCountsComposer` provides `$cartCount`/`$wishlistCount` server-side. JS updates the three cart badges with `setCartCount(response.cart_count)` from cart endpoint responses, rather than re-fetching `/cart/count`.
- Pages push page-specific assets with `@push('styles')`/`@push('scripts')`; both layouts render those stacks. Inner pages start with a breadcrumb, and `.sf-main` gives them the top gap; the home, shop and product pages opt out with `@section('main_class', 'is-flush')`. The layout also has a `head` stack (the product page's JSON-LD). `storefront.js` publishes the sticky header's height as `--sf-sticky-top` for other sticky bars.
- Store-wide copy lives in `config/shop.php`: name, tagline (the home page title), description, promises, payment methods, contact/social from `SHOP_*` env, and the display timezone. The footer reads it, and contact lines only render when set. Payment options come from `Order::PAYMENT_METHODS`.
- Home page (`HomeController` + `home.blade.php`): all data is one `CatalogCache::remember('home')` array. The page has:
  - a hero with the banner slider and up to two side banners;
  - Featured Categories: tiles for the `is_featured` categories with products, or all of them when none are featured;
  - rows of Deals (`ProductOptimized::onSale()->orderBySaving()`, also behind the shop's `?sale=1`), Featured, Latest and Best Sellers.

  Best Sellers ranks units ordered, ignoring cancelled orders. It only shows once at least 4 products have sales, and refreshes with the cache (orders don't bust it). Each row is `<x-product-rail>`, which scrolls sideways, and its cards are `<x-product-card>` (the `variant="deal"` card has an orange button). `<x-price>` formats taka (`৳164,999`). `<x-category-icon>` holds the category line icons (Lucide, ISC licence in the file), picked by keyword from the category's name by `Category::getIconAttribute()`; an image uploaded in admin replaces the icon. The `<h1>` is the store name, hidden with `sr-only`.
- Shop page (`ShopController::index` + `shop.blade.php`, styles `.shop-*` and script inline):
  - **Query string:** `App\Support\ShopFilters` reads it forgivingly (search, category, `brand[]`, `attributes[id][]`, min/max price, in stock, sale, featured, sort). It applies them, except the search, which `ShopController::applySearch()` owns.
  - **Links:** `url($changes)` builds shop links with one value changed; `chips()` gives the removable filter chips.
  - **Heading:** the search and the category are what the page is about (the heading), and "Clear all" keeps them.
  - **Filters:** a sidebar from 992px, a slide-in panel below. Ticks apply at once on computers and wait for "Show results" in the panel.
  - **Brands:** the brand list is scoped to the category (`shop-brands:{category}` cache).
  - **Paging and sort:** 24 per page. Deals default to "Biggest saving", everything else to newest; every sort ends with `id desc`, so pages never overlap.
  - **Cards:** `<x-product-card :specs="true" :compare="true">` adds `keySpecs()` lines ("Chip: Apple M5") and a compare button; the home rails use the plain card.
- Product page (`ProductController::show` + `product-detail.blade.php`, styles `.pdp-*` and script inline):
  - a gallery (scroll-snap track, thumbnails, arrows, counter, `<dialog>` lightbox), images ordered main first then `sort_order`;
  - a buy box: chips (stock, SKU, brand, model, warranty), key features (`ProductOptimized::keyFeatures()`, the first 4 specs), price and delivery boxes, options (only when a product has more than one variant; choosing one updates the price and `variant_id`), and cart/wishlist/compare;
  - a Specification table from `ProductOptimized::specGroups()` (the specs, plus attribute values the specs don't already give, then General details), then the description HTML under a sticky tab bar;
  - related products from the same category (a sidebar at ≥1200px, below otherwise).

  There are no reviews or ratings yet; don't add placeholder ones.
- Banners (`Banner` model, Admin → Home Banners) have a `placement`: `slider` or `side` (the first two live side banners show). Uploads are kept as originals and served as WebP versions from `Banner::generateImages()`:
  - slides are cropped to 3:1 for desktop and 2:1 for phones (from the optional phone image);
  - side banners are 2:1 everywhere.

  Changing the placement recrops the banner. Resizing goes through `App\Support\WebpImage`, which product thumbnails also use. Schedules (`starts_at`/`ends_at`, entered in shop time) are checked per request because the home data is cached.

### Demo data

- `DatabaseSeeder` seeds the demo tech store: `CategorySeeder` (16 categories), `AttributeSeeder` (Screen Size, Storage, RAM, Color), `DemoCatalogSeeder` (100 products with brands) and `BannerSeeder` (3 slides and 2 side banners). Each is safe to re-run on its own. `DemoCatalogSeeder` also removes the earlier fashion demo products, categories and brands, but keeps anything added in admin.
- Product covers are drawn from `database/seeders/demo-art/<category-slug>.jpg`, with the brand and model added by GD. Featured products also get a close-up and a key-specs card, so their pages show a 3-image gallery. Images are only created when missing. Banner artwork is original, with its text designed in, in `public/img/banners/`.

### Migrations

Older migration history was hand-cleaned (see `MIGRATION_CLEANUP_SUMMARY.md`), so a long-lived DB may differ from `migrate:fresh`; some old migrations guard with `Schema::hasColumn`. `migrate:fresh --seed` on MySQL and the SQLite test DB are both verified to work end-to-end.
