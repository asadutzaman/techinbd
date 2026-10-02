@extends('layouts.app')

@php
    $total = $products->total();
    // Top-level categories with products; the open category's family also lists its subcategories
    $categoryList = $categories->filter(fn ($item) => $item->products_count > 0);
    $openFamily = $category?->parent_id ?? $category?->id;
    $subcategoryList = $categoryList->firstWhere('id', $openFamily)?->children->filter(fn ($child) => $child->products_count > 0) ?? collect();
    // Brands with products here, plus any already chosen so they can be unticked
    $brandList = $brands->filter(fn ($brand) => $brand->products_count > 0 || in_array($brand->id, $filters->brands));
    $clearUrl = $filters->withoutFilters()->url();
    // Picking a category keeps the search, sort and the in stock / sale / featured ticks; brands, prices and
    // attributes belong to the category that was open
    $categoryUrl = fn (?App\Models\Category $item) => $filters->url(['category' => $item, 'brands' => [], 'attributes' => [], 'minPrice' => null, 'maxPrice' => null]);
    // Colour attributes get a swatch; only plain CSS colour names ("Black", "Space Gray" → spacegray) reach the style
    $isColour = fn (array $attribute) => $attribute['type'] === 'color' || in_array(Str::lower($attribute['name']), ['color', 'colour']);
    $swatch = fn (string $value) => preg_match('/^[a-z]+$/i', $plain = str_replace(' ', '', $value)) ? Str::lower($plain) : 'transparent';
    $crumb = match (true) {
        $filters->search !== null => 'Search results',
        $heading === 'All products' => null,
        default => $heading,
    };
@endphp

@section('title', $heading . ' | ' . config('shop.name'))
@section('meta_description', $total
    ? $heading . ': ' . $total . ' ' . Str::plural('product', $total) . ' from ৳' . number_format((float) $prices->low) . ' at ' . config('shop.name') . '.'
    : config('shop.description'))
@section('main_class', 'is-flush')

@section('content')
<nav class="sf-crumbs" aria-label="Breadcrumb">
    <div class="sf-container">
        <ol>
            <li><a href="{{ route('home') }}">Home</a></li>
            @if($crumb)
                <li><a href="{{ route('shop') }}">Shop</a></li>
                @if($category?->parent?->status && $crumb === $category->name)
                    <li><a href="{{ route('shop.category', $category->parent) }}">{{ $category->parent->name }}</a></li>
                @endif
                <li aria-current="page">{{ $crumb }}</li>
            @else
                <li aria-current="page">Shop</li>
            @endif
        </ol>
    </div>
</nav>

