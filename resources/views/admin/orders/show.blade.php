@extends('admin.layouts.app')

@section('title', 'Order Details - MultiShop Admin')
@section('page-title', 'Order Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
    <li class="breadcrumb-item active">Order #{{ $order->order_number }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <!-- Order Items -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Order Items</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Size</th>
                                <th>Color</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderItems as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->product && $item->product->image)
                                            <img src="{{ asset('img/' . $item->product->image) }}" alt="{{ $item->product_name }}" class="img-thumbnail mr-2" style="width: 50px; height: 50px;">
                                        @else
                                            <img src="{{ asset('img/product-1.jpg') }}" alt="{{ $item->product_name }}" class="img-thumbnail mr-2" style="width: 50px; height: 50px;">
                                        @endif
                                        <span>{{ $item->product_name }}</span>
                                    </div>
                                </td>
                                <td>${{ number_format($item->product_price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->size ?? 'N/A' }}</td>
                                <td>{{ $item->color ?? 'N/A' }}</td>
                                <td>${{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Subtotal:</th>
                                <th>${{ number_format($order->subtotal, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-right">Shipping:</th>
                                <th>${{ number_format($order->shipping_cost, 2) }}</th>
                            </tr>
                            <tr class="bg-light">
                                <th colspan="5" class="text-right">Total:</th>
                                <th>${{ number_format($order->total, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Shipping Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Billing Address</h5>
                            <address>
                                <strong>{{ $order->customer_name }}</strong><br>
                                {{ $order->billing_address }}<br>
                                <strong>Phone:</strong> {{ $order->customer_phone }}<br>
                                <strong>Email:</strong> {{ $order->customer_email }}
                            </address>
                        </div>
                        <div class="col-md-6">
                            <h5>Shipping Address</h5>
                            <address>
                                @if($order->shipping_address)
                                    {{ $order->shipping_address }}
                                @else
                                    <em>Same as billing address</em>
                                @endif
                            </address>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Order Status -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Order Status</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="status">Order Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </form>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Payment Information</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.updatePayment', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <input type="text" class="form-control" value="{{ ucfirst($order->payment_method) }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="payment_status">Payment Status</label>
                            <select name="payment_status" id="payment_status" class="form-control">
                                <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="failed" {{ $order->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Update Payment</button>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Order Summary</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-6">Order Number:</dt>
                        <dd class="col-sm-6">{{ $order->order_number }}</dd>
                        
                        <dt class="col-sm-6">Order Date:</dt>
                        <dd class="col-sm-6">{{ $order->created_at->format('M d, Y H:i') }}</dd>
                        
                        <dt class="col-sm-6">Items Count:</dt>
                        <dd class="col-sm-6">{{ $order->orderItems->sum('quantity') }} items</dd>
                        
                        <dt class="col-sm-6">Order Total:</dt>
                        <dd class="col-sm-6"><strong>${{ number_format($order->total, 2) }}</strong></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection