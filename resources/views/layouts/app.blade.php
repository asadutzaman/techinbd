<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', config('shop.name'))</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="description" content="@yield('meta_description', config('shop.description'))">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link href="{{ asset('img/favicon.ico') }}" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('css/style.min.css') }}" rel="stylesheet">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">

    <!-- Storefront chrome and components -->
    <link href="{{ asset('css/storefront.css') }}?v={{ filemtime(public_path('css/storefront.css')) }}" rel="stylesheet">

    @stack('head')
    @stack('styles')
</head>

@php
    $shopName = config('shop.name');
    // Two-tone wordmark: "MultiShop" → Multi + Shop
    $logoParts = preg_split('/(?<=[a-z])(?=[A-Z])|\s+/', $shopName, 2);
    // Menu categories with products, for the category bar and the phone menu
    $navCategories = collect($menuCategories ?? [])->filter(fn ($category) => $category->is_menu && $category->products_count > 0);
    $shopContact = array_filter(config('shop.contact', []));
    $shopSocial = array_filter(config('shop.social', []));
    $accountUrl = auth()->check() ? route('customer.dashboard') : route('login');
    // Query values can arrive as arrays (?search[]=x); the header only uses plain ones
    $searchTerm = is_string(request('search')) ? request('search') : '';
    // The open category page's slug (/category/{slug})
    $currentCategory = request()->routeIs('shop.category') ? (string) request()->route('category') : '';
@endphp

<body>
    <a class="sf-skip" href="#main">Skip to content</a>

    <!-- Header -->
    <header class="sf-header {{ trim($searchTerm) !== '' ? 'is-search-open' : '' }}">
        <div class="sf-container sf-header-row">
            <button type="button" class="sf-icon-btn sf-menu-btn" data-drawer-open aria-controls="sf-drawer" aria-expanded="false" aria-label="Open menu">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>

            <a class="sf-logo" href="{{ route('home') }}" aria-label="{{ $shopName }} home">{{ $logoParts[0] }}<span>{{ $logoParts[1] ?? '' }}</span></a>

            <form class="sf-search" action="{{ route('shop') }}" method="GET" id="search-form" role="search">
                <label class="sr-only" for="search-input">Search products</label>
                <input type="search" name="search" id="search-input" placeholder="Search laptops, phones, parts and more…"
                       value="{{ $searchTerm }}" autocomplete="off">
                <button class="sf-search-btn" type="submit">
                    <i class="fas fa-search" aria-hidden="true"></i><span class="sf-search-label">Search</span>
                </button>
                <!-- Search suggestions, filled by the autocomplete script below -->
                <div id="search-suggestions" class="sf-suggestions" style="display: none;"></div>
            </form>

            <nav class="sf-actions" aria-label="Shopping">
                <a class="sf-pill" href="{{ route('shop', ['sale' => 1]) }}">
                    <i class="fas fa-tag" aria-hidden="true"></i>Deals
                </a>
                <div class="sf-icon-group">
                    <button type="button" class="sf-icon-btn" data-toggle="modal" data-target="#compareModal" aria-label="Compare products" title="Compare products">
                        <i class="fas fa-balance-scale" aria-hidden="true"></i>
                    </button>
                    <a class="sf-icon-btn" href="{{ auth()->check() ? route('customer.wishlist.index') : route('login') }}" aria-label="Wishlist" title="Wishlist">
                        <i class="fas fa-heart" aria-hidden="true"></i>
                        <span class="sf-count wishlist-count" @if(! $wishlistCount) hidden @endif>{{ $wishlistCount }}</span>
                    </a>
                    <a class="sf-icon-btn" href="{{ route('cart') }}" aria-label="Cart" title="Cart">
                        <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                        <span class="sf-count cart-count" @if(! $cartCount) hidden @endif>{{ $cartCount }}</span>
                    </a>
                </div>
                @auth
                    <div class="dropdown sf-account">
                        <button type="button" class="sf-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle" aria-hidden="true"></i>Account
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <h6 class="dropdown-header">Signed in as {{ Auth::user()->name }}</h6>
                            <a class="dropdown-item" href="{{ route('customer.dashboard') }}"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
                            <a class="dropdown-item" href="{{ route('customer.orders.index') }}"><i class="fas fa-shopping-bag"></i>My orders</a>
                            <a class="dropdown-item" href="{{ route('customer.wishlist.index') }}"><i class="fas fa-heart"></i>Wishlist</a>
                            <a class="dropdown-item" href="{{ route('customer.profile') }}"><i class="fas fa-user"></i>Profile</a>
                            <div class="dropdown-divider"></div>
                            <button type="submit" form="logout-form" class="dropdown-item"><i class="fas fa-sign-out-alt"></i>Log out</button>
                        </div>
                    </div>
                @else
                    <a class="sf-btn" href="{{ route('login') }}">
                        <i class="fas fa-sign-in-alt" aria-hidden="true"></i>Login
                    </a>
                @endauth
            </nav>

            <div class="sf-mobile-tools">
                <button type="button" class="sf-icon-btn" data-search-toggle aria-controls="search-form"
                        aria-expanded="{{ trim($searchTerm) !== '' ? 'true' : 'false' }}" aria-label="Search">
                    <i class="fas fa-search" aria-hidden="true"></i>
                </button>
                <a class="sf-icon-btn" href="{{ route('cart') }}" aria-label="Cart">
                    <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                    <span class="sf-count cart-count" @if(! $cartCount) hidden @endif>{{ $cartCount }}</span>
                </a>
            </div>
        </div>

        @if($navCategories->isNotEmpty())
            <nav class="sf-catbar" aria-label="Categories">
                <div class="sf-container">
                    <ul class="sf-catbar-list">
                        @foreach($navCategories as $category)
                            <li>
                                <a href="{{ route('shop.category', $category) }}"
                                   @if($currentCategory === $category->slug) aria-current="page" @endif>{{ $category->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </nav>
        @endif
    </header>

    <!-- Phone menu -->
    <div class="sf-drawer" id="sf-drawer" role="dialog" aria-modal="true" aria-labelledby="sf-drawer-title" data-drawer>
        <div class="sf-drawer-backdrop" data-drawer-close></div>
        <div class="sf-drawer-panel">
            <div class="sf-drawer-head">
                <h2 class="sf-drawer-title" id="sf-drawer-title">Menu</h2>
                <button type="button" class="sf-icon-btn" data-drawer-close aria-label="Close menu">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <nav class="sf-drawer-body" aria-label="Menu" data-drawer-body>
                @if($navCategories->isNotEmpty())
                    <p class="sf-drawer-label">Categories</p>
                    <ul class="sf-drawer-list">
                        @foreach($navCategories as $category)
                            <li>
                                <a href="{{ route('shop.category', $category) }}">
                                    <x-category-icon :name="$category->icon" />{{ $category->name }}
                                    <span class="sf-drawer-count">{{ $category->products_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <p class="sf-drawer-label">Shop</p>
                <ul class="sf-drawer-list">
                    <li><a href="{{ route('shop') }}"><i class="fas fa-th-large" aria-hidden="true"></i>All products</a></li>
                    <li><a href="{{ route('shop', ['sale' => 1]) }}"><i class="fas fa-tag" aria-hidden="true"></i>Deals</a></li>
                    <li><a href="{{ route('shop', ['featured' => 1]) }}"><i class="fas fa-star" aria-hidden="true"></i>Featured</a></li>
                    <li>
                        <button type="button" data-toggle="modal" data-target="#compareModal" data-drawer-close>
                            <i class="fas fa-balance-scale" aria-hidden="true"></i>Compare
                        </button>
                    </li>
                    <li><a href="{{ route('contact') }}"><i class="fas fa-envelope" aria-hidden="true"></i>Contact us</a></li>
                </ul>

                <p class="sf-drawer-label">Account</p>
                <ul class="sf-drawer-list">
                    @auth
                        <li><a href="{{ route('customer.dashboard') }}"><i class="fas fa-tachometer-alt" aria-hidden="true"></i>Dashboard</a></li>
                        <li><a href="{{ route('customer.orders.index') }}"><i class="fas fa-shopping-bag" aria-hidden="true"></i>My orders</a></li>
                        <li><a href="{{ route('customer.wishlist.index') }}"><i class="fas fa-heart" aria-hidden="true"></i>Wishlist</a></li>
                        <li><button type="submit" form="logout-form"><i class="fas fa-sign-out-alt" aria-hidden="true"></i>Log out</button></li>
                    @else
                        <li><a href="{{ route('login') }}"><i class="fas fa-sign-in-alt" aria-hidden="true"></i>Sign in</a></li>
                        <li><a href="{{ route('register') }}"><i class="fas fa-user-plus" aria-hidden="true"></i>Create an account</a></li>
                    @endauth
                </ul>
            </nav>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            {{ session('info') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <main id="main" class="sf-main @yield('main_class')">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="sf-footer">
        <div class="sf-container">
            <div class="sf-footer-grid">
                <section>
                    <p class="sf-footer-logo">{{ $logoParts[0] }}<span>{{ $logoParts[1] ?? '' }}</span></p>
                    <p class="sf-footer-about">{{ config('shop.description') }}</p>
                    @if($shopContact)
                        <ul class="sf-footer-list sf-footer-contact">
                            @if(!empty($shopContact['phone']))
                                <li><i class="fas fa-phone-alt" aria-hidden="true"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $shopContact['phone']) }}">{{ $shopContact['phone'] }}</a></li>
                            @endif
                            @if(!empty($shopContact['email']))
                                <li><i class="fas fa-envelope" aria-hidden="true"></i><a href="mailto:{{ $shopContact['email'] }}">{{ $shopContact['email'] }}</a></li>
                            @endif
                            @if(!empty($shopContact['address']))
                                <li><i class="fas fa-map-marker-alt" aria-hidden="true"></i><span>{{ $shopContact['address'] }}</span></li>
                            @endif
                        </ul>
                    @endif
                    @if($shopSocial)
                        <div class="sf-footer-social">
                            @foreach($shopSocial as $network => $url)
                                <a class="is-{{ $network }}" href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($network) }}">
                                    <i class="fab fa-{{ $network === 'facebook' ? 'facebook-f' : $network }}" aria-hidden="true"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section aria-labelledby="footer-shop">
                    <h2 class="sf-footer-title" id="footer-shop">Shop</h2>
                    <ul class="sf-footer-list">
                        <li><a href="{{ route('shop') }}">All products</a></li>
                        <li><a href="{{ route('shop', ['sale' => 1]) }}">Deals</a></li>
                        <li><a href="{{ route('shop', ['featured' => 1]) }}">Featured</a></li>
                        <li><a href="{{ route('shop', ['sort' => 'latest']) }}">New arrivals</a></li>
                        <li><a href="{{ route('cart') }}">Cart</a></li>
                        <li><a href="{{ route('contact') }}">Contact us</a></li>
                    </ul>
                </section>

                <section aria-labelledby="footer-account">
                    <h2 class="sf-footer-title" id="footer-account">My Account</h2>
                    <ul class="sf-footer-list">
                        @auth
                            <li><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
                            <li><a href="{{ route('customer.orders.index') }}">My orders</a></li>
                            <li><a href="{{ route('customer.wishlist.index') }}">Wishlist</a></li>
                            <li><a href="{{ route('customer.profile') }}">Profile</a></li>
                        @else
                            <li><a href="{{ route('login') }}">Sign in</a></li>
                            <li><a href="{{ route('register') }}">Create an account</a></li>
                        @endauth
                    </ul>
                </section>

                <section aria-labelledby="footer-promises">
                    <h2 class="sf-footer-title" id="footer-promises">Shopping with us</h2>
                    <ul class="sf-footer-list sf-footer-promises">
                        @foreach(config('shop.promises') as $promise)
                            <li><i class="fas fa-check" aria-hidden="true"></i>{{ $promise }}</li>
                        @endforeach
                    </ul>
                </section>
            </div>

            <div class="sf-footer-bottom">
                <p>
                    &copy; {{ date('Y') }} {{ $shopName }}. All rights reserved. Designed by
                    <a href="https://htmlcodex.com">HTML Codex</a>
                </p>
                <p>We accept {{ implode(' · ', config('shop.payment_methods')) }}</p>
            </div>
        </div>
    </footer>

    <!-- Phone bottom bar -->
    <nav class="sf-bottom-nav" aria-label="Quick links">
        <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>
            <i class="fas fa-home" aria-hidden="true"></i>Home
        </a>
        <button type="button" data-drawer-open aria-controls="sf-drawer" aria-expanded="false">
            <i class="fas fa-th-large" aria-hidden="true"></i>Categories
        </button>
        <a href="{{ route('shop', ['sale' => 1]) }}" @if(request()->routeIs('shop') && request()->boolean('sale')) aria-current="page" @endif>
            <i class="fas fa-tag" aria-hidden="true"></i>Deals
        </a>
        <a href="{{ route('cart') }}" @if(request()->routeIs('cart', 'checkout')) aria-current="page" @endif>
            <i class="fas fa-shopping-cart" aria-hidden="true"></i>Cart
            <span class="sf-count cart-count" @if(! $cartCount) hidden @endif>{{ $cartCount }}</span>
        </a>
        <a href="{{ $accountUrl }}" @if(request()->routeIs('customer.*', 'login', 'register')) aria-current="page" @endif>
            <i class="fas fa-user-circle" aria-hidden="true"></i>Account
        </a>
    </nav>

    <!-- Compare Modal -->
    <div class="modal fade" id="compareModal" tabindex="-1" role="dialog" aria-labelledby="compareModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="compareModalLabel">
                        <i class="fas fa-balance-scale mr-2"></i>Product Comparison
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <p class="text-muted">Select products from the shop to compare their features and prices.</p>
                        </div>
                    </div>
                    <div id="compare-container">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-header text-center">
                                        <h6>Product 1</h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="compare-placeholder">
                                            <i class="fas fa-plus fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Click "Add to Compare" on any product in the shop</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-header text-center">
                                        <h6>Product 2</h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="compare-placeholder">
                                            <i class="fas fa-plus fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Add second product to compare</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-header text-center">
                                        <h6>Product 3</h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="compare-placeholder">
                                            <i class="fas fa-plus fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Add third product to compare</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="{{ route('shop') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-bag mr-2"></i>Browse Products
                        </a>
                        <button class="btn btn-secondary ml-2" id="clear-comparison">
                            <i class="fas fa-trash mr-2"></i>Clear All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top -->
    <a href="#" class="back-to-top" aria-label="Back to top"><i class="fas fa-chevron-up" aria-hidden="true"></i></a>

    <!-- Logout Form -->
    @auth
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    @endauth

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('lib/easing/easing.min.js') }}"></script>
    <script src="{{ asset('lib/owlcarousel/owl.carousel.min.js') }}"></script>

    <!-- Template Javascript -->
    <script src="{{ asset('js/main.js') }}"></script>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>

    <!-- Storefront behaviour: drawers, phone search, product rails, product card buttons -->
    <script src="{{ asset('js/storefront.js') }}?v={{ filemtime(public_path('js/storefront.js')) }}"></script>

    <script>
    $(document).ready(function() {
        const escapeHtml = function(value) {
            return $('<div>').text(value == null ? '' : value).html();
        };

        // Compare functionality
        let compareProducts = JSON.parse(localStorage.getItem('compareProducts') || '[]');

        function updateCompareModal() {
            const container = $('#compare-container .row');
            container.empty();

            for (let i = 0; i < 3; i++) {
                const product = compareProducts[i];
                let cardHtml = '<div class="col-md-4"><div class="card h-100">';
                cardHtml += '<div class="card-header text-center"><h6>Product ' + (i + 1) + '</h6></div>';
                cardHtml += '<div class="card-body text-center">';

                if (product) {
                    cardHtml += '<img src="' + escapeHtml(product.image) + '" alt="' + escapeHtml(product.name) + '" class="img-fluid mb-3" style="height: 150px; object-fit: contain;">';
                    cardHtml += '<h6>' + escapeHtml(product.name) + '</h6>';
                    cardHtml += '<p class="font-weight-bold" style="color: var(--sf-price);">৳' + Number(product.price).toLocaleString('en-US') + '</p>';
                    cardHtml += '<button class="btn btn-sm btn-danger" onclick="removeFromCompare(' + i + ')">Remove</button>';
                } else {
                    cardHtml += '<div class="compare-placeholder">';
                    cardHtml += '<i class="fas fa-plus fa-3x text-muted mb-3"></i>';
                    cardHtml += '<p class="text-muted">Add product to compare</p>';
                    cardHtml += '</div>';
                }

                cardHtml += '</div></div></div>';
                container.append(cardHtml);
            }
        }

        window.addToCompare = function(productId, productName, productPrice, productImage) {
            if (compareProducts.length >= 3) {
                toastr.warning('You can only compare up to 3 products');
                return;
            }

            // Check if product already in compare
            if (compareProducts.find(p => p.id === productId)) {
                toastr.info('Product already in comparison');
                return;
            }

            compareProducts.push({
                id: productId,
                name: productName,
                price: productPrice,
                image: productImage
            });

            localStorage.setItem('compareProducts', JSON.stringify(compareProducts));
            toastr.success('Product added to comparison');
            updateCompareModal();
        };

        window.removeFromCompare = function(index) {
            compareProducts.splice(index, 1);
            localStorage.setItem('compareProducts', JSON.stringify(compareProducts));
            updateCompareModal();
            toastr.info('Product removed from comparison');
        };

        $('#clear-comparison').click(function() {
            compareProducts = [];
            localStorage.setItem('compareProducts', JSON.stringify(compareProducts));
            updateCompareModal();
            toastr.info('Comparison cleared');
        });

        // Initialize compare modal
        updateCompareModal();

        // Search autocomplete functionality
        let searchTimeout;
        $('#search-input').on('input', function() {
            const query = $(this).val().trim();
            const suggestionsDiv = $('#search-suggestions');

            clearTimeout(searchTimeout);

            if (query.length < 2) {
                suggestionsDiv.hide();
                return;
            }

            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: '{{ route("search.suggestions") }}',
                    method: 'GET',
                    data: { q: query },
                    success: function(suggestions) {
                        if (suggestions.length > 0) {
                            let html = '';
                            suggestions.forEach(function(item) {
                                html += '<div class="suggestion-item" data-name="' + escapeHtml(item.name) + '">';
                                html += '<div class="d-flex align-items-center">';
                                html += '<i class="fas fa-search text-muted mr-2"></i>';
                                html += '<div>';
                                html += '<div class="font-weight-bold">' + escapeHtml(item.name) + '</div>';
                                if (item.category) {
                                    html += '<small class="text-muted">in ' + escapeHtml(item.category) + '</small>';
                                }
                                html += '</div>';
                                html += '</div>';
                                html += '</div>';
                            });
                            suggestionsDiv.html(html).show();
                        } else {
                            suggestionsDiv.hide();
                        }
                    },
                    error: function() {
                        suggestionsDiv.hide();
                    }
                });
            }, 300);
        });

        // Handle suggestion clicks
        $(document).on('click', '.suggestion-item', function() {
            const productName = $(this).data('name');
            $('#search-input').val(productName);
            $('#search-suggestions').hide();
            $('#search-form').submit();
        });

        // Hide suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#search-input, #search-suggestions').length) {
                $('#search-suggestions').hide();
            }
        });

        // Handle keyboard navigation
        $('#search-input').on('keydown', function(e) {
            const suggestions = $('.suggestion-item');
            const current = $('.suggestion-item.active');

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (current.length === 0) {
                    suggestions.first().addClass('active');
                } else {
                    current.removeClass('active');
                    const next = current.next('.suggestion-item');
                    if (next.length) {
                        next.addClass('active');
                    } else {
                        suggestions.first().addClass('active');
                    }
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (current.length === 0) {
                    suggestions.last().addClass('active');
                } else {
                    current.removeClass('active');
                    const prev = current.prev('.suggestion-item');
                    if (prev.length) {
                        prev.addClass('active');
                    } else {
                        suggestions.last().addClass('active');
                    }
                }
            } else if (e.key === 'Enter' && current.length) {
                e.preventDefault();
                current.click();
            } else if (e.key === 'Escape') {
                $('#search-suggestions').hide();
            }
        });

    });

    // The cart badges are rendered server-side; cart endpoints return cart_count to keep them current
    window.setCartCount = function(count) {
        $('.cart-count').text(count).prop('hidden', !Number(count));
    };
    </script>

    @stack('scripts')
</body>

</html>