<div class="sf-container">
    <div class="shop-layout">
        <!-- Filters: a card beside the products on computers, a panel that slides in on smaller screens -->
        <aside class="shop-filters" id="shop-filters" aria-labelledby="shop-filters-title" data-drawer data-drawer-until="992">
            <div class="shop-filters-backdrop" data-drawer-close></div>
            <div class="shop-filters-panel">
                <div class="shop-filters-head">
                    <h2 class="shop-filters-title" id="shop-filters-title">Filters</h2>
                    @if($filters->filtered())
                        <a class="shop-clear" href="{{ $clearUrl }}">Clear all</a>
                    @endif
                    <button type="button" class="sf-icon-btn shop-filters-close" data-drawer-close aria-label="Close filters">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>

                <form class="shop-filters-form" id="shop-filter-form" action="{{ $filters->path() }}" method="GET" data-drawer-body>
                    @if($filters->search !== null)
                        <input type="hidden" name="search" value="{{ $filters->search }}">
                    @endif
                    @if(! $total && $filters->sort)
                        {{-- The sort menu only shows with results --}}
                        <input type="hidden" name="sort" value="{{ $filters->sort }}">
                    @endif

                    {{-- Inside a category the list folds away (the heading names it), putting brands and prices first --}}
                    <details class="shop-facet" @if(! $category) open @endif>
                        <summary>
                            <span class="shop-facet-name">Category</span>
                            @if($category)
                                <span class="shop-facet-value">{{ $category->name }}</span>
                            @endif
                        </summary>
                        <ul class="shop-options {{ $categoryList->count() + $subcategoryList->count() >= 8 ? 'is-long' : '' }}">
                            <li>
                                <a class="shop-option" href="{{ $categoryUrl(null) }}" @if(! $filters->category) aria-current="page" @endif>
                                    <span class="shop-option-label">All categories</span>
                                    <span class="shop-count">{{ $totalActiveProducts }}</span>
                                </a>
                            </li>
                            @foreach($categoryList as $item)
                                <li>
                                    <a class="shop-option" href="{{ $categoryUrl($item) }}" @if($filters->category?->id === $item->id) aria-current="page" @endif>
                                        <span class="shop-option-label">{{ $item->name }}</span>
                                        <span class="shop-count">{{ $item->products_count }}</span>
                                    </a>
                                    @if($item->id === $openFamily && $subcategoryList->isNotEmpty())
                                        <ul class="shop-suboptions">
                                            @foreach($subcategoryList as $child)
                                                <li>
                                                    <a class="shop-option" href="{{ $categoryUrl($child) }}" @if($filters->category?->id === $child->id) aria-current="page" @endif>
                                                        <span class="shop-option-label">{{ $child->name }}</span>
                                                        <span class="shop-count">{{ $child->products_count }}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </details>

                    @if($brandList->isNotEmpty())
                        <details class="shop-facet" open>
                            <summary>Brand</summary>
                            <ul class="shop-options {{ $brandList->count() > 8 ? 'is-long' : '' }}">
                                @foreach($brandList as $brand)
                                    <li>
                                        <label class="shop-option">
                                            <input type="checkbox" name="brand[]" value="{{ $brand->id }}" @checked(in_array($brand->id, $filters->brands))>
                                            <span class="shop-option-label">{{ $brand->name }}</span>
                                            <span class="shop-count">{{ $brand->products_count }}</span>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    @endif

                    <details class="shop-facet" open>
                        <summary>Price</summary>
                        <div class="shop-price">
                            <label class="shop-price-field">
                                <span class="price-sign" aria-hidden="true">৳</span>
                                <span class="sr-only">Lowest price in taka</span>
                                <input type="number" name="min_price" value="{{ $filters->minPrice }}" min="0" step="1" inputmode="numeric"
                                       placeholder="{{ $prices->low !== null ? number_format(floor($prices->low)) : 'Min' }}">
                            </label>
                            <span class="shop-price-to" aria-hidden="true">to</span>
                            <label class="shop-price-field">
                                <span class="price-sign" aria-hidden="true">৳</span>
                                <span class="sr-only">Highest price in taka</span>
                                <input type="number" name="max_price" value="{{ $filters->maxPrice }}" min="0" step="1" inputmode="numeric"
                                       placeholder="{{ $prices->high !== null ? number_format(ceil($prices->high)) : 'Max' }}">
                            </label>
                            <button type="submit" class="shop-apply">Apply</button>
                        </div>
                    </details>

                    <details class="shop-facet" open>
                        <summary>Availability</summary>
                        <ul class="shop-options">
                            <li>
                                <label class="shop-option">
                                    <input type="checkbox" name="in_stock" value="1" @checked($filters->inStock)>
                                    <span class="shop-option-label">In stock</span>
                                </label>
                            </li>
                            <li>
                                <label class="shop-option">
                                    <input type="checkbox" name="sale" value="1" @checked($filters->sale)>
                                    <span class="shop-option-label">On sale</span>
                                </label>
                            </li>
                            <li>
                                <label class="shop-option">
                                    <input type="checkbox" name="featured" value="1" @checked($filters->featured)>
                                    <span class="shop-option-label">Featured</span>
                                </label>
                            </li>
                        </ul>
                    </details>

                    @foreach($filterableAttributes as $attribute)
                        @php($chosen = $filters->attributes[$attribute['id']] ?? [])
                        <details class="shop-facet" @if($chosen) open @endif>
                            <summary>{{ $attribute['name'] }}</summary>
                            <ul class="shop-options {{ count($attribute['values']) > 8 ? 'is-long' : '' }}">
                                @foreach($attribute['values'] as $value)
                                    <li>
                                        <label class="shop-option">
                                            <input type="checkbox" name="attributes[{{ $attribute['id'] }}][]" value="{{ $value }}" @checked(in_array($value, $chosen, true))>
                                            @if($isColour($attribute))
                                                <span class="shop-swatch" style="--swatch: {{ $swatch($value) }}" aria-hidden="true"></span>
                                            @endif
                                            <span class="shop-option-label">{{ $value }}</span>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    @endforeach

                    <!-- Phones and tablets: choices wait for this button -->
                    <div class="shop-filters-foot">
                        <a class="shop-foot-clear" href="{{ $clearUrl }}">Clear all</a>
                        <button type="submit" class="shop-foot-apply">Show results</button>
                    </div>
                </form>
            </div>
        </aside>

        <section class="shop-results" aria-labelledby="shop-title">
            <div class="shop-head">
                <div>
                    <h1 class="shop-title" id="shop-title">{{ $heading }}</h1>
                    @if($total)
                        <p class="shop-summary">
                            {{ $total }} {{ Str::plural('product', $total) }}
                            · <x-price :amount="$prices->low" />@if((float) $prices->high > (float) $prices->low) to <x-price :amount="$prices->high" />@endif
                            @if($filters->search !== null)
                                · <a href="{{ $filters->url(['search' => null]) }}">Clear search</a>
                            @endif
                        </p>
                    @endif
                </div>
                <div class="shop-tools">
                    <button type="button" class="shop-filter-btn" data-drawer-open aria-controls="shop-filters" aria-expanded="false">
                        <i class="fas fa-sliders-h" aria-hidden="true"></i>Filters
                        @if($chips)
                            <span class="shop-filter-count">{{ count($chips) }}<span class="sr-only"> on</span></span>
                        @endif
                    </button>
                    @if($total)
                        <div class="shop-sort">
                            <label for="shop-sort">Sort by</label>
                            <select id="shop-sort" name="sort" form="shop-filter-form">
                                @foreach(App\Support\ShopFilters::SORTS as $value => $label)
                                    <option value="{{ $value }}" @selected($filters->sortKey() === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <noscript><button type="submit" form="shop-filter-form" class="shop-apply">Sort</button></noscript>
                        </div>
                    @endif
                </div>
            </div>

            @if($chips)
                <ul class="shop-chips" aria-label="Filters in use">
                    @foreach($chips as $chip)
                        <li>
                            {{-- The label is escaped first; only the taka sign gets the price font --}}
                            <a class="shop-chip" href="{{ $chip['url'] }}"><span class="sr-only">Remove filter: </span>{!! str_replace('৳', '<span class="price-sign">৳</span>', e($chip['label'])) !!}<i class="fas fa-times" aria-hidden="true"></i></a>
                        </li>
                    @endforeach
                    <li><a class="shop-chips-clear" href="{{ $clearUrl }}">Clear all</a></li>
                </ul>
            @endif

            @if($total)
                <ul class="shop-grid">
                    @foreach($products as $product)
                        <li>
                            <x-product-card :product="$product" :variant="$filters->sale ? 'deal' : 'default'" :specs="true" :compare="true" :lazy="$loop->index >= 4" />
                        </li>
                    @endforeach
                </ul>

                @if($products->hasPages())
                    <div class="shop-pages">
                        <p class="shop-showing">Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $total }} products</p>
                        {{ $products->onEachSide(1)->links('custom-pagination') }}
                    </div>
                @endif
            @else
                <div class="shop-empty">
                    <span class="shop-empty-icon" aria-hidden="true"><i class="fas fa-search"></i></span>
                    @if($filters->search !== null)
                        <h2>No products found</h2>
                        <p>Check the spelling, or search for a brand or model instead.</p>
                    @elseif($filters->filtered())
                        <h2>No products match these filters</h2>
                        <p>Remove a filter or two to see more.</p>
                    @else
                        <h2>Nothing here yet</h2>
                        <p>New products are on their way. Browse the rest of the shop in the meantime.</p>
                    @endif
                    <div class="shop-empty-actions">
                        @if($filters->filtered())
                            <a class="sf-btn" href="{{ $clearUrl }}">Clear all filters</a>
                        @endif
                        <a class="sf-pill" href="{{ route('shop') }}">Browse all products</a>
                    </div>
                    @if($filters->search !== null && $categoryList->isNotEmpty())
                        <p class="shop-empty-label">Or pick a category</p>
                        <ul class="shop-empty-categories">
                            @foreach($categoryList->sortByDesc('products_count')->take(8) as $item)
                                <li><a class="sf-view-all" href="{{ route('shop.category', $item) }}">{{ $item->name }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ---------- Layout: filters beside the products from 992px ---------- */
.shop-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 24px;
    align-items: start;
    padding: 20px 0 8px;
}

