@extends('admin.layouts.app')

@section('title', 'Add Product - MultiShop Admin')
@section('page-title', 'Add Product')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">Add Product</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Product Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Product Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Enter product name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select class="form-control @error('category') is-invalid @enderror" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="men" {{ old('category') == 'men' ? 'selected' : '' }}>Men's Fashion</option>
                                        <option value="women" {{ old('category') == 'women' ? 'selected' : '' }}>Women's Fashion</option>
                                        <option value="kids" {{ old('category') == 'kids' ? 'selected' : '' }}>Kids Fashion</option>
                                        <option value="electronics" {{ old('category') == 'electronics' ? 'selected' : '' }}>Electronics</option>
                                        <option value="sports" {{ old('category') == 'sports' ? 'selected' : '' }}>Sports</option>
                                        <option value="accessories" {{ old('category') == 'accessories' ? 'selected' : '' }}>Accessories</option>
                                        <option value="shoes" {{ old('category') == 'shoes' ? 'selected' : '' }}>Shoes</option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="price">Price ($)</label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" placeholder="0.00" step="0.01" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sale_price">Sale Price ($)</label>
                                    <input type="number" class="form-control @error('sale_price') is-invalid @enderror" id="sale_price" name="sale_price" value="{{ old('sale_price') }}" placeholder="0.00" step="0.01">
                                    @error('sale_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="stock">Stock Quantity</label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock') }}" placeholder="0" required>
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Enter product description">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="image">Product Image</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                                    <label class="custom-file-label" for="image">Choose file</label>
                                </div>
                            </div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Supported formats: JPEG, PNG, JPG, GIF. Max size: 2MB</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="status" name="status" {{ old('status', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="status">Active</label>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Create Product</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>
@endpush