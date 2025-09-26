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
                <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" onsubmit="console.log('Form submitted'); return true;">
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
                                <!-- <div class="form-group">
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
                                </div> -->
                                {{-- In your products/create.blade.php and products/edit.blade.php --}}
                                    <div class="form-group">
                                        <label for="category_id">Category *</label>
                                        <select name="category_id" id="category_id" class="form-control" required 
                                                onchange="loadCategoryAttributes(this.value)">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
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
                                <div id="attributes-container">
                                    <div class="alert alert-info">Please select a category to see attributes</div>
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
        function loadCategoryAttributes(categoryId) {
            const container = document.getElementById('attributes-container');
            console.log('Loading attributes for category:', categoryId);
            
            if (!categoryId) {
                container.innerHTML = '<div class="alert alert-info">Please select a category to see attributes</div>';
                return;
            }

            // Show loading state
            container.innerHTML = `
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading attributes...</span>
                    </div>
                    <p class="mt-2">Loading attributes...</p>
                </div>
            `;

            // Use the correct route URL
            fetch(`/admin/categories/${categoryId}/attributes`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to load attributes');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Attributes loaded:', data);
                    if (data.success && data.attributes && data.attributes.length > 0) {
                        renderAttributes(data.attributes);
                    } else {
                        container.innerHTML = '<div class="alert alert-info">No attributes found for this category</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Error loading attributes. Please try again.
                        </div>
                    `;
                });
        }

        function renderAttributes(attributes) {
            const container = document.getElementById('attributes-container');
            let html = '<h5>Product Attributes</h5>';
            
            attributes.forEach(attr => {
                html += `
                    <div class="form-group attribute-field" data-attribute-id="${attr.id}">
                        <label for="attribute_${attr.id}">
                            ${attr.name} ${attr.required ? '<span class="text-danger">*</span>' : ''}
                        </label>
                        ${renderAttributeField(attr)}
                        ${attr.required ? '<small class="form-text text-muted">This field is required</small>' : ''}
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        function renderAttributeField(attribute) {
            
            // Check if attribute has values - try different property names
            const hasValues = (attribute.active_values && attribute.active_values.length > 0) ||
                             (attribute.activeValues && attribute.activeValues.length > 0) ||
                             (attribute.values && attribute.values.length > 0);
            
            // Get the actual values array (prioritize active_values since that's what the API returns)
            const values = attribute.active_values || attribute.activeValues || attribute.values || [];
            
            switch(attribute.type) {
                case 'select':
                    if (!hasValues) {
                        return '<div class="alert alert-warning">No options available for this attribute</div>';
                    }
                    return `
                        <select name="attributes[${attribute.id}]" id="attribute_${attribute.id}" 
                                class="form-control" ${attribute.required ? 'required' : ''}>
                            <option value="">Select ${attribute.name}</option>
                            ${values.map(value => `
                                <option value="${value.value}">${value.display_value || value.value}</option>
                            `).join('')}
                        </select>
                    `;
                
                case 'checkbox':
                    return `
                        <div class="form-check">
                            <input type="checkbox" name="attributes[${attribute.id}]" 
                                id="attribute_${attribute.id}" value="1" class="form-check-input"
                                ${attribute.required ? 'required' : ''}>
                            <label class="form-check-label" for="attribute_${attribute.id}">Yes</label>
                        </div>
                    `;
                
                case 'radio':
                    if (!hasValues) {
                        return '<div class="alert alert-warning">No options available for this attribute</div>';
                    }
                    return values.map(value => `
                        <div class="form-check form-check-inline">
                            <input type="radio" name="attributes[${attribute.id}]" 
                                id="attribute_${attribute.id}_${value.id}" 
                                value="${value.value}" class="form-check-input"
                                ${attribute.required ? 'required' : ''}>
                            <label class="form-check-label" for="attribute_${attribute.id}_${value.id}">
                                ${value.display_value || value.value}
                            </label>
                        </div>
                    `).join('');
                
                case 'text':
                default:
                    return `
                        <input type="text" name="attributes[${attribute.id}]" id="attribute_${attribute.id}" 
                            class="form-control" ${attribute.required ? 'required' : ''} 
                            placeholder="Enter ${attribute.name}">
                    `;
            }
        }

        // Load attributes on page load if category is already selected
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category_id');
            if (categorySelect && categorySelect.value) {
                // Small delay to ensure DOM is fully loaded
                setTimeout(() => {
                    loadCategoryAttributes(categorySelect.value);
                }, 100);
            }
        });
    </script>
@endpush