.shop-results {
    min-width: 0;
}

/* ---------- Filters ---------- */
.shop-filters-panel {
    background: var(--sf-surface);
    border: 1px solid var(--sf-line);
    border-radius: var(--sf-radius);
}

.shop-filters-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--sf-line);
}

.shop-filters-title {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: var(--sf-ink);
}

.shop-clear {
    margin-left: auto;
    font-size: 13px;
    font-weight: 600;
    color: var(--sf-accent-strong);
}

.shop-filters-close,
.shop-filters-foot {
    display: none;
}

.shop-facet + .shop-facet {
    border-top: 1px solid var(--sf-line);
}

.shop-facet > summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 700;
    color: var(--sf-ink);
    list-style: none;
    cursor: pointer;
}

.shop-facet-name {
    flex: 1;
}

.shop-facet-value {
    min-width: 0;
    overflow: hidden;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    text-overflow: ellipsis;
    color: var(--sf-accent-strong);
}

.shop-facet > summary::-webkit-details-marker {
    display: none;
}

.shop-facet > summary::after {
    content: "\f078";
    font-family: "Font Awesome 5 Free";
    font-size: 11px;
    font-weight: 900;
    color: var(--sf-faint);
    transition: transform .15s ease;
}

.shop-facet[open] > summary::after {
    transform: rotate(180deg);
}

