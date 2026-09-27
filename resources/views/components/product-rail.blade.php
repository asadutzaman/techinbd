@props(['id', 'title', 'subtitle' => null, 'products', 'viewAll' => null, 'variant' => 'default'])
{{-- A home page row of product cards that scrolls sideways; storefront.js wires up the arrow buttons --}}
<section class="sf-section" aria-labelledby="{{ $id }}-title" data-rail>
    <div class="sf-container">
        <div class="sf-section-head">
            <div>
                <h2 class="sf-section-title" id="{{ $id }}-title">{{ $title }}</h2>
                @if($subtitle)
                    <p class="sf-section-sub">{{ $subtitle }}</p>
                @endif
            </div>
            <div class="sf-section-tools">
                <div class="sf-rail-nav">
                    <button type="button" class="sf-round-btn" data-rail-prev aria-controls="{{ $id }}-rail" aria-label="Scroll {{ $title }} back">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="sf-round-btn" data-rail-next aria-controls="{{ $id }}-rail" aria-label="Scroll {{ $title }} forward">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
                @if($viewAll)
                    <a class="sf-view-all" href="{{ $viewAll }}">View all<span class="sr-only"> {{ $title }}</span> <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                @endif
            </div>
        </div>
        <ul class="sf-rail" id="{{ $id }}-rail">
            @foreach($products as $product)
                <li><x-product-card :product="$product" :variant="$variant" /></li>
            @endforeach
        </ul>
    </div>
</section>
