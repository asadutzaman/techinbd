@extends('layouts.app')

@section('title', $product->name . ' - MultiShop')

@push('styles')
<style>
    .product-image {
        height: 400px;
        object-fit: cover;
    }
    .thumbnail-image {
        height: 80px;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid transparent;
    }
    .thumbnail-image.active {
        border-color: #FFD333;
    }
    .attribute-item {
        border-bottom: 1px solid #eee;
        padding: 8px 0;
    }
    .attribute-item:last-child {
        border-bottom: none;
    }
    .variant-option {
        margin: 5px;
        padding: 8px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .variant-option:hover,
    .variant-option.active {
        border-color: #FFD333;
        background-color: #FFD333;
        color: #fff;
    }
    .stock-badge {
        font-size: 0.8rem;
        padding: 4px 8px;
    }
</style>
@endpush

@section('content')
    <!-- Breadcrumb Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-12">
                <nav class="breadcrumb bg-light mb-30">
                    <a class="breadcrumb-item text-dark" href="{{ route('home') }}">Home</a>
                    <a class="breadcrumb-item text-dark" href="{{ route('shop') }}">Shop</a>
                    @if($product->category)
                        <a class="breadcrumb-item text-dark" href="{{ route('shop') }}?category={{ $product->category->id }}">{{ $product->category->name }}</a>
                    @endif
                    <span class="breadcrumb-item active">{{ $product->name }}</span>
                </nav>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Shop Detail Start -->
    <div class="container-fluid pb-5">
        <div class="row px-xl-5">
            <div class="col-lg-5 mb-30">
                @if($product->images->count() > 0)
                    <div id="product-carousel" class="carousel slide" data-ride="carousel">
                        <div class="carousel-inner bg-light">
                            @foreach($product->images as $index => $image)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <img class="w-100 product-image" src="{{ asset('storage/' . $image->url) }}" alt="{{ $image->alt_text ?: $product->name }}">
                                </div>
                            @endforeach
                        </div>
                        @if($product->images->count() > 1)
                            <a class="carousel-control-prev" href="#product-carousel" data-slide="prev">
                                <i class="fa fa-2x fa-angle-left text-dark"></i>
                            </a>
                            <a class="carousel-control-next" href="#product-carousel" data-slide="next">
                                <i class="fa fa-2x fa-angle-right text-dark"></i>
                            </a>
                        @endif
                    </div>
                    
                    @if($product->images->count() > 1)
                        <div class="row mt-3">
                            @foreach($product->images as $index => $image)
                                <div class="col-3">
                                    <img class="w-100 thumbnail-image {{ $index === 0 ? 'active' : '' }}" 
                                         src="{{ asset('storage/' . $image->url) }}" 
                                         alt="{{ $image->alt_text ?: $product->name }}"
                                         onclick="changeMainImage({{ $index }})">
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                        <i class="fa fa-image fa-5x text-muted"></i>
                    </div>
                @endif
            </div>

            <div class="col-lg-7 h-auto mb-30">
                <div class="h-100 bg-light p-30">
                    <h3>{{ $product->name }}</h3>
                    
                    @if($product->brand)
                        <p class="text-muted mb-2">Brand: <strong>{{ $product->brand->name }}</strong></p>
                    @endif
                    
                    @if($product->sku)
                        <p class="text-muted mb-2">SKU: <code>{{ $product->sku }}</code></p>
                    @endif

                    <div class="d-flex mb-3">
                        <div class="text-primary mr-2">
                            <small class="fas fa-star"></small>
                            <small class="fas fa-star"></small>
                            <small class="fas fa-star"></small>
                            <small class="fas fa-star-half-alt"></small>
                            <small class="far fa-star"></small>
                        </div>
                        <small class="pt-1">({{ rand(10, 99) }} Reviews)</small>
                    </div>

                    <div class="mb-3">
                        <h3 class="font-weight-semi-bold text-primary d-inline">{{ $product->currency }} {{ number_format($product->base_price, 2) }}</h3>
                        @if($product->variants->where('compare_price', '>', 0)->count() > 0)
                            <h5 class="text-muted d-inline ml-2"><del>{{ $product->currency }} {{ number_format($product->variants->where('compare_price', '>', 0)->first()->compare_price, 2) }}</del></h5>
                        @endif
                    </div>

                    <div class="mb-3">
                        <span class="badge stock-badge {{ $product->stock_status === 'in_stock' ? 'badge-success' : ($product->stock_status === 'out_of_stock' ? 'badge-danger' : 'badge-warning') }}">
                            {{ ucfirst(str_replace('_', ' ', $product->stock_status)) }}
                        </span>
                        @if($product->manage_stock && $product->total_stock > 0)
                            <small class="text-muted ml-2">{{ $product->total_stock }} in stock</small>
                        @endif
                    </div>

                    @if($product->short_description)
                        <p class="mb-4">{{ $product->short_description }}</p>
                    @endif
                    @if($product->variants->count() > 0)
                        <div class="mb-3">
                            <strong class="text-dark mr-3">Variants:</strong>
                            <div class="d-flex flex-wrap">
                                @foreach($product->variants as $variant)
                                    <div class="variant-option {{ $variant->is_default ? 'active' : '' }}" 
                                         data-variant-id="{{ $variant->id }}"
                                         data-price="{{ $variant->price ?: $product->base_price }}"
                                         data-stock="{{ $variant->stock }}">
                                        {{ $variant->name ?: 'Default' }}
                                        @if($variant->price && $variant->price != $product->base_price)
                                            <small class="d-block">{{ $product->currency }} {{ number_format($variant->price, 2) }}</small>
                                        @endif
                                        <small class="d-block text-muted">Stock: {{ $variant->stock }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($product->productAttributes->count() > 0)
                        <div class="mb-4">
                            <strong class="text-dark d-block mb-2">Specifications:</strong>
                            <div class="row">
                                @foreach($product->productAttributes->chunk(ceil($product->productAttributes->count() / 2)) as $chunk)
                                    <div class="col-md-6">
                                        @foreach($chunk as $productAttribute)
                                            <div class="attribute-item">
                                                <strong>{{ $productAttribute->attribute->name }}:</strong>
                                                <span class="ml-2">{!! $productAttribute->display_value !!}</span>
                                                @if($productAttribute->attribute->unit)
                                                    <small class="text-muted">{{ $productAttribute->attribute->unit }}</small>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <form id="add-to-cart-form" class="mb-4">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="variant_id" id="selected-variant-id" value="{{ $product->variants->where('is_default', true)->first()?->id }}">
                        
                        <div class="d-flex align-items-center pt-2">
                            <div class="input-group quantity mr-3" style="width: 130px;">
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-primary btn-minus">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                                <input type="number" name="quantity" class="form-control bg-secondary border-0 text-center" value="1" min="1" max="{{ $product->total_stock }}">
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-primary btn-plus">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary px-3" {{ $product->stock_status !== 'in_stock' ? 'disabled' : '' }}>
                                <i class="fa fa-shopping-cart mr-1"></i> 
                                {{ $product->stock_status === 'in_stock' ? 'Add To Cart' : 'Out of Stock' }}
                            </button>
                        </div>
                    </form>
                    <div class="d-flex pt-2">
                        <strong class="text-dark mr-2">Share on:</strong>
                        <div class="d-inline-flex">
                            <a class="text-dark px-2" href="">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a class="text-dark px-2" href="">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a class="text-dark px-2" href="">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a class="text-dark px-2" href="">
                                <i class="fab fa-pinterest"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row px-xl-5">
            <div class="col">
                <div class="bg-light p-30">
                    <div class="nav nav-tabs mb-4">
                        <a class="nav-item nav-link text-dark active" data-toggle="tab" href="#tab-pane-1">Description</a>
                        <a class="nav-item nav-link text-dark" data-toggle="tab" href="#tab-pane-2">Information</a>
                        <a class="nav-item nav-link text-dark" data-toggle="tab" href="#tab-pane-3">Reviews ({{ rand(10, 99) }})</a>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-pane-1">
                            <h4 class="mb-3">Product Description</h4>
                            @if($product->description)
                                <div class="product-description">
                                    {!! $product->description !!}
                                </div>
                            @else
                                <p>No detailed description available for this product.</p>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="tab-pane-2">
                            <h4 class="mb-3">Additional Information</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-group list-group-flush">
                                        @if($product->weight)
                                            <li class="list-group-item px-0">
                                                <strong>Weight:</strong> {{ $product->weight }} kg
                                            </li>
                                        @endif
                                        @if($product->dimensions)
                                            <li class="list-group-item px-0">
                                                <strong>Dimensions:</strong> {{ $product->dimensions }}
                                            </li>
                                        @endif
                                        @if($product->warranty)
                                            <li class="list-group-item px-0">
                                                <strong>Warranty:</strong> {{ $product->warranty }}
                                            </li>
                                        @endif
                                        @if($product->manufacturer_part_no)
                                            <li class="list-group-item px-0">
                                                <strong>Manufacturer Part No:</strong> {{ $product->manufacturer_part_no }}
                                            </li>
                                        @endif
                                    </ul> 
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-group list-group-flush">
                                        @if($product->ean_upc)
                                            <li class="list-group-item px-0">
                                                <strong>EAN/UPC:</strong> {{ $product->ean_upc }}
                                            </li>
                                        @endif
                                        @if($product->category)
                                            <li class="list-group-item px-0">
                                                <strong>Category:</strong> {{ $product->category->name }}
                                            </li>
                                        @endif
                                        @if($product->brand)
                                            <li class="list-group-item px-0">
                                                <strong>Brand:</strong> {{ $product->brand->name }}
                                            </li>
                                        @endif
                                        <li class="list-group-item px-0">
                                            <strong>Currency:</strong> {{ $product->currency }}
                                        </li>
                                    </ul> 
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-pane-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4 class="mb-4">{{ rand(10, 99) }} review for "{{ $product->name }}"</h4>
                                    @for($i = 1; $i <= 3; $i++)
                                    <div class="media mb-4">
                                        <img src="{{ asset('img/user.jpg') }}" alt="Image" class="img-fluid mr-3 mt-1" style="width: 45px;">
                                        <div class="media-body">
                                            <h6>John Doe<small> - <i>{{ now()->subDays($i)->format('M d, Y') }}</i></small></h6>
                                            <div class="text-primary mb-2">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star-half-alt"></i>
                                                <i class="far fa-star"></i>
                                            </div>
                                            <p>Diam amet duo labore stet elitr ea clita ipsum, tempor labore accusam ipsum et no at. Kasd diam tempor rebum magna dolores sed sed eirmod ipsum.</p>
                                        </div>
                                    </div>
                                    @endfor
                                </div>
                                <div class="col-md-6">
                                    <h4 class="mb-4">Leave a review</h4>
                                    <small>Your email address will not be published. Required fields are marked *</small>
                                    <div class="d-flex my-3">
                                        <p class="mb-0 mr-2">Your Rating * :</p>
                                        <div class="text-primary">
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                        </div>
                                    </div>
                                    <form>
                                        <div class="form-group">
                                            <label for="message">Your Review *</label>
                                            <textarea id="message" cols="30" rows="5" class="form-control"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="name">Your Name *</label>
                                            <input type="text" class="form-control" id="name">
                                        </div>
                                        <div class="form-group">
                                            <label for="email">Your Email *</label>
                                            <input type="email" class="form-control" id="email">
                                        </div>
                                        <div class="form-group mb-0">
                                            <input type="submit" value="Leave Your Review" class="btn btn-primary px-3">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Shop Detail End -->

    <!-- Related Products Start -->
    @if($relatedProducts->count() > 0)
    <div class="container-fluid py-5">
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span class="bg-secondary pr-3">You May Also Like</span></h2>
        <div class="row px-xl-5">
            <div class="col">
                <div class="owl-carousel related-carousel">
                    @foreach($relatedProducts as $relatedProduct)
                    <div class="product-item bg-light">
                        <div class="product-img position-relative overflow-hidden">
                            @if($relatedProduct->mainImage->first())
                                <img class="img-fluid w-100" src="{{ asset('storage/' . $relatedProduct->mainImage->first()->url) }}" alt="{{ $relatedProduct->name }}" style="height: 250px; object-fit: cover;">
                            @else
                                <div class="img-fluid w-100 d-flex align-items-center justify-content-center bg-light" style="height: 250px;">
                                    <i class="fa fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                            <div class="product-action">
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-shopping-cart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="far fa-heart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-sync-alt"></i></a>
                                <a class="btn btn-outline-dark btn-square" href="{{ route('product.detail', $relatedProduct->id) }}"><i class="fa fa-search"></i></a>
                            </div>
                        </div>
                        <div class="text-center py-4">
                            <a class="h6 text-decoration-none text-truncate" href="{{ route('product.detail', $relatedProduct->id) }}">{{ $relatedProduct->name }}</a>
                            <div class="d-flex align-items-center justify-content-center mt-2">
                                <h5>{{ $relatedProduct->currency }} {{ number_format($relatedProduct->base_price, 2) }}</h5>
                                @if($relatedProduct->variants->where('compare_price', '>', 0)->count() > 0)
                                    <h6 class="text-muted ml-2"><del>{{ $relatedProduct->currency }} {{ number_format($relatedProduct->variants->where('compare_price', '>', 0)->first()->compare_price, 2) }}</del></h6>
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
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- Related Products End -->
@endsection

@push('scripts')
<script>
    // Image carousel functionality
    function changeMainImage(index) {
        $('#product-carousel').carousel(index);
        $('.thumbnail-image').removeClass('active');
        $('.thumbnail-image').eq(index).addClass('active');
    }

    // Quantity controls
    $('.btn-plus').click(function() {
        var input = $(this).closest('.input-group').find('input[name="quantity"]');
        var currentVal = parseInt(input.val());
        var maxVal = parseInt(input.attr('max'));
        if (currentVal < maxVal) {
            input.val(currentVal + 1);
        }
    });

    $('.btn-minus').click(function() {
        var input = $(this).closest('.input-group').find('input[name="quantity"]');
        var currentVal = parseInt(input.val());
        if (currentVal > 1) {
            input.val(currentVal - 1);
        }
    });

    // Variant selection
    $('.variant-option').click(function() {
        $('.variant-option').removeClass('active');
        $(this).addClass('active');
        
        var variantId = $(this).data('variant-id');
        var price = $(this).data('price');
        var stock = $(this).data('stock');
        
        $('#selected-variant-id').val(variantId);
        
        // Update price display
        var currency = '{{ $product->currency }}';
        $('.font-weight-semi-bold.text-primary').text(currency + ' ' + parseFloat(price).toFixed(2));
        
        // Update stock info
        $('input[name="quantity"]').attr('max', stock);
        if (stock === 0) {
            $('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-shopping-cart mr-1"></i> Out of Stock');
        } else {
            $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-shopping-cart mr-1"></i> Add To Cart');
        }
    });

    // Add to cart form submission
    $('#add-to-cart-form').submit(function(e) {
        e.preventDefault();
        
        // Here you can add AJAX call to add product to cart
        var formData = $(this).serialize();
        
        // For now, just show an alert
        alert('Product added to cart! (This is a demo - implement actual cart functionality)');
        
        // You can implement actual cart functionality here
        // $.ajax({
        //     url: '{{ route("cart.add") }}',
        //     method: 'POST',
        //     data: formData,
        //     success: function(response) {
        //         // Handle success
        //     }
        // });
    });
</script>
@endpush