.shop-facet > summary:hover {
    color: var(--sf-accent-strong);
}

.shop-options {
    margin: 0;
    padding: 0 8px 12px;
    list-style: none;
}

/* Long lists scroll inside their section on computers */
.shop-options.is-long {
    max-height: 272px;
    margin-right: 4px;
    overflow-y: auto;
    overscroll-behavior: contain;
    scrollbar-width: thin;
}

.shop-option {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 34px;
    margin: 0;
    padding: 5px 8px;
    border-radius: 6px;
    font-size: 14px;
    line-height: 1.35;
    color: var(--sf-text);
    cursor: pointer;
}

.shop-option:hover {
    background: var(--sf-hover);
    color: var(--sf-ink);
    text-decoration: none;
}

a.shop-option[aria-current="page"] {
    background: #EFF6FF;
    font-weight: 600;
    color: var(--sf-accent-strong);
}

.shop-option input {
    flex: none;
    width: 16px;
    height: 16px;
    margin: 0;
    accent-color: var(--sf-accent);
    cursor: pointer;
}

.shop-option-label {
    flex: 1;
    min-width: 0;
}

/* The open category's subcategories, under it */
.shop-suboptions {
    margin: 0;
    padding: 0 0 0 14px;
    list-style: none;
}

.shop-count {
    flex: none;
    font-size: 12px;
    font-variant-numeric: tabular-nums;
    color: var(--sf-faint);
}

.shop-swatch {
    flex: none;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: var(--swatch, transparent);
    box-shadow: inset 0 0 0 1px rgba(17, 24, 39, .25);
}

.shop-price {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
    align-items: center;
    gap: 8px;
    padding: 0 16px 14px;
}

.shop-price-field {
    display: flex;
    align-items: center;
    gap: 4px;
    min-width: 0;
    height: 38px;
    margin: 0;
    padding: 0 10px;
    background: var(--sf-surface);
    border: 1px solid var(--sf-line-strong);
    border-radius: 8px;
    cursor: text;
}

.shop-price-field:focus-within {
    border-color: var(--sf-accent);
    box-shadow: inset 0 0 0 1px var(--sf-accent);
}

.shop-price-field .price-sign {
    font-size: 14px;
    color: var(--sf-faint);
}

.shop-price-field input {
    flex: 1;
    width: 100%;
    min-width: 0;
    height: 100%;
    padding: 0;
    background: transparent;
    border: 0;
    font-size: 14px;
    color: var(--sf-ink);
    -moz-appearance: textfield;
    appearance: textfield;
}

.shop-price-field input:focus,
.shop-price-field input:focus-visible {
    outline: 0;
}

.shop-price-field input::-webkit-inner-spin-button,
.shop-price-field input::-webkit-outer-spin-button {
    margin: 0;
    -webkit-appearance: none;
}

.shop-price-field input::placeholder {
    color: var(--sf-faint);
}

.shop-price-to {
    font-size: 13px;
    color: var(--sf-faint);
}

