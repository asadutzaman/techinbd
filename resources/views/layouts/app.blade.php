<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'MultiShop - Online Shop Website Template')</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link href="{{ asset('img/favicon.ico') }}" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">  

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>

<body>
    <!-- Topbar Start -->
    <div class="container-fluid">
        <div class="row bg-secondary py-1 px-xl-5">
            <div class="col-lg-6 d-none d-lg-block">
                <div class="d-inline-flex align-items-center h-100">
                    <a class="text-body mr-3" href="">About</a>
                    <a class="text-body mr-3" href="">Contact</a>
                    <a class="text-body mr-3" href="">Help</a>
                    <a class="text-body mr-3" href="">FAQs</a>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-right">
                <div class="d-inline-flex align-items-center">
                    @auth
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">
                                Welcome, {{ Auth::user()->name }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('customer.dashboard') }}">
                                    <i class="fa fa-tachometer-alt mr-2"></i>Dashboard
                                </a>
                                <a class="dropdown-item" href="{{ route('customer.orders.index') }}">
                                    <i class="fa fa-shopping-bag mr-2"></i>My Orders
                                </a>
                                <a class="dropdown-item" href="{{ route('customer.wishlist.index') }}">
                                    <i class="fa fa-heart mr-2"></i>Wishlist
                                </a>
                                <a class="dropdown-item" href="{{ route('customer.profile') }}">
                                    <i class="fa fa-user mr-2"></i>Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('logout') }}" 
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fa fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">My Account</button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('login') }}">
                                    <i class="fa fa-sign-in-alt mr-2"></i>Sign In
                                </a>
                                <a class="dropdown-item" href="{{ route('register') }}">
                                    <i class="fa fa-user-plus mr-2"></i>Sign Up
                                </a>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
        <div class="row align-items-center bg-light py-3 px-xl-5 d-none d-lg-flex">
            <div class="col-lg-4">
                <a href="{{ route('home') }}" class="text-decoration-none">
                    <span class="h1 text-uppercase text-primary bg-dark px-2">Multi</span>
                    <span class="h1 text-uppercase text-dark bg-primary px-2 ml-n1">Shop</span>
                </a>
            </div>
            <div class="col-lg-5 col-6 text-left">
                <form action="{{ route('shop') }}" method="GET" id="search-form">
                    <div class="input-group position-relative">
                        <input type="text" class="form-control" name="search" id="search-input" 
                               placeholder="Search for products..." value="{{ request('search') }}" 
                               autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                        <!-- Search Suggestions Dropdown -->
                        <div id="search-suggestions" class="position-absolute bg-white border rounded shadow-sm" 
                             style="top: 100%; left: 0; right: 0; z-index: 1000; display: none; max-height: 300px; overflow-y: auto;">
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-3 col-6 text-right">
                <div class="d-flex justify-content-end align-items-center">
                    <a href="#" class="btn btn-outline-primary btn-sm mr-2" data-toggle="modal" data-target="#offersModal">
                        <i class="fas fa-tags mr-1"></i>Offers
                    </a>
                    <a href="#" class="btn btn-outline-success btn-sm mr-2" data-toggle="modal" data-target="#pcBuilderModal">
                        <i class="fas fa-desktop mr-1"></i>PC Builder
                    </a>
                    <a href="#" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#compareModal">
                        <i class="fas fa-balance-scale mr-1"></i>Compare
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Navbar Start -->
    <div class="container-fluid bg-dark mb-30">
        <div class="row px-xl-5">
            <div class="col-lg-3 d-none d-lg-block">
                <a class="btn d-flex align-items-center justify-content-between bg-primary w-100" data-toggle="collapse" href="#navbar-vertical" style="height: 65px; padding: 0 30px;">
                    <h6 class="text-dark m-0"><i class="fa fa-bars mr-2"></i>Categories</h6>
                    <i class="fa fa-angle-down text-dark"></i>
                </a>
                <nav class="collapse position-absolute navbar navbar-vertical navbar-light align-items-start p-0 bg-light" id="navbar-vertical" style="width: calc(100% - 30px); z-index: 999;">
                    <div class="navbar-nav w-100">
                        @forelse($menuCategories ?? [] as $category)
                            <a href="{{ route('shop', ['category' => strtolower($category->name)]) }}" class="nav-item nav-link">
                                {{ $category->name }}
                                @if($category->products_count > 0)
                                    <small class="text-muted">({{ $category->products_count }})</small>
                                @endif
                            </a>
                        @empty
                            <!-- Fallback menu items if no categories are set as menu items -->
                            <a href="{{ route('shop', ['category' => 'men']) }}" class="nav-item nav-link">Men's Fashion</a>
                            <a href="{{ route('shop', ['category' => 'women']) }}" class="nav-item nav-link">Women's Fashion</a>
                            <a href="{{ route('shop', ['category' => 'kids']) }}" class="nav-item nav-link">Kids Fashion</a>
                            <a href="{{ route('shop', ['category' => 'electronics']) }}" class="nav-item nav-link">Electronics</a>
                            <a href="{{ route('shop', ['category' => 'sports']) }}" class="nav-item nav-link">Sports</a>
                            <a href="{{ route('shop', ['category' => 'accessories']) }}" class="nav-item nav-link">Accessories</a>
                            <a href="{{ route('shop', ['category' => 'shoes']) }}" class="nav-item nav-link">Shoes</a>
                        @endforelse
                        
                        <!-- View All Categories Link -->
                        <div class="nav-item border-top pt-2 mt-2">
                            <a href="{{ route('shop') }}" class="nav-item nav-link text-primary">
                                <i class="fas fa-th-large mr-2"></i>View All Products
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
            <div class="col-lg-9">
                <nav class="navbar navbar-expand-lg bg-dark navbar-dark py-3 py-lg-0 px-0">
                    <a href="{{ route('home') }}" class="text-decoration-none d-block d-lg-none">
                        <span class="h1 text-uppercase text-dark bg-light px-2">Multi</span>
                        <span class="h1 text-uppercase text-light bg-primary px-2 ml-n1">Shop</span>
                    </a>
                    <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                        <div class="navbar-nav mr-auto py-0">
                            <a href="{{ route('home') }}" class="nav-item nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                            <a href="{{ route('shop') }}" class="nav-item nav-link {{ request()->routeIs('shop') ? 'active' : '' }}">Shop</a>
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Pages <i class="fa fa-angle-down mt-1"></i></a>
                                <div class="dropdown-menu bg-primary rounded-0 border-0 m-0">
                                    <a href="{{ route('cart') }}" class="dropdown-item">Shopping Cart</a>
                                    <a href="{{ route('checkout') }}" class="dropdown-item">Checkout</a>
                                </div>
                            </div>
                            <a href="{{ route('contact') }}" class="nav-item nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                        </div>
                        <div class="navbar-nav ml-auto py-0 d-none d-lg-block">
                            @auth
                                <a href="{{ route('customer.wishlist.index') }}" class="btn px-0">
                                    <i class="fas fa-heart text-primary"></i>
                                    <span class="badge text-secondary border border-secondary rounded-circle wishlist-count" style="padding-bottom: 2px;">{{ Auth::user()->wishlistItems()->count() }}</span>
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn px-0" title="Login to view wishlist">
                                    <i class="fas fa-heart text-primary"></i>
                                    <span class="badge text-secondary border border-secondary rounded-circle" style="padding-bottom: 2px;">0</span>
                                </a>
                            @endauth
                            <a href="{{ route('cart') }}" class="btn px-0 ml-3">
                                <i class="fas fa-shopping-cart text-primary"></i>
                                <span class="badge text-secondary border border-secondary rounded-circle cart-count" style="padding-bottom: 2px;">0</span>
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar End -->

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

    @yield('content')

    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-secondary mt-5 pt-5">
        <div class="row px-xl-5 pt-5">
            <div class="col-lg-4 col-md-12 mb-5 pr-3 pr-xl-5">
                <h5 class="text-secondary text-uppercase mb-4">Get In Touch</h5>
                <p class="mb-4">No dolore ipsum accusam no lorem. Invidunt sed clita kasd clita et et dolor sed dolor. Rebum tempor no vero est magna amet no</p>
                <p class="mb-2"><i class="fa fa-map-marker-alt text-primary mr-3"></i>123 Street, New York, USA</p>
                <p class="mb-2"><i class="fa fa-envelope text-primary mr-3"></i>info@example.com</p>
                <p class="mb-0"><i class="fa fa-phone-alt text-primary mr-3"></i>+012 345 67890</p>
            </div>
            <div class="col-lg-8 col-md-12">
                <div class="row">
                    <div class="col-md-4 mb-5">
                        <h5 class="text-secondary text-uppercase mb-4">Quick Shop</h5>
                        <div class="d-flex flex-column justify-content-start">
                            <a class="text-secondary mb-2" href="{{ route('home') }}"><i class="fa fa-angle-right mr-2"></i>Home</a>
                            <a class="text-secondary mb-2" href="{{ route('shop') }}"><i class="fa fa-angle-right mr-2"></i>Our Shop</a>
                            <a class="text-secondary mb-2" href=""><i class="fa fa-angle-right mr-2"></i>Shop Detail</a>
                            <a class="text-secondary mb-2" href="{{ route('cart') }}"><i class="fa fa-angle-right mr-2"></i>Shopping Cart</a>
                            <a class="text-secondary mb-2" href="{{ route('checkout') }}"><i class="fa fa-angle-right mr-2"></i>Checkout</a>
                            <a class="text-secondary" href="{{ route('contact') }}"><i class="fa fa-angle-right mr-2"></i>Contact Us</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-5">
                        <h5 class="text-secondary text-uppercase mb-4">My Account</h5>
                        <div class="d-flex flex-column justify-content-start">
                            <a class="text-secondary mb-2" href="{{ route('home') }}"><i class="fa fa-angle-right mr-2"></i>Home</a>
                            <a class="text-secondary mb-2" href="{{ route('shop') }}"><i class="fa fa-angle-right mr-2"></i>Our Shop</a>
                            <a class="text-secondary mb-2" href=""><i class="fa fa-angle-right mr-2"></i>Shop Detail</a>
                            <a class="text-secondary mb-2" href="{{ route('cart') }}"><i class="fa fa-angle-right mr-2"></i>Shopping Cart</a>
                            <a class="text-secondary mb-2" href="{{ route('checkout') }}"><i class="fa fa-angle-right mr-2"></i>Checkout</a>
                            <a class="text-secondary" href="{{ route('contact') }}"><i class="fa fa-angle-right mr-2"></i>Contact Us</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-5">
                        <h5 class="text-secondary text-uppercase mb-4">Newsletter</h5>
                        <p>Duo stet tempor ipsum sit amet magna ipsum tempor est</p>
                        <form action="">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Your Email Address">
                                <div class="input-group-append">
                                    <button class="btn btn-primary">Sign Up</button>
                                </div>
                            </div>
                        </form>
                        <h6 class="text-secondary text-uppercase mt-4 mb-3">Follow Us</h6>
                        <div class="d-flex">
                            <a class="btn btn-primary btn-square mr-2" href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-primary btn-square mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-primary btn-square mr-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a class="btn btn-primary btn-square" href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row border-top mx-xl-5 py-4" style="border-color: rgba(256, 256, 256, .1) !important;">
            <div class="col-md-6 px-xl-0">
                <p class="mb-md-0 text-center text-md-left text-secondary">
                    &copy; <a class="text-primary" href="#">Your Site Name</a>. All Rights Reserved. Designed by
                    <a class="text-primary" href="https://htmlcodex.com">HTML Codex</a>
                </p>
            </div>
            <div class="col-md-6 px-xl-0 text-center text-md-right">
                <img class="img-fluid" src="{{ asset('img/payments.png') }}" alt="">
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Offers Modal -->
    <div class="modal fade" id="offersModal" tabindex="-1" role="dialog" aria-labelledby="offersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="offersModalLabel">
                        <i class="fas fa-tags mr-2"></i>Special Offers & Deals
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-percentage mr-2"></i>Flash Sale</h6>
                                </div>
                                <div class="card-body">
                                    <h5 class="text-primary">Up to 50% OFF</h5>
                                    <p class="mb-2">Limited time offer on selected items</p>
                                    <small class="text-muted">Ends in: 2 days 5 hours</small>
                                    <br>
                                    <a href="{{ route('shop') }}?sale=1" class="btn btn-primary btn-sm mt-2">Shop Now</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-gift mr-2"></i>Bundle Deal</h6>
                                </div>
                                <div class="card-body">
                                    <h5 class="text-success">Buy 2 Get 1 Free</h5>
                                    <p class="mb-2">On all fashion items</p>
                                    <small class="text-muted">Valid until stock lasts</small>
                                    <br>
                                    <a href="{{ route('shop') }}?category=fashion" class="btn btn-success btn-sm mt-2">Shop Fashion</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-shipping-fast mr-2"></i>Free Shipping</h6>
                                </div>
                                <div class="card-body">
                                    <h5 class="text-warning">Free Delivery</h5>
                                    <p class="mb-2">On orders above $50</p>
                                    <small class="text-muted">Worldwide shipping</small>
                                    <br>
                                    <a href="{{ route('shop') }}" class="btn btn-warning btn-sm mt-2">Start Shopping</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-star mr-2"></i>New Customer</h6>
                                </div>
                                <div class="card-body">
                                    <h5 class="text-info">20% OFF</h5>
                                    <p class="mb-2">First order discount</p>
                                    <small class="text-muted">Use code: WELCOME20</small>
                                    <br>
                                    <button class="btn btn-info btn-sm mt-2" onclick="copyCode('WELCOME20')">Copy Code</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PC Builder Modal -->
    <div class="modal fade" id="pcBuilderModal" tabindex="-1" role="dialog" aria-labelledby="pcBuilderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="pcBuilderModalLabel">
                        <i class="fas fa-desktop mr-2"></i>PC Builder Tool
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="mb-3">Build Your Custom PC</h6>
                            <div class="pc-builder-components">
                                <div class="component-row mb-3 p-3 border rounded">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <i class="fas fa-microchip fa-2x text-primary"></i>
                                            <strong class="ml-2">CPU</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <select class="form-control" id="cpu-select">
                                                <option value="">Select CPU</option>
                                                <option value="intel-i5">Intel Core i5-12400F - $199</option>
                                                <option value="intel-i7">Intel Core i7-12700K - $349</option>
                                                <option value="amd-ryzen5">AMD Ryzen 5 5600X - $229</option>
                                                <option value="amd-ryzen7">AMD Ryzen 7 5800X - $329</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="price-display">$0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="component-row mb-3 p-3 border rounded">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <i class="fas fa-memory fa-2x text-success"></i>
                                            <strong class="ml-2">RAM</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <select class="form-control" id="ram-select">
                                                <option value="">Select RAM</option>
                                                <option value="8gb-ddr4">8GB DDR4 3200MHz - $79</option>
                                                <option value="16gb-ddr4">16GB DDR4 3200MHz - $149</option>
                                                <option value="32gb-ddr4">32GB DDR4 3200MHz - $299</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="price-display">$0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="component-row mb-3 p-3 border rounded">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <i class="fas fa-hdd fa-2x text-warning"></i>
                                            <strong class="ml-2">Storage</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <select class="form-control" id="storage-select">
                                                <option value="">Select Storage</option>
                                                <option value="ssd-500gb">500GB NVMe SSD - $89</option>
                                                <option value="ssd-1tb">1TB NVMe SSD - $159</option>
                                                <option value="hdd-1tb">1TB HDD - $49</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="price-display">$0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="component-row mb-3 p-3 border rounded">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <i class="fas fa-tv fa-2x text-danger"></i>
                                            <strong class="ml-2">GPU</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <select class="form-control" id="gpu-select">
                                                <option value="">Select Graphics Card</option>
                                                <option value="rtx-3060">NVIDIA RTX 3060 - $399</option>
                                                <option value="rtx-3070">NVIDIA RTX 3070 - $599</option>
                                                <option value="rx-6600">AMD RX 6600 - $329</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="price-display">$0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">Build Summary</h6>
                                </div>
                                <div class="card-body">
                                    <div id="build-summary">
                                        <p class="text-muted">Select components to see your build</p>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong>Total Price:</strong>
                                        <strong id="total-price">$0</strong>
                                    </div>
                                    <button class="btn btn-success btn-block mt-3" id="add-build-to-cart" disabled>
                                        Add Build to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
    <a href="#" class="btn btn-primary back-to-top"><i class="fa fa-angle-double-up"></i></a>

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

    <!-- Contact Javascript File -->
    <script src="{{ asset('mail/jqBootstrapValidation.min.js') }}"></script>
    <script src="{{ asset('mail/contact.js') }}"></script>

    <!-- Template Javascript -->
    <script src="{{ asset('js/main.js') }}"></script>
    
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // PC Builder functionality
        const componentPrices = {
            'intel-i5': 199,
            'intel-i7': 349,
            'amd-ryzen5': 229,
            'amd-ryzen7': 329,
            '8gb-ddr4': 79,
            '16gb-ddr4': 149,
            '32gb-ddr4': 299,
            'ssd-500gb': 89,
            'ssd-1tb': 159,
            'hdd-1tb': 49,
            'rtx-3060': 399,
            'rtx-3070': 599,
            'rx-6600': 329
        };

        const componentNames = {
            'intel-i5': 'Intel Core i5-12400F',
            'intel-i7': 'Intel Core i7-12700K',
            'amd-ryzen5': 'AMD Ryzen 5 5600X',
            'amd-ryzen7': 'AMD Ryzen 7 5800X',
            '8gb-ddr4': '8GB DDR4 3200MHz',
            '16gb-ddr4': '16GB DDR4 3200MHz',
            '32gb-ddr4': '32GB DDR4 3200MHz',
            'ssd-500gb': '500GB NVMe SSD',
            'ssd-1tb': '1TB NVMe SSD',
            'hdd-1tb': '1TB HDD',
            'rtx-3060': 'NVIDIA RTX 3060',
            'rtx-3070': 'NVIDIA RTX 3070',
            'rx-6600': 'AMD RX 6600'
        };

        let selectedComponents = {};

        // Handle component selection
        $('#cpu-select, #ram-select, #storage-select, #gpu-select').change(function() {
            const componentType = $(this).attr('id').replace('-select', '');
            const selectedValue = $(this).val();
            const priceDisplay = $(this).closest('.component-row').find('.price-display');
            
            if (selectedValue) {
                const price = componentPrices[selectedValue];
                priceDisplay.text('$' + price);
                selectedComponents[componentType] = {
                    value: selectedValue,
                    name: componentNames[selectedValue],
                    price: price
                };
            } else {
                priceDisplay.text('$0');
                delete selectedComponents[componentType];
            }
            
            updateBuildSummary();
        });

        function updateBuildSummary() {
            const summaryDiv = $('#build-summary');
            const totalPriceSpan = $('#total-price');
            const addToCartBtn = $('#add-build-to-cart');
            
            if (Object.keys(selectedComponents).length === 0) {
                summaryDiv.html('<p class="text-muted">Select components to see your build</p>');
                totalPriceSpan.text('$0');
                addToCartBtn.prop('disabled', true);
                return;
            }
            
            let html = '';
            let totalPrice = 0;
            
            Object.keys(selectedComponents).forEach(function(type) {
                const component = selectedComponents[type];
                html += '<div class="mb-2"><strong>' + type.toUpperCase() + ':</strong><br>' + 
                       component.name + ' - $' + component.price + '</div>';
                totalPrice += component.price;
            });
            
            summaryDiv.html(html);
            totalPriceSpan.text('$' + totalPrice);
            addToCartBtn.prop('disabled', Object.keys(selectedComponents).length < 2);
        }

        // Add build to cart
        $('#add-build-to-cart').click(function() {
            if (Object.keys(selectedComponents).length < 2) {
                toastr.warning('Please select at least 2 components');
                return;
            }
            
            // Here you would typically send the build to the server
            toastr.success('PC Build added to cart successfully!');
            $('#pcBuilderModal').modal('hide');
            
            // Reset the builder
            $('#cpu-select, #ram-select, #storage-select, #gpu-select').val('');
            $('.price-display').text('$0');
            selectedComponents = {};
            updateBuildSummary();
        });

        // Copy code functionality for offers
        window.copyCode = function(code) {
            navigator.clipboard.writeText(code).then(function() {
                toastr.success('Code copied to clipboard: ' + code);
            }).catch(function() {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = code;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                toastr.success('Code copied to clipboard: ' + code);
            });
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
                    cardHtml += '<img src="' + product.image + '" alt="' + product.name + '" class="img-fluid mb-3" style="height: 150px; object-fit: cover;">';
                    cardHtml += '<h6>' + product.name + '</h6>';
                    cardHtml += '<p class="text-primary">$' + product.price + '</p>';
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
                                html += '<div class="suggestion-item p-2 border-bottom" style="cursor: pointer;" data-name="' + item.name + '">';
                                html += '<div class="d-flex align-items-center">';
                                html += '<i class="fas fa-search text-muted mr-2"></i>';
                                html += '<div>';
                                html += '<div class="font-weight-bold">' + item.name + '</div>';
                                html += '<small class="text-muted">in ' + item.category + '</small>';
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
                    suggestions.first().addClass('active bg-light');
                } else {
                    current.removeClass('active bg-light');
                    const next = current.next('.suggestion-item');
                    if (next.length) {
                        next.addClass('active bg-light');
                    } else {
                        suggestions.first().addClass('active bg-light');
                    }
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (current.length === 0) {
                    suggestions.last().addClass('active bg-light');
                } else {
                    current.removeClass('active bg-light');
                    const prev = current.prev('.suggestion-item');
                    if (prev.length) {
                        prev.addClass('active bg-light');
                    } else {
                        suggestions.last().addClass('active bg-light');
                    }
                }
            } else if (e.key === 'Enter' && current.length) {
                e.preventDefault();
                current.click();
            } else if (e.key === 'Escape') {
                $('#search-suggestions').hide();
            }
        });

        // Update cart count on page load
        updateCartCount();

        function updateCartCount() {
            $.get('{{ route("cart.count") }}', function(data) {
                $('.cart-count').text(data.count);
            }).fail(function() {
                // If cart count route doesn't exist, set to 0
                $('.cart-count').text('0');
            });
        }
    });
    </script>
    
    @stack('scripts')
</body>

</html>