@extends('admin.layouts.app')

@section('title', 'Attributes - MultiShop Admin')
@section('page-title', 'Product Attributes')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Attributes</li>
@endsection

@push('styles')
<style>
    .filters-card {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .attribute-type {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Filters -->
            <div class="filters-card">
                <form method="GET" action="{{ route('admin.attributes.index') }}" class="row align-items-end">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Attribute name...">
                    </div>
                    <div class="col-md-2">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-control" id="category" name="category">
                            <option value="">All Categories</option>
                            <option value="global" {{ request('category') === 'global' ? 'selected' : '' }}>Global Attributes</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-control" id="type" name="type">
                            <option value="">All Types</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="per_page" class="form-label">Per Page</label>
                        <select class="form-control" id="per_page" name="per_page">
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Attributes List</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Attribute
                        </a>
                    </div>
                </div>
                
                <div class="card-body table-responsive p-0">
                    @if($attributes->count() > 0)
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Unit</th>
                                    <th>Properties</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attributes as $attribute)
                                    <tr>
                                        <td>
                                            <strong>{{ $attribute->name }}</strong>
                                            <br><small class="text-muted">{{ $attribute->slug }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info attribute-type">
                                                {{ ucfirst($attribute->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($attribute->category)
                                                {{ $attribute->category->name }}
                                            @else
                                                <span class="badge badge-secondary">Global</span>
                                            @endif
                                        </td>
                                        <td>{{ $attribute->unit ?: '-' }}</td>
                                        <td>
                                            @if($attribute->required)
                                                <span class="badge badge-danger">Required</span>
                                            @endif
                                            @if($attribute->filterable)
                                                <span class="badge badge-success">Filterable</span>
                                            @endif
                                        </td>
                                        <td>{{ $attribute->sort_order }}</td>
                                        <td>
                                            <span class="badge {{ $attribute->status ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $attribute->status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.attributes.show', $attribute->id) }}" 
                                                   class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.attributes.edit', $attribute->id) }}" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if(in_array($attribute->type, ['select', 'color']))
                                                    <a href="{{ route('admin.attributes.values', $attribute->id) }}" 
                                                       class="btn btn-success btn-sm" title="Manage Values">
                                                        <i class="fas fa-list"></i>
                                                    </a>
                                                @endif
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        onclick="deleteAttribute({{ $attribute->id }})" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No attributes found</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'category', 'type']))
                                    Try adjusting your filters or 
                                    <a href="{{ route('admin.attributes.index') }}">clear all filters</a>
                                @else
                                    <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create your first attribute
                                    </a>
                                @endif
                            </p>
                        </div>
                    @endif
                </div>

                @if($attributes->hasPages())
                    <div class="card-footer">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    Showing {{ $attributes->firstItem() }} to {{ $attributes->lastItem() }} 
                                    of {{ $attributes->total() }} results
                                </small>
                            </div>
                            <div class="col-md-6">
                                {{ $attributes->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this attribute? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function deleteAttribute(attributeId) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/attributes/${attributeId}`;
        $('#deleteModal').modal('show');
    }

    // Auto-submit form on filter change
    document.querySelectorAll('#category, #type, #per_page').forEach(element => {
        element.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endpush