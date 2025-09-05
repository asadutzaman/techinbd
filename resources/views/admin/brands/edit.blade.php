@extends('admin.layouts.app')

@section('title', 'Edit Brand')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Edit Brand: {{ $brand->name }}</h3>
                    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Brands
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

                    <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Brand Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="{{ old('name', $brand->name) }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="slug">Slug</label>
                                    <input type="text" class="form-control" id="slug" name="slug" 
                                           value="{{ old('slug', $brand->slug) }}">
                                    <small class="form-text text-muted">URL-friendly version of the name.</small>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="4" placeholder="Brand description...">{{ old('description', $brand->description) }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="website">Website URL</label>
                                    <input type="url" class="form-control" id="website" name="website" 
                                           value="{{ old('website', $brand->website) }}" placeholder="https://example.com">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="logo">Brand Logo</label>
                                    
                                    @if($brand->logo)
                                        <div class="mb-3">
                                            <img src="{{ asset('img/brands/' . $brand->logo) }}" 
                                                 alt="{{ $brand->name }}" class="img-thumbnail" 
                                                 style="max-width: 200px; max-height: 200px;">
                                            <p class="small text-muted mt-1">Current logo</p>
                                        </div>
                                    @endif
                                    
                                    <input type="file" class="form-control-file" id="logo" name="logo" 
                                           accept="image/*">
                                    <small class="form-text text-muted">Upload new logo to replace current one (JPG, PNG, GIF - Max: 2MB)</small>
                                    
                                    <div id="logo-preview" class="mt-3" style="display: none;">
                                        <img id="preview-image" src="" alt="Logo Preview" 
                                             class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                        <p class="small text-muted mt-1">New logo preview</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="status" 
                                               name="status" {{ old('status', $brand->status) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="status">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Update Brand
                            </button>
                            <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary ml-2">
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

@push('scripts')
<script>
$(document).ready(function() {
    // Logo preview
    $('#logo').on('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-image').attr('src', e.target.result);
                $('#logo-preview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#logo-preview').hide();
        }
    });
});
</script>
@endpush