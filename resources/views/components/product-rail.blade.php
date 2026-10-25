@props(['id', 'title', 'subtitle' => null, 'products', 'viewAll' => null, 'wishlist' => []])
{{-- A home page row of product cards in a white panel, with its title and a link to the whole list --}}
<section class="sf-section" aria-labelledby="{{ $id }}-title">
    <div class="sf-container">
        <div class="sf-panel">
            <div class="sf-panel-head">
                <div>
                    <h2 class="sf-section-title" id="{{ $id }}-title">{{ $title }}</h2>
                    @if($subtitle)
                        <p class="sf-section-sub">{{ $subtitle }}</p>
                    @endif
                </div>
                @if($viewAll)
                    <a class="sf-panel-more" href="{{ $viewAll }}">See more<span class="sr-only"> {{ $title }}</span></a>
                @endif
            </div>
            <x-rail-cards :id="$id" :title="$title" :products="$products" :wishlist="$wishlist" />
        </div>
    </div>
</section>
