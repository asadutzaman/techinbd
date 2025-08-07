@extends('layouts.app')

@section('title', 'MultiShop - Home')

@section('content')
    <!-- Carousel Start -->
    <div class="container-fluid mb-3">
        <div class="row px-xl-5">
            <div class="col-lg-8">
                <div id="header-carousel" class="carousel slide carousel-fade mb-30 mb-lg-0" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <li data-target="#header-carousel" data-slide-to="0" class="active"></li>
                        <li data-target="#header-carousel" data-slide-to="1"></li>
                        <li data-target="#header-carousel" data-slide-to="2"></li>
                    </ol>
                    <div class="carousel-inner">
                        <div class="carousel-item position-relative active" style="height: 430px;">
                            <img class="position-absolute w-100 h-100" src="{{ asset('img/carousel-1.jpg') }}" style="object-fit: cover;">
                            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                                <div class="p-3" style="max-width: 700px;">
                                    <h1 class="display-4 text-white mb-3 animate__animated animate__fadeInDown">Men Fashion</h1>
                                    <p class="mx-md-5 px-5 animate__animated animate__bounceIn">Lorem rebum magna amet lorem magna erat diam stet. Sadips duo stet amet amet ndiam elitr ipsum diam</p>
                                    <a class="btn btn-outline-light py-2 px-4 mt-3 animate__animated animate__fadeInUp" href="{{ route('shop') }}">Shop Now</a>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item position-relative" style="height: 430px;">
                            <img class="position-absolute w-100 h-100" src="{{ asset('img/carousel-2.jpg') }}" style="object-fit: cover;">
                            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                                <div class="p-3" style="max-width: 700px;">
                                    <h1 class="display-4 text-white mb-3 animate__animated animate__fadeInDown">Women Fashion</h1>
                                    <p class="mx-md-5 px-5 animate__animated animate__bounceIn">Lorem rebum magna amet lorem magna erat diam stet. Sadips duo stet amet amet ndiam elitr ipsum diam</p>
                                    <a class="btn btn-outline-light py-2 px-4 mt-3 animate__animated animate__fadeInUp" href="{{ route('shop') }}">Shop Now</a>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item position-relative" style="height: 430px;">
                            <img class="position-absolute w-100 h-100" src="{{ asset('img/carousel-3.jpg') }}" style="object-fit: cover;">
                            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                                <div class="p-3" style="max-width: 700px;">
                                    <h1 class="display-4 text-white mb-3 animate__animated animate__fadeInDown">Kids Fashion</h1>
                                    <p class="mx-md-5 px-5 animate__animated animate__bounceIn">Lorem rebum magna amet lorem magna erat diam stet. Sadips duo stet amet amet ndiam elitr ipsum diam</p>
                                    <a class="btn btn-outline-light py-2 px-4 mt-3 animate__animated animate__fadeInUp" href="{{ route('shop') }}">Shop Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="product-offer mb-30" style="height: 200px;">
                    <img class="img-fluid" src="{{ asset('img/offer-1.jpg') }}" alt="">
                    <div class="offer-text">
                        <h6 class="text-white text-uppercase">Save 20%</h6>
                        <h3 class="text-white mb-3">Special Offer</h3>
                        <a href="{{ route('shop') }}" class="btn btn-primary">Shop Now</a>
                    </div>
                </div>
                <div class="product-offer mb-30" style="height: 200px;">
                    <img class="img-fluid" src="{{ asset('img/offer-2.jpg') }}" alt="">
                    <div class="offer-text">
                        <h6 class="text-white text-uppercase">Save 20%</h6>
                        <h3 class="text-white mb-3">Special Offer</h3>
                        <a href="{{ route('shop') }}" class="btn btn-primary">Shop Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Carousel End -->

    <!-- Featured Start -->
    <div class="container-fluid pt-5">
        <div class="row px-xl-5 pb-3">
            <div class="col-lg-3 col-md-6 col-sm-12 pb-1">
                <div class="d-flex align-items-center bg-light mb-4" style="padding: 30px;">
                    <h1 class="fa fa-check text-primary m-0 mr-3"></h1>
                    <h5 class="font-weight-semi-bold m-0">Quality Product</h5>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 pb-1">
                <div class="d-flex align-items-center bg-light mb-4" style="padding: 30px;">
                    <h1 class="fa fa-shipping-fast text-primary m-0 mr-3"></h1>
                    <h5 class="font-weight-semi-bold m-0">Free Shipping</h5>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 pb-1">
                <div class="d-flex align-items-center bg-light mb-4" style="padding: 30px;">
                    <h1 class="fa fa-exchange-alt text-primary m-0 mr-3"></h1>
                    <h5 class="font-weight-semi-bold m-0">14-Day Return</h5>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 pb-1">
                <div class="d-flex align-items-center bg-light mb-4" style="padding: 30px;">
                    <h1 class="fa fa-phone-volume text-primary m-0 mr-3"></h1>
                    <h5 class="font-weight-semi-bold m-0">24/7 Support</h5>
                </div>
            </div>
        </div>
    </div>
    <!-- Featured End -->

    <!-- Categories Start -->
    <div class="container-fluid pt-5">
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span class="bg-secondary pr-3">Categories</span></h2>
        <div class="row px-xl-5 pb-3">
            @forelse($categories as $index => $category)
            <div class="col-lg-3 col-md-4 col-sm-6 pb-1">
                <a class="text-decoration-none" href="{{ route('shop') }}">
                    <div class="cat-item d-flex align-items-center mb-4">
                        <div class="overflow-hidden" style="width: 100px; height: 100px;">
                            <img class="img-fluid" src="{{ asset('img/cat-' . ($index + 1) . '.jpg') }}" alt="{{ $category->name }}" style="object-fit: cover;">
                        </div>
                        <div class="flex-fill pl-3">
                            <h6>{{ $category->name }}</h6>
                            <small class="text-body">{{ $category->products_count ?? 0 }} Products</small>
                        </div>
                    </div>
                </a>
            </div>
            @empty
            @for($i = 1; $i <= 4; $i++)
            <div class="col-lg-3 col-md-4 col-sm-6 pb-1">
                <a class="text-decoration-none" href="{{ route('shop') }}">
                    <div class="cat-item d-flex align-items-center mb-4">
                        <div class="overflow-hidden" style="width: 100px; height: 100px;">
                            <img class="img-fluid" src="{{ asset('img/cat-' . $i . '.jpg') }}" alt="">
                        </div>
                        <div class="flex-fill pl-3">
                            <h6>Category Name</h6>
                            <small class="text-body">0 Products</small>
                        </div>
                    </div>
                </a>
            </div>
            @endfor
            @endforelse
        </div>
    </div>
    <!-- Categories End -->

    <!-- Products Start -->
    <div class="container-fluid pt-5 pb-3">
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span class="bg-secondary pr-3">Featured Products</span></h2>
        <div class="row px-xl-5">
            @forelse($featuredProducts as $product)
            <div class="col-lg-3 col-md-4 col-sm-6 pb-1">
                <div class="product-item bg-light mb-4">
                    <div class="product-img position-relative overflow-hidden">
                        <img class="img-fluid w-100" src="{{ asset('img/' . $product->image) }}" alt="{{ $product->name }}" style="height: 250px; object-fit: cover;">
                        <div class="product-action">
                            <button class="btn btn-outline-dark btn-square add-to-cart-btn" 
                                    data-product-id="{{ $product->id }}" 
                                    data-product-name="{{ $product->name }}" 
                                    data-product-price="{{ $product->display_price }}"
                                    data-product-image="{{ $product->image }}">
                                <i class="fa fa-shopping-cart"></i>
                            </button>
                            <a class="btn btn-outline-dark btn-square" href="#"><i class="far fa-heart"></i></a>
                            <button class="btn btn-outline-dark btn-square add-to-compare-btn" 
                                    data-product-id="{{ $product->id }}" 
                                    data-product-name="{{ $product->name }}" 
                                    data-product-price="{{ $product->display_price }}"
                                    data-product-image="{{ asset('img/' . $product->image) }}"
                                    title="Add to Compare">
                                <i class="fa fa-balance-scale"></i>
                            </button>
                            <a class="btn btn-outline-dark btn-square" href="{{ route('product.detail', $product->id) }}"><i class="fa fa-search"></i></a>
                        </div>
                    </div>
                    <div class="text-center py-4">
                        <a class="h6 text-decoration-none text-truncate" href="{{ route('product.detail', $product->id) }}">{{ $product->name }}</a>
                        <div class="d-flex align-items-center justify-content-center mt-2">
                            @if($product->is_on_sale)
                                <h5>${{ number_format($product->sale_price, 2) }}</h5>
                                <h6 class="text-muted ml-2"><del>${{ number_format($product->price, 2) }}</del></h6>
                            @else
                                <h5>${{ number_format($product->price, 2) }}</h5>
                            @endif
                        </div>
                        <div class="d-flex align-items-center justify-content-center mb-1">
                            <small class="fa fa-star text-primary mr-1"></small>
                            <small class="fa fa-star text-primary mr-1"></small>
                            <small class="fa fa-star text-primary mr-1"></small>
                            <small class="fa fa-star text-primary mr-1"></small>
                            <small class="fa fa-star text-primary mr-1"></small>
                            <small>({{ rand(10, 99) }})</small>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <h4>No Featured Products Available</h4>
                    <p class="text-muted">Check back later for featured products!</p>
                    <a href="{{ route('shop') }}" class="btn btn-primary">Browse All Products</a>
                </div>
            </div>
            @endforelse
        </div>
    </div>
    <!-- Products End -->

    <!-- Vendor Start -->
    <div class="container-fluid py-5">
        <div class="row px-xl-5">
            <div class="col">
                <div class="owl-carousel vendor-carousel">
                    @for($i = 1; $i <= 8; $i++)
                    <div class="bg-light p-4">
                        <img src="{{ asset('img/vendor-' . $i . '.jpg') }}" alt="">
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
    <!-- Vendor End -->
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Add to cart functionality
    $('.add-to-cart-btn').click(function(e) {
        e.preventDefault();
        
        const button = $(this);
        const productId = button.data('product-id');
        const productName = button.data('product-name');
        const productPrice = button.data('product-price');
        const productImage = button.data('product-image');
        
        // Disable button and show loading
        button.prop('disabled', true);
        const originalHtml = button.html();
        button.html('<i class="fa fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '{{ route("cart.add") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_id: productId,
                quantity: 1,
                price: productPrice
            },
            success: function(response) {
                // Show success message
                toastr.success('Product added to cart successfully!');
                
                // Update cart count in navbar
                updateCartCount();
                
                // Re-enable button
                button.prop('disabled', false);
                button.html(originalHtml);
            },
            error: function(xhr) {
                // Show error message
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error('Failed to add product to cart. Please try again.');
                }
                
                // Re-enable button
                button.prop('disabled', false);
                button.html(originalHtml);
            }
        });
    });
    
    // Add to compare functionality
    $('.add-to-compare-btn').click(function(e) {
        e.preventDefault();
        
        const button = $(this);
        const productId = button.data('product-id');
        const productName = button.data('product-name');
        const productPrice = button.data('product-price');
        const productImage = button.data('product-image');
        
        // Use the global addToCompare function from layout
        if (typeof addToCompare === 'function') {
            addToCompare(productId, productName, productPrice, productImage);
        } else {
            toastr.error('Compare functionality not available');
        }
    });
    
    // Function to update cart count
    function updateCartCount() {
        $.get('{{ route("cart.count") }}', function(data) {
            $('.cart-count').text(data.count);
        });
    }
});
</script>
@endpush