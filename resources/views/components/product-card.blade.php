@props(['product', 'variant' => 'default', 'layout' => 'grid', 'specs' => false, 'compare' => false, 'lazy' => true])
@php
    // Expects mainImage eager loaded, plus withMax('variants', 'compare_price')
    $price = (float) $product->base_price;
    $was = (float) ($product->variants_max_compare_price ?? 0);
    $saving = $was > $price ? $was - $price : 0;
    $url = route('product.detail', $product);
    $keySpecs = $specs ? $product->keySpecs() : [];
    // The home page's rails: Best Buy-style cards, left-aligned, with the model, a saving tag and the stock
    $rail = $layout === 'rail';
@endphp
<article @class(['sf-card', 'is-deal' => $variant === 'deal', 'is-rail' => $rail])>
    <div class="sf-card-media">
        @if($rail)
            @if($variant === 'deal')
                <span class="sf-card-tag">Deal</span>
            @endif
        @elseif($saving)
            <span class="sf-card-save">Save <x-price :amount="$saving" :currency="$product->currency" /></span>
        @endif
        <a href="{{ $url }}" tabindex="-1" aria-hidden="true">
            <img src="{{ $product->main_image_url }}" alt="" width="600" height="600" loading="{{ $lazy ? 'lazy' : 'eager' }}" decoding="async">
        </a>
    </div>
    <h3 class="sf-card-title"><a href="{{ $url }}">{{ $product->name }}</a></h3>
    @if($rail && $product->manufacturer_part_no)
        <p class="sf-card-model">Model: {{ $product->manufacturer_part_no }}</p>
    @endif
    @if($keySpecs)
        <ul class="sf-card-specs">
            @foreach($keySpecs as $label => $value)
                <li><span class="sf-card-spec-label">{{ $label }}:</span> {{ $value }}</li>
            @endforeach
        </ul>
    @endif
    @if($rail)
        <div class="sf-card-pricing">
            <p class="sf-card-price"><x-price :amount="$price" :currency="$product->currency" /></p>
            @if($saving)
                <p class="sf-card-saving">
                    <span class="sf-card-save-tag">Save <x-price :amount="$saving" :currency="$product->currency" /></span>
                    <span class="sf-card-was">Was <x-price :amount="$was" :currency="$product->currency" /></span>
                </p>
            @endif
        </div>
        {{-- Pre-order and out of stock are what the button says --}}
        @if($product->stock_status === 'in_stock')
            <p class="sf-card-stock"><i class="fas fa-check" aria-hidden="true"></i>In stock</p>
        @endif
    @else
        <p class="sf-card-price">
            <x-price :amount="$price" :currency="$product->currency" />
            @if($saving)
                <s class="sf-card-was"><span class="sr-only">Was </span><x-price :amount="$was" :currency="$product->currency" /></s>
            @endif
        </p>
    @endif
    <div class="sf-card-actions">
        @if($product->stock_status === 'out_of_stock')
            <button type="button" class="sf-card-cta" disabled>Out of stock</button>
        @else
            <button type="button" class="sf-card-cta add-to-cart-btn" data-product-id="{{ $product->id }}" data-cart-url="{{ route('cart.add') }}">
                <i class="fas {{ $variant === 'deal' && ! $rail ? 'fa-bolt' : 'fa-cart-plus' }}" aria-hidden="true"></i>{{ $product->stock_status === 'preorder' ? 'Pre-order' : 'Add to cart' }}
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
