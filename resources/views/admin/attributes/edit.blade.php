@extends('admin.layouts.app')

@section('title', 'Edit Attribute')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Edit Attribute: {{ $attribute->name }}</h3>
                    <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Attributes
                    </a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.attributes.update', $attribute->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">Category <span class="text-danger">*</span></label>
                                    <select class="form-control" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $attribute->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="name">Attribute Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="{{ old('name', $attribute->name) }}" required placeholder="e.g., Resolution, Storage, RAM">
                                </div>

                                <div class="form-group">
                                    <label for="type">Attribute Type <span class="text-danger">*</span></label>
                                    <select class="form-control" id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="text" {{ old('type', $attribute->type) == 'text' ? 'selected' : '' }}>Text</option>
                                        <option value="number" {{ old('type', $attribute->type) == 'number' ? 'selected' : '' }}>Number</option>
                                        <option value="select" {{ old('type', $attribute->type) == 'select' ? 'selected' : '' }}>Select (Dropdown)</option>
                                        <option value="boolean" {{ old('type', $attribute->type) == 'boolean' ? 'selected' : '' }}>Boolean (Yes/No)</option>
                                        <option value="textarea" {{ old('type', $attribute->type) == 'textarea' ? 'selected' : '' }}>Textarea</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="sort_order">Sort Order</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                           value="{{ old('sort_order', $attribute->sort_order) }}" min="0" placeholder="0">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="required" 
                                               name="required" {{ old('required', $attribute->required) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="required">Required</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="filterable" 
                                               name="filterable" {{ old('filterable', $attribute->filterable) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="filterable">Filterable</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="status" 
                                               name="status" {{ old('status', $attribute->status) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="status">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Update Attribute
                            </button>
                            <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary ml-2">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection