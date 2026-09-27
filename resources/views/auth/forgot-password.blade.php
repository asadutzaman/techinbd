@extends('layouts.app')

@section('title', 'Reset your password | ' . config('shop.name'))
@section('main_class', 'is-flush')

@section('content')
<nav class="sf-crumbs" aria-label="Breadcrumb">
    <div class="sf-container">
        <ol>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('login') }}">Sign in</a></li>
            <li aria-current="page">Forgot password</li>
        </ol>
    </div>
</nav>

<div class="sf-container sf-auth">
    <div class="sf-auth-card">
        <h1 class="sf-auth-title">Forgot your password?</h1>
        <p class="sf-auth-lead">Enter the email you signed up with and we'll send you a link to set a new one.</p>

        @if(session('status'))
            <p class="sf-alert is-success" role="status">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="sf-field">
                <label class="sf-field-label" for="forgot-email">Email</label>
                <input class="sf-input" type="email" id="forgot-email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                       @error('email') aria-invalid="true" aria-describedby="forgot-email-error" @enderror>
                @error('email')
                    <p class="sf-field-error" id="forgot-email-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="sf-submit">Email me a reset link</button>
        </form>

        <p class="sf-auth-alt">Remembered it? <a href="{{ route('login') }}">Sign in</a></p>
    </div>
</div>
@endsection
