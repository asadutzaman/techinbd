@extends('admin.layouts.app')

@section('title', 'Create Attribute - MultiShop Admin')
@section('page-title', 'Create Attribute')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.attributes.index') }}">Attributes</a></li>
    <li class="breadcrumb-item active">Create Attribute</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Attribute Information</h3>
                </div>
                <form action="{{ route('admin.attributes.store') }}" method="POST">
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
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Attribute Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" 
                                           placeholder="e.g., Screen Size, Storage, Color" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="slug">Slug</label>
                                    <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                           id="slug" name="slug" value="{{ old('slug') }}" 
                                           placeholder="Auto-generated if empty">
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Attribute Type *</label>
                                    <select class="form-control @error('type') is-invalid @enderror" 
                                            id="type" name="type" required onchange="toggleValueManagement()">
                                        <option value="">Select Type</option>
                                        @foreach($types as $value => $label)
                                            <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Select and Color types can have predefined values
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">Category</label>
                                    <select class="form-control @error('category_id') is-invalid @enderror" 
                                            id="category_id" name="category_id">
                                        <option value="">Global Attribute</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Leave empty for global attributes available to all categories
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unit">Unit</label>
                                    <input type="text" class="form-control @error('unit') is-invalid @enderror" 
                                           id="unit" name="unit" value="{{ old('unit') }}" 
                                           placeholder="e.g., GB, inches, MHz">
                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Optional unit of measurement (e.g., GB, inches, MHz)
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order">Sort Order</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" 
                                           min="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Attribute
                        </button>
                        <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Attribute Properties</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" 
                                   id="required" name="required" value="1" 
                                   {{ old('required') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="required">Required</label>
                        </div>
                        <small class="form-text text-muted">
                            Must be filled when creating products
                        </small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" 
                                   id="filterable" name="filterable" value="1" 
                                   {{ old('filterable', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="filterable">Filterable</label>
                        </div>
                        <small class="form-text text-muted">
                            Can be used as filter in shop
                        </small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" 
                                   id="searchable" name="searchable" value="1" 
                                   {{ old('searchable') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="searchable">Searchable</label>
                        </div>
                        <small class="form-text text-muted">
                            Include in search index
                        </small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" 
                                   id="comparable" name="comparable" value="1" 
                                   {{ old('comparable') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="comparable">Comparable</label>
                        </div>
                        <small class="form-text text-muted">
                            Show in product comparison
                        </small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" 
                                   id="status" name="status" value="1" 
                                   {{ old('status', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="status">Active</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Attribute Types</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><strong>Text:</strong> Single line text input</li>
                        <li><strong>Number:</strong> Numeric input with validation</li>
                        <li><strong>Select:</strong> Dropdown with predefined values</li>
                        <li><strong>Boolean:</strong> Yes/No checkbox</li>
                        <li><strong>Textarea:</strong> Multi-line text input</li>
                        <li><strong>Color:</strong> Color picker with hex values</li>
                        <li><strong>Date:</strong> Date picker input</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
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

    function toggleValueManagement() {
        const type = document.getElementById('type').value;
        // This function can be used to show/hide additional options based on type
    }
</script>
@endpush