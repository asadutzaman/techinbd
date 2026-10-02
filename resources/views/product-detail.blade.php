@extends('layouts.app')

@php
    $images = $product->images;
    // Options only show when there's a real choice; a lone "Standard" variant just carries the compare-at price
    $options = $product->variants->count() > 1 ? $product->variants : collect();
    $defaultVariant = $product->variants->firstWhere('is_default', true) ?? $product->variants->first();
    $price = (float) ($defaultVariant?->price ?? $product->base_price);
    $was = (float) ($defaultVariant?->compare_price ?? 0);
    $saving = $was > $price ? $was - $price : 0;
    $status = $product->stock_status;
    $statusLabel = ['in_stock' => 'In stock', 'out_of_stock' => 'Out of stock', 'preorder' => 'Pre-order'][$status] ?? ucfirst(str_replace('_', ' ', $status));
    $stock = $options->isNotEmpty() ? (int) $defaultVariant->stock : (int) $product->total_stock;
    $maxQuantity = $product->manage_stock && $stock > 0 ? $stock : 99;
    $keyFeatures = $product->keyFeatures();
    $specGroups = $product->specGroups();
    $hasDescription = filled(trim(strip_tags((string) $product->description)));
    $inWishlist = auth()->check() && auth()->user()->wishlistItems()->where('product_id', $product->id)->exists();

    $structuredData = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'image' => $images->map(fn ($image) => $image->gallery_url)->values()->all(),
        'description' => $product->short_description ?: Str::limit(trim(strip_tags((string) $product->description)), 300),
        'sku' => $product->sku,
        'mpn' => $product->manufacturer_part_no,
        'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
        'offers' => [
            '@type' => 'Offer',
            'url' => route('product.detail', $product),
            'priceCurrency' => $product->currency ?: 'BDT',
            'price' => number_format($price, 2, '.', ''),
            'itemCondition' => 'https://schema.org/NewCondition',
            'availability' => [
                'in_stock' => 'https://schema.org/InStock',
                'out_of_stock' => 'https://schema.org/OutOfStock',
                'preorder' => 'https://schema.org/PreOrder',
            ][$status] ?? 'https://schema.org/InStock',
        ],
    ]);
@endphp

@section('title', $product->meta_title ?: $product->name . ' | ' . config('shop.name'))
@section('meta_description', $product->meta_description ?: ($product->short_description ?: config('shop.description')))
@section('main_class', 'is-flush')

@push('head')
<script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<nav class="sf-crumbs" aria-label="Breadcrumb">
    <div class="sf-container">
        <ol>
            <li><a href="{{ route('home') }}">Home</a></li>
            {{-- A hidden (inactive) category has no page to link to --}}
            @if($product->category?->status && $product->category->parent?->status)
                <li><a href="{{ route('shop.category', $product->category->parent) }}">{{ $product->category->parent->name }}</a></li>
            @endif
            @if($product->category?->status)
                <li><a href="{{ route('shop.category', $product->category) }}">{{ $product->category->name }}</a></li>
            @endif
            <li aria-current="page">{{ $product->name }}</li>
        </ol>
    </div>
</nav>

<div class="sf-container">
    <div class="pdp-layout">
        <div class="pdp-main">
            <article class="pdp-card pdp-top" aria-labelledby="product-title">
                <!-- Gallery: swipe or use the arrows and thumbnails; the image opens larger -->
                <div class="pdp-gallery" data-gallery>
                    <div class="pdp-stage">
                        @if($saving)
                            <span class="sf-card-save" data-gallery-save>Save <x-price :amount="$saving" :currency="$product->currency" /></span>
                        @endif
                        @if($images->isNotEmpty())
                            <ul class="pdp-track" data-gallery-track aria-label="{{ $product->name }} images">
                                @foreach($images as $image)
                                    <li class="pdp-slide">
                                        <button type="button" class="pdp-zoom" data-zoom="{{ $loop->index }}" data-large="{{ $image->gallery_url }}"
                                                aria-label="Open image {{ $loop->iteration }} of {{ $images->count() }} larger">
                                            <img src="{{ $image->gallery_url }}"
                                                 @if($image->gallery_srcset) srcset="{{ $image->gallery_srcset }}" sizes="(min-width: 992px) 400px, (min-width: 576px) 480px, calc(100vw - 108px)" @endif
                                                 alt="{{ $image->alt_text ?: $product->name }}" decoding="async"
                                                 @if($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                            <span class="pdp-zoom-hint" aria-hidden="true"><i class="fas fa-search-plus"></i></span>
                            @if($images->count() > 1)
                                <button type="button" class="pdp-nav is-prev" data-gallery-prev aria-label="Previous image" disabled>
                                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="pdp-nav is-next" data-gallery-next aria-label="Next image">
                                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                                </button>
                                <span class="pdp-counter"><span data-gallery-current>1</span> / {{ $images->count() }}</span>
                            @endif
                        @else
                            <div class="pdp-no-image">
                                <x-category-icon :name="$product->category?->icon ?? 'package'" />
                                <span>No photo yet</span>
                            </div>
                        @endif
                    </div>

                    @if($images->count() > 1)
                        <ul class="pdp-thumbs" data-gallery-thumbs aria-label="Choose an image">
                            @foreach($images as $image)
                                <li>
                                    <button type="button" class="pdp-thumb" data-thumb="{{ $loop->index }}"
                                            aria-label="Show image {{ $loop->iteration }}" aria-current="{{ $loop->first ? 'true' : 'false' }}">
                                        <img src="{{ $image->card_url }}" alt="" loading="lazy" decoding="async">
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- Buy box -->
                <div class="pdp-buy">
                    <h1 class="pdp-title" id="product-title">{{ $product->name }}</h1>

                    <ul class="pdp-chips">
                        <li class="pdp-chip is-{{ $status }}"><span class="pdp-dot" aria-hidden="true"></span>{{ $statusLabel }}</li>
                        @if($product->sku)
                            <li class="pdp-chip"><span class="pdp-chip-label">SKU</span> {{ $product->sku }}</li>
                        @endif
                        @if($product->brand)
                            <li class="pdp-chip"><span class="pdp-chip-label">Brand</span> <a href="{{ route('shop', ['brand' => $product->brand->id]) }}">{{ $product->brand->name }}</a></li>
                        @endif
                        @if($product->manufacturer_part_no)
                            <li class="pdp-chip"><span class="pdp-chip-label">Model</span> {{ $product->manufacturer_part_no }}</li>
                        @endif
                        @if($product->warranty)
                            <li class="pdp-chip"><span class="pdp-chip-label">Warranty</span> {{ $product->warranty }}</li>
                        @endif
                    </ul>

                    @if($keyFeatures)
                        <div class="pdp-features">
                            <h2 class="pdp-label">Key features</h2>
                            <ul>
                                @foreach($keyFeatures as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                            <a class="pdp-more" href="#specification">View full specification</a>
                        </div>
                    @elseif($product->short_description)
                        <p class="pdp-lead">{{ $product->short_description }}</p>
                    @endif

                    <div class="pdp-boxes">
                        <div class="pdp-box pdp-price-box">
                            <h2 class="pdp-label">Price</h2>
                            <p class="pdp-price">
                                <x-price :amount="$price" :currency="$product->currency" data-price-now />
                                <s class="pdp-was" data-price-was @if(! $saving) hidden @endif><span class="sr-only">Was </span><x-price :amount="$was ?: $price" :currency="$product->currency" /></s>
                            </p>
                            <p class="pdp-save" data-price-save @if(! $saving) hidden @endif>Save <x-price :amount="$saving" :currency="$product->currency" /></p>
                        </div>
                        <div class="pdp-box">
                            <h2 class="pdp-label">Payment and delivery</h2>
                            <ul class="pdp-promises">
                                @foreach(config('shop.promises') as $promise)
                                    <li><i class="fas fa-check" aria-hidden="true"></i>{{ $promise }}</li>
                                @endforeach
                            </ul>
                            <p class="pdp-accept">We accept {{ implode(' · ', config('shop.payment_methods')) }}</p>
                        </div>
                    </div>

                    @if($options->isNotEmpty())
                        <div class="pdp-options">
                            <h2 class="pdp-label" id="options-label">Options</h2>
                            <div class="pdp-option-list" role="radiogroup" aria-labelledby="options-label">
                                @foreach($options as $option)
                                    @php
                                        $checked = $option->id === $defaultVariant->id;
                                        $available = ! $product->manage_stock || $option->stock > 0;
                                    @endphp
                                    <button type="button" class="pdp-option" role="radio" aria-checked="{{ $checked ? 'true' : 'false' }}" tabindex="{{ $checked ? 0 : -1 }}"
                                            data-variant="{{ $option->id }}" data-price="{{ (float) ($option->price ?? $product->base_price) }}"
                                            data-was="{{ (float) ($option->compare_price ?? 0) }}" data-stock="{{ $product->manage_stock ? (int) $option->stock : 99 }}"
                                            @if(! $available) disabled @endif>
                                        <span class="pdp-option-name">{{ $option->name }}</span>
                                        @if($option->price && (float) $option->price !== (float) $product->base_price)
                                            <span class="pdp-option-price"><x-price :amount="$option->price" :currency="$product->currency" /></span>
                                        @endif
                                        @unless($available)
                                            <span class="pdp-option-price">Out of stock</span>
                                        @endunless
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form class="pdp-actions" id="add-to-cart-form" data-cart-url="{{ route('cart.add') }}" data-currency="{{ $product->currency ?: 'BDT' }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        @if($options->isNotEmpty())
                            <input type="hidden" name="variant_id" value="{{ $defaultVariant->id }}" data-variant-input>
                        @endif
                        <div class="pdp-qty">
                            <button type="button" class="pdp-qty-btn" data-quantity-step="-1" aria-label="Decrease quantity">
                                <i class="fas fa-minus" aria-hidden="true"></i>
                            </button>
                            <label class="sr-only" for="quantity">Quantity</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $maxQuantity }}" inputmode="numeric">
                            <button type="button" class="pdp-qty-btn" data-quantity-step="1" aria-label="Increase quantity">
                                <i class="fas fa-plus" aria-hidden="true"></i>
                            </button>
                        </div>
                        <button type="submit" class="pdp-cart-btn" @disabled($status === 'out_of_stock')>
                            @if($status === 'out_of_stock')
                                Out of stock
                            @else
                                <i class="fas fa-cart-plus" aria-hidden="true"></i>{{ $status === 'preorder' ? 'Pre-order' : 'Add to cart' }}
                            @endif
                        </button>
                        @auth
                            <button type="button" class="pdp-icon-btn" data-wishlist="{{ route('customer.wishlist.toggle') }}" aria-pressed="{{ $inWishlist ? 'true' : 'false' }}" aria-label="Save to wishlist" title="Wishlist">
                                <i class="{{ $inWishlist ? 'fas' : 'far' }} fa-heart" aria-hidden="true"></i>
                            </button>
                        @else
                            <a class="pdp-icon-btn" href="{{ route('login') }}" aria-label="Sign in to save to your wishlist" title="Wishlist">
                                <i class="far fa-heart" aria-hidden="true"></i>
                            </a>
                        @endauth
                        <button type="button" class="pdp-icon-btn" data-compare data-name="{{ $product->name }}" data-image="{{ $product->main_image_url }}" aria-label="Add to compare" title="Compare">
                            <i class="fas fa-balance-scale" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </article>

            <!-- Details: specification and description, with a bar that follows the page -->
            <div class="pdp-card pdp-details">
                <nav class="pdp-tabs" aria-label="Product information">
                    <a class="pdp-tab" href="#specification" data-section-link aria-current="true">Specification</a>
                    @if($hasDescription)
                        <a class="pdp-tab" href="#description" data-section-link>Description</a>
                    @endif
                </nav>

                <section class="pdp-section" id="specification" aria-labelledby="specification-title" data-section>
                    <h2 class="pdp-section-title" id="specification-title">Specification</h2>
                    <div class="pdp-spec-wrap">
                        <table class="pdp-spec">
                            @foreach($specGroups as $group => $rows)
                                <tbody>
                                    <tr class="pdp-spec-group"><th colspan="2" scope="colgroup">{{ $group }}</th></tr>
                                    @foreach($rows as $label => $value)
                                        <tr>
                                            <th scope="row">{{ $label }}</th>
                                            <td>{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            @endforeach
                        </table>
                    </div>
                </section>

                @if($hasDescription)
                    <section class="pdp-section" id="description" aria-labelledby="description-title" data-section>
                        <h2 class="pdp-section-title" id="description-title">Description</h2>
                        <div class="pdp-prose">{!! $product->description !!}</div>
                    </section>
                @endif
            </div>
        </div>

        @if($relatedProducts->isNotEmpty())
            <aside class="pdp-related" aria-labelledby="related-title">
                <h2 class="pdp-related-title" id="related-title">Related products</h2>
                <ul class="pdp-related-list">
                    @foreach($relatedProducts as $related)
                        @php
                            $relatedWas = (float) ($related->variants_max_compare_price ?? 0);
                            $relatedSaving = $relatedWas > (float) $related->base_price ? $relatedWas - (float) $related->base_price : 0;
                        @endphp
                        <li>
                            <a class="pdp-related-item" href="{{ route('product.detail', $related) }}">
                                <img src="{{ $related->main_image_url }}" alt="" width="64" height="64" loading="lazy" decoding="async">
                                <span class="pdp-related-body">
                                    <span class="pdp-related-name">{{ $related->name }}</span>
                                    <span class="pdp-related-price">
                                        <x-price :amount="$related->base_price" :currency="$related->currency" />
                                        @if($relatedSaving)
                                            <s><span class="sr-only">Was </span><x-price :amount="$relatedWas" :currency="$related->currency" /></s>
                                        @endif
                                    </span>
                                    @if($relatedSaving)
                                        <span class="pdp-related-save">Save <x-price :amount="$relatedSaving" :currency="$related->currency" /></span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                @if($product->category?->status)
                    <a class="sf-view-all pdp-related-all" href="{{ route('shop.category', $product->category) }}">
                        View all {{ $product->category->name }} <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                @endif
            </aside>
        @endif
    </div>
</div>

@if($images->isNotEmpty())
    <!-- Larger images -->
    <dialog class="pdp-lightbox" data-lightbox aria-label="{{ $product->name }} images">
        <div class="pdp-lightbox-bar">
            <span class="pdp-lightbox-count"><span data-lightbox-current>1</span> / {{ $images->count() }}</span>
            <button type="button" class="pdp-lightbox-close" data-lightbox-close aria-label="Close">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
        <div class="pdp-lightbox-stage" data-lightbox-stage>
            <img src="" alt="" data-lightbox-image>
            @if($images->count() > 1)
                <button type="button" class="pdp-lightbox-nav is-prev" data-lightbox-prev aria-label="Previous image">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button type="button" class="pdp-lightbox-nav is-next" data-lightbox-next aria-label="Next image">
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </button>
            @endif
        </div>
    </dialog>
@endif
@endsection

@push('styles')
<style>
/* ---------- Layout: related products beside the product on computers, below it otherwise ---------- */
.pdp-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 24px;
    padding: 20px 0 8px;
}

.pdp-main {
    min-width: 0;
}

.pdp-card {
    background: var(--sf-surface);
    border: 1px solid var(--sf-line);
    border-radius: var(--sf-radius);
}

.pdp-top {
    display: grid;
    gap: 20px;
    padding: 16px;
}

/* ---------- Gallery ---------- */
.pdp-gallery {
    width: 100%;
    max-width: 520px;
    min-width: 0;
    margin: 0 auto;
}

.pdp-stage {
    position: relative;
    overflow: hidden;
    border: 1px solid var(--sf-line);
    border-radius: 10px;
    background: #FFFFFF;
}

.pdp-stage .sf-card-save {
    top: 12px;
    left: 12px;
    font-size: 13px;
}

.pdp-track {
    display: flex;
    margin: 0;
    padding: 0;
    overflow-x: auto;
    list-style: none;
    scroll-snap-type: x mandatory;
    overscroll-behavior-x: contain;
    scrollbar-width: none;
}

.pdp-track::-webkit-scrollbar {
    display: none;
}

.pdp-slide {
    flex: 0 0 100%;
    aspect-ratio: 1 / 1;
    scroll-snap-align: start;
}

.pdp-zoom {
    display: block;
    width: 100%;
    height: 100%;
    padding: 20px;
    background: #FFFFFF;
    border: 0;
    cursor: zoom-in;
}

