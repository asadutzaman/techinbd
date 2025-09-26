@extends('layouts.app')

@section('title', 'MultiShop - Shop')

@section('content')
    <!-- Breadcrumb Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-12">
                <nav class="breadcrumb bg-light mb-30">
                    <a class="breadcrumb-item text-dark" href="{{ route('home') }}">Home</a>
                    <span class="breadcrumb-item active">Shop</span>
                </nav>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    @if($isSearching)
    <!-- Search Results Header Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-12">
                <div class="bg-light p-4 mb-30">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">
                                <i class="fas fa-search text-primary mr-2"></i>
                                Search Results for "{{ $searchTerm }}"
                            </h4>
                            <p class="mb-0 text-muted">
                                Found {{ $products->total() }} {{ Str::plural('product', $products->total()) }}
                                @if(request('category'))
                                    in {{ ucfirst(request('category')) }} category
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="{{ route('shop') }}" class="btn btn-outline-primary">
                                <i class="fas fa-times mr-1"></i>Clear Search
                            </a>
                        </div>
                    </div>
                    
                    @if($suggestions->count() > 0 && $products->count() < 3)
                    <div class="mt-3">
                        <small class="text-muted">Did you mean:</small>
                        @foreach($suggestions as $suggestion)
                            <a href="{{ route('shop', ['search' => $suggestion['name']]) }}" 
                               class="badge badge-light border ml-1">{{ $suggestion['name'] }}</a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Search Results Header End -->
    @endif

    <!-- Shop Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <!-- Shop Sidebar Start -->
            <div class="col-lg-3 col-md-4">
                <form id="filter-form" method="GET" action="{{ route('shop') }}">
                    <!-- Preserve search term -->
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    
                    <!-- Categories Filter Start -->
                    @if($categories->count() > 0)
                    <h5 class="section-title position-relative text-uppercase mb-3"><span class="bg-secondary pr-3">Categories</span></h5>
                    <div class="bg-light p-4 mb-30">
                        <div class="custom-control custom-radio d-flex align-items-center justify-content-between mb-3">
                            <input type="radio" class="custom-control-input" name="category" value="" id="category-all" 
                                   {{ !request('category') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="category-all">All Categories</label>
                            <span class="badge badge-secondary badge-pill">{{ \App\Models\Product::where('status', 1)->count() }}</span>
                        </div>
                        @foreach($categories as $category)
                        <div class="custom-control custom-radio d-flex align-items-center justify-content-between mb-3">
                            <input type="radio" class="custom-control-input" name="category" value="{{ $category->id }}" 
                                   id="category-{{ $category->id }}" {{ request('category') == $category->id ? 'checked' : '' }}>
                            <label class="custom-control-label" for="category-{{ $category->id }}">{{ $category->name }}</label>
                            <span class="badge badge-secondary badge-pill">{{ $category->products()->where('status', 1)->count() }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    <!-- Categories Filter End -->
                    
                    <!-- Brands Filter Start -->
                    @if($brands->count() > 0)
                    <h5 class="section-title position-relative text-uppercase mb-3"><span class="bg-secondary pr-3">Brands</span></h5>
                    <div class="bg-light p-4 mb-30">
                        <div class="custom-control custom-radio d-flex align-items-center justify-content-between mb-3">
                            <input type="radio" class="custom-control-input" name="brand" value="" id="brand-all" 
                                   {{ !request('brand') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="brand-all">All Brands</label>
                        </div>
                        @foreach($brands as $brand)
                        <div class="custom-control custom-radio d-flex align-items-center justify-content-between mb-3">
                            <input type="radio" class="custom-control-input" name="brand" value="{{ $brand->id }}" 
                                   id="brand-{{ $brand->id }}" {{ request('brand') == $brand->id ? 'checked' : '' }}>
                            <label class="custom-control-label" for="brand-{{ $brand->id }}">{{ $brand->name }}</label>
                            <span class="badge badge-secondary badge-pill">{{ $brand->products()->where('status', 1)->count() }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    <!-- Brands Filter End -->
                    
                    <!-- Product Attributes Filters Start -->
                    @if(!empty($filterableAttributes))
                        @foreach($filterableAttributes as $attribute)
                        <h5 class="section-title position-relative text-uppercase mb-3">
                            <span class="bg-secondary pr-3">{{ $attribute['name'] }}</span>
                        </h5>
                        <div class="bg-light p-4 mb-30">
                            @if($attribute['type'] === 'color')
                                <!-- Color attribute with color swatches -->
                                <div class="d-flex flex-wrap">
                                    @foreach($attribute['values'] as $value)
                                    <div class="custom-control custom-checkbox mr-2 mb-2">
                                        <input type="checkbox" class="custom-control-input" 
                                               name="attributes[{{ $attribute['id'] }}][]" 
                                               value="{{ $value }}" 
                                               id="attr-{{ $attribute['id'] }}-{{ $loop->index }}"
                                               {{ in_array($value, (array) request('attributes.'.$attribute['id'], [])) ? 'checked' : '' }}>
                                        <label class="custom-control-label d-flex align-items-center" 
                                               for="attr-{{ $attribute['id'] }}-{{ $loop->index }}">
                                            <span class="color-swatch mr-2" 
                                                  style="width: 20px; height: 20px; background-color: {{ $value }}; border: 1px solid #ddd; border-radius: 3px; display: inline-block;"></span>
                                            {{ ucfirst($value) }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            @elseif($attribute['type'] === 'size')
                                <!-- Size attribute with size boxes -->
                                <div class="d-flex flex-wrap">
                                    @foreach($attribute['values'] as $value)
                                    <div class="custom-control custom-checkbox mr-2 mb-2">
                                        <input type="checkbox" class="custom-control-input" 
                                               name="attributes[{{ $attribute['id'] }}][]" 
                                               value="{{ $value }}" 
                                               id="attr-{{ $attribute['id'] }}-{{ $loop->index }}"
                                               {{ in_array($value, (array) request('attributes.'.$attribute['id'], [])) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="attr-{{ $attribute['id'] }}-{{ $loop->index }}">
                                            <span class="size-box px-2 py-1 border rounded">{{ strtoupper($value) }}</span>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <!-- Default checkbox list for other attributes -->
                                @foreach($attribute['values'] as $value)
                                <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-2">
                                    <input type="checkbox" class="custom-control-input" 
                                           name="attributes[{{ $attribute['id'] }}][]" 
                                           value="{{ $value }}" 
                                           id="attr-{{ $attribute['id'] }}-{{ $loop->index }}"
                                           {{ in_array($value, (array) request('attributes.'.$attribute['id'], [])) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="attr-{{ $attribute['id'] }}-{{ $loop->index }}">
                                        {{ ucfirst($value) }}
                                    </label>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        @endforeach
                    @endif
                    <!-- Product Attributes Filters End -->
                    
                    <!-- Price Range Filter Start -->
                    <h5 class="section-title position-relative text-uppercase mb-3"><span class="bg-secondary pr-3">Price Range</span></h5>
                    <div class="bg-light p-4 mb-30">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="min_price" class="small">Min Price</label>
                                    <input type="number" class="form-control form-control-sm" name="min_price" 
                                           id="min_price" placeholder="$0" value="{{ request('min_price') }}" min="0">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="max_price" class="small">Max Price</label>
                                    <input type="number" class="form-control form-control-sm" name="max_price" 
                                           id="max_price" placeholder="$1000" value="{{ request('max_price') }}" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Price Filters -->
                        <div class="mb-2">
                            <small class="text-muted">Quick filters:</small>
                        </div>
                        <div class="d-flex flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-primary mr-1 mb-1 price-filter" 
                                    data-min="0" data-max="50">$0-$50</button>
                            <button type="button" class="btn btn-sm btn-outline-primary mr-1 mb-1 price-filter" 
                                    data-min="50" data-max="100">$50-$100</button>
                            <button type="button" class="btn btn-sm btn-outline-primary mr-1 mb-1 price-filter" 
                                    data-min="100" data-max="200">$100-$200</button>
                            <button type="button" class="btn btn-sm btn-outline-primary mr-1 mb-1 price-filter" 
                                    data-min="200" data-max="">$200+</button>
                        </div>
                    </div>
                    <!-- Price Range Filter End -->
                    
                    <!-- Additional Filters Start -->
                    <h5 class="section-title position-relative text-uppercase mb-3"><span class="bg-secondary pr-3">Filters</span></h5>
                    <div class="bg-light p-4 mb-30">
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" name="sale" value="1" 
                                   id="filter-sale" {{ request('sale') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="filter-sale">
                                <i class="fas fa-tag text-danger mr-1"></i>On Sale
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" name="in_stock" value="1" 
                                   id="filter-stock" {{ request('in_stock') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="filter-stock">
                                <i class="fas fa-check-circle text-success mr-1"></i>In Stock
                            </label>
                        </div>
                    </div>
                    <!-- Additional Filters End -->
                    
                    <!-- Active Filters Display -->
                    @php
                        $activeFilters = 0;
                        if(request('category')) $activeFilters++;
                        if(request('brand')) $activeFilters++;
                        if(request('min_price') || request('max_price')) $activeFilters++;
                        if(request('sale')) $activeFilters++;
                        if(request('in_stock')) $activeFilters++;
                        if(request('attributes')) {
                            foreach(request('attributes', []) as $attrValues) {
                                if(!empty($attrValues)) $activeFilters++;
                            }
                        }
                    @endphp
                    
                    @if($activeFilters > 0)
                    <div class="bg-primary text-white p-3 mb-3 rounded">
                        <small><i class="fas fa-filter mr-1"></i>{{ $activeFilters }} filter(s) active</small>
                    </div>
                    @endif
                    
                    <!-- Filter Actions -->
                    <div class="mb-30">
                        <button type="submit" class="btn btn-primary btn-block mb-2">
                            <i class="fas fa-filter mr-1"></i>Apply Filters
                        </button>
                        <button type="button" id="clear-all-filters" class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-times mr-1"></i>Clear All
                        </button>
                    </div>
                </form>
            </div>
            <!-- Shop Sidebar End -->

            <!-- Shop Product Start -->
            <div class="col-lg-9 col-md-8">
                <div class="row pb-3">
                    <div class="col-12 pb-1">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <button class="btn btn-sm btn-light"><i class="fa fa-th-large"></i></button>
                                <button class="btn btn-sm btn-light ml-2"><i class="fa fa-bars"></i></button>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="text-muted mr-3">
                                    Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
                                </span>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">
                                        Sorting
                                        @if(request('sort'))
                                            <small class="text-primary">({{ ucfirst(str_replace('_', ' ', request('sort'))) }})</small>
                                        @endif
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item {{ request('sort', 'latest') == 'latest' ? 'active' : '' }}" 
                                           href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}">Latest</a>
                                        <a class="dropdown-item {{ request('sort') == 'price_low' ? 'active' : '' }}" 
                                           href="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}">Price: Low to High</a>
                                        <a class="dropdown-item {{ request('sort') == 'price_high' ? 'active' : '' }}" 
                                           href="{{ request()->fullUrlWithQuery(['sort' => 'price_high']) }}">Price: High to Low</a>
                                        <a class="dropdown-item {{ request('sort') == 'name' ? 'active' : '' }}" 
                                           href="{{ request()->fullUrlWithQuery(['sort' => 'name']) }}">Name A-Z</a>
                                    </div>
                                </div>
                                <div class="btn-group ml-2">
                                    <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown">Per Page</button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['per_page' => 9]) }}">9</a>
                                        <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['per_page' => 15]) }}">15</a>
                                        <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['per_page' => 21]) }}">21</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @forelse($products as $product)
                    <div class="col-lg-4 col-md-6 col-sm-6 pb-1">
                        <div class="product-item bg-light mb-4">
                            <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100" src="{{ asset('img/' . $product->image) }}" alt="{{ $product->name }}">
                                <div class="product-action">
                                    <button class="btn btn-outline-dark btn-square add-to-cart-btn" 
                                            data-product-id="{{ $product->id }}" 
                                            data-product-name="{{ $product->name }}" 
                                            data-product-price="{{ $product->display_price }}">
                                        <i class="fa fa-shopping-cart"></i>
                                    </button>
                                    <a class="btn btn-outline-dark btn-square" href=""><i class="far fa-heart"></i></a>
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
                                    <h5>${{ number_format($product->display_price, 2) }}</h5>
                                    @if($product->is_on_sale)
                                        <h6 class="text-muted ml-2"><del>${{ number_format($product->price, 2) }}</del></h6>
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
                        <div class="alert alert-info text-center">
                            <h4>No products found</h4>
                            <p>Please check back later for new products.</p>
                        </div>
                    </div>
                    @endforelse
                    @if($products->hasPages())
                    <div class="col-12">
                        {{ $products->links('custom-pagination') }}
                    </div>
                    @endif
                </div>
            </div>
            <!-- Shop Product End -->
        </div>
    </div>
    <!-- Shop End -->
@endsection

@push('styles')
<style>
.color-swatch {
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-radius: 3px;
    display: inline-block;
    cursor: pointer;
    transition: all 0.3s ease;
}

.custom-control-input:checked ~ .custom-control-label .color-swatch {
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.size-box {
    background: #f8f9fa;
    border: 1px solid #dee2e6 !important;
    transition: all 0.3s ease;
    min-width: 35px;
    text-align: center;
}

.custom-control-input:checked ~ .custom-control-label .size-box {
    background: #007bff;
    color: white;
    border-color: #007bff !important;
}

.filter-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 255, 255, 0.9);
    padding: 10px;
    border-radius: 5px;
    z-index: 10;
}

.badge-pill {
    font-size: 0.75em;
}

/* Hide default checkbox for color and size attributes */
.custom-control-input[name*="attributes"] {
    position: absolute;
    opacity: 0;
}

.custom-control-input[name*="attributes"] + .custom-control-label {
    cursor: pointer;
}

.custom-control-input[name*="attributes"] + .custom-control-label::before,
.custom-control-input[name*="attributes"] + .custom-control-label::after {
    display: none;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Add to cart functionality
    $('.add-to-cart-btn').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var productId = button.data('product-id');
        var productName = button.data('product-name');
        var productPrice = button.data('product-price');
        
        // Disable button and show loading
        button.prop('disabled', true);
        button.html('<i class="fa fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '{{ route("cart.add") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_id: productId,
                quantity: 1,
                size: null,
                color: null
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showNotification('success', response.message);
                    
                    // Update cart count
                    updateCartCount();
                } else {
                    showNotification('error', 'Failed to add product to cart');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                showNotification('error', 'An error occurred while adding to cart');
            },
            complete: function() {
                // Re-enable button
                button.prop('disabled', false);
                button.html('<i class="fa fa-shopping-cart"></i>');
            }
        });
    });
    
    // Function to show notifications
    function showNotification(type, message) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var notification = $('<div class="alert ' + alertClass + ' alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">' +
            '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
            message +
            '</div>');
        
        $('body').append(notification);
        
        // Auto remove after 3 seconds
        setTimeout(function() {
            notification.alert('close');
        }, 3000);
    }
    
    // Function to update cart count
    function updateCartCount() {
        $.ajax({
            url: '{{ route("cart.count") }}',
            method: 'GET',
            success: function(response) {
                // Update cart badge in navbar
                $('.navbar-nav .badge').text(response.count);
            }
        });
    }
    
    // Add to compare functionality
    $('.add-to-compare-btn').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var productId = button.data('product-id');
        var productName = button.data('product-name');
        var productPrice = button.data('product-price');
        var productImage = button.data('product-image');
        
        // Use the global addToCompare function from layout
        if (typeof addToCompare === 'function') {
            addToCompare(productId, productName, productPrice, productImage);
        } else {
            showNotification('error', 'Compare functionality not available');
        }
    });
    
    // Price filter buttons functionality
    $('.price-filter').on('click', function() {
        var minPrice = $(this).data('min');
        var maxPrice = $(this).data('max');
        
        $('#min_price').val(minPrice);
        $('#max_price').val(maxPrice || '');
        
        // Highlight active button
        $('.price-filter').removeClass('btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
        
        // Auto-submit form
        $('#filter-form').submit();
    });
    
    // Handle category change to load dynamic attributes
    $('input[name="category"]').on('change', function() {
        var categoryId = $(this).val();
        
        if (categoryId) {
            loadAttributesForCategory(categoryId);
        } else {
            // If "All Categories" selected, remove attribute filters
            $('.attribute-filter-section').remove();
            $('#filter-form').submit();
        }
    });
    
    // Function to load attributes for selected category
    function loadAttributesForCategory(categoryId) {
        // Show loading state
        showAttributeLoading();
        
        $.ajax({
            url: '{{ route("shop.attributes-by-category") }}',
            method: 'GET',
            data: { category_id: categoryId },
            success: function(attributes) {
                // Remove existing attribute filters
                $('.attribute-filter-section').remove();
                
                // Add new attribute filters
                if (attributes.length > 0) {
                    var attributeHtml = '';
                    
                    attributes.forEach(function(attribute) {
                        attributeHtml += buildAttributeFilterHtml(attribute);
                    });
                    
                    // Insert after brands filter
                    $('.bg-light.p-4.mb-30').last().after(attributeHtml);
                }
                
                // Submit form to apply category filter
                $('#filter-form').submit();
            },
            error: function() {
                console.error('Failed to load attributes');
                $('#filter-form').submit();
            }
        });
    }
    
    // Function to build HTML for attribute filter
    function buildAttributeFilterHtml(attribute) {
        var html = '<h5 class="section-title position-relative text-uppercase mb-3 attribute-filter-section">';
        html += '<span class="bg-secondary pr-3">' + attribute.name + '</span></h5>';
        html += '<div class="bg-light p-4 mb-30 attribute-filter-section">';
        
        if (attribute.type === 'color') {
            html += '<div class="d-flex flex-wrap">';
            attribute.values.forEach(function(value, index) {
                html += '<div class="custom-control custom-checkbox mr-2 mb-2">';
                html += '<input type="checkbox" class="custom-control-input" name="attributes[' + attribute.id + '][]" value="' + value + '" id="attr-' + attribute.id + '-' + index + '">';
                html += '<label class="custom-control-label d-flex align-items-center" for="attr-' + attribute.id + '-' + index + '">';
                html += '<span class="color-swatch mr-2" style="width: 20px; height: 20px; background-color: ' + value + '; border: 1px solid #ddd; border-radius: 3px; display: inline-block;"></span>';
                html += value.charAt(0).toUpperCase() + value.slice(1) + '</label></div>';
            });
            html += '</div>';
        } else if (attribute.type === 'size') {
            html += '<div class="d-flex flex-wrap">';
            attribute.values.forEach(function(value, index) {
                html += '<div class="custom-control custom-checkbox mr-2 mb-2">';
                html += '<input type="checkbox" class="custom-control-input" name="attributes[' + attribute.id + '][]" value="' + value + '" id="attr-' + attribute.id + '-' + index + '">';
                html += '<label class="custom-control-label" for="attr-' + attribute.id + '-' + index + '">';
                html += '<span class="size-box px-2 py-1 border rounded">' + value.toUpperCase() + '</span></label></div>';
            });
            html += '</div>';
        } else {
            attribute.values.forEach(function(value, index) {
                html += '<div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-2">';
                html += '<input type="checkbox" class="custom-control-input" name="attributes[' + attribute.id + '][]" value="' + value + '" id="attr-' + attribute.id + '-' + index + '">';
                html += '<label class="custom-control-label" for="attr-' + attribute.id + '-' + index + '">';
                html += value.charAt(0).toUpperCase() + value.slice(1) + '</label></div>';
            });
        }
        
        html += '</div>';
        return html;
    }
    
    // Function to show loading state for attributes
    function showAttributeLoading() {
        $('.attribute-filter-section').remove();
        var loadingHtml = '<div class="attribute-filter-section text-center p-4 mb-30 bg-light">';
        loadingHtml += '<i class="fa fa-spinner fa-spin mr-2"></i>Loading attributes...';
        loadingHtml += '</div>';
        $('.bg-light.p-4.mb-30').last().after(loadingHtml);
    }
    
    // Auto-submit form when other filters change
    $(document).on('change', '#filter-form input[type="radio"]:not([name="category"]), #filter-form input[type="checkbox"]', function() {
        $('#filter-form').submit();
    });
    
    // Highlight current price filter on page load
    var currentMin = $('#min_price').val();
    var currentMax = $('#max_price').val();
    
    $('.price-filter').each(function() {
        var btnMin = $(this).data('min').toString();
        var btnMax = $(this).data('max').toString();
        
        if (currentMin == btnMin && (currentMax == btnMax || (btnMax === '' && currentMax === ''))) {
            $(this).removeClass('btn-outline-primary').addClass('btn-primary');
        }
    });
    
    // Clear all filters functionality
    $('#clear-all-filters').on('click', function() {
        // Reset all form inputs
        $('#filter-form')[0].reset();
        
        // Remove dynamic attribute filters
        $('.attribute-filter-section').remove();
        
        // Redirect to clean shop page
        window.location.href = '{{ route("shop") }}';
    });
    
    // Load cart count on page load
    updateCartCount();
});
</script>
@endpush