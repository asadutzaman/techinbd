@props(['product', 'variant' => 'default', 'layout' => 'grid', 'specs' => false, 'compare' => false, 'lazy' => true, 'rank' => null, 'label' => null, 'wishlisted' => false])
@php
    // Expects mainImage eager loaded, plus withMax('variants', 'compare_price')
    $price = (float) $product->base_price;
    $was = (float) ($product->variants_max_compare_price ?? 0);
    $saving = $was > $price ? $was - $price : 0;
    $url = route('product.detail', $product);
    $keySpecs = $specs ? $product->keySpecs() : [];
    // The home page's rows (layout="rail"), after Best Buy's: a grey picture with the saving (or the rank) and a heart,
    // then the name, an optional label ("Top Deal"), the price and the old price. The card opens the product page.
    $rail = $layout === 'rail';
@endphp
<article @class(['sf-card', 'is-deal' => $variant === 'deal', 'is-rail' => $rail])>
    <div class="sf-card-media">
        @if($rail)
            @if($rank)
                <span class="sf-card-rank">#{{ $rank }}</span>
            @elseif($saving)
                <span class="sf-card-save-pill">Save <x-price :amount="$saving" :currency="$product->currency" /></span>
            @endif
            @auth
                <button type="button" class="sf-card-wish" data-wishlist-url="{{ route('customer.wishlist.toggle') }}" data-product-id="{{ $product->id }}"
                        aria-pressed="{{ $wishlisted ? 'true' : 'false' }}" aria-label="Save {{ $product->name }} to your wishlist">
                    <i class="{{ $wishlisted ? 'fas' : 'far' }} fa-heart" aria-hidden="true"></i>
                </button>
            @else
                <a class="sf-card-wish" href="{{ route('login') }}" aria-label="Sign in to save {{ $product->name }} to your wishlist">
                    <i class="far fa-heart" aria-hidden="true"></i>
                </a>
            @endauth
        @elseif($saving)
            <span class="sf-card-save">Save <x-price :amount="$saving" :currency="$product->currency" /></span>
        @endif
        <a class="sf-card-image" href="{{ $url }}" tabindex="-1" aria-hidden="true">
            <img src="{{ $product->main_image_url }}" alt="" width="600" height="600" loading="{{ $lazy ? 'lazy' : 'eager' }}" decoding="async">
        </a>
    </div>
    <h3 class="sf-card-title"><a href="{{ $url }}">{{ $product->name }}</a></h3>
    @if($keySpecs)
        <ul class="sf-card-specs">
            @foreach($keySpecs as $specLabel => $value)
                <li><span class="sf-card-spec-label">{{ $specLabel }}:</span> {{ $value }}</li>
            @endforeach
        </ul>
    @endif
    @if($rail)
        @if($label)
            <p class="sf-card-label">{{ $label }}</p>
        @endif
        <p class="sf-card-price"><x-price :amount="$price" :currency="$product->currency" /></p>
        @if($saving)
            <p class="sf-card-old"><s><span class="sr-only">Was </span><x-price :amount="$was" :currency="$product->currency" /></s></p>
        @endif
        @if($product->stock_status !== 'in_stock')
            <p class="sf-card-status">{{ $product->stock_status === 'preorder' ? 'Pre-order' : 'Out of stock' }}</p>
        @endif
    @else
        <p class="sf-card-price">
            <x-price :amount="$price" :currency="$product->currency" />
            @if($saving)
                <s class="sf-card-was"><span class="sr-only">Was </span><x-price :amount="$was" :currency="$product->currency" /></s>
            @endif
        </p>
        <div class="sf-card-actions">
            @if($product->stock_status === 'out_of_stock')
                <button type="button" class="sf-card-cta" disabled>Out of stock</button>
            @else
                <button type="button" class="sf-card-cta add-to-cart-btn" data-product-id="{{ $product->id }}" data-cart-url="{{ route('cart.add') }}">
                    <i class="fas {{ $variant === 'deal' ? 'fa-bolt' : 'fa-cart-plus' }}" aria-hidden="true"></i>{{ $product->stock_status === 'preorder' ? 'Pre-order' : 'Add to cart' }}
                </button>
            @endif
            @if($compare)
                <button type="button" class="sf-card-compare" data-compare-id="{{ $product->id }}" data-compare-name="{{ $product->name }}"
                        data-compare-price="{{ $price }}" data-compare-image="{{ $product->main_image_url }}"
                        aria-label="Compare {{ $product->name }}" title="Compare">
                    <i class="fas fa-balance-scale" aria-hidden="true"></i>
                </button>
            @endif
        </div>
    @endif
</article>