.pdp-zoom img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.pdp-zoom:focus-visible {
    outline-offset: -4px;
}

.pdp-zoom-hint {
    position: absolute;
    left: 12px;
    bottom: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255, 255, 255, .92);
    box-shadow: 0 2px 8px rgba(17, 24, 39, .15);
    font-size: 13px;
    color: var(--sf-muted);
    pointer-events: none;
}

.pdp-nav {
    position: absolute;
    top: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    padding: 0;
    background: rgba(255, 255, 255, .95);
    border: 1px solid var(--sf-line-strong);
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(17, 24, 39, .12);
    font-size: 12px;
    color: var(--sf-text);
    transform: translateY(-50%);
}

.pdp-nav.is-prev {
    left: 10px;
}

.pdp-nav.is-next {
    right: 10px;
}

.pdp-nav:hover:not(:disabled) {
    border-color: var(--sf-accent);
    color: var(--sf-accent);
}

.pdp-nav:disabled {
    opacity: 0;
    pointer-events: none;
}

.pdp-counter {
    position: absolute;
    right: 12px;
    bottom: 12px;
    padding: 3px 10px;
    border-radius: 999px;
    background: rgba(17, 24, 39, .72);
    font-size: 12px;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
    color: #FFFFFF;
}

.pdp-no-image {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    aspect-ratio: 1 / 1;
    background: var(--sf-hover);
    font-size: 14px;
    color: var(--sf-faint);
}

.pdp-no-image .category-icon {
    width: 96px;
    height: 96px;
    stroke-width: 1;
}

.pdp-thumbs {
    display: flex;
    gap: 8px;
    margin: 12px 0 0;
    padding: 2px;
    overflow-x: auto;
    list-style: none;
    scrollbar-width: thin;
}

.pdp-thumb {
    display: block;
    width: 64px;
    height: 64px;
    padding: 4px;
    background: #FFFFFF;
    border: 1px solid var(--sf-line);
    border-radius: 8px;
}

.pdp-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.pdp-thumb:hover {
    border-color: var(--sf-line-strong);
}

.pdp-thumb[aria-current="true"] {
    border-color: var(--sf-accent);
    box-shadow: 0 0 0 1px var(--sf-accent);
}

/* ---------- Buy box ---------- */
.pdp-buy {
    min-width: 0;
}

.pdp-title {
    margin: 0 0 12px;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.3;
    color: var(--sf-ink);
}

.pdp-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin: 0 0 16px;
    padding: 0;
    list-style: none;
}

.pdp-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    background: var(--sf-page);
    font-size: 13px;
    color: var(--sf-text);
}

.pdp-chip-label {
    color: var(--sf-muted);
}

.pdp-chip a {
    font-weight: 600;
    color: var(--sf-accent-strong);
}

.pdp-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

.pdp-chip.is-in_stock {
    background: #ECFDF5;
    font-weight: 600;
    color: #047857;
}

.pdp-chip.is-out_of_stock {
    background: #FEF2F2;
    font-weight: 600;
    color: #B91C1C;
}

.pdp-chip.is-preorder {
    background: #FFFBEB;
    font-weight: 600;
    color: #B45309;
}

.pdp-label {
    margin: 0 0 8px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--sf-faint);
}

.pdp-features ul {
    margin: 0 0 6px;
    padding: 0;
    list-style: none;
}

.pdp-features li {
    position: relative;
    margin: 5px 0;
    padding-left: 18px;
    font-size: 15px;
    line-height: 1.45;
    color: var(--sf-text);
}

.pdp-features li::before {
    content: "";
    position: absolute;
    top: .6em;
    left: 3px;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--sf-accent);
}

.pdp-more {
    font-size: 14px;
    font-weight: 600;
    color: var(--sf-accent-strong);
}

.pdp-lead {
    margin: 0;
    font-size: 15px;
    line-height: 1.6;
    color: var(--sf-muted);
}

.pdp-boxes {
    display: grid;
    gap: 12px;
    margin: 20px 0;
}

.pdp-box {
    padding: 14px 16px;
    border: 1px solid var(--sf-line);
    border-radius: 10px;
}

.pdp-price-box {
    background: var(--sf-hover);
}

.pdp-price {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 4px 10px;
    margin: 0;
}

.pdp-price > .price {
    font-size: 28px;
    font-weight: 700;
    line-height: 1.15;
    color: var(--sf-price);
}

.pdp-was {
    font-size: 15px;
    color: var(--sf-faint);
}

.pdp-save {
    display: inline-block;
    margin: 8px 0 0;
    padding: 2px 8px;
    border-radius: 4px;
    background: var(--sf-save);
    font-size: 12px;
    font-weight: 700;
    color: #FFFFFF;
}

.pdp-promises {
    display: grid;
    gap: 4px;
    margin: 0;
    padding: 0;
    list-style: none;
    font-size: 14px;
    color: var(--sf-text);
}

.pdp-promises i {
    width: 16px;
    margin-right: 8px;
    color: var(--sf-save);
}

.pdp-accept {
    margin: 8px 0 0;
    font-size: 12px;
    color: var(--sf-muted);
}

.pdp-options {
    margin-bottom: 20px;
}

