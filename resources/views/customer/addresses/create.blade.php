@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="{{ route('home') }}">Home</a>
                <a class="breadcrumb-item text-dark" href="{{ route('customer.dashboard') }}">My Account</a>
                <a class="breadcrumb-item text-dark" href="{{ route('customer.addresses.index') }}">Addresses</a>
                <span class="breadcrumb-item active">Add Address</span>
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
                <span class="bg-secondary pr-3">Add New Address</span>
            </h5>
            
            <div class="bg-light p-30">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.addresses.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label>Address Type *</label>
                            <select class="form-control" name="type" required>
                                <option value="shipping" {{ old('type') == 'shipping' ? 'selected' : '' }}>Shipping Address</option>
                                <option value="billing" {{ old('type') == 'billing' ? 'selected' : '' }}>Billing Address</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>First Name *</label>
                            <input class="form-control" type="text" name="first_name" value="{{ old('first_name') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Last Name *</label>
                            <input class="form-control" type="text" name="last_name" value="{{ old('last_name') }}" required>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Company</label>
                            <input class="form-control" type="text" name="company" value="{{ old('company') }}">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Address Line 1 *</label>
                            <input class="form-control" type="text" name="address_line_1" value="{{ old('address_line_1') }}" required>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Address Line 2</label>
                            <input class="form-control" type="text" name="address_line_2" value="{{ old('address_line_2') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>City *</label>
                            <input class="form-control" type="text" name="city" value="{{ old('city') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>State *</label>
                            <input class="form-control" type="text" name="state" value="{{ old('state') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Postal Code *</label>
                            <input class="form-control" type="text" name="postal_code" value="{{ old('postal_code') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Country *</label>
                            <input class="form-control" type="text" name="country" value="{{ old('country', 'United States') }}" required>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Phone Number</label>
                            <input class="form-control" type="text" name="phone" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-12 form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_default" name="is_default" {{ old('is_default') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_default">Set as default address</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button class="btn btn-primary py-2 px-4 mr-2" type="submit">Save Address</button>
                            <a href="{{ route('customer.addresses.index') }}" class="btn btn-secondary py-2 px-4">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection