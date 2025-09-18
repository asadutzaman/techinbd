@extends('admin.layouts.app')

@section('title', 'Attributes Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Attributes Management</h3>
                    <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i>Add New Attribute
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Values Count</th>
                                    <th>Required</th>
                                    <th>Filterable</th>
                                    <th>Status</th>
                                    <th>Sort Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attributes as $attribute)
                                <tr>
                                    <td>{{ $attribute->id }}</td>
                                    <td>{{ $attribute->name }}</td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $attribute->category->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($attribute->type) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $attribute->values_count }}</span>
                                    </td>
                                    <td>
                                        @if($attribute->required)
                                            <span class="badge badge-danger">Required</span>
                                        @else
                                            <span class="badge badge-light">Optional</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attribute->filterable)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attribute->status)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $attribute->sort_order }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.attributes.show', $attribute->id) }}" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.attributes.values', $attribute->id) }}" 
                                               class="btn btn-sm btn-success" title="Manage Values">
                                                <i class="fas fa-list"></i>
                                            </a>
                                            <a href="{{ route('admin.attributes.edit', $attribute->id) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.attributes.destroy', $attribute->id) }}" 
                                                  method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this attribute?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No attributes found</h5>
                                        <p class="text-muted">Create your first attribute to get started.</p>
                                        <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus mr-1"></i>Add New Attribute
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($attributes->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $attributes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection