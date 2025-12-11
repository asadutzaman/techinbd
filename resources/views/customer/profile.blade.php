@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="{{ route('home') }}">Home</a>
                <a class="breadcrumb-item text-dark" href="{{ route('customer.dashboard') }}">My Account</a>
                <span class="breadcrumb-item active">Profile</span>
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
                <span class="bg-secondary pr-3">My Profile</span>
            </h5>
            
            <!-- Profile Information -->
            <div class="bg-light p-30 mb-4">
                <h6 class="mb-3">Profile Information</h6>
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Full Name *</label>
                            <input class="form-control" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Email Address *</label>
                            <input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Phone Number</label>
                            <input class="form-control" type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Date of Birth</label>
                            <input class="form-control" type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Gender</label>
                            <select class="form-control" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Profile Image</label>
                            <input class="form-control" type="file" name="profile_image" accept="image/*">
                            @if($user->profile_image)
                                <small class="text-muted">Current image: {{ basename($user->profile_image) }}</small>
                            @endif
                        </div>
                        <div class="col-md-12">
                            <button class="btn btn-primary py-2 px-4" type="submit">Update Profile</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-light p-30">
                <h6 class="mb-3">Change Password</h6>
                
                <form method="POST" action="{{ route('customer.profile.change-password') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label>Current Password *</label>
                            <input class="form-control" type="password" name="current_password" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>New Password *</label>
                            <input class="form-control" type="password" name="password" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Confirm New Password *</label>
                            <input class="form-control" type="password" name="password_confirmation" required>
                        </div>
                        <div class="col-md-12">
                            <button class="btn btn-primary py-2 px-4" type="submit">Change Password</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection