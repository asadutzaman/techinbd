@extends('admin.layouts.app')

@section('title', 'Edit Banner')
@section('page-title', 'Edit Banner')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">{{ $banner->title }}</h3>
            <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary ml-auto">
                <i class="fas fa-arrow-left mr-1"></i>Back to banners
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.banners._form')
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>Save changes
                </button>
                <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary ml-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
