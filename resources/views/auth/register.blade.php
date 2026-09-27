@extends('layouts.app')

@section('title', 'Create an account | ' . config('shop.name'))
@section('main_class', 'is-flush')

@section('content')
<nav class="sf-crumbs" aria-label="Breadcrumb">
    <div class="sf-container">
        <ol>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li aria-current="page">Create an account</li>
        </ol>
    </div>
</nav>

<div class="sf-container sf-auth">
    <div class="sf-auth-card">
        <h1 class="sf-auth-title">Create your account</h1>
        <p class="sf-auth-lead">Follow your orders, keep a wishlist and check out faster. It takes a minute.</p>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="sf-field">
                <label class="sf-field-label" for="register-name">Full name</label>
                <input class="sf-input" type="text" id="register-name" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus
                       @error('name') aria-invalid="true" aria-describedby="register-name-error" @enderror>
                @error('name')
                    <p class="sf-field-error" id="register-name-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sf-field">
                <label class="sf-field-label" for="register-email">Email</label>
                <input class="sf-input" type="email" id="register-email" name="email" value="{{ old('email') }}" autocomplete="email" required
                       aria-describedby="register-email-hint @error('email') register-email-error @enderror" @error('email') aria-invalid="true" @enderror>
                <p class="sf-field-hint" id="register-email-hint">Order updates go here.</p>
                @error('email')
                    <p class="sf-field-error" id="register-email-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sf-field">
                <label class="sf-field-label" for="register-phone">Mobile number <span class="sf-field-optional">(optional)</span></label>
                <input class="sf-input" type="tel" id="register-phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="01XXXXXXXXX"
                       @error('phone') aria-invalid="true" aria-describedby="register-phone-error" @enderror>
                @error('phone')
                    <p class="sf-field-error" id="register-phone-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sf-field">
                <label class="sf-field-label" for="register-password">Password</label>
                <div class="sf-password">
                    <input class="sf-input" type="password" id="register-password" name="password" autocomplete="new-password" minlength="8" required
                           aria-describedby="register-password-hint @error('password') register-password-error @enderror" @error('password') aria-invalid="true" @enderror>
                    <button type="button" class="sf-password-toggle" data-password-toggle aria-controls="register-password" aria-label="Show password">Show</button>
                </div>
                <p class="sf-field-hint" id="register-password-hint">At least 8 characters.</p>
                @error('password')
                    <p class="sf-field-error" id="register-password-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sf-field">
                <label class="sf-field-label" for="register-password-confirmation">Confirm password</label>
                <input class="sf-input" type="password" id="register-password-confirmation" name="password_confirmation" autocomplete="new-password" minlength="8" required>
            </div>

            <button type="submit" class="sf-submit">Create account</button>
        </form>

        <p class="sf-auth-alt">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
    </div>
</div>
@endsection
