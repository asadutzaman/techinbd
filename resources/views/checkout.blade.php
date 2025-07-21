@extends('layouts.app')

@section('title', 'MultiShop - Checkout')

@section('content')
    <!-- Breadcrumb Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-12">
                <nav class="breadcrumb bg-light mb-30">
                    <a class="breadcrumb-item text-dark" href="{{ route('home') }}">Home</a>
                    <a class="breadcrumb-item text-dark" href="{{ route('shop') }}">Shop</a>
                    <span class="breadcrumb-item active">Checkout</span>
                </nav>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Checkout Start -->
    <div class="container-fluid">
        <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
            @csrf
            <div class="row px-xl-5">
                <div class="col-lg-8">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <h5 class="section-title position-relative text-uppercase mb-3"><span class="bg-secondary pr-3">Billing Address</span></h5>
                    <div class="bg-light p-30 mb-5">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>First Name *</label>
                                <input class="form-control @error('first_name') is-invalid @enderror" type="text" name="first_name" value="{{ old('first_name') }}" placeholder="John" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Last Name *</label>
                                <input class="form-control @error('last_name') is-invalid @enderror" type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Doe" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>E-mail *</label>
                                <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="example@email.com" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Mobile No *</label>
                                <input class="form-control @error('phone') is-invalid @enderror" type="text" name="phone" value="{{ old('phone') }}" placeholder="+123 456 789" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Address Line 1 *</label>
                                <input class="form-control @error('address_line_1') is-invalid @enderror" type="text" name="address_line_1" value="{{ old('address_line_1') }}" placeholder="123 Street" required>
                                @error('address_line_1')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Address Line 2</label>
                                <input class="form-control @error('address_line_2') is-invalid @enderror" type="text" name="address_line_2" value="{{ old('address_line_2') }}" placeholder="Apartment, suite, etc.">
                                @error('address_line_2')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Country *</label>
                                <select class="custom-select @error('country') is-invalid @enderror" name="country" required>
                                    <option value="">Select Country</option>
                                    <option value="United States" {{ old('country') == 'United States' ? 'selected' : '' }}>United States</option>
                                    <option value="Canada" {{ old('country') == 'Canada' ? 'selected' : '' }}>Canada</option>
                                    <option value="United Kingdom" {{ old('country') == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="Australia" {{ old('country') == 'Australia' ? 'selected' : '' }}>Australia</option>
                                    <option value="Germany" {{ old('country') == 'Germany' ? 'selected' : '' }}>Germany</option>
                                    <option value="France" {{ old('country') == 'France' ? 'selected' : '' }}>France</option>
                                    <option value="Other" {{ old('country') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>City *</label>
                                <input class="form-control @error('city') is-invalid @enderror" type="text" name="city" value="{{ old('city') }}" placeholder="New York" required>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>State *</label>
                                <input class="form-control @error('state') is-invalid @enderror" type="text" name="state" value="{{ old('state') }}" placeholder="New York" required>
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>ZIP Code *</label>
                                <input class="form-control @error('zip_code') is-invalid @enderror" type="text" name="zip_code" value="{{ old('zip_code') }}" placeholder="12345" required>
                                @error('zip_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="newaccount" name="create_account" {{ old('create_account') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="newaccount">Create an account</label>
                                </div>
                            </div>
                            <div class="col-md-12 form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="shipto" name="ship_to_different" {{ old('ship_to_different') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="shipto">Ship to different address</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h5 class="section-title position-relative text-uppercase mb-3"><span class="bg-secondary pr-3">Order Total</span></h5>
                    <div class="bg-light p-30 mb-5">
                        <div class="border-bottom">
                            <h6 class="mb-3">Products</h6>
                            @foreach($cartItems as $item)
                            <div class="d-flex justify-content-between">
                                <p>{{ $item->product->name }} 
                                    @if($item->size) <small>(Size: {{ $item->size }})</small> @endif
                                    @if($item->color) <small>(Color: {{ $item->color }})</small> @endif
                                    X {{ $item->quantity }}
                                </p>
                                <p>${{ number_format($item->total, 2) }}</p>
                            </div>
                            @endforeach
                        </div>
                        <div class="border-bottom pt-3 pb-2">
                            <div class="d-flex justify-content-between mb-3">
                                <h6>Subtotal</h6>
                                <h6>${{ number_format($subtotal, 2) }}</h6>
                            </div>
                            <div class="d-flex justify-content-between">
                                <h6 class="font-weight-medium">Shipping</h6>
                                <h6 class="font-weight-medium">${{ number_format($shipping, 2) }}</h6>
                            </div>
                        </div>
                        <div class="pt-2">
                            <div class="d-flex justify-content-between mt-2">
                                <h5>Total</h5>
                                <h5>${{ number_format($total, 2) }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="mb-5">
                        <h5 class="section-title position-relative text-uppercase mb-3"><span class="bg-secondary pr-3">Payment</span></h5>
                        <div class="bg-light p-30">
                            <div class="form-group">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" name="payment_method" id="paypal" value="paypal" {{ old('payment_method') == 'paypal' ? 'checked' : '' }} required>
                                    <label class="custom-control-label" for="paypal">PayPal</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" name="payment_method" id="directcheck" value="directcheck" {{ old('payment_method') == 'directcheck' ? 'checked' : '' }} required>
                                    <label class="custom-control-label" for="directcheck">Direct Check</label>
                                </div>
                            </div>
                            <div class="form-group mb-4">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" name="payment_method" id="banktransfer" value="banktransfer" {{ old('payment_method') == 'banktransfer' ? 'checked' : '' }} required>
                                    <label class="custom-control-label" for="banktransfer">Bank Transfer</label>
                                </div>
                            </div>
                            @error('payment_method')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            <button type="submit" class="btn btn-block btn-primary font-weight-bold py-3">Place Order</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- Checkout End -->
@endsection