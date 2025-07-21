@extends('layouts.app')

@section('title', 'MultiShop - Shopping Cart')

@section('content')
    <!-- Breadcrumb Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-12">
                <nav class="breadcrumb bg-light mb-30">
                    <a class="breadcrumb-item text-dark" href="{{ route('home') }}">Home</a>
                    <a class="breadcrumb-item text-dark" href="{{ route('shop') }}">Shop</a>
                    <span class="breadcrumb-item active">Shopping Cart</span>
                </nav>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Cart Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-lg-8 table-responsive mb-5">
                <table class="table table-light table-borderless table-hover text-center mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Products</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                        @forelse($cartItems as $item)
                        <tr>
                            <td class="align-middle">
                                <img src="{{ asset('img/' . $item->product->image) }}" alt="{{ $item->product->name }}" style="width: 50px;">
                                {{ $item->product->name }}
                                @if($item->size)
                                    <br><small class="text-muted">Size: {{ $item->size }}</small>
                                @endif
                                @if($item->color)
                                    <br><small class="text-muted">Color: {{ $item->color }}</small>
                                @endif
                            </td>
                            <td class="align-middle">${{ number_format($item->price, 2) }}</td>
                            <td class="align-middle">
                                <div class="input-group quantity mx-auto" style="width: 100px;">
                                    <div class="input-group-btn">
                                        <button class="btn btn-sm btn-primary btn-minus" data-cart-id="{{ $item->id }}">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                    </div>
                                    <input type="text" class="form-control form-control-sm bg-secondary border-0 text-center quantity-input" 
                                           value="{{ $item->quantity }}" data-cart-id="{{ $item->id }}" readonly>
                                    <div class="input-group-btn">
                                        <button class="btn btn-sm btn-primary btn-plus" data-cart-id="{{ $item->id }}">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle">${{ number_format($item->total, 2) }}</td>
                            <td class="align-middle">
                                <button class="btn btn-sm btn-danger remove-item" data-cart-id="{{ $item->id }}">
                                    <i class="fa fa-times"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <h4>Your cart is empty</h4>
                                <p>Add some products to your cart to see them here.</p>
                                <a href="{{ route('shop') }}" class="btn btn-primary">Continue Shopping</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="col-lg-4">
                <form class="mb-30" action="">
                    <div class="input-group">
                        <input type="text" class="form-control border-0 p-4" placeholder="Coupon Code">
                        <div class="input-group-append">
                            <button class="btn btn-primary">Apply Coupon</button>
                        </div>
                    </div>
                </form>
                <h5 class="section-title position-relative text-uppercase mb-3"><span class="bg-secondary pr-3">Cart Summary</span></h5>
                <div class="bg-light p-30 mb-5">
                    @if($cartItems->count() > 0)
                    <div class="border-bottom pb-2">
                        <div class="d-flex justify-content-between mb-3">
                            <h6>Subtotal</h6>
                            <h6 id="cart-subtotal">${{ number_format($subtotal, 2) }}</h6>
                        </div>
                        <div class="d-flex justify-content-between">
                            <h6 class="font-weight-medium">Shipping</h6>
                            <h6 class="font-weight-medium">${{ number_format($shipping, 2) }}</h6>
                        </div>
                    </div>
                    <div class="pt-2">
                        <div class="d-flex justify-content-between mt-2">
                            <h5>Total</h5>
                            <h5 id="cart-total">${{ number_format($total, 2) }}</h5>
                        </div>
                        <a href="{{ route('checkout') }}" class="btn btn-block btn-primary font-weight-bold my-3 py-3">Proceed To Checkout</a>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <p>Your cart is empty</p>
                        <a href="{{ route('shop') }}" class="btn btn-primary">Start Shopping</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Cart End -->
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update quantity buttons
    $('.btn-plus').on('click', function() {
        var cartId = $(this).data('cart-id');
        var quantityInput = $('input[data-cart-id="' + cartId + '"]');
        var currentQuantity = parseInt(quantityInput.val());
        updateCartQuantity(cartId, currentQuantity + 1);
    });

    $('.btn-minus').on('click', function() {
        var cartId = $(this).data('cart-id');
        var quantityInput = $('input[data-cart-id="' + cartId + '"]');
        var currentQuantity = parseInt(quantityInput.val());
        if (currentQuantity > 1) {
            updateCartQuantity(cartId, currentQuantity - 1);
        }
    });

    // Remove item from cart
    $('.remove-item').on('click', function() {
        var cartId = $(this).data('cart-id');
        if (confirm('Are you sure you want to remove this item from your cart?')) {
            removeCartItem(cartId);
        }
    });

    // Function to update cart quantity
    function updateCartQuantity(cartId, quantity) {
        $.ajax({
            url: '{{ route("cart.update", ":id") }}'.replace(':id', cartId),
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                quantity: quantity
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload page to update totals
                } else {
                    alert('Failed to update cart');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                alert('An error occurred while updating cart');
            }
        });
    }

    // Function to remove cart item
    function removeCartItem(cartId) {
        $.ajax({
            url: '{{ route("cart.remove", ":id") }}'.replace(':id', cartId),
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload page to update cart
                } else {
                    alert('Failed to remove item');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                alert('An error occurred while removing item');
            }
        });
    }
});
</script>
@endpush