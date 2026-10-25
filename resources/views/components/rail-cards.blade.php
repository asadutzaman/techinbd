@props(['id', 'title', 'products', 'wishlist' => [], 'label' => null, 'ranked' => false, 'variant' => 'default'])
{{--
    A row of home page cards that scrolls sideways, with arrows over its ends (storefront.js wires them up through
    data-rail). Ranked rows number their cards (#1, #2…); a label ("Top Deal") goes above each price.
--}}
<div class="sf-rail-row" data-rail>
    <button type="button" class="sf-rail-arrow is-prev" data-rail-prev aria-controls="{{ $id }}-rail" aria-label="Scroll {{ $title }} back">
        <i class="fas fa-chevron-left" aria-hidden="true"></i>
    </button>
    <ul class="sf-rail" id="{{ $id }}-rail">
        @foreach($products as $product)
            <li>
                <x-product-card :product="$product" :variant="$variant" layout="rail" :label="$label"
                                :rank="$ranked ? $loop->iteration : null" :wishlisted="in_array($product->id, $wishlist)" />
            </li>
        @endforeach
    </ul>
    <button type="button" class="sf-rail-arrow is-next" data-rail-next aria-controls="{{ $id }}-rail" aria-label="Scroll {{ $title }} forward">
        <i class="fas fa-chevron-right" aria-hidden="true"></i>
    </button>
</div>
