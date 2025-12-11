@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="{{ route('home') }}">Home</a>
                <a class="breadcrumb-item text-dark" href="{{ route('customer.dashboard') }}">My Account</a>
                <span class="breadcrumb-item active">Orders</span>
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
                <span class="bg-secondary pr-3">Order History</span>
            </h5>
            
            <!-- Filters -->
            <div class="bg-light p-30 mb-4">
                <form method="GET" action="{{ route('customer.orders.index') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Search by order number..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary mr-2">Filter</button>
                            <a href="{{ route('customer.orders.index') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
            
            @if($orders->count() > 0)
                <div class="bg-light p-30">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center mb-0">
                            <thead class="bg-secondary text-dark">
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle">
                                @foreach($orders as $order)
                                <tr>
                                    <td class="align-middle">
                                        <a href="{{ route('customer.orders.show', $order) }}" class="text-primary">
                                            #{{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="align-middle">{{ $order->created_at->format('M d, Y') }}</td>
                                    <td class="align-middle">
                                        <span class="badge badge-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'info') }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle">{{ $order->orderItems->count() }} item(s)</td>
                                    <td class="align-middle">${{ number_format($order->total_amount, 2) }}</td>
                                    <td class="align-middle">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a>
                                            @if(in_array($order->status, ['delivered', 'completed']))
                                                <a href="{{ route('customer.orders.reorder', $order) }}" class="btn btn-sm btn-outline-success">Reorder</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $orders->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="bg-light p-30 text-center">
                    <i class="fa fa-shopping-bag fa-3x text-muted mb-3"></i>
                    <h6>No orders found</h6>
                    <p class="mb-3">You haven't placed any orders yet.</p>
                    <a href="{{ route('shop') }}" class="btn btn-primary">Start Shopping</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection