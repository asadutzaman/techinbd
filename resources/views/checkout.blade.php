@extends('layouts.app')

@section('title', 'Checkout | ' . config('shop.name'))

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
                            {{-- Signed-in customers start with their saved details ($prefill, CheckoutController) --}}
                            <div class="col-md-6 form-group">
                                <label for="checkout-first-name">First Name *</label>
                                <input class="form-control @error('first_name') is-invalid @enderror" type="text" id="checkout-first-name" name="first_name" value="{{ old('first_name', $prefill['first_name']) }}" placeholder="Rahim" autocomplete="given-name" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="checkout-last-name">Last Name *</label>
                                <input class="form-control @error('last_name') is-invalid @enderror" type="text" id="checkout-last-name" name="last_name" value="{{ old('last_name', $prefill['last_name']) }}" placeholder="Uddin" autocomplete="family-name" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="checkout-email">E-mail *</label>
                                <input class="form-control @error('email') is-invalid @enderror" type="email" id="checkout-email" name="email" value="{{ old('email', $prefill['email']) }}" placeholder="you@example.com" autocomplete="email" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="checkout-phone">Mobile No *</label>
                                <input class="form-control @error('phone') is-invalid @enderror" type="tel" id="checkout-phone" name="phone" value="{{ old('phone', $prefill['phone']) }}" placeholder="01XXXXXXXXX" autocomplete="tel" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="checkout-address-1">Address Line 1 *</label>
                                <input class="form-control @error('address_line_1') is-invalid @enderror" type="text" id="checkout-address-1" name="address_line_1" value="{{ old('address_line_1', $prefill['address_line_1']) }}" placeholder="House 12, Road 5" autocomplete="address-line1" required>
                                @error('address_line_1')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="checkout-address-2">Address Line 2</label>
                                <input class="form-control @error('address_line_2') is-invalid @enderror" type="text" id="checkout-address-2" name="address_line_2" value="{{ old('address_line_2', $prefill['address_line_2']) }}" placeholder="Area, flat or landmark" autocomplete="address-line2">
                                @error('address_line_2')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="checkout-country">Country *</label>
                                @php($country = old('country', $prefill['country']))
                                {{-- The shop delivers within Bangladesh; a saved address in another country keeps its own --}}
                                <select class="custom-select @error('country') is-invalid @enderror" id="checkout-country" name="country" autocomplete="country-name" required>
                                    <option value="Bangladesh" @selected($country === 'Bangladesh')>Bangladesh</option>
                                    @if(filled($country) && $country !== 'Bangladesh')
                                        <option value="{{ $country }}" selected>{{ $country }}</option>
                                    @endif
                                </select>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="checkout-city">City *</label>
                                <input class="form-control @error('city') is-invalid @enderror" type="text" id="checkout-city" name="city" value="{{ old('city', $prefill['city']) }}" placeholder="Dhaka" autocomplete="address-level2" required>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="checkout-state">District *</label>
                                <input class="form-control @error('state') is-invalid @enderror" type="text" id="checkout-state" name="state" value="{{ old('state', $prefill['state']) }}" placeholder="Dhaka" autocomplete="address-level1" required>
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="checkout-zip">Postcode *</label>
                                <input class="form-control @error('zip_code') is-invalid @enderror" type="text" id="checkout-zip" name="zip_code" value="{{ old('zip_code', $prefill['zip_code']) }}" placeholder="1205" autocomplete="postal-code" required>
                                @error('zip_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                    @if($item->variant?->name) <small>({{ $item->variant->name }})</small> @endif
                                    @if($item->size) <small>(Size: {{ $item->size }})</small> @endif
                                    @if($item->color) <small>(Color: {{ $item->color }})</small> @endif
                                    X {{ $item->quantity }}
                                </p>
                                <p><x-price :amount="$item->total" /></p>
                            </div>
                            @endforeach
                        </div>
                        <div class="border-bottom pt-3 pb-2">
                            <div class="d-flex justify-content-between mb-3">
                                <h6>Subtotal</h6>
                                <h6><x-price :amount="$subtotal" /></h6>
                            </div>
                            <div class="d-flex justify-content-between">
                                <h6 class="font-weight-medium">Shipping</h6>
                                <h6 class="font-weight-medium"><x-price :amount="$shipping" /></h6>
                            </div>
                        </div>
                        <div class="pt-2">
                            <div class="d-flex justify-content-between mt-2">
                                <h5>Total</h5>
                                <h5><x-price :amount="$total" /></h5>
                            </div>
                        </div>
                    </div>
                    <div class="mb-5">
                        <h5 class="section-title position-relative text-uppercase mb-3"><span class="bg-secondary pr-3">Payment</span></h5>
                        <div class="bg-light p-30">
                            @foreach(\App\Models\Order::PAYMENT_METHODS as $method => $label)
                                <div class="form-group {{ $loop->last ? 'mb-4' : '' }}">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="payment_method" id="payment-{{ $method }}" value="{{ $method }}"
                                               {{ old('payment_method', 'cod') === $method ? 'checked' : '' }} required>
                                        <label class="custom-control-label" for="payment-{{ $method }}">{{ $label }}</label>
                                    </div>
                                </div>
                            @endforeach
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