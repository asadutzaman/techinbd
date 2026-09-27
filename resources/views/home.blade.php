@extends('layouts.app')

@section('title', config('shop.name') . ' — ' . config('shop.tagline'))
@section('main_class', 'is-flush')

@php
    // The slider shares the row with the side banners on computers
    $sliderSizes = $sideBanners->isNotEmpty()
        ? '(min-width: 1536px) 1110px, (min-width: 992px) calc(75vw - 36px), (min-width: 768px) calc(100vw - 48px), calc(100vw - 32px)'
        : '(min-width: 1536px) 1488px, (min-width: 768px) calc(100vw - 48px), calc(100vw - 32px)';
@endphp

@section('content')
<div class="home">
    {{-- The page's main heading, for screen readers and search engines; the banners lead visually --}}
    <h1 class="sr-only">{{ config('shop.name') }}</h1>

    @if($sliderBanners->isNotEmpty() || $sideBanners->isNotEmpty())
        <!-- Hero: the banner slider, with up to two side banners (Admin → Home Banners) -->
        <section class="home-hero" aria-label="Offers and collections">
            <div class="sf-container">
                <div class="home-hero-grid {{ $sliderBanners->isNotEmpty() && $sideBanners->isNotEmpty() ? 'has-side' : '' }}">
                    @if($sliderBanners->isNotEmpty())
                        <div id="home-banner" class="home-banner carousel slide" aria-roledescription="carousel">
                            <div class="carousel-inner">
                                @foreach($sliderBanners as $banner)
                                    @php $tag = $banner->link_url ? 'a' : 'div'; @endphp
                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}" role="group" aria-roledescription="slide"
                                         aria-label="{{ $loop->iteration }} of {{ $sliderBanners->count() }}">
                                        <{{ $tag }} class="home-banner-slide" @if($banner->link_url) href="{{ $banner->link_url }}" @endif>
                                            <picture>
                                                <source media="(max-width: 767.98px)" srcset="{{ $banner->srcset('mobile') }}" sizes="calc(100vw - 32px)" width="1200" height="600">
                                                <img src="{{ $banner->src('desktop') }}" srcset="{{ $banner->srcset('desktop') }}"
                                                     sizes="{{ $sliderSizes }}" width="1600" height="533"
                                                     alt="{{ $banner->show_text ? '' : $banner->title }}" decoding="async"
                                                     @if($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                                            </picture>
                                            @if($banner->show_text)
                                                <span class="home-banner-caption">
                                                    <span class="home-banner-title">{{ $banner->title }}</span>
                                                    @if($banner->subtitle)
                                                        <span class="home-banner-sub">{{ $banner->subtitle }}</span>
                                                    @endif
                                                    @if($banner->link_url)
                                                        <span class="home-banner-cta">{{ $banner->button_text ?: 'Shop now' }} <span aria-hidden="true">→</span></span>
                                                    @endif
                                                </span>
                                            @endif
                                        </{{ $tag }}>
                                    </div>
                                @endforeach
                            </div>

                            @if($sliderBanners->count() > 1)
                                <button type="button" class="home-banner-arrow is-prev" data-target="#home-banner" data-slide="prev" aria-label="Previous banner">
                                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="home-banner-arrow is-next" data-target="#home-banner" data-slide="next" aria-label="Next banner">
                                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                                </button>
                                <div class="home-banner-controls">
                                    <span class="home-banner-count" aria-hidden="true">1 / {{ $sliderBanners->count() }}</span>
                                    <button type="button" class="home-banner-pause" aria-label="Pause banners">
                                        <i class="fas fa-pause icon-pause" aria-hidden="true"></i>
                                        <i class="fas fa-play icon-play" aria-hidden="true"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($sideBanners->isNotEmpty())
                        <div class="home-side">
                            @foreach($sideBanners as $banner)
                                @php $tag = $banner->link_url ? 'a' : 'div'; @endphp
                                <{{ $tag }} class="home-side-banner" @if($banner->link_url) href="{{ $banner->link_url }}" @endif>
                                    <img src="{{ $banner->src('side') }}" srcset="{{ $banner->srcset('side') }}"
                                         sizes="(min-width: 1536px) 370px, (min-width: 992px) calc(25vw - 12px), calc(50vw - 20px)"
                                         width="800" height="400" alt="{{ $banner->title }}" decoding="async">
                                </{{ $tag }}>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if($categories->isNotEmpty())
        <section class="sf-section" aria-labelledby="categories-title">
            <div class="sf-container">
                <div class="sf-section-head">
                    <div>
                        <h2 class="sf-section-title" id="categories-title">Featured Categories</h2>
                        <p class="sf-section-sub">Browse {{ number_format($productCount) }} {{ Str::plural('product', $productCount) }} by category</p>
                    </div>
                </div>
                <ul class="home-categories">
                    @foreach($categories as $category)
                        <li>
                            <a class="home-category" href="{{ route('shop', ['category' => $category->id]) }}">
                                @if($category->image_url)
                                    <img src="{{ $category->image_url }}" alt="" width="56" height="56" loading="lazy">
                                @else
                                    <x-category-icon :name="$category->icon" />
                                @endif
                                <span class="home-category-name">{{ $category->name }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if($deals->isNotEmpty())
        <x-product-rail id="deals" title="Deals" subtitle="Marked down from their regular price"
                        :products="$deals" :view-all="route('shop', ['sale' => 1])" variant="deal" />
    @endif

    @if($featured->isNotEmpty())
        <x-product-rail id="featured" title="Featured Products" subtitle="Picked by our team"
                        :products="$featured" :view-all="route('shop', ['featured' => 1])" />
    @endif

    @if($latest->isNotEmpty())
        <x-product-rail id="latest" title="Latest Products" subtitle="New in the shop"
                        :products="$latest" :view-all="route('shop', ['sort' => 'latest'])" />
    @endif

    @if($bestSellers->isNotEmpty())
        <x-product-rail id="best-sellers" title="Best Sellers" subtitle="Most ordered by our customers" :products="$bestSellers" />
    @endif
</div>
@endsection

@push('styles')
@if($sliderBanners->isNotEmpty())
    {{-- Start the first banner (the largest thing on screen) downloading straight away --}}
    <link rel="preload" as="image" media="(max-width: 767.98px)"
          imagesrcset="{{ $sliderBanners->first()->srcset('mobile') }}" imagesizes="calc(100vw - 32px)" fetchpriority="high">
    <link rel="preload" as="image" media="(min-width: 768px)"
          imagesrcset="{{ $sliderBanners->first()->srcset('desktop') }}" imagesizes="{{ $sliderSizes }}" fetchpriority="high">
@endif
<style>
/* ---------- Hero: slider and side banners ---------- */
.home-hero {
    padding: 12px 0 4px;
}

.home-hero-grid {
    display: grid;
    gap: 8px;
}

.home-banner {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    background: var(--sf-line);
}

.home-banner-slide,
.home-banner-slide:hover {
    position: relative;
    display: block;
    color: var(--sf-ink);
    text-decoration: none;
}

.home-banner img {
    display: block;
    width: 100%;
    height: auto;
    aspect-ratio: 3 / 1;
    object-fit: cover;
}

/* Text for banners that don't have it designed in, on a white panel */
.home-banner-caption {
    position: absolute;
    left: clamp(1rem, 3vw, 2.5rem);
    bottom: clamp(1rem, 3vw, 2.5rem);
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: .35rem;
    max-width: min(30rem, 52%);
    padding: 1.1rem 1.3rem 1.2rem;
    background: rgba(255, 255, 255, .96);
    border-radius: 8px;
    box-shadow: 0 16px 32px -18px rgba(17, 24, 39, .5);
}

.home-banner-title {
    font-size: clamp(1.25rem, .9rem + 1.2vw, 2rem);
    font-weight: 700;
    line-height: 1.15;
    color: var(--sf-ink);
}

.home-banner-sub {
    font-size: .95rem;
    line-height: 1.45;
    color: var(--sf-muted);
}

.home-banner-cta {
    margin-top: .5rem;
    padding: .5rem 1rem;
    background: var(--sf-button);
    border-radius: 6px;
    font-size: .92rem;
    font-weight: 600;
    color: #FFFFFF;
}

.home-banner-arrow {
    position: absolute;
    top: 50%;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    padding: 0;
    background: rgba(255, 255, 255, .92);
    border: 0;
    border-radius: 50%;
    box-shadow: 0 4px 12px rgba(17, 24, 39, .2);
    font-size: 14px;
    color: var(--sf-ink);
    transform: translateY(-50%);
}

.home-banner-arrow.is-prev {
    left: 12px;
}

.home-banner-arrow.is-next {
    right: 12px;
}

.home-banner-arrow:hover {
    background: #FFFFFF;
    color: var(--sf-accent);
}

.home-banner-controls {
    position: absolute;
    right: 12px;
    bottom: 12px;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 2px;
    padding: 2px 2px 2px 10px;
    background: rgba(255, 255, 255, .92);
    border-radius: 999px;
    box-shadow: 0 4px 12px rgba(17, 24, 39, .2);
}

.home-banner-count {
    font-size: 12px;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
    color: var(--sf-ink);
}

.home-banner-pause {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    padding: 0;
    background: none;
    border: 0;
    border-radius: 50%;
    font-size: 11px;
    color: var(--sf-ink);
}

.home-banner-pause:hover {
    color: var(--sf-accent);
}

.home-banner .icon-play,
.home-banner.is-paused .icon-pause {
    display: none;
}

.home-banner.is-paused .icon-play {
    display: inline;
}

/* Side banners: side by side under the slider on phones and tablets, stacked beside it on computers */
.home-side {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.home-side-banner {
    position: relative;
    display: block;
    overflow: hidden;
    border-radius: 8px;
    background: var(--sf-line);
}

.home-side-banner img {
    display: block;
    width: 100%;
    height: auto;
    aspect-ratio: 2 / 1;
    object-fit: cover;
    transition: transform .3s ease;
}

.home-side-banner:hover img {
    transform: scale(1.03);
}

/* ---------- Featured Categories ---------- */
.home-categories {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.home-category {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    height: 100%;
    padding: 12px 4px;
    background: var(--sf-surface);
    border: 1px solid var(--sf-line);
    border-radius: 8px;
    text-align: center;
    color: var(--sf-label);
    transition: transform .2s ease, box-shadow .2s ease;
}

.home-category:hover {
    transform: translateY(-2px);
    box-shadow: var(--sf-shadow);
    color: var(--sf-accent);
    text-decoration: none;
}

.home-category .category-icon,
.home-category img {
    flex: none;
    width: 40px;
    height: 40px;
    object-fit: contain;
    color: var(--sf-ink);
    stroke-width: 1.1;
}

.home-category-name {
    display: -webkit-box;
    overflow: hidden;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.3;
}

/* Four columns until there's room for eight, so 16 categories always fill whole rows */
@media (min-width: 576px) {
    .home-categories {
        gap: 12px;
    }
}

@media (min-width: 768px) {
    .home-hero-grid,
    .home-side {
        gap: 16px;
    }

    .home-category {
        padding: 16px 8px;
        border-radius: 12px;
    }

    .home-category .category-icon,
    .home-category img {
        width: 56px;
        height: 56px;
    }

    .home-category-name {
        font-size: 14px;
    }
}

@media (min-width: 992px) {
    .home-hero {
        padding: 24px 0 8px;
    }

    .home-hero-grid.has-side {
        grid-template-columns: minmax(0, 3fr) minmax(0, 1fr);
    }

    /* Two banners share the slider's height */
    .home-hero-grid.has-side .home-side {
        grid-template-columns: minmax(0, 1fr);
        grid-template-rows: repeat(2, minmax(0, 1fr));
    }

    .home-hero-grid.has-side .home-side-banner img {
        position: absolute;
        inset: 0;
        height: 100%;
        aspect-ratio: auto;
    }

    .home-categories {
        grid-template-columns: repeat(8, minmax(0, 1fr));
        gap: 16px;
    }
}

/* Narrower slides: smaller arrows, tucked closer to the edge so they stay clear of the artwork's text */
@media (max-width: 1279.98px) {
    .home-banner-arrow {
        width: 32px;
        height: 32px;
        font-size: 12px;
    }

    .home-banner-arrow.is-prev {
        left: 8px;
    }

    .home-banner-arrow.is-next {
        right: 8px;
    }
}

/* Tablets and phones: the banner text sits under the image, so the picture stays uncovered */
@media (max-width: 991.98px) {
    .home-banner-caption {
        position: static;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        grid-template-areas:
            "title cta"
            "sub cta";
        align-items: center;
        gap: .15rem 1rem;
        max-width: none;
        padding: .85rem 1.1rem .95rem;
        border-radius: 0;
        box-shadow: none;
    }

    .home-banner-title {
        grid-area: title;
        font-size: 1.15rem;
    }

    .home-banner-sub {
        grid-area: sub;
        font-size: .88rem;
    }

    .home-banner-cta {
        grid-area: cta;
        margin: 0;
    }
}

@media (max-width: 767.98px) {
    /* Phones: the 2:1 phone image, and smaller controls */
    .home-banner img {
        aspect-ratio: 2 / 1;
    }

    .home-banner-sub {
        display: none;
    }

    /* Phones swipe between slides; arrows would cover the banner's text */
    .home-banner-arrow {
        display: none;
    }

    .home-banner-controls {
        right: 8px;
        bottom: 8px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .home-category,
    .home-side-banner img {
        transition: none;
    }

    .home-category:hover,
    .home-side-banner:hover img {
        transform: none;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Banner slider: moves every 6s unless the visitor prefers reduced motion; anyone can pause it
    var $banner = $('#home-banner');
    if ($banner.find('.carousel-item').length > 1) {
        var total = $banner.find('.carousel-item').length;
        var setPaused = function(paused) {
            $banner.toggleClass('is-paused', paused);
            $banner.find('.home-banner-pause').attr('aria-label', paused ? 'Play banners' : 'Pause banners');
            $banner.carousel(paused ? 'pause' : 'cycle');
        };

        // ride starts the cycle; Bootstrap 4 doesn't autoplay without it
        $banner.carousel({ interval: 6000, ride: 'carousel', pause: 'hover', touch: true });
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            setPaused(true);
        }

        $banner.on('slid.bs.carousel', function(e) {
            $banner.find('.home-banner-count').text((e.to + 1) + ' / ' + total);
        });

        $banner.find('.home-banner-pause').on('click', function() {
            setPaused(!$banner.hasClass('is-paused'));
        });
    }

    // The product cards' cart buttons are wired up in public/js/storefront.js
});
</script>
@endpush
