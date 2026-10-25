@props(['tabs', 'wishlist' => []])
{{--
    Trending deals: a tab per category (HomeController::trendingDeals()), each a ranked row of its deals. storefront.js
    switches the tabs ([data-tabs]) and points "See more" at the open tab's deals.
--}}
@php($first = $tabs->first()['category'])
<section class="sf-section" aria-labelledby="trending-title">
    <div class="sf-container">
        <div class="sf-panel" data-tabs>
            <div class="sf-panel-head">
                <h2 class="sf-section-title" id="trending-title">Trending deals</h2>
                <a class="sf-panel-more" href="{{ route('shop.category', ['category' => $first, 'sale' => 1]) }}" data-tabs-more>
                    See more<span class="sr-only" data-tabs-more-label> {{ $first->name }} deals</span>
                </a>
            </div>
            <div class="home-trending-tabs" role="tablist" aria-label="Trending deals by category">
                @foreach($tabs as $tab)
                    <button type="button" role="tab" id="trending-tab-{{ $tab['category']->slug }}" aria-controls="trending-{{ $tab['category']->slug }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? '0' : '-1' }}"
                            data-more-url="{{ route('shop.category', ['category' => $tab['category'], 'sale' => 1]) }}"
                            data-more-label=" {{ $tab['category']->name }} deals">{{ $tab['category']->name }}</button>
                @endforeach
            </div>
            @foreach($tabs as $tab)
                <div role="tabpanel" id="trending-{{ $tab['category']->slug }}" aria-labelledby="trending-tab-{{ $tab['category']->slug }}" @if(! $loop->first) hidden @endif>
                    <x-rail-cards :id="'trending-' . $tab['category']->slug" :title="$tab['category']->name . ' deals'"
                                  :products="$tab['products']" :wishlist="$wishlist" :ranked="true" />
                </div>
            @endforeach
        </div>
    </div>
</section>