.pdp-option-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.pdp-option {
    display: inline-flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
    padding: 8px 14px;
    background: #FFFFFF;
    border: 1px solid var(--sf-line-strong);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-align: left;
    color: var(--sf-text);
}

.pdp-option:hover:not(:disabled) {
    border-color: var(--sf-accent);
}

.pdp-option[aria-checked="true"] {
    background: #EFF6FF;
    border-color: var(--sf-accent);
    box-shadow: inset 0 0 0 1px var(--sf-accent);
    color: var(--sf-accent-strong);
}

.pdp-option:disabled {
    opacity: .55;
    cursor: not-allowed;
}

.pdp-option-price {
    font-size: 12px;
    font-weight: 500;
    color: var(--sf-muted);
}

.pdp-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
}

.pdp-qty {
    display: inline-flex;
    align-items: stretch;
    height: 44px;
    overflow: hidden;
    border: 1px solid var(--sf-line-strong);
    border-radius: 8px;
}

.pdp-qty-btn {
    width: 40px;
    padding: 0;
    background: var(--sf-hover);
    border: 0;
    font-size: 12px;
    color: var(--sf-text);
}

.pdp-qty-btn:hover {
    color: var(--sf-accent);
}

.pdp-qty input {
    width: 48px;
    border: 0;
    border-right: 1px solid var(--sf-line);
    border-left: 1px solid var(--sf-line);
    font-size: 15px;
    font-weight: 600;
    text-align: center;
    color: var(--sf-ink);
    -moz-appearance: textfield;
    appearance: textfield;
}

.pdp-qty input::-webkit-inner-spin-button,
.pdp-qty input::-webkit-outer-spin-button {
    margin: 0;
    -webkit-appearance: none;
}

.pdp-cart-btn {
    display: inline-flex;
    flex: 1 1 180px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 44px;
    padding: 0 20px;
    background: var(--sf-button);
    border: 0;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    color: #FFFFFF;
}

.pdp-cart-btn:hover:not(:disabled) {
    background: var(--sf-button-hover);
}

.pdp-cart-btn:disabled {
    background: var(--sf-line);
    color: var(--sf-faint);
    cursor: not-allowed;
}

.pdp-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    background: #FFFFFF;
    border: 1px solid var(--sf-line-strong);
    border-radius: 8px;
    font-size: 17px;
    color: var(--sf-text);
}

.pdp-icon-btn:hover {
    border-color: var(--sf-accent);
    color: var(--sf-accent);
    text-decoration: none;
}

.pdp-icon-btn[aria-pressed="true"] {
    border-color: #FECACA;
    color: var(--sf-price);
}

/* ---------- Details: specification and description ---------- */
.pdp-details {
    margin-top: 24px;
}

.pdp-tabs {
    position: sticky;
    top: var(--sf-sticky-top, 61px);
    z-index: 5;
    display: flex;
    gap: 28px;
    padding: 0 20px;
    background: var(--sf-surface);
    border-bottom: 1px solid var(--sf-line);
    border-radius: var(--sf-radius) var(--sf-radius) 0 0;
}

.pdp-tab {
    padding: 14px 0 12px;
    border-bottom: 2px solid transparent;
    font-size: 15px;
    font-weight: 600;
    color: var(--sf-muted);
}

.pdp-tab:hover,
.pdp-tab[aria-current="true"] {
    border-bottom-color: var(--sf-accent);
    color: var(--sf-accent-strong);
    text-decoration: none;
}

.pdp-section {
    padding: 20px;
    scroll-margin-top: calc(var(--sf-sticky-top, 61px) + 56px);
}

.pdp-section + .pdp-section {
    border-top: 1px solid var(--sf-line);
}

.pdp-section-title {
    margin: 0 0 16px;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: var(--sf-ink);
}

.pdp-spec-wrap {
    overflow-x: auto;
    border: 1px solid var(--sf-line);
    border-radius: 8px;
}

