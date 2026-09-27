@extends('layouts.app')

@section('title', 'Sign in | ' . config('shop.name'))
@section('main_class', 'is-flush')

@section('content')
<nav class="sf-crumbs" aria-label="Breadcrumb">
    <div class="sf-container">
        <ol>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li aria-current="page">Sign in</li>
        </ol>
    </div>
</nav>

<div class="sf-container sf-auth">
    <div class="sf-auth-card">
        <h1 class="sf-auth-title">Sign in</h1>
        <p class="sf-auth-lead">Welcome back. Sign in to follow your orders and check out faster.</p>

        @if(session('status'))
            <p class="sf-alert is-success" role="status">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="sf-field">
                <label class="sf-field-label" for="login-email">Email</label>
                <input class="sf-input" type="email" id="login-email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                       @error('email') aria-invalid="true" aria-describedby="login-email-error" @enderror>
                @error('email')
                    <p class="sf-field-error" id="login-email-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sf-field">
                <div class="sf-field-head">
                    <label class="sf-field-label" for="login-password">Password</label>
                    <a class="sf-field-link" href="{{ route('password.request') }}">Forgot password?</a>
                </div>
                <div class="sf-password">
                    <input class="sf-input" type="password" id="login-password" name="password" autocomplete="current-password" required
                           @error('password') aria-invalid="true" aria-describedby="login-password-error" @enderror>
                    <button type="button" class="sf-password-toggle" data-password-toggle aria-controls="login-password" aria-label="Show password">Show</button>
                </div>
                @error('password')
                    <p class="sf-field-error" id="login-password-error">{{ $message }}</p>
                @enderror
            </div>

            <label class="sf-check">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                Keep me signed in
            </label>

            <button type="submit" class="sf-submit">Sign in</button>
        </form>

        <p class="sf-auth-alt">New to {{ config('shop.name') }}? <a href="{{ route('register') }}">Create an account</a></p>
    </div>
</div>
@endsection
