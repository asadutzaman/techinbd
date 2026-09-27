@extends('admin.layouts.app')

@section('title', 'Orders | ' . config('shop.name') . ' Admin')
@section('page-title', 'Orders')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Orders</li>
@endsection

@php
    $tabs = ['' => 'All'] + App\Models\Order::STATUSES;
    $badge = ['pending' => 'warning', 'processing' => 'info', 'shipped' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger'];
    $tz = config('shop.timezone');
@endphp

@push('styles')
<style>
    .order-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .order-toolbar .nav-link {
        padding: .35rem .8rem;
    }

    .order-search {
        flex: 0 1 340px;
    }

    .order-row-meta {
        font-size: 12px;
        color: #6c757d;
    }

    .order-actions form {
        display: inline;
    }
</style>
@endpush

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <div class="order-toolbar">
                <ul class="nav nav-pills">
                    @foreach($tabs as $value => $label)
                        <li class="nav-item">
                            <a class="nav-link {{ (string) $status === (string) $value && ! $failedEmails ? 'active' : '' }}"
                               href="{{ route('admin.orders.index', array_filter(['status' => $value, 'q' => $search])) }}">
                                {{ $label }}
                                @if($statusCounts->has($value))
                                    <span class="badge {{ (string) $status === (string) $value && ! $failedEmails ? 'badge-light' : 'badge-secondary' }} ml-1">{{ $statusCounts[$value] }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                    @if($failedEmailCount)
                        <li class="nav-item">
                            <a class="nav-link {{ $failedEmails ? 'active bg-danger' : 'text-danger' }}" href="{{ route('admin.orders.index', ['failed_emails' => 1]) }}">
                                <i class="fas fa-exclamation-circle mr-1"></i>Email failed <span class="badge badge-danger ml-1">{{ $failedEmailCount }}</span>
                            </a>
                        </li>
                    @endif
                </ul>

                <form class="order-search" method="GET" action="{{ route('admin.orders.index') }}" role="search">
                    @if($status)
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif
                    <div class="input-group input-group-sm">
                        <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Order number, name, email or phone" aria-label="Search orders">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($search !== '')
            <div class="px-3 pt-3 text-muted">
                {{ $orders->total() }} {{ Str::plural('order', $orders->total()) }} matching “{{ $search }}” ·
                <a href="{{ route('admin.orders.index', array_filter(['status' => $status])) }}">clear search</a>
            </div>
        @endif

        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap mb-0">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Placed</th>
                        <th>Customer</th>
                        <th class="text-right">Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-right">Next step</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php($next = collect($order->nextSteps())->except('cancelled'))
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="font-weight-bold">{{ $order->order_number }}</a>
                                <div class="order-row-meta">{{ $order->order_items_count }} {{ Str::plural('item', $order->order_items_count) }}</div>
                            </td>
                            <td>
                                {{ $order->created_at->timezone($tz)->format('d M Y') }}
                                <div class="order-row-meta">{{ $order->created_at->timezone($tz)->format('g:i A') }}</div>
                            </td>
                            <td>
                                {{ $order->customer_name }}
                                <div class="order-row-meta">
                                    {{ $order->customer_email }}
                                    @if($order->last_email_failed)
                                        <span class="text-danger ml-1" title="The last email to this customer failed; open the order to resend it">
                                            <i class="fas fa-exclamation-circle"></i> email failed
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right font-weight-bold"><x-price :amount="$order->total" /></td>
                            <td>
                                {{ $order->payment_method_label }}
                                <div><span class="badge badge-{{ ['paid' => 'success', 'failed' => 'danger'][$order->payment_status] ?? 'secondary' }}">{{ ucfirst($order->payment_status) }}</span></div>
                            </td>
                            <td><span class="badge badge-{{ $badge[$order->status] ?? 'secondary' }}">{{ $order->status_label }}</span></td>
                            <td class="text-right order-actions">
                                @foreach($next as $to => $label)
                                    <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="{{ $to }}">
                                        <input type="hidden" name="notify_customer" value="1">
                                        <input type="hidden" name="from" value="list">
                                        <button type="submit" class="btn btn-sm btn-primary" title="Marks it {{ App\Models\Order::STATUSES[$to] }} and emails the customer">{{ $label }}</button>
                                    </form>
                                @endforeach
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                @if($search !== '' || $status || $failedEmails)
                                    No orders match. <a href="{{ route('admin.orders.index') }}">See all orders</a>
                                @else
                                    No orders yet. They'll appear here as customers check out.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="card-footer clearfix">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection
