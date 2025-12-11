@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="{{ route('home') }}">Home</a>
                <span class="breadcrumb-item active">My Account</span>
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
                <span class="bg-secondary pr-3">Dashboard</span>
            </h5>
            
            <div class="bg-light p-30 mb-4">
                <h6>Welcome back, {{ $user->name }}!</h6>
                <p class="mb-0">From your account dashboard you can view your recent orders, manage your shipping and billing addresses, and edit your password and account details.</p>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="bg-light p-30 text-center">
                        <h4 class="text-primary">{{ $recentOrders->count() }}</h4>
                        <p class="mb-0">Total Orders</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light p-30 text-center">
                        <h4 class="text-primary">{{ $wishlistCount }}</h4>
                        <p class="mb-0">Wishlist Items</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light p-30 text-center">
                        <h4 class="text-primary">{{ $addressesCount }}</h4>
                        <p class="mb-0">Saved Addresses</p>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            @if($recentOrders->count() > 0)
            <h5 class="section-title position-relative text-uppercase mb-3">
                <span class="bg-secondary pr-3">Recent Orders</span>
            </h5>
            <div class="bg-light p-30">
                <div class="table-responsive">
                    <table class="table table-bordered text-center mb-0">
                        <thead class="bg-secondary text-dark">
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            @foreach($recentOrders as $order)
                            <tr>
                                <td class="align-middle">#{{ $order->order_number }}</td>
                                <td class="align-middle">{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'info') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="align-middle">${{ number_format($order->total_amount, 2) }}</td>
                                <td class="align-middle">
                                    <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('customer.orders.index') }}" class="btn btn-primary">View All Orders</a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection