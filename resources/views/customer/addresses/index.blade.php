@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="{{ route('home') }}">Home</a>
                <a class="breadcrumb-item text-dark" href="{{ route('customer.dashboard') }}">My Account</a>
                <span class="breadcrumb-item active">Addresses</span>
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="section-title position-relative text-uppercase mb-0">
                    <span class="bg-secondary pr-3">My Addresses</span>
                </h5>
                <a href="{{ route('customer.addresses.create') }}" class="btn btn-primary">Add New Address</a>
            </div>
            
            @if($addresses->count() > 0)
                <div class="row">
                    @foreach($addresses as $address)
                    <div class="col-md-6 mb-4">
                        <div class="bg-light p-30 h-100">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h6 class="mb-0">
                                    {{ ucfirst($address->type) }} Address
                                    @if($address->is_default)
                                        <span class="badge badge-primary ml-2">Default</span>
                                    @endif
                                </h6>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('customer.addresses.edit', $address) }}">Edit</a>
                                        @if(!$address->is_default)
                                            <a class="dropdown-item" href="{{ route('customer.addresses.set-default', $address) }}">Set as Default</a>
                                        @endif
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-danger" href="#" onclick="deleteAddress({{ $address->id }})">Delete</a>
                                    </div>
                                </div>
                            </div>
                            
                            <p class="mb-2"><strong>{{ $address->full_name }}</strong></p>
                            @if($address->company)
                                <p class="mb-2">{{ $address->company }}</p>
                            @endif
                            <p class="mb-2">{{ $address->address_line_1 }}</p>
                            @if($address->address_line_2)
                                <p class="mb-2">{{ $address->address_line_2 }}</p>
                            @endif
                            <p class="mb-2">{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}</p>
                            <p class="mb-2">{{ $address->country }}</p>
                            @if($address->phone)
                                <p class="mb-0"><strong>Phone:</strong> {{ $address->phone }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="bg-light p-30 text-center">
                    <h6>No addresses found</h6>
                    <p class="mb-3">You haven't added any addresses yet.</p>
                    <a href="{{ route('customer.addresses.create') }}" class="btn btn-primary">Add Your First Address</a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
function deleteAddress(addressId) {
    if (confirm('Are you sure you want to delete this address?')) {
        const form = document.getElementById('delete-form');
        form.action = `/customer/addresses/${addressId}`;
        form.submit();
    }
}
</script>
@endpush
@endsection