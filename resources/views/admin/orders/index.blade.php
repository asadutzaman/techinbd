@extends('admin.layouts.app')

@section('title', 'Orders - MultiShop Admin')
@section('page-title', 'Orders')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Orders</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Orders</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 150px;">
                            <input type="text" name="table_search" class="form-control float-right" placeholder="Search">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->customer_name }}</td>
                                <td>{{ $order->customer_email }}</td>
                                <td>${{ number_format($order->total, 2) }}</td>
                                <td>
                                    @switch($order->status)
                                        @case('pending')
                                            <span class="badge badge-warning">Pending</span>
                                            @break
                                        @case('processing')
                                            <span class="badge badge-info">Processing</span>
                                            @break
                                        @case('shipped')
                                            <span class="badge badge-primary">Shipped</span>
                                            @break
                                        @case('delivered')
                                            <span class="badge badge-success">Delivered</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge badge-danger">Cancelled</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    @switch($order->payment_status)
                                        @case('pending')
                                            <span class="badge badge-warning">Pending</span>
                                            @break
                                        @case('paid')
                                            <span class="badge badge-success">Paid</span>
                                            @break
                                        @case('failed')
                                            <span class="badge badge-danger">Failed</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">
                                            Status
                                        </button>
                                        <div class="dropdown-menu">
                                            <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" name="status" value="pending" class="dropdown-item">Pending</button>
                                                <button type="submit" name="status" value="processing" class="dropdown-item">Processing</button>
                                                <button type="submit" name="status" value="shipped" class="dropdown-item">Shipped</button>
                                                <button type="submit" name="status" value="delivered" class="dropdown-item">Delivered</button>
                                                <button type="submit" name="status" value="cancelled" class="dropdown-item">Cancelled</button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No orders found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                @if($orders->hasPages())
                <div class="card-footer clearfix">
                    {{ $orders->links() }}
                </div>
                @endif
            </div>
            <!-- /.card -->
        </div>
    </div>

    <!-- Order Statistics -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $orders->where('status', 'pending')->count() }}</h3>
                    <p>Pending Orders</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $orders->where('status', 'processing')->count() }}</h3>
                    <p>Processing Orders</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cog"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $orders->where('status', 'shipped')->count() }}</h3>
                    <p>Shipped Orders</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $orders->where('status', 'delivered')->count() }}</h3>
                    <p>Delivered Orders</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check"></i>
                </div>
            </div>
        </div>
    </div>
@endsection