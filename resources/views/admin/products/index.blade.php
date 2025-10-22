@extends('admin.layouts.app')

@section('title', 'Products - MultiShop Admin')
@section('page-title', 'Products (Optimized)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Products</li>
@endsection

@push('styles')
<style>
    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }
    .stock-badge {
        font-size: 0.75rem;
    }
    .price-display {
        font-weight: 600;
        color: #28a745;
    }
    .filters-card {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Filters -->
            <div class="filters-card">
                <form method="GET" action="{{ route('admin.products.index') }}" class="row align-items-end">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Name, SKU, description...">
                    </div>
                    <div class="col-md-2">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-control" id="category" name="category">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="brand" class="form-label">Brand</label>
                        <select class="form-control" id="brand" name="brand">
                            <option value="">All Brands</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" 
                                        {{ request('brand') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="per_page" class="form-label">Per Page</label>
                        <select class="form-control" id="per_page" name="per_page">
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
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
                    <h3 class="card-title">Products List</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Product
                        </a>
                    </div>
                </div>
                
                <div class="card-body table-responsive p-0">
                    @if($products->count() > 0)
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                    <tr>
                                        <td>
                                            @if($product->mainImage->first())
                                                <img src="{{ asset('storage/' . $product->mainImage->first()->url) }}" 
                                                     alt="{{ $product->name }}" class="product-image">
                                            @else
                                                <img src="{{ asset('img/default-product.jpg') }}" 
                                                     alt="No image" class="product-image">
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $product->name }}</strong>
                                                @if($product->short_description)
                                                    <br><small class="text-muted">{{ Str::limit($product->short_description, 50) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <code>{{ $product->sku ?: 'N/A' }}</code>
                                        </td>
                                        <td>
                                            {{ $product->category->name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $product->brand->name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <span class="price-display">
                                                {{ $product->currency }} {{ number_format($product->base_price, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <span class="badge stock-badge 
                                                    @if($product->stock_status === 'in_stock') badge-success
                                                    @elseif($product->stock_status === 'out_of_stock') badge-danger
                                                    @else badge-warning @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $product->stock_status)) }}
                                                </span>
                                                @if($product->manage_stock)
                                                    <br><small class="text-muted">Qty: {{ $product->total_stock }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $product->status ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $product->status ? 'Active' : 'Draft' }}
                                            </span>
                                            @if($product->featured)
                                                <br><span class="badge badge-warning">Featured</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $product->created_at->format('M d, Y') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.products.show', $product->id) }}" 
                                                   class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.products.edit', $product->id) }}" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        onclick="deleteProduct({{ $product->id }})" title="Delete">
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
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No products found</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'category', 'brand', 'status']))
                                    Try adjusting your filters or 
                                    <a href="{{ route('admin.products.index') }}">clear all filters</a>
                                @else
                                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add your first product
                                    </a>
                                @endif
                            </p>
                        </div>
                    @endif
                </div>

                @if($products->hasPages())
                    <div class="card-footer">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} 
                                    of {{ $products->total() }} results
                                </small>
                            </div>
                            <div class="col-md-6">
                                {{ $products->appends(request()->query())->links() }}
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
                    <p>Are you sure you want to delete this product? This action cannot be undone.</p>
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
    function deleteProduct(productId) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/products/${productId}`;
        $('#deleteModal').modal('show');
    }

    // Auto-submit form on filter change
    document.querySelectorAll('#category, #brand, #status, #per_page').forEach(element => {
        element.addEventListener('change', function() {
            this.form.submit();
        });
    });

    // Clear filters
    function clearFilters() {
        window.location.href = '{{ route("admin.products.index") }}';
    }
</script>
@endpush