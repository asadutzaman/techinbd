@props(['product', 'variant' => 'default', 'specs' => false, 'compare' => false, 'lazy' => true])
@php
    // Expects mainImage eager loaded, plus withMax('variants', 'compare_price')
    $price = (float) $product->base_price;
    $was = (float) ($product->variants_max_compare_price ?? 0);
    $saving = $was > $price ? $was - $price : 0;
    $url = route('product.detail', $product);
    $keySpecs = $specs ? $product->keySpecs() : [];
@endphp
<article class="sf-card {{ $variant === 'deal' ? 'is-deal' : '' }}">
    <div class="sf-card-media">
        @if($saving)
            <span class="sf-card-save">Save <x-price :amount="$saving" :currency="$product->currency" /></span>
        @endif
        <a href="{{ $url }}" tabindex="-1" aria-hidden="true">
            <img src="{{ $product->main_image_url }}" alt="" width="600" height="600" loading="{{ $lazy ? 'lazy' : 'eager' }}" decoding="async">
        </a>
    </div>
    <h3 class="sf-card-title"><a href="{{ $url }}">{{ $product->name }}</a></h3>
    @if($keySpecs)
        <ul class="sf-card-specs">
            @foreach($keySpecs as $label => $value)
                <li><span class="sf-card-spec-label">{{ $label }}:</span> {{ $value }}</li>
            @endforeach
        </ul>
    @endif
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
</article>
