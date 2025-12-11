@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="{{ route('home') }}">Home</a>
                <a class="breadcrumb-item text-dark" href="{{ route('customer.dashboard') }}">My Account</a>
                <a class="breadcrumb-item text-dark" href="{{ route('customer.orders.index') }}">Orders</a>
                <span class="breadcrumb-item active">Order #{{ $order->order_number }}</span>
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
                <span class="bg-secondary pr-3">Order #{{ $order->order_number }}</span>
            </h5>
            
            <!-- Order Info -->
            <div class="bg-light p-30 mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Order Information</h6>
                        <p><strong>Order Date:</strong> {{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
                        <p><strong>Order Status:</strong> 
                            <span class="badge badge-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'info') }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </p>
                        <p><strong>Payment Status:</strong> 
                            <span class="badge badge-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6>Billing Address</h6>
                        <p class="mb-1">{{ $order->billing_first_name }} {{ $order->billing_last_name }}</p>
                        <p class="mb-1">{{ $order->billing_address_1 }}</p>
                        @if($order->billing_address_2)
                            <p class="mb-1">{{ $order->billing_address_2 }}</p>
                        @endif
                        <p class="mb-1">{{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_zip }}</p>
                        <p class="mb-1">{{ $order->billing_country }}</p>
                        @if($order->billing_phone)
                            <p class="mb-0"><strong>Phone:</strong> {{ $order->billing_phone }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-light p-30 mb-4">
                <h6 class="mb-3">Order Items</h6>
                <div class="table-responsive">
                    <table class="table table-bordered text-center mb-0">
                        <thead class="bg-secondary text-dark">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            @foreach($order->orderItems as $item)
                            <tr>
                                <td class="align-middle text-left">
                                    <div class="d-flex align-items-center">
                                        @if($item->product && $item->product->images->count() > 0)
                                            <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" 
                                                 alt="{{ $item->product_name }}" style="width: 50px;">
                                        @else
                                            <img src="{{ asset('img/product-1.jpg') }}" 
                                                 alt="{{ $item->product_name }}" style="width: 50px;">
                                        @endif
                                        <div class="ml-3">
                                            <h6 class="mb-0">{{ $item->product_name }}</h6>
                                            @if($item->product_size || $item->product_color)
                                                <small class="text-muted">
                                                    @if($item->product_size) Size: {{ $item->product_size }} @endif
                                                    @if($item->product_color) Color: {{ $item->product_color }} @endif
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">${{ number_format($item->price, 2) }}</td>
                                <td class="align-middle">{{ $item->quantity }}</td>
                                <td class="align-middle">${{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-light p-30 mb-4">
                <div class="row justify-content-end">
                    <div class="col-md-6">
                        <h6 class="mb-3">Order Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>${{ number_format($order->subtotal_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>${{ number_format($order->shipping_amount, 2) }}</span>
                        </div>
                        @if($order->tax_amount > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span>${{ number_format($order->tax_amount, 2) }}</span>
                        </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong>${{ number_format($order->total_amount, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="text-center">
                @if(in_array($order->status, ['delivered', 'completed']))
                    <a href="{{ route('customer.orders.reorder', $order) }}" class="btn btn-primary mr-2">Reorder</a>
                @endif
                <a href="{{ route('customer.orders.download-invoice', $order) }}" class="btn btn-outline-primary mr-2">Download Invoice</a>
                <a href="{{ route('customer.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
            </div>
        </div>
    </div>
</div>
@endsection