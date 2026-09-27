@extends('admin.layouts.app')

@section('title', 'Settings | ' . config('shop.name') . ' Admin')
@section('page-title', 'Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@php
    $unset = '<span class="text-muted">not set</span>';
    $contact = config('shop.contact');
    $social = config('shop.social');
@endphp

@section('content')
    <div class="callout callout-info">
        <p class="mb-0">
            These settings live in <code>config/shop.php</code> and the server's <code>.env</code> file (<code>SHOP_*</code> and <code>MAIL_*</code> keys).
            Change them there; the site picks them up on the next request. Blank contact and social lines are simply left off the shop.
        </p>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-store mr-1"></i> Store</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tr><th style="width: 38%;">Name</th><td>{{ config('shop.name') }} <code class="small">SHOP_NAME</code></td></tr>
                        <tr><th>Tagline</th><td>{{ config('shop.tagline') }}</td></tr>
                        <tr><th>Description</th><td>{{ config('shop.description') }}</td></tr>
                        <tr><th>Currency</th><td>Bangladeshi taka (৳, BDT)</td></tr>
                        <tr><th>Time zone</th><td>{{ config('shop.timezone') }} <code class="small">SHOP_TIMEZONE</code></td></tr>
                        <tr><th>Promises</th><td>{{ implode(' · ', config('shop.promises')) }}</td></tr>
                        <tr><th>Payment methods</th><td>{{ implode(' · ', config('shop.payment_methods')) }}</td></tr>
                        <tr><th>Public address</th><td><a href="{{ config('app.url') }}" target="_blank" rel="noopener">{{ config('app.url') }}</a> <code class="small">APP_URL</code></td></tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-address-card mr-1"></i> Contact and social</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tr><th style="width: 38%;">Email <code class="small">SHOP_EMAIL</code></th><td>{!! filled($contact['email']) ? e($contact['email']) : $unset !!}</td></tr>
                        <tr><th>Phone <code class="small">SHOP_PHONE</code></th><td>{!! filled($contact['phone']) ? e($contact['phone']) : $unset !!}</td></tr>
                        <tr><th>Address <code class="small">SHOP_ADDRESS</code></th><td>{!! filled($contact['address']) ? e($contact['address']) : $unset !!}</td></tr>
                        @foreach($social as $network => $url)
                            <tr><th>{{ ucfirst($network) }}</th><td>{!! filled($url) ? '<a href="' . e($url) . '" target="_blank" rel="noopener">' . e($url) . '</a>' : $unset !!}</td></tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-envelope mr-1"></i> Email</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tr><th style="width: 38%;">Sending with</th><td>{{ $mailer === 'smtp' ? 'SMTP, ' . $smtpHost : ucfirst($mailer) . ($mailer === 'log' ? ' (written to the log, not sent)' : '') }}</td></tr>
                        <tr><th>From</th><td>{{ $from['name'] }} &lt;{{ $from['address'] }}&gt;</td></tr>
                        <tr><th>Last 30 days</th><td>{{ $emailsSent }} order {{ Str::plural('email', $emailsSent) }} sent @if($emailsFailed) · <span class="text-danger">{{ $emailsFailed }} failed</span> @endif</td></tr>
                        @if($lastFailure)
                            <tr>
                                <th>Last failure</th>
                                <td class="small">
                                    {{ $lastFailure->created_at->timezone(config('shop.timezone'))->format('d M Y, g:i A') }}:
                                    <span class="text-danger">{{ Str::limit($lastFailure->error, 160) }}</span>
                                    (<a href="{{ route('admin.orders.show', $lastFailure->order_id) }}#emails">order</a>)
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
                <div class="card-footer">
                    <form action="{{ route('admin.settings.test-email') }}" method="POST" class="d-flex align-items-center flex-wrap">
                        @csrf
                        <button type="submit" class="btn btn-primary mr-3"><i class="fas fa-paper-plane mr-1"></i> Send a test email</button>
                        <span class="text-muted small">Goes to the shop's own address, {{ $from['address'] }}. Gmail takes a few seconds.</span>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-shield mr-1"></i> Admin accounts</h3>
                </div>
                <div class="card-body">
                    <p>Admin accounts can't reset their password by email: whoever controls an admin's mailbox would get the admin panel. To give access or change an admin's password, run on the server:</p>
                    <pre class="mb-0 bg-light p-2 rounded small">php artisan admin:grant someone@example.com
php artisan admin:grant someone@example.com --revoke
php artisan admin:password someone@example.com</pre>
                </div>
            </div>
        </div>
    </div>
@endsection
