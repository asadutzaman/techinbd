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
                                    <label for="category_id">Category</label>
                                    <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="brand_id">Brand</label>
                                    <select class="form-control @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id">
                                        <option value="">Select Brand (Optional)</option>
                                        @foreach(\App\Models\Brand::where('status', true)->orderBy('name')->get() as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Attributes will be loaded here dynamically -->
                                <div id="attributes-container" style="display: none;">
                                    <h6 class="text-primary">Product Attributes</h6>
                                    <div id="attributes-fields"></div>
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="status" name="status" {{ old('status', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="status">Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="featured" name="featured" {{ old('featured') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="featured">Featured Product</label>
                                    </div>
                                </div>
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
    
    // Load attributes when category changes
    $('#category_id').on('change', function() {
        const categoryId = $(this).val();
        const attributesContainer = $('#attributes-container');
        const attributesFields = $('#attributes-fields');
        
        if (!categoryId) {
            attributesContainer.hide();
            attributesFields.empty();
            return;
        }
        
        // Show loading
        attributesFields.html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading attributes...</div>');
        attributesContainer.show();
        
        // Fetch attributes for this category
        console.log('Fetching attributes for category ID:', categoryId);
        $.get('{{ route("admin.products.attributes-by-category") }}', { category_id: categoryId })
            .done(function(attributes) {
                console.log('Received attributes:', attributes);
                attributesFields.empty();
                
                if (attributes.length === 0) {
                    attributesContainer.hide();
                    return;
                }
                
                attributes.forEach(function(attribute) {
                    let fieldHtml = '<div class="form-group">';
                    fieldHtml += '<label for="attribute_' + attribute.id + '">' + attribute.name;
                    if (attribute.required) {
                        fieldHtml += ' <span class="text-danger">*</span>';
                    }
                    fieldHtml += '</label>';
                    
                    if (attribute.type === 'select' && attribute.active_attribute_values.length > 0) {
                        fieldHtml += '<select class="form-control" id="attribute_' + attribute.id + '" name="attributes[' + attribute.id + ']"';
                        if (attribute.required) fieldHtml += ' required';
                        fieldHtml += '>';
                        fieldHtml += '<option value="">Select ' + attribute.name + '</option>';
                        attribute.active_attribute_values.forEach(function(value) {
                            fieldHtml += '<option value="' + value.value + '">' + (value.display_value || value.value) + '</option>';
                        });
                        fieldHtml += '</select>';
                    } else if (attribute.type === 'boolean') {
                        fieldHtml += '<select class="form-control" id="attribute_' + attribute.id + '" name="attributes[' + attribute.id + ']"';
                        if (attribute.required) fieldHtml += ' required';
                        fieldHtml += '>';
                        fieldHtml += '<option value="">Select ' + attribute.name + '</option>';
                        fieldHtml += '<option value="1">Yes</option>';
                        fieldHtml += '<option value="0">No</option>';
                        fieldHtml += '</select>';
                    } else if (attribute.type === 'textarea') {
                        fieldHtml += '<textarea class="form-control" id="attribute_' + attribute.id + '" name="attributes[' + attribute.id + ']" rows="3"';
                        if (attribute.required) fieldHtml += ' required';
                        fieldHtml += ' placeholder="Enter ' + attribute.name + '"></textarea>';
                    } else if (attribute.type === 'number') {
                        fieldHtml += '<input type="number" class="form-control" id="attribute_' + attribute.id + '" name="attributes[' + attribute.id + ']"';
                        if (attribute.required) fieldHtml += ' required';
                        fieldHtml += ' placeholder="Enter ' + attribute.name + '">';
                    } else {
                        fieldHtml += '<input type="text" class="form-control" id="attribute_' + attribute.id + '" name="attributes[' + attribute.id + ']"';
                        if (attribute.required) fieldHtml += ' required';
                        fieldHtml += ' placeholder="Enter ' + attribute.name + '">';
                    }
                    
                    fieldHtml += '</div>';
                    attributesFields.append(fieldHtml);
                });
            })
            .fail(function() {
                attributesFields.html('<div class="text-danger">Error loading attributes</div>');
            });
    });
});
</script>
@endpush
