@extends('admin.layouts.app')

@section('title', 'Dashboard | ' . config('shop.name') . ' Admin')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@php
    $tz = config('shop.timezone');
    $badge = ['pending' => 'warning', 'processing' => 'info', 'shipped' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger'];
    $best = max(array_column($sales, 'total')) ?: 1;
    $fortnight = array_sum(array_column($sales, 'total'));
@endphp

@push('styles')
<style>
    .sales-chart {
        display: flex;
        align-items: flex-end;
        gap: 6px;
        height: 200px;
        padding-top: 8px;
    }

    .sales-bar {
        display: flex;
        flex: 1;
        flex-direction: column;
        justify-content: flex-end;
        align-items: center;
        height: 100%;
        min-width: 0;
    }

    .sales-bar-fill {
        width: 100%;
        min-height: 2px;
        border-radius: 4px 4px 0 0;
        background: #3B82F6;
    }

    .sales-bar:hover .sales-bar-fill {
        background: #1D4ED8;
    }

    .sales-bar-day {
        margin-top: 6px;
        font-size: 11px;
        color: #6c757d;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')
    @if($stats['failed_emails'])
        <div class="callout callout-danger">
            <h5 class="mb-1"><i class="fas fa-exclamation-circle text-danger mr-1"></i> {{ $stats['failed_emails'] }} {{ Str::plural('order', $stats['failed_emails']) }} with a failed customer email</h5>
            <p class="mb-0">The customer hasn't heard about their order. <a href="{{ route('admin.orders.index', ['failed_emails' => 1]) }}">Open them</a> and use <em>Send again</em>, or check <a href="{{ route('admin.settings.index') }}">the mail settings</a>.</p>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending'] }}</h3>
                    <p>{{ Str::plural('Order', $stats['pending']) }} to confirm</p>
                </div>
                <div class="icon"><i class="fas fa-inbox"></i></div>
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="small-box-footer">Confirm them <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['to_ship'] }}</h3>
                    <p>{{ Str::plural('Order', $stats['to_ship']) }} to ship</p>
                </div>
                <div class="icon"><i class="fas fa-shipping-fast"></i></div>
                <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="small-box-footer">See them <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3><x-price :amount="$stats['revenue_month']" /></h3>
                    <p>Sales this month · {{ $stats['orders_month'] }} {{ Str::plural('order', $stats['orders_month']) }}</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
                <a href="{{ route('admin.orders.index') }}" class="small-box-footer">All orders <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['products'] }}</h3>
                    <p>Products on sale · {{ $stats['customers'] }} {{ Str::plural('customer', $stats['customers']) }}</p>
                </div>
                <div class="icon"><i class="fas fa-box"></i></div>
                <a href="{{ route('admin.products.index') }}" class="small-box-footer">Products <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Sales, last 14 days</h3>
                    <div class="card-tools text-muted small pt-1">
                        <x-price :amount="$fortnight" /> · {{ array_sum(array_column($sales, 'orders')) }} orders (cancelled left out)
                    </div>
                </div>
                <div class="card-body">
                    <div class="sales-chart" role="img" aria-label="Daily sales for the last 14 days">
                        @foreach($sales as $day)
                            <div class="sales-bar" title="{{ $day['date']->format('D d M') }}: ৳{{ App\Support\Money::amount($day['total']) }}, {{ $day['orders'] }} {{ Str::plural('order', $day['orders']) }}">
                                <div class="sales-bar-fill" style="height: {{ round($day['total'] / $best * 100, 1) }}%"></div>
                                <span class="sales-bar-day">{{ $day['date']->format($loop->first || $day['date']->day === 1 ? 'd M' : 'd') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-receipt mr-1"></i> Latest orders</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-tool">All orders</a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order->id) }}" class="font-weight-bold">{{ $order->order_number }}</a></td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td class="text-muted">{{ $order->created_at->timezone($tz)->diffForHumans() }}</td>
                                    <td class="text-right"><x-price :amount="$order->total" /></td>
                                    <td class="text-right"><span class="badge badge-{{ $badge[$order->status] ?? 'secondary' }}">{{ $order->status_label }}</span></td>
                                </tr>
                            @empty
                                <tr><td class="text-center text-muted py-4">No orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-bolt mr-1"></i> Quick actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-block"><i class="fas fa-plus mr-1"></i> Add a product</a>
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary btn-block"><i class="fas fa-images mr-1"></i> Home banners</a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-block" target="_blank" rel="noopener"><i class="fas fa-external-link-alt mr-1"></i> View the shop</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cubes mr-1"></i> Running low</h3>
                    <div class="card-tools text-muted small pt-1">{{ $stats['out_of_stock'] }} out of stock</div>
                </div>
                <div class="card-body p-0">
                    @if($lowStock->isEmpty())
                        <p class="text-muted p-3 mb-0">No products with stock tracking are at 3 or fewer.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($lowStock as $product)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('admin.products.show', $product->id) }}" class="text-truncate mr-2">{{ $product->name }}</a>
                                    <span class="badge badge-{{ $product->total_stock > 0 ? 'warning' : 'danger' }}">{{ $product->total_stock }} left</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
