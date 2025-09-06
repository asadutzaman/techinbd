@extends('admin.layouts.app')

@section('title', 'Products - MultiShop Admin')
@section('page-title', 'Products')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Products</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Products</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm mr-3" style="width: 200px;">
                            <span class="text-muted mr-2">
                                Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }}
                            </span>
                            <select class="form-control form-control-sm" onchange="changePerPage(this.value)">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 per page</option>
                                <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 per page</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per page</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                            </select>
                        </div>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Product
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Featured</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td colspan="9"></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <h5>No products found</h5>
                                    <p class="text-muted">Start by adding your first product.</p>
                                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Product
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                @if($products->hasPages())
                <div class="card-footer clearfix">
                    {{ $products->links() }}
                </div>
                @endif
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection

@push('scripts')
<script>
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to first page when changing per_page
    window.location.href = url.toString();
}
</script>
@endpush