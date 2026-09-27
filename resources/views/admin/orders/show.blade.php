@extends('admin.layouts.app')

@php
    $tz = config('shop.timezone');
    $when = fn ($time) => $time?->timezone($tz)->format('d M Y, g:i A');
    $badge = ['pending' => 'warning', 'processing' => 'info', 'shipped' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger'];
    $steps = $order->nextSteps();

    // The progress line: placed, then each workflow status with the time it was first reached
    $reached = $order->statusChanges->groupBy('to_status')->map->first();
    $track = ['pending' => 'Placed', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
    $position = array_search($order->status, array_keys($track), true);
@endphp

@section('title', $order->order_number . ' | ' . config('shop.name') . ' Admin')
@section('page-title')
    Order {{ $order->order_number }}
    <span class="badge badge-{{ $badge[$order->status] ?? 'secondary' }} align-middle ml-2" style="font-size: 14px;">{{ $order->status_label }}</span>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
    <li class="breadcrumb-item active">{{ $order->order_number }}</li>
@endsection

@push('styles')
<style>
    .order-steps {
        display: flex;
        margin: 0 0 8px;
        padding: 0;
        list-style: none;
    }

    .order-steps li {
        position: relative;
        flex: 1;
        text-align: center;
        font-size: 13px;
        color: #6c757d;
    }

    /* The line between steps */
    .order-steps li + li::before {
        content: "";
        position: absolute;
        top: 15px;
        right: 50%;
        width: 100%;
        height: 3px;
        background: #dee2e6;
    }

    /* The line into a step turns green once that step is reached */
    .order-steps li + li.is-done::before {
        background: #28a745;
    }

    .order-step-dot {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        margin-bottom: 6px;
        border: 3px solid #dee2e6;
        border-radius: 50%;
        background: #FFFFFF;
        font-size: 13px;
        font-weight: 700;
    }

    .order-steps li.is-done .order-step-dot {
        border-color: #28a745;
        background: #28a745;
        color: #FFFFFF;
    }

    .order-steps li.is-current .order-step-label {
        font-weight: 700;
        color: #212529;
    }

    .order-step-time {
        display: block;
        font-size: 11px;
    }

    .order-item-image {
        width: 48px;
        height: 48px;
        object-fit: contain;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        background: #FFFFFF;
    }

    .order-history {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .order-history li {
        position: relative;
        padding: 0 0 16px 22px;
        border-left: 2px solid #e9ecef;
        margin-left: 6px;
    }

    .order-history li:last-child {
        padding-bottom: 0;
        border-left-color: transparent;
    }

    .order-history li::before {
        content: "";
        position: absolute;
        top: 3px;
        left: -7px;
        width: 12px;
        height: 12px;
        border: 2px solid #007bff;
        border-radius: 50%;
        background: #FFFFFF;
    }

    .order-history-time {
        font-size: 12px;
        color: #6c757d;
    }

    .order-email-error {
        max-width: 360px;
        font-size: 12px;
        white-space: normal;
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <!-- Progress and the next step -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-route mr-1"></i> Progress</h3>
                    <div class="card-tools text-muted small pt-1">Placed {{ $when($order->created_at) }}</div>
                </div>
                <div class="card-body">
                    @if($order->status === 'cancelled')
                        @php($cancelled = $reached->get('cancelled'))
                        <div class="alert alert-danger mb-3">
                            <i class="fas fa-ban mr-1"></i> Cancelled{{ $cancelled ? ' ' . $when($cancelled->created_at) . ($cancelled->user ? ' by ' . $cancelled->user->name : '') : '' }}.
                            @if($cancelled?->note) <em>“{{ $cancelled->note }}”</em> @endif
                        </div>
                    @else
                        <ol class="order-steps">
                            @foreach($track as $value => $label)
                                @php($index = $loop->index)
                                <li class="{{ $index <= $position ? 'is-done' : '' }} {{ $index === $position ? 'is-current' : '' }}">
                                    <span class="order-step-dot">@if($index <= $position)<i class="fas fa-check" aria-hidden="true"></i>@else{{ $loop->iteration }}@endif</span>
                                    <span class="order-step-label d-block">{{ $label }}</span>
                                    <span class="order-step-time">
                                        {{ $value === 'pending' ? $order->created_at->timezone($tz)->format('d M, g:i A') : ($reached->get($value)?->created_at->timezone($tz)->format('d M, g:i A')) }}
                                    </span>
                                </li>
                            @endforeach
                        </ol>
                    @endif

                    @if($steps)
                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="border-top pt-3 mt-3">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="step-note">Note for the history <span class="text-muted font-weight-normal">(optional; the customer doesn't see it)</span></label>
                                <input type="text" id="step-note" name="note" class="form-control" maxlength="500" placeholder="e.g. Courier: Pathao, tracking 12345">
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="step-notify" name="notify_customer" value="1" checked>
                                <label class="custom-control-label" for="step-notify">Email the customer ({{ $order->customer_email }})</label>
                            </div>
                            @if(array_key_exists('delivered', $steps) && $order->payment_method === 'cod' && $order->payment_status !== 'paid')
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="step-paid" name="mark_paid" value="1" checked>
                                    <label class="custom-control-label" for="step-paid">Cash collected: mark the payment as paid</label>
                                </div>
                            @endif
                            <div class="mt-3">
                                @foreach($steps as $to => $label)
                                    @if($to === 'cancelled')
                                        <button type="submit" name="status" value="cancelled" class="btn btn-outline-danger float-right"
                                                data-confirm="Cancel {{ $order->order_number }}? The customer is emailed if the box is ticked.">
                                            <i class="fas fa-ban mr-1"></i>{{ $label }}
                                        </button>
                                    @else
                                        <button type="submit" name="status" value="{{ $to }}" class="btn btn-primary">
                                            <i class="fas fa-arrow-right mr-1"></i>{{ $label }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </form>
                    @else
                        <p class="text-muted mb-0 border-top pt-3 mt-3">
                            {{ $order->status === 'delivered' ? 'Delivered: nothing left to do.' : 'This order is closed.' }}
                            To correct its status, use <em>Change status manually</em>.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Items -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-box-open mr-1"></i> Items</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th colspan="2">Product</th>
                                <th class="text-right">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderItems as $item)
                                <tr>
                                    <td style="width: 60px;">
                                        <img class="order-item-image" src="{{ $item->product?->main_image_url ?? asset('img/product-1.jpg') }}" alt="">
                                    </td>
                                    <td class="align-middle">
                                        @if($item->product)
                                            <a href="{{ route('admin.products.show', $item->product_id) }}">{{ $item->product_name }}</a>
                                        @else
                                            {{ $item->product_name }} <span class="badge badge-secondary">deleted</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-right"><x-price :amount="$item->product_price" /></td>
                                    <td class="align-middle text-center">{{ $item->quantity }}</td>
                                    <td class="align-middle text-right"><x-price :amount="$item->total" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right">Subtotal</td>
                                <td class="text-right"><x-price :amount="$order->subtotal" /></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right">Delivery</td>
                                <td class="text-right"><x-price :amount="$order->shipping_cost" /></td>
                            </tr>
                            <tr class="bg-light">
                                <th colspan="4" class="text-right">Total</th>
                                <th class="text-right"><x-price :amount="$order->total" /></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Emails -->
            <div class="card" id="emails">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-envelope mr-1"></i> Emails to the customer</h3>
                    <div class="card-tools">
                        <form action="{{ route('admin.orders.resendEmail', $order->id) }}" method="POST" class="form-inline">
                            @csrf
                            <label class="sr-only" for="resend-kind">Email to send</label>
                            <select id="resend-kind" name="kind" class="form-control form-control-sm mr-1">
                                <option value="placed">Order confirmation</option>
                                @if(App\Mail\OrderStatusChanged::covers($order->status))
                                    <option value="status" selected>Status: {{ $order->status_label }}</option>
                                @endif
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-paper-plane mr-1"></i>Send again</button>
                        </form>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    @if($order->emails->isEmpty())
                        <p class="text-muted p-3 mb-0">No emails logged for this order yet{{ $order->created_at->lt(now()->subDay()) ? ' (orders placed before email logging began have none)' : '' }}.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-3">When</th>
                                    <th>Email</th>
                                    <th>To</th>
                                    <th>Result</th>
                                    <th>Sent by</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->emails as $email)
                                    <tr>
                                        <td class="pl-3 text-nowrap">{{ $when($email->created_at) }}</td>
                                        <td>
                                            {{ $email->label }}
                                            <div class="small text-muted">{{ $email->subject }}</div>
                                        </td>
                                        <td>{{ $email->recipient }}</td>
                                        <td>
                                            @if($email->sent)
                                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Sent</span>
                                            @else
                                                <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Failed</span>
                                                <div class="order-email-error text-danger">{{ Str::limit($email->error, 200) }}</div>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">{{ $email->user?->name ?? 'Shop (automatic)' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <!-- History -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history mr-1"></i> History</h3>
                </div>
                <div class="card-body">
                    <ul class="order-history">
                        <li>
                            <strong>Order placed</strong> by {{ $order->customer_name }} ({{ $order->user_id ? 'customer account' : 'guest' }})
                            <div class="order-history-time">{{ $when($order->created_at) }}</div>
                        </li>
                        @foreach($order->statusChanges as $change)
                            <li>
                                <strong>{{ App\Models\Order::STATUSES[$change->from_status] ?? ucfirst((string) $change->from_status) }} → {{ App\Models\Order::STATUSES[$change->to_status] ?? ucfirst($change->to_status) }}</strong>
                                by {{ $change->user?->name ?? 'the system' }}
                                @if($change->note)
                                    <div>“{{ $change->note }}”</div>
                                @endif
                                <div class="order-history-time">{{ $when($change->created_at) }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Customer -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user mr-1"></i> Customer</h3>
                </div>
                <div class="card-body">
                    <p class="font-weight-bold mb-2">{{ $order->customer_name }}</p>
                    <p class="mb-1"><i class="fas fa-envelope text-muted mr-2"></i><a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a></p>
                    <p class="mb-3"><i class="fas fa-phone text-muted mr-2"></i><a href="tel:{{ $order->customer_phone }}">{{ $order->customer_phone }}</a></p>
                    <p class="small text-muted mb-0">
                        @if($order->user)
                            Account since {{ $order->user->created_at->timezone($tz)->format('M Y') }} · {{ $order->user->orders_count }} {{ Str::plural('order', $order->user->orders_count) }}
                        @else
                            Guest checkout (no account)
                        @endif
                    </p>
                    <hr>
                    <h6 class="font-weight-bold">Delivery address</h6>
                    <address class="mb-0">{{ $order->shipping_address ?: $order->billing_address }}</address>
                </div>
            </div>

            <!-- Payment -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-money-bill-wave mr-1"></i> Payment</h3>
                </div>
                <div class="card-body">
                    <p class="mb-1">{{ $order->payment_method_label }} · <strong><x-price :amount="$order->total" /></strong></p>
                    <p class="mb-3">
                        <span class="badge badge-{{ ['paid' => 'success', 'failed' => 'danger'][$order->payment_status] ?? 'secondary' }}">{{ ucfirst($order->payment_status) }}</span>
                    </p>
                    <form action="{{ route('admin.orders.updatePayment', $order->id) }}" method="POST" class="form-inline">
                        @csrf
                        @method('PUT')
                        <label class="sr-only" for="payment_status">Payment status</label>
                        <select name="payment_status" id="payment_status" class="form-control form-control-sm mr-2">
                            @foreach(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed'] as $value => $label)
                                <option value="{{ $value }}" @selected($order->payment_status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-success">Update payment</button>
                    </form>
                </div>
            </div>

            <!-- Manual correction -->
            <div class="card collapsed-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-wrench mr-1"></i> Change status manually</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" aria-label="Show"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="small text-muted">For corrections: reopening a cancelled order, or moving one back. The workflow buttons above cover the usual steps.</p>
                    <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="manual-status">Status</label>
                            <select name="status" id="manual-status" class="form-control">
                                @foreach(App\Models\Order::STATUSES as $value => $label)
                                    <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="manual-note">Why <span class="text-muted font-weight-normal">(for the history)</span></label>
                            <input type="text" id="manual-note" name="note" class="form-control" maxlength="500">
                        </div>
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="manual-notify" name="notify_customer" value="1">
                            <label class="custom-control-label" for="manual-notify">Email the customer (not for pending)</label>
                        </div>
                        <button type="submit" class="btn btn-secondary">Save status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Buttons that need a second thought (Cancel order)
    document.querySelectorAll('[data-confirm]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            if (!window.confirm(button.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
</script>
@endpush
