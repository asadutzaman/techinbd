@extends('layouts.app')

@section('title', 'Order confirmation | ' . config('shop.name'))

@section('content')
    <!-- Breadcrumb Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-12">
                <nav class="breadcrumb bg-light mb-30">
                    <a class="breadcrumb-item text-dark" href="{{ route('home') }}">Home</a>
                    <a class="breadcrumb-item text-dark" href="{{ route('shop') }}">Shop</a>
                    <a class="breadcrumb-item text-dark" href="{{ route('checkout') }}">Checkout</a>
                    <span class="breadcrumb-item active">Order Confirmation</span>
                </nav>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Order Success Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-12">
                <div class="bg-light p-30 mb-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                        <h2 class="mt-3">Thank You For Your Order!</h2>
                        <p class="lead">Your order has been placed successfully.</p>
                        <p>Order #: <strong>{{ $order->order_number }}</strong></p>
                        <p class="text-muted mb-0">We've emailed a confirmation to <strong>{{ $order->customer_email }}</strong>.</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h4 class="mb-3">Order Details</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Order Number:</th>
                                    <td>{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <th>Order Date:</th>
                                    <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Order Status:</th>
                                    <td>
                                        <span class="badge badge-warning">{{ $order->status_label }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td>{{ $order->payment_method_label }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Status:</th>
                                    <td>
                                        <span class="badge badge-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total Amount:</th>
                                    <td><x-price :amount="$order->total" /></td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6 mb-4">
                            <h4 class="mb-3">Shipping Information</h4>
                            <address>
                                <strong>{{ $order->customer_name }}</strong><br>
                                {{ $order->billing_address }}<br>
                                <strong>Phone:</strong> {{ $order->customer_phone }}<br>
                                <strong>Email:</strong> {{ $order->customer_email }}
                            </address>
                        </div>
                    </div>

                    <h4 class="mb-3">Order Items</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="bg-secondary text-dark">
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                <tr>
                                    <td>
                                        {{ $item->product_name }}
                                        @if($item->size) <br><small>Size: {{ $item->size }}</small> @endif
                                        @if($item->color) <br><small>Color: {{ $item->color }}</small> @endif
                                    </td>
                                    <td><x-price :amount="$item->product_price" /></td>
                                    <td>{{ $item->quantity }}</td>
                                    <td><x-price :amount="$item->total" /></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Subtotal:</th>
                                    <td><x-price :amount="$order->subtotal" /></td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right">Shipping:</th>
                                    <td><x-price :amount="$order->shipping_cost" /></td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right">Total:</th>
                                    <td><x-price :amount="$order->total" /></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('shop') }}" class="btn btn-primary">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Order Success End -->
@endsection