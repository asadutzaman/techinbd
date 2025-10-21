@extends('admin.layouts.app')

@section('title', 'Edit Product - MultiShop Admin')
@section('page-title', 'Edit Product (Optimized)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">Edit Product</li>
@endsection

@push('styles')
<style>
    .image-preview {
        max-width: 100px;
        max-height: 100px;
        margin: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
        position: relative;
    }
    .image-delete-btn {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 12px;
        cursor: pointer;
    }
    .existing-images {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 15px;
    }
    .variant-row {
        border: 1px solid #e9ecef;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 10px;
        background-color: #f8f9fa;
    }
</style>
@endpush

@section('content')
    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Basic Information -->
            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Basic Information</h3>
                    </div>
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
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Product Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $product->name) }}" 
                                           placeholder="Enter product name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sku">SKU</label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                           id="sku" name="sku" value="{{ old('sku', $product->sku) }}" 
                                           placeholder="Product SKU">
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="slug">URL Slug</label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" name="slug" value="{{ old('slug', $product->slug) }}" 
                                   placeholder="URL-friendly slug">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="short_description">Short Description</label>
                            <textarea class="form-control @error('short_description') is-invalid @enderror" 
                                      id="short_description" name="short_description" rows="2" 
                                      maxlength="512" placeholder="Brief product description">{{ old('short_description', $product->short_description) }}</textarea>
                            @error('short_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Maximum 512 characters</small>
                        </div>

                        <div class="form-group">
                            <label for="description">Full Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="6" 
                                      placeholder="Detailed product description and specifications">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pricing & Inventory -->
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Pricing & Inventory</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="base_price">Base Price *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" class="form-control @error('base_price') is-invalid @enderror" 
                                               id="base_price" name="base_price" value="{{ old('base_price', $product->base_price) }}" 
                                               placeholder="0.00" step="0.01" min="0" required>
                                    </div>
                                    @error('base_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cost_price">Cost Price</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" class="form-control @error('cost_price') is-invalid @enderror" 
                                               id="cost_price" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" 
                                               placeholder="0.00" step="0.01" min="0">
                                    </div>
                                    @error('cost_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="currency">Currency</label>
                                    <select class="form-control @error('currency') is-invalid @enderror" 
                                            id="currency" name="currency">
                                        <option value="BDT" {{ old('currency', $product->currency) == 'BDT' ? 'selected' : '' }}>BDT</option>
                                        <option value="USD" {{ old('currency', $product->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                                        <option value="EUR" {{ old('currency', $product->currency) == 'EUR' ? 'selected' : '' }}>EUR</option>
                                    </select>
                                    @error('currency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="stock_status">Stock Status *</label>
                                    <select class="form-control @error('stock_status') is-invalid @enderror" 
                                            id="stock_status" name="stock_status" required>
                                        <option value="in_stock" {{ old('stock_status', $product->stock_status) == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                        <option value="out_of_stock" {{ old('stock_status', $product->stock_status) == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                        <option value="preorder" {{ old('stock_status', $product->stock_status) == 'preorder' ? 'selected' : '' }}>Pre-order</option>
                                    </select>
                                    @error('stock_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total_stock">Total Stock</label>
                                    <input type="number" class="form-control @error('total_stock') is-invalid @enderror" 
                                           id="total_stock" name="total_stock" value="{{ old('total_stock', $product->total_stock) }}" 
                                           min="0">
                                    @error('total_stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="custom-control custom-switch mt-4">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="manage_stock" name="manage_stock" value="1" 
                                               {{ old('manage_stock', $product->manage_stock) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="manage_stock">Manage Stock</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Images -->
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Product Images</h3>
                    </div>
                    <div class="card-body">
                        @if($product->images->count() > 0)
                            <div class="form-group">
                                <label>Existing Images</label>
                                <div class="existing-images">
                                    @foreach($product->images as $image)
                                        <div class="image-preview" data-image-id="{{ $image->id }}">
                                            <img src="{{ asset('storage/' . $image->url) }}" alt="{{ $image->alt_text }}">
                                            <button type="button" class="image-delete-btn" onclick="deleteImage({{ $image->id }})">
                                                ×
                                            </button>
                                            @if($image->is_main)
                                                <small class="badge badge-primary">Main</small>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="images">Add New Images</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('images.*') is-invalid @enderror" 
                                           id="images" name="images[]" accept="image/*" multiple>
                                    <label class="custom-file-label" for="images">Choose files</label>
                                </div>
                            </div>
                            @error('images.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Supported formats: JPEG, PNG, JPG, GIF, WebP. Max size: 2MB each.
                            </small>
                        </div>
                        <div id="image-preview" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Category & Brand -->
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Category & Brand</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="category_id">Category *</label>
                            <select class="form-control @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required 
                                    onchange="loadCategoryAttributes(this.value)">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="brand_id">Brand</label>
                            <select class="form-control @error('brand_id') is-invalid @enderror" 
                                    id="brand_id" name="brand_id">
                                <option value="">Select Brand (Optional)</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" 
                                            {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Product Attributes -->
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Product Attributes</h3>
                    </div>
                    <div class="card-body">
                        <div id="attributes-container">
                            <div class="alert alert-info">Loading attributes...</div>
                        </div>
                    </div>
                </div>

                <!-- Physical Properties -->
                <div class="card card-dark">
                    <div class="card-header">
                        <h3 class="card-title">Physical Properties</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="weight">Weight (kg)</label>
                            <input type="number" class="form-control @error('weight') is-invalid @enderror" 
                                   id="weight" name="weight" value="{{ old('weight', $product->weight) }}" 
                                   placeholder="0.000" step="0.001" min="0">
                            @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="dimensions">Dimensions</label>
                            <input type="text" class="form-control @error('dimensions') is-invalid @enderror" 
                                   id="dimensions" name="dimensions" value="{{ old('dimensions', $product->dimensions) }}" 
                                   placeholder="e.g., 133x34x7 mm">
                            @error('dimensions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="warranty">Warranty</label>
                            <input type="text" class="form-control @error('warranty') is-invalid @enderror" 
                                   id="warranty" name="warranty" value="{{ old('warranty', $product->warranty) }}" 
                                   placeholder="e.g., 1 Year">
                            @error('warranty')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="manufacturer_part_no">Manufacturer Part No.</label>
                            <input type="text" class="form-control @error('manufacturer_part_no') is-invalid @enderror" 
                                   id="manufacturer_part_no" name="manufacturer_part_no" 
                                   value="{{ old('manufacturer_part_no', $product->manufacturer_part_no) }}" 
                                   placeholder="Part number">
                            @error('manufacturer_part_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="ean_upc">EAN/UPC</label>
                            <input type="text" class="form-control @error('ean_upc') is-invalid @enderror" 
                                   id="ean_upc" name="ean_upc" value="{{ old('ean_upc', $product->ean_upc) }}" 
                                   placeholder="Barcode number">
                            @error('ean_upc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- SEO -->
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">SEO Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="meta_title">Meta Title</label>
                            <input type="text" class="form-control @error('meta_title') is-invalid @enderror" 
                                   id="meta_title" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" 
                                   maxlength="255" placeholder="SEO title">
                            @error('meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="meta_description">Meta Description</label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                      id="meta_description" name="meta_description" rows="3" 
                                      maxlength="512" placeholder="SEO description">{{ old('meta_description', $product->meta_description) }}</textarea>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="meta_keywords">Meta Keywords</label>
                            <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" 
                                   id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $product->meta_keywords) }}" 
                                   placeholder="keyword1, keyword2, keyword3">
                            @error('meta_keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="status">Product Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="1" {{ old('status', $product->status) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $product->status) == 0 ? 'selected' : '' }}>Draft</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> View Product
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        const name = this.value;
        const slug = name.toLowerCase()
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
        document.getElementById('slug').value = slug;
    });

    // Image preview for new images
    document.getElementById('images').addEventListener('change', function() {
        const preview = document.getElementById('image-preview');
        preview.innerHTML = '';
        
        Array.from(this.files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Delete existing image
    function deleteImage(imageId) {
        if (confirm('Are you sure you want to delete this image?')) {
            fetch(`/admin/products/images/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-image-id="${imageId}"]`).remove();
                } else {
                    alert('Failed to delete image');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete image');
            });
        }
    }

    // Load category attributes
    function loadCategoryAttributes(categoryId) {
        const container = document.getElementById('attributes-container');
        
        if (!categoryId) {
            container.innerHTML = '<div class="alert alert-info">Please select a category to see attributes</div>';
            return;
        }

        container.innerHTML = `
            <div class="text-center py-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading attributes...</span>
                </div>
                <p class="mt-2">Loading attributes...</p>
            </div>
        `;

        fetch(`/admin/products/categories/${categoryId}/attributes`)
            .then(response => response.json())
            .then(data => {
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
        const currentAttributes = @json($currentAttributes);
        let html = '';
        
        attributes.forEach(attr => {
            const currentValue = currentAttributes[attr.id] || '';
            html += `
                <div class="form-group attribute-field" data-attribute-id="${attr.id}">
                    <label for="attribute_${attr.id}">
                        ${attr.name} ${attr.required ? '<span class="text-danger">*</span>' : ''}
                        ${attr.unit ? '<small class="text-muted">(' + attr.unit + ')</small>' : ''}
                    </label>
                    ${renderAttributeField(attr, currentValue)}
                    ${attr.required ? '<small class="form-text text-muted">This field is required</small>' : ''}
                </div>
            `;
        });
        
        container.innerHTML = html;
    }

    function renderAttributeField(attribute, currentValue) {
        const hasValues = (attribute.active_values && attribute.active_values.length > 0);
        const values = attribute.active_values || [];
        
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
                            <option value="${value.value}" ${currentValue === value.value ? 'selected' : ''}>
                                ${value.display_value || value.value}
                            </option>
                        `).join('')}
                    </select>
                `;
            
            case 'boolean':
            case 'checkbox':
                return `
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="attributes[${attribute.id}]" 
                            id="attribute_${attribute.id}" value="1" class="custom-control-input"
                            ${currentValue === '1' ? 'checked' : ''} ${attribute.required ? 'required' : ''}>
                        <label class="custom-control-label" for="attribute_${attribute.id}">Yes</label>
                    </div>
                `;
            
            case 'color':
                return `
                    <div class="input-group">
                        <input type="color" name="attributes[${attribute.id}]" id="attribute_${attribute.id}" 
                            class="form-control" value="${currentValue}" ${attribute.required ? 'required' : ''} 
                            style="max-width: 60px;">
                        <input type="text" name="attributes[${attribute.id}]_text" 
                            class="form-control ml-2" value="${currentValue}" placeholder="Color name or hex code">
                    </div>
                `;
            
            case 'number':
                return `
                    <input type="number" name="attributes[${attribute.id}]" id="attribute_${attribute.id}" 
                        class="form-control" value="${currentValue}" ${attribute.required ? 'required' : ''} 
                        placeholder="Enter ${attribute.name}" step="0.01">
                `;
            
            case 'textarea':
                return `
                    <textarea name="attributes[${attribute.id}]" id="attribute_${attribute.id}" 
                        class="form-control" rows="3" ${attribute.required ? 'required' : ''} 
                        placeholder="Enter ${attribute.name}">${currentValue}</textarea>
                `;
            
            case 'text':
            default:
                return `
                    <input type="text" name="attributes[${attribute.id}]" id="attribute_${attribute.id}" 
                        class="form-control" value="${currentValue}" ${attribute.required ? 'required' : ''} 
                        placeholder="Enter ${attribute.name}">
                `;
        }
    }

    // Load attributes on page load
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category_id');
        if (categorySelect && categorySelect.value) {
            setTimeout(() => {
                loadCategoryAttributes(categorySelect.value);
            }, 100);
        }
    });
</script>
@endpush