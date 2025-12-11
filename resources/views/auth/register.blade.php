@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="{{ route('home') }}">Home</a>
                <span class="breadcrumb-item active">Register</span>
            </nav>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row px-xl-5 justify-content-center">
        <div class="col-lg-8">
            <h5 class="section-title position-relative text-uppercase mb-3">
                <span class="bg-secondary pr-3">Customer Registration</span>
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

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Full Name *</label>
                            <input class="form-control" type="text" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Email Address *</label>
                            <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Phone Number</label>
                            <input class="form-control" type="text" name="phone" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Date of Birth</label>
                            <input class="form-control" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Gender</label>
                            <select class="form-control" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Password *</label>
                            <input class="form-control" type="password" name="password" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Confirm Password *</label>
                            <input class="form-control" type="password" name="password_confirmation" required>
                        </div>
                        <div class="col-md-12">
                            <button class="btn btn-primary py-2 px-4" type="submit">Register</button>
                        </div>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection