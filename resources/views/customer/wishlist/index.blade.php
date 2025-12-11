@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="{{ route('home') }}">Home</a>
                <a class="breadcrumb-item text-dark" href="{{ route('customer.dashboard') }}">My Account</a>
                <span class="breadcrumb-item active">Wishlist</span>
            </nav>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row px-xl-5">
        <!-- Sidebar -->
        <div class="col-lg-3">
            @include('customer.partials.sidebar')
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <h5 class="section-title position-relative text-uppercase mb-3">
                <span class="bg-secondary pr-3">My Wishlist ({{ $wishlistItems->count() }})</span>
            </h5>
            
            @if($wishlistItems->count() > 0)
                <div class="bg-light p-30">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center mb-0">
                            <thead class="bg-secondary text-dark">
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Stock Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle">
                                @foreach($wishlistItems as $item)
                                <tr id="wishlist-item-{{ $item->id }}">
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            @if($item->product->images->count() > 0)
                                                <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" 
                                                     alt="{{ $item->product->name }}" style="width: 50px;">
                                            @else
                                                <img src="{{ asset('img/product-1.jpg') }}" 
                                                     alt="{{ $item->product->name }}" style="width: 50px;">
                                            @endif
                                            <div class="ml-3 text-left">
                                                <h6 class="mb-1">{{ $item->product->name }}</h6>
                                                @if($item->product->brand)
                                                    <small class="text-muted">{{ $item->product->brand->name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">${{ number_format($item->product->price, 2) }}</td>
                                    <td class="align-middle">
                                        @if($item->product->stock_quantity > 0)
                                            <span class="badge badge-success">In Stock</span>
                                        @else
                                            <span class="badge badge-danger">Out of Stock</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($item->product->stock_quantity > 0)
                                            <button class="btn btn-sm btn-primary mr-2" onclick="moveToCart({{ $item->product->id }})">
                                                <i class="fa fa-shopping-cart"></i> Add to Cart
                                            </button>
                                        @endif
                                        <button class="btn btn-sm btn-outline-danger" onclick="removeFromWishlist({{ $item->product->id }})">
                                            <i class="fa fa-times"></i> Remove
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-light p-30 text-center">
                    <i class="fa fa-heart fa-3x text-muted mb-3"></i>
                    <h6>Your wishlist is empty</h6>
                    <p class="mb-3">Save your favorite products to your wishlist for easy access later.</p>
                    <a href="{{ route('shop') }}" class="btn btn-primary">Continue Shopping</a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function removeFromWishlist(productId) {
    if (confirm('Are you sure you want to remove this item from your wishlist?')) {
        fetch('{{ route("customer.wishlist.remove") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}

function moveToCart(productId) {
    fetch('{{ route("customer.wishlist.move-to-cart") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>
@endpush
@endsection