@extends('admin.layouts.app')

@section('title', 'Edit Product - MultiShop Admin')
@section('page-title', 'Edit Product')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">Edit Product</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Edit Product Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
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
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
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
                                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
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
                                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
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
                                <div id="attributes-container">
                                    <h6 class="text-primary">Product Attributes</h6>
                                    <div id="attributes-fields"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="price">Price ($)</label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" step="0.01" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sale_price">Sale Price ($)</label>
                                    <input type="number" class="form-control @error('sale_price') is-invalid @enderror" id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" step="0.01">
                                    @error('sale_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="stock">Stock Quantity</label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" required>
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Current Image</label>
                            <div class="mb-2">
                                @if($product->image)
                                    <img src="{{ asset('img/' . $product->image) }}" alt="Current Product Image" style="width: 100px; height: 100px; object-fit: cover;" class="img-thumbnail">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <label for="image">Update Product Image</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                                    <label class="custom-file-label" for="image">Choose file</label>
                                </div>
                            </div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="status" name="status" {{ old('status', $product->status) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="status">Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="featured" name="featured" {{ old('featured', $product->featured) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="featured">Featured Product</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Product</button>
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
    
    // Current attributes from server
    const currentAttributes = @json($currentAttributes ?? []);
    
    // Load attributes when category changes
    function loadAttributes(categoryId, setValues = false) {
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
                    
                    const currentValue = setValues ? (currentAttributes[attribute.id] || '') : '';
                    
                    if (attribute.type === 'select' && attribute.active_attribute_values.length > 0) {
                        fieldHtml += '<select class="form-control" id="attribute_' + attribute.id + '" name="attributes[' + attribute.id + ']"';
                        if (attribute.required) fieldHtml += ' required';
                        fieldHtml += '>';
                        fieldHtml += '<option value="">Select ' + attribute.name + '</option>';
                        attribute.active_attribute_values.forEach(function(value) {
                            const selected = currentValue === value.value ? ' selected' : '';
                            fieldHtml += '<option value="' + value.value + '"' + selected + '>' + (value.display_value || value.value) + '</option>';
                        });
                        fieldHtml += '</select>';
                    } else if (attribute.type === 'boolean') {
                        fieldHtml += '<select class="form-control" id="attribute_' + attribute.id + '" name="attributes[' + attribute.id + ']"';
                        if (attribute.required) fieldHtml += ' required';
                        fieldHtml += '>';
                        fieldHtml += '<option value="">Select ' + attribute.name + '</option>';
                        fieldHtml += '<option value="1"' + (currentValue === '1' ? ' selected' : '') + '>Yes</option>';
                        fieldHtml += '<option value="0"' + (currentValue === '0' ? ' selected' : '') + '>No</option>';
                        fieldHtml += '</select>';
                    } else if (attribute.type === 'textarea') {
                        fieldHtml += '<textarea class="form-control" id="attribute_' + attribute.id + '" name="attributes[' + attribute.id + ']" rows="3"';
                        if (attribute.required) fieldHtml += ' required';
                        fieldHtml += ' placeholder="Enter ' + attribute.name + '">' + currentValue + '</textarea>';
                    } else if (attribute.type === 'number') {
                        fieldHtml += '<input type="number" class="form-control" id="attribute_' + attribute.id + '" name="attributes[' + attribute.id + ']"';
                        if (attribute.required) fieldHtml += ' required';
                        fieldHtml += ' placeholder="Enter ' + attribute.name + '" value="' + currentValue + '">';
                    } else {
                        fieldHtml += '<input type="text" class="form-control" id="attribute_' + attribute.id + '" name="attributes[' + attribute.id + ']"';
                        if (attribute.required) fieldHtml += ' required';
                        fieldHtml += ' placeholder="Enter ' + attribute.name + '" value="' + currentValue + '">';
                    }
                    
                    fieldHtml += '</div>';
                    attributesFields.append(fieldHtml);
                });
            })
            .fail(function() {
                attributesFields.html('<div class="text-danger">Error loading attributes</div>');
            });
    }
    
    // Load attributes on page load if category is selected
    const initialCategoryId = $('#category_id').val();
    if (initialCategoryId) {
        loadAttributes(initialCategoryId, true);
    }
    
    // Load attributes when category changes
    $('#category_id').on('change', function() {
        loadAttributes($(this).val(), false);
    });
});
</script>
@endpush