.shop-apply {
    grid-column: 1 / -1;
    height: 38px;
    padding: 0 14px;
    background: var(--sf-surface);
    border: 1px solid var(--sf-line-strong);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: var(--sf-text);
}

.shop-apply:hover {
    border-color: var(--sf-accent);
    color: var(--sf-accent);
}

/* ---------- Heading, sort and the Filters button ---------- */
.shop-head {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: 12px 24px;
    margin-bottom: 16px;
}

.shop-title {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    line-height: 1.3;
    color: var(--sf-ink);
}

.shop-summary {
    margin: 4px 0 0;
    font-size: 14px;
    color: var(--sf-muted);
}

.shop-summary .price {
    font-weight: 600;
    color: var(--sf-text);
}

.shop-summary a {
    font-weight: 600;
    color: var(--sf-accent-strong);
}

.shop-tools {
    display: flex;
    align-items: center;
    gap: 10px;
}

.shop-filter-btn {
    display: none;
    flex: 1;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 42px;
    padding: 0 14px;
    background: var(--sf-surface);
    border: 1px solid var(--sf-line-strong);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: var(--sf-text);
}

.shop-filter-btn:hover {
    border-color: var(--sf-accent);
    color: var(--sf-accent);
}

.shop-filter-count {
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 10px;
    background: var(--sf-accent);
    font-size: 12px;
    font-weight: 700;
    line-height: 20px;
    color: #FFFFFF;
}

.shop-sort {
    display: flex;
    align-items: center;
    gap: 8px;
}

.shop-sort label {
    margin: 0;
    font-size: 14px;
    color: var(--sf-muted);
    white-space: nowrap;
}

.shop-sort select {
    height: 40px;
    padding: 0 36px 0 12px;
    background: var(--sf-surface) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 8'%3E%3Cpath d='M1 1.5l5 5 5-5' fill='none' stroke='%236B7280' stroke-width='1.6'/%3E%3C/svg%3E") no-repeat right 12px center / 12px 8px;
    border: 1px solid var(--sf-line-strong);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: var(--sf-text);
    -webkit-appearance: none;
    appearance: none;
    cursor: pointer;
}

.shop-sort select:hover {
    border-color: var(--sf-accent);
}

/* ---------- Filters in use ---------- */
.shop-chips {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin: 0 0 16px;
    padding: 0;
    list-style: none;
}

.shop-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 32px;
    padding: 0 10px 0 12px;
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
    color: var(--sf-accent-strong);
    white-space: nowrap;
}

.shop-chip i {
    font-size: 11px;
}

.shop-chip:hover {
    background: #DBEAFE;
    color: var(--sf-accent-strong);
    text-decoration: none;
}

.shop-chips-clear {
    padding: 0 4px;
    font-size: 13px;
    font-weight: 600;
    color: var(--sf-muted);
    text-decoration: underline;
    white-space: nowrap;
}

/* ---------- Products ---------- */
.shop-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.shop-grid > li {
    display: flex;
}

.shop-grid .sf-card-media {
    height: 150px;
}

.shop-pages {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px 24px;
    margin-top: 24px;
}

.shop-showing {
    margin: 0;
    font-size: 14px;
    color: var(--sf-muted);
}

.shop-empty {
    padding: 48px 24px;
    background: var(--sf-surface);
    border: 1px solid var(--sf-line);
    border-radius: var(--sf-radius);
    text-align: center;
}

.shop-empty-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: var(--sf-page);
    font-size: 24px;
    color: var(--sf-faint);
}

.shop-empty h2 {
    margin: 16px 0 6px;
    font-size: 19px;
    font-weight: 700;
    color: var(--sf-ink);
}

.shop-empty p {
    max-width: 44ch;
    margin: 0 auto;
    font-size: 14px;
    color: var(--sf-muted);
}

.shop-empty-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.shop-empty .shop-empty-label {
    margin-top: 28px;
    font-size: 13px;
    font-weight: 600;
    color: var(--sf-faint);
}

.shop-empty-categories {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
    margin: 10px 0 0;
    padding: 0;
    list-style: none;
}

/* ---------- Responsive ---------- */
@media (min-width: 640px) {
    .shop-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }
}

@media (min-width: 992px) {
    .shop-layout {
        grid-template-columns: 260px minmax(0, 1fr);
        padding-top: 24px;
    }
}

@media (min-width: 1200px) {
    .shop-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

/* Below 992px the filters are a panel that slides in from the left (opened by the Filters button) */
@media (max-width: 991.98px) {
    .shop-filters {
        position: fixed;
        inset: 0;
        z-index: 1045;
        visibility: hidden;
        transition: visibility 0s linear .25s;
    }

    .shop-filters.is-open {
        visibility: visible;
        transition: none;
    }

    .shop-filters-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(17, 24, 39, .45);
        opacity: 0;
        transition: opacity .25s ease;
    }

    .shop-filters.is-open .shop-filters-backdrop {
        opacity: 1;
    }

    .shop-filters-panel {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        display: flex;
        flex-direction: column;
        width: min(88vw, 360px);
        border: 0;
        border-radius: 0;
        box-shadow: 0 0 32px rgba(17, 24, 39, .25);
        transform: translateX(-100%);
        transition: transform .25s ease;
    }

    .shop-filters.is-open .shop-filters-panel {
        transform: none;
    }

    .shop-filters-head {
        flex: none;
        height: 60px;
        padding: 0 8px 0 16px;
    }

    .shop-filters-head .shop-clear {
        display: none;
    }

    .shop-filters-close {
        display: inline-flex;
        margin-left: auto;
    }

    .shop-filters-form {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .shop-options.is-long {
        max-height: none;
        margin-right: 0;
        overflow: visible;
    }

    .shop-filters-foot {
        position: sticky;
        bottom: 0;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: auto;
        padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
        background: var(--sf-surface);
        border-top: 1px solid var(--sf-line);
    }

    .shop-foot-clear {
        padding: 0 8px;
        font-size: 14px;
        font-weight: 600;
        color: var(--sf-accent-strong);
    }

    .shop-foot-apply {
        flex: 1;
        height: 44px;
        background: var(--sf-button);
        border: 0;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        color: #FFFFFF;
    }

    .shop-foot-apply:hover {
        background: var(--sf-button-hover);
    }

    .shop-tools {
        width: 100%;
    }

    .shop-filter-btn {
        display: inline-flex;
    }

    .shop-sort {
        flex: 1;
    }

    .shop-sort label {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
    }

    .shop-sort select {
        width: 100%;
        height: 42px;
    }
}

@media (max-width: 575.98px) {
    .shop-layout {
        padding-top: 16px;
    }

    .shop-title {
        font-size: 20px;
    }

    .shop-head {
        margin-bottom: 12px;
    }

    /* One line of chips that scrolls sideways */
    .shop-chips {
        flex-wrap: nowrap;
        margin-right: -16px;
        padding-right: 16px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .shop-chips::-webkit-scrollbar {
        display: none;
    }

    .shop-grid .sf-card-media {
        height: 120px;
    }

    .shop-pages {
        justify-content: center;
    }

    .shop-showing {
        width: 100%;
        text-align: center;
    }
}

@media (prefers-reduced-motion: reduce) {
    .shop-filters,
    .shop-filters-backdrop,
    .shop-filters-panel,
    .shop-facet > summary::after {
        transition: none;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    var form = document.getElementById('shop-filter-form');
    var panel = document.getElementById('shop-filters');
    var sort = document.getElementById('shop-sort');

    // Go to the shop URL for the form, leaving out empty fields and the page's default order
    function apply() {
        var params = new URLSearchParams();
        var defaultSort = form.elements.sale && form.elements.sale.checked ? 'saving' : 'latest';
        new FormData(form).forEach(function (value, key) {
            if (value !== '' && !(key === 'sort' && value === defaultSort)) {
                params.append(key, value);
            }
        });
        var query = params.toString();
        window.location.assign(form.action + (query ? '?' + query : ''));
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        apply();
    });

    // Beside the products each tick applies at once; the sliding panel waits for "Show results"
    form.addEventListener('change', function (event) {
        if (event.target.type === 'checkbox' && !panel.classList.contains('is-open')) {
            apply();
        }
    });

    if (sort) {
        sort.addEventListener('change', apply);
    }

    // Closing the panel without applying puts back the filters that are in use
    new MutationObserver(function () {
        if (!panel.classList.contains('is-open')) {
            form.reset();
        }
    }).observe(panel, { attributes: true, attributeFilter: ['class'] });
})();
</script>
@endpush