.pdp-spec {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.pdp-spec-group th {
    padding: 10px 16px;
    background: var(--sf-page);
    font-weight: 700;
    color: var(--sf-ink);
}

.pdp-spec th[scope="row"] {
    width: 32%;
    padding: 11px 16px;
    font-weight: 500;
    vertical-align: top;
    color: var(--sf-muted);
}

.pdp-spec td {
    padding: 11px 16px;
    color: var(--sf-text);
}

.pdp-spec tr + tr > *,
.pdp-spec tbody + tbody tr:first-child > * {
    border-top: 1px solid var(--sf-line);
}

.pdp-prose {
    max-width: 78ch;
    font-size: 15px;
    line-height: 1.7;
    color: var(--sf-text);
}

.pdp-prose > :first-child {
    margin-top: 0;
}

.pdp-prose > :last-child {
    margin-bottom: 0;
}

.pdp-prose h2,
.pdp-prose h3,
.pdp-prose h4,
.pdp-prose h5,
.pdp-prose h6 {
    margin: 1.5em 0 .5em;
    font-weight: 700;
    line-height: 1.3;
    color: var(--sf-ink);
}

.pdp-prose h2 {
    font-size: 19px;
}

.pdp-prose h3 {
    font-size: 17px;
}

.pdp-prose h4,
.pdp-prose h5,
.pdp-prose h6 {
    font-size: 15px;
}

.pdp-prose p,
.pdp-prose ul,
.pdp-prose ol,
.pdp-prose table {
    margin: 0 0 1em;
}

.pdp-prose ul,
.pdp-prose ol {
    padding-left: 1.3em;
}

.pdp-prose li {
    margin: .3em 0;
}

.pdp-prose li::marker {
    color: var(--sf-accent);
}

.pdp-prose table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.pdp-prose th,
.pdp-prose td {
    padding: 8px 12px;
    border: 1px solid var(--sf-line);
    text-align: left;
    vertical-align: top;
}

.pdp-prose th {
    background: var(--sf-page);
    font-weight: 600;
}

.pdp-prose img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

.pdp-prose a {
    color: var(--sf-accent-strong);
    text-decoration: underline;
}

/* ---------- Related products ---------- */
.pdp-related {
    min-width: 0;
}

.pdp-related-title {
    margin: 0 0 12px;
    font-size: 16px;
    font-weight: 700;
    color: var(--sf-ink);
}

.pdp-related-list {
    display: grid;
    gap: 8px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.pdp-related-item {
    display: flex;
    gap: 12px;
    height: 100%;
    padding: 10px;
    background: var(--sf-surface);
    border: 1px solid var(--sf-line);
    border-radius: 10px;
    color: var(--sf-text);
    transition: box-shadow .2s ease;
}

.pdp-related-item:hover {
    box-shadow: var(--sf-shadow);
    text-decoration: none;
}

.pdp-related-item img {
    flex: none;
    width: 64px;
    height: 64px;
    object-fit: contain;
}

.pdp-related-body {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}

.pdp-related-name {
    display: -webkit-box;
    overflow: hidden;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.35;
    color: var(--sf-title);
}

.pdp-related-item:hover .pdp-related-name {
    color: var(--sf-accent);
}

.pdp-related-price {
    font-size: 14px;
}

.pdp-related-price > .price {
    font-weight: 700;
    color: var(--sf-price);
}

.pdp-related-price s {
    margin-left: 6px;
    font-size: 12px;
    color: var(--sf-faint);
}

.pdp-related-save {
    align-self: flex-start;
    padding: 0 6px;
    border-radius: 4px;
    background: var(--sf-save);
    font-size: 11px;
    font-weight: 700;
    line-height: 18px;
    color: #FFFFFF;
}

.pdp-related-all {
    margin-top: 12px;
}

/* ---------- Lightbox ---------- */
.pdp-lightbox {
    width: 100vw;
    max-width: 100vw;
    height: 100dvh;
    max-height: 100dvh;
    margin: 0;
    padding: 0;
    background: rgba(17, 24, 39, .96);
    border: 0;
    color: #FFFFFF;
}

.pdp-lightbox::backdrop {
    background: rgba(17, 24, 39, .96);
}

.pdp-lightbox[open] {
    display: flex;
    flex-direction: column;
}

.pdp-lightbox-bar {
    display: flex;
    flex: none;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px 10px 20px;
}

.pdp-lightbox-count {
    font-size: 14px;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
}

.pdp-lightbox-close,
.pdp-lightbox-nav {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    padding: 0;
    background: rgba(255, 255, 255, .12);
    border: 0;
    border-radius: 50%;
    font-size: 18px;
    color: #FFFFFF;
}

.pdp-lightbox-close:hover,
.pdp-lightbox-nav:hover {
    background: rgba(255, 255, 255, .24);
}

.pdp-lightbox-stage {
    position: relative;
    display: flex;
    flex: 1;
    align-items: center;
    justify-content: center;
    min-height: 0;
    padding: 0 72px 24px;
    touch-action: pan-y pinch-zoom;
}

.pdp-lightbox-stage img {
    max-width: 100%;
    max-height: 100%;
    border-radius: 8px;
    background: #FFFFFF;
    object-fit: contain;
    user-select: none;
}

.pdp-lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
}

.pdp-lightbox-nav.is-prev {
    left: 16px;
}

.pdp-lightbox-nav.is-next {
    right: 16px;
}

/* ---------- Responsive ---------- */
@media (min-width: 576px) {
    .pdp-boxes {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .pdp-related-list {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 992px) {
    .pdp-top {
        grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
        gap: 32px;
        padding: 24px;
    }

    .pdp-gallery {
        max-width: none;
    }

    .pdp-title {
        font-size: 22px;
    }

    .pdp-section,
    .pdp-tabs {
        padding-right: 24px;
        padding-left: 24px;
    }
}

@media (min-width: 1200px) {
    .pdp-layout {
        grid-template-columns: 290px minmax(0, 1fr);
        padding-top: 24px;
    }

    .pdp-main {
        grid-column: 2;
        grid-row: 1;
    }

    .pdp-related {
        grid-column: 1;
        grid-row: 1;
    }

    .pdp-related-list {
        grid-template-columns: minmax(0, 1fr);
    }
}

/* Phones: quantity and the icon buttons on one row, a full-width Add to cart under them */
@media (max-width: 575.98px) {
    .pdp-cart-btn {
        flex-basis: 100%;
        order: 1;
    }

    .pdp-qty {
        flex: 1;
    }

    .pdp-qty input {
        flex: 1;
    }

    .pdp-lightbox-stage {
        padding: 0 8px 16px;
    }

    .pdp-lightbox-nav {
        width: 38px;
        height: 38px;
        font-size: 15px;
        background: rgba(17, 24, 39, .6);
    }

    .pdp-lightbox-nav.is-prev {
        left: 12px;
    }

    .pdp-lightbox-nav.is-next {
        right: 12px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .pdp-related-item {
        transition: none;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    var smooth = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
    var form = document.getElementById('add-to-cart-form');
    var productId = form.elements.product_id.value;
    var csrfToken = form.elements._token.value;
    var currency = form.dataset.currency;

    // "৳184,999", the same markup as the price component (components/price.blade.php)
    function priceHtml(amount) {
        var formatted = Number(amount).toLocaleString('en-US', Number.isInteger(Number(amount)) ? {} : { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        return currency.toUpperCase() === 'BDT'
            ? '<span class="price-sign" aria-hidden="true">৳</span><span class="sr-only">Tk </span>' + formatted
            : '<span class="price-sign">' + currency + '</span> ' + formatted;
    }

    // ---------- Gallery ----------
    var gallery = document.querySelector('[data-gallery]');
    var track = gallery && gallery.querySelector('[data-gallery-track]');
    var zooms = gallery ? Array.prototype.slice.call(gallery.querySelectorAll('[data-zoom]')) : [];
    var thumbs = gallery ? Array.prototype.slice.call(gallery.querySelectorAll('[data-thumb]')) : [];
    var thumbStrip = gallery && gallery.querySelector('[data-gallery-thumbs]');
    var prev = gallery && gallery.querySelector('[data-gallery-prev]');
    var next = gallery && gallery.querySelector('[data-gallery-next]');
    var counter = gallery && gallery.querySelector('[data-gallery-current]');
    var current = 0;

    function showImage(index, instant) {
        index = Math.max(0, Math.min(zooms.length - 1, index));
        track.scrollTo({ left: index * track.clientWidth, behavior: instant ? 'auto' : smooth });
    }

    function markImage(index) {
        current = index;
        thumbs.forEach(function (thumb, i) {
            thumb.setAttribute('aria-current', i === index ? 'true' : 'false');
        });
        if (counter) {
            counter.textContent = index + 1;
        }
        if (prev) {
            prev.disabled = index === 0;
            next.disabled = index === zooms.length - 1;
        }
        // Keep the active thumbnail in view without scrolling the page
        var thumb = thumbs[index];
        if (thumb && thumbStrip) {
            var item = thumb.parentNode;
            thumbStrip.scrollTo({ left: item.offsetLeft - (thumbStrip.clientWidth - item.offsetWidth) / 2, behavior: smooth });
        }
    }

    if (track && zooms.length > 1) {
        var ticking = false;
        track.addEventListener('scroll', function () {
            if (ticking) {
                return;
            }
            ticking = true;
            window.requestAnimationFrame(function () {
                ticking = false;
                var index = Math.round(track.scrollLeft / Math.max(1, track.clientWidth));
                if (index !== current) {
                    markImage(index);
                }
            });
        }, { passive: true });

        thumbs.forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                showImage(Number(thumb.dataset.thumb));
            });
        });
        prev.addEventListener('click', function () { showImage(current - 1); });
        next.addEventListener('click', function () { showImage(current + 1); });

        track.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                event.preventDefault();
                showImage(current + (event.key === 'ArrowRight' ? 1 : -1));
                window.setTimeout(function () { zooms[current].focus({ preventScroll: true }); }, 300);
            }
        });

        // Keep the same image in place when the gallery changes width
        window.addEventListener('resize', function () { showImage(current, true); });
    }

    // ---------- Lightbox ----------
    var lightbox = document.querySelector('[data-lightbox]');
    if (lightbox && typeof lightbox.showModal === 'function') {
        var lightboxImage = lightbox.querySelector('[data-lightbox-image]');
        var lightboxCurrent = lightbox.querySelector('[data-lightbox-current]');
        var lightboxIndex = 0;

        var showLarge = function (index) {
            lightboxIndex = (index + zooms.length) % zooms.length;
            lightboxImage.src = zooms[lightboxIndex].dataset.large;
            lightboxImage.alt = zooms[lightboxIndex].querySelector('img').alt;
            lightboxCurrent.textContent = lightboxIndex + 1;
        };

        zooms.forEach(function (zoom) {
            zoom.addEventListener('click', function () {
                showLarge(Number(zoom.dataset.zoom));
                lightbox.showModal();
            });
        });

        lightbox.querySelector('[data-lightbox-close]').addEventListener('click', function () { lightbox.close(); });
        var lightboxPrev = lightbox.querySelector('[data-lightbox-prev]');
        if (lightboxPrev) {
            lightboxPrev.addEventListener('click', function () { showLarge(lightboxIndex - 1); });
            lightbox.querySelector('[data-lightbox-next]').addEventListener('click', function () { showLarge(lightboxIndex + 1); });
        }

        lightbox.addEventListener('keydown', function (event) {
            if (zooms.length > 1 && (event.key === 'ArrowLeft' || event.key === 'ArrowRight')) {
                event.preventDefault();
                showLarge(lightboxIndex + (event.key === 'ArrowRight' ? 1 : -1));
            }
        });

        // Swipe between images
        var startX = null;
        var swiped = false;
        lightbox.addEventListener('pointerdown', function (event) { startX = event.clientX; });
        lightbox.addEventListener('pointerup', function (event) {
            swiped = startX !== null && zooms.length > 1 && Math.abs(event.clientX - startX) > 50;
            if (swiped) {
                showLarge(lightboxIndex + (event.clientX < startX ? 1 : -1));
            }
            startX = null;
        });

        // A click on the dark area around the image closes it (a mouse swipe there isn't a click)
        lightbox.addEventListener('click', function (event) {
            if (swiped) {
                swiped = false;
                return;
            }
            if (event.target === lightbox || event.target.hasAttribute('data-lightbox-stage')) {
                lightbox.close();
            }
        });

        // Back on the page, the gallery shows the image looked at last, and it has focus
        lightbox.addEventListener('close', function () {
            if (track && zooms.length > 1) {
                showImage(lightboxIndex, true);
                markImage(lightboxIndex);
            }
            zooms[lightboxIndex].focus({ preventScroll: true });
        });
    }

    // ---------- Options ----------
    var options = Array.prototype.slice.call(document.querySelectorAll('[data-variant]'));
    var variantInput = document.querySelector('[data-variant-input]');
    var quantityInput = document.getElementById('quantity');
    var priceNow = document.querySelector('[data-price-now]');
    var priceWas = document.querySelector('[data-price-was]');
    var priceSave = document.querySelector('[data-price-save]');
    var gallerySave = document.querySelector('[data-gallery-save]');

    function showPrice(price, was) {
        priceNow.innerHTML = priceHtml(price);
        var saving = was > price ? was - price : 0;
        priceWas.hidden = !saving;
        priceSave.hidden = !saving;
        if (saving) {
            priceWas.querySelector('.price').innerHTML = priceHtml(was);
            priceSave.querySelector('.price').innerHTML = priceHtml(saving);
        }
        if (gallerySave) {
            gallerySave.hidden = !saving;
            if (saving) {
                gallerySave.querySelector('.price').innerHTML = priceHtml(saving);
            }
        }
    }

    function chooseOption(option) {
        options.forEach(function (item) {
            item.setAttribute('aria-checked', item === option ? 'true' : 'false');
            item.tabIndex = item === option ? 0 : -1;
        });
        variantInput.value = option.dataset.variant;
        showPrice(Number(option.dataset.price), Number(option.dataset.was));
        quantityInput.max = Math.max(1, Number(option.dataset.stock));
        if (Number(quantityInput.value) > Number(quantityInput.max)) {
            quantityInput.value = quantityInput.max;
        }
    }

    options.forEach(function (option, index) {
        option.addEventListener('click', function () { chooseOption(option); });
        // Arrow keys move between the choices, as in any radio group
        option.addEventListener('keydown', function (event) {
            var step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[event.key];
            if (!step) {
                return;
            }
            event.preventDefault();
            for (var i = 1; i < options.length; i++) {
                var candidate = options[(index + step * i + options.length) % options.length];
                if (!candidate.disabled) {
                    chooseOption(candidate);
                    candidate.focus();
                    break;
                }
            }
        });
    });

    // ---------- Quantity ----------
    document.querySelectorAll('[data-quantity-step]').forEach(function (button) {
        button.addEventListener('click', function () {
            var value = (Number(quantityInput.value) || 1) + Number(button.dataset.quantityStep);
            quantityInput.value = Math.max(1, Math.min(Number(quantityInput.max) || 99, value));
        });
    });

    // ---------- Add to cart ----------
    $(form).on('submit', function (event) {
        event.preventDefault();
        var button = $(this).find('.pdp-cart-btn');
        var label = button.html();
        button.prop('disabled', true).text('Adding…');

        $.ajax({
            url: form.dataset.cartUrl,
            method: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                toastr.success(response.message);
                setCartCount(response.cart_count);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not add this product to your cart. Try again.');
            },
            complete: function () {
                button.prop('disabled', false).html(label);
            }
        });
    });

    // ---------- Wishlist and compare ----------
    $('[data-wishlist]').on('click', function () {
        var button = $(this);
        button.prop('disabled', true);
        $.ajax({
            url: button.data('wishlist'),
            method: 'POST',
            data: { _token: csrfToken, product_id: productId },
            success: function (response) {
                button.attr('aria-pressed', response.in_wishlist ? 'true' : 'false');
                button.find('i').toggleClass('fas', response.in_wishlist).toggleClass('far', !response.in_wishlist);
                $('.wishlist-count').text(response.wishlist_count).prop('hidden', !response.wishlist_count);
                toastr.success(response.message);
            },
            error: function () {
                toastr.error('Could not update your wishlist. Try again.');
            },
            complete: function () {
                button.prop('disabled', false);
            }
        });
    });

    $('[data-compare]').on('click', function () {
        var price = priceNow.textContent.replace(/[^0-9.]/g, '');
        addToCompare(Number(productId), this.dataset.name, Number(price), this.dataset.image);
    });

    // ---------- The details bar marks the section being read ----------
    var links = Array.prototype.slice.call(document.querySelectorAll('[data-section-link]'));
    var sections = Array.prototype.slice.call(document.querySelectorAll('[data-section]'));
    if (links.length > 1) {
        var tabs = document.querySelector('.pdp-tabs');
        var spying = false;
        var markSection = function () {
            spying = false;
            // The last section whose top has passed under the sticky header and this bar,
            // or the last one when the page can't scroll any further
            var line = tabs.getBoundingClientRect().bottom + 24;
            var active = sections[0];
            sections.forEach(function (section) {
                if (section.getBoundingClientRect().top <= line) {
                    active = section;
                }
            });
            if (window.scrollY > 0 && window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) {
                active = sections[sections.length - 1];
            }
            links.forEach(function (link) {
                link.setAttribute('aria-current', link.getAttribute('href') === '#' + active.id ? 'true' : 'false');
            });
        };
        window.addEventListener('scroll', function () {
            if (!spying) {
                spying = true;
                window.requestAnimationFrame(markSection);
            }
        }, { passive: true });
        markSection();
    }
})();
</script>
@endpush
