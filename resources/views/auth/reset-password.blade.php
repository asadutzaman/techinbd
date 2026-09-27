@extends('layouts.app')

@section('title', 'Set a new password | ' . config('shop.name'))
@section('main_class', 'is-flush')

@section('content')
<nav class="sf-crumbs" aria-label="Breadcrumb">
    <div class="sf-container">
        <ol>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('login') }}">Sign in</a></li>
            <li aria-current="page">New password</li>
        </ol>
    </div>
</nav>

<div class="sf-container sf-auth">
    <div class="sf-auth-card">
        <h1 class="sf-auth-title">Set a new password</h1>
        <p class="sf-auth-lead">Choose a new password for your account. You'll use it the next time you sign in.</p>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="sf-field">
                <label class="sf-field-label" for="reset-email">Email</label>
                <input class="sf-input" type="email" id="reset-email" name="email" value="{{ old('email', $email) }}" autocomplete="username" required
                       @error('email') aria-invalid="true" aria-describedby="reset-email-error" @enderror>
                @error('email')
                    <p class="sf-field-error" id="reset-email-error">
                        {{ $message }} <a href="{{ route('password.request') }}">Send a new link</a>
                    </p>
                @enderror
            </div>

            <div class="sf-field">
                <label class="sf-field-label" for="reset-password">New password</label>
                <div class="sf-password">
                    <input class="sf-input" type="password" id="reset-password" name="password" autocomplete="new-password" minlength="8" required autofocus
                           aria-describedby="reset-password-hint @error('password') reset-password-error @enderror" @error('password') aria-invalid="true" @enderror>
                    <button type="button" class="sf-password-toggle" data-password-toggle aria-controls="reset-password" aria-label="Show password">Show</button>
                </div>
                <p class="sf-field-hint" id="reset-password-hint">At least 8 characters.</p>
                @error('password')
                    <p class="sf-field-error" id="reset-password-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sf-field">
                <label class="sf-field-label" for="reset-password-confirmation">Confirm new password</label>
                <input class="sf-input" type="password" id="reset-password-confirmation" name="password_confirmation" autocomplete="new-password" minlength="8" required>
            </div>

            <button type="submit" class="sf-submit">Save new password</button>
        </form>
    </div>
</div>
@endsection
