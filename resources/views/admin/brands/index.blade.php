@extends('admin.layouts.app')

@section('title', 'Brands Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Brands Management</h3>
                    <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i>Add New Brand
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
                                    <th>Logo</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Products Count</th>
                                    <th>Website</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($brands as $brand)
                                <tr>
                                    <td>{{ $brand->id }}</td>
                                    <td>
                                        @if($brand->logo)
                                            <img src="{{ asset('img/brands/' . $brand->logo) }}" 
                                                 alt="{{ $brand->name }}" class="img-thumbnail" 
                                                 style="width: 50px; height: 50px; object-fit: contain;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $brand->name }}</td>
                                    <td><code>{{ $brand->slug }}</code></td>
                                    <td>
                                        <span class="badge badge-info">{{ $brand->products_count }}</span>
                                    </td>
                                    <td>
                                        @if($brand->website)
                                            <a href="{{ $brand->website }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($brand->status)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $brand->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.brands.show', $brand->id) }}" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.brands.edit', $brand->id) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.brands.destroy', $brand->id) }}" 
                                                  method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this brand?')">
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
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No brands found</h5>
                                        <p class="text-muted">Create your first brand to get started.</p>
                                        <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus mr-1"></i>Add New Brand
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($brands->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $brands->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection