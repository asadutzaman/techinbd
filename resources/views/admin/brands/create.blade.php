@extends('admin.layouts.app')

@section('title', 'Add New Brand')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Add New Brand</h3>
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

                    <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Brand Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="{{ old('name') }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="slug">Slug</label>
                                    <input type="text" class="form-control" id="slug" name="slug" 
                                           value="{{ old('slug') }}" placeholder="Auto-generated if empty">
                                    <small class="form-text text-muted">URL-friendly version of the name. Leave empty to auto-generate.</small>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="4" placeholder="Brand description...">{{ old('description') }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="website">Website URL</label>
                                    <input type="url" class="form-control" id="website" name="website" 
                                           value="{{ old('website') }}" placeholder="https://example.com">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="logo">Brand Logo</label>
                                    <input type="file" class="form-control-file" id="logo" name="logo" 
                                           accept="image/*">
                                    <small class="form-text text-muted">Upload brand logo (JPG, PNG, GIF - Max: 2MB)</small>
                                    
                                    <div id="logo-preview" class="mt-3" style="display: none;">
                                        <img id="preview-image" src="" alt="Logo Preview" 
                                             class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="status" 
                                               name="status" {{ old('status', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="status">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Create Brand
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
    // Auto-generate slug from name
    $('#name').on('input', function() {
        if ($('#slug').val() === '') {
            var slug = $(this).val().toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove special characters
                .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
                .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
            $('#slug').val(slug);
        }
    });
    
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