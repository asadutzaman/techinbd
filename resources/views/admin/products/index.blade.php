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
                                <td>
                                    <img src="{{ asset('img/' . $product->image) }}" alt="{{ $product->name }}" style="width: 50px; height: 50px; object-fit: cover;" class="img-thumbnail">
                                </td>
                                <td>{{ $product->name }}</td>
                                <td>
                                    <span class="badge badge-secondary">{{ ucfirst($product->category) }}</span>
                                </td>
                                <td>
                                    @if($product->sale_price)
                                        <span class="text-success font-weight-bold">${{ number_format($product->sale_price, 2) }}</span>
                                        <br><small class="text-muted"><del>${{ number_format($product->price, 2) }}</del></small>
                                    @else
                                        <span class="font-weight-bold">${{ number_format($product->price, 2) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->stock > 10)
                                        <span class="badge badge-success">{{ $product->stock }}</span>
                                    @elseif($product->stock > 0)
                                        <span class="badge badge-warning">{{ $product->stock }}</span>
                                    @else
                                        <span class="badge badge-danger">Out of Stock</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->status)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->featured)
                                        <span class="badge badge-warning"><i class="fas fa-star"></i> Featured</span>
                                    @else
                                        <span class="badge badge-light">Regular</span>
                                    @endif
                                </td>
                                <td>{{ $product->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
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