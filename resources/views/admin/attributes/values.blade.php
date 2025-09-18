@extends('admin.layouts.app')

@section('title', 'Manage Attribute Values')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title">Manage Values for: {{ $attribute->name }}</h3>
                        <p class="text-muted mb-0">Category: {{ $attribute->category->name }} | Type: {{ ucfirst($attribute->type) }}</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.attributes.show', $attribute->id) }}" class="btn btn-info">
                            <i class="fas fa-eye mr-1"></i>View Attribute
                        </a>
                        <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>Back to Attributes
                        </a>
                    </div>
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

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Add New Value Form -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-plus mr-1"></i>Add New Value</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.attributes.values.store', $attribute->id) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="value">Value <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('value') is-invalid @enderror" 
                                                   id="value" name="value" value="{{ old('value') }}" required>
                                            @error('value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="display_value">Display Name</label>
                                            <input type="text" class="form-control @error('display_value') is-invalid @enderror" 
                                                   id="display_value" name="display_value" value="{{ old('display_value') }}">
                                            @error('display_value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="sort_order">Sort Order</label>
                                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                                   id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                            @error('sort_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="status" name="status" value="1" 
                                                       {{ old('status', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="status">Active</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-plus mr-1"></i>Add Value
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Existing Values -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-list mr-1"></i>Existing Values ({{ $attribute->values->count() }})</h5>
                        </div>
                        <div class="card-body">
                            @if($attribute->values->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Value</th>
                                                <th>Display Name</th>
                                                <th>Status</th>
                                                <th>Sort Order</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($attribute->values->sortBy('sort_order') as $value)
                                            <tr>
                                                <td>{{ $value->id }}</td>
                                                <td><code>{{ $value->value }}</code></td>
                                                <td>{{ $value->display_value ?: '-' }}</td>
                                                <td>
                                                    @if($value->status)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>{{ $value->sort_order }}</td>
                                                <td>{{ $value->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-warning" 
                                                                data-toggle="modal" data-target="#editModal{{ $value->id }}" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('admin.attributes.values.destroy', [$attribute->id, $value->id]) }}" 
                                                              method="POST" class="d-inline" 
                                                              onsubmit="return confirm('Are you sure you want to delete this value?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editModal{{ $value->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Value</h5>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <form action="{{ route('admin.attributes.values.update', [$attribute->id, $value->id]) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label for="edit_value{{ $value->id }}">Value <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" 
                                                                           id="edit_value{{ $value->id }}" name="value" 
                                                                           value="{{ $value->value }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="edit_display_value{{ $value->id }}">Display Name</label>
                                                                    <input type="text" class="form-control" 
                                                                           id="edit_display_value{{ $value->id }}" name="display_value" 
                                                                           value="{{ $value->display_value }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="edit_sort_order{{ $value->id }}">Sort Order</label>
                                                                    <input type="number" class="form-control" 
                                                                           id="edit_sort_order{{ $value->id }}" name="sort_order" 
                                                                           value="{{ $value->sort_order }}" min="0">
                                                                </div>
                                                                <div class="form-group">
                                                                    <div class="form-check">
                                                                        <input type="checkbox" class="form-check-input" 
                                                                               id="edit_status{{ $value->id }}" name="status" value="1" 
                                                                               {{ $value->status ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="edit_status{{ $value->id }}">Active</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">
                                                                    <i class="fas fa-save mr-1"></i>Update Value
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No values found</h5>
                                    <p class="text-muted">Add the first value for this attribute using the form above.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection