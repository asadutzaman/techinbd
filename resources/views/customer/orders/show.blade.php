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
                            <span class="badge badge-{{ ['delivered' => 'success', 'pending' => 'warning', 'cancelled' => 'danger'][$order->status] ?? 'info' }}">
                                {{ $order->status_label }}
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
                        <p class="mb-1">{{ $order->customer_name }}</p>
                        <p class="mb-1">{{ $order->billing_address }}</p>
                        @if($order->customer_phone)
                            <p class="mb-0"><strong>Phone:</strong> {{ $order->customer_phone }}</p>
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
                                        <img src="{{ $item->product ? $item->product->main_image_url : asset('img/product-1.jpg') }}"
                                             alt="{{ $item->product_name }}" style="width: 50px;">
                                        <div class="ml-3">
                                            <h6 class="mb-0">{{ $item->product_name }}</h6>
                                            @if($item->size || $item->color)
                                                <small class="text-muted">
                                                    @if($item->size) Size: {{ $item->size }} @endif
                                                    @if($item->color) Color: {{ $item->color }} @endif
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle"><x-price :amount="$item->product_price" /></td>
                                <td class="align-middle">{{ $item->quantity }}</td>
                                <td class="align-middle"><x-price :amount="$item->total" /></td>
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
                            <span><x-price :amount="$order->subtotal" /></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span><x-price :amount="$order->shipping_cost" /></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong><x-price :amount="$order->total" /></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="text-center">
                @if(in_array($order->status, ['delivered', 'completed']))
                    <form action="{{ route('customer.orders.reorder', $order) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary mr-2">Reorder</button>
                    </form>
                @endif
                <a href="{{ route('customer.orders.download-invoice', $order) }}" class="btn btn-outline-primary mr-2">Download Invoice</a>
                <a href="{{ route('customer.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
            </div>
        </div>
    </div>
</div>
@endsection