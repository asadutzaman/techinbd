@extends('admin.layouts.app')

@section('title', 'View Product - MultiShop Admin')
@section('page-title', 'Product Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">{{ $product->name }}</li>
@endsection

@push('styles')
<style>
    .product-image {
        max-width: 200px;
        max-height: 200px;
        object-fit: cover;
        border-radius: 8px;
        margin: 5px;
    }
    .main-image {
        border: 3px solid #007bff;
    }
    .attribute-badge {
        margin: 2px;
    }
    .spec-item {
        border-bottom: 1px solid #eee;
        padding: 8px 0;
    }
    .spec-item:last-child {
        border-bottom: none;
    }
</style>
@endpush

@section('content')
    <div class="row">
        <!-- Product Information -->
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Product Information</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit Product
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>{{ $product->name }}</h4>
                            <p class="text-muted">{{ $product->short_description }}</p>
                            
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>SKU:</strong></td>
                                    <td><code>{{ $product->sku ?: 'N/A' }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Slug:</strong></td>
                                    <td><code>{{ $product->slug }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Brand:</strong></td>
                                    <td>{{ $product->brand->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge {{ $product->status ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $product->status ? 'Active' : 'Draft' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Pricing & Inventory</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Base Price:</strong></td>
                                    <td class="text-success font-weight-bold">{{ $product->currency }} {{ number_format($product->base_price, 2) }}</td>
                                </tr>
                                @if($product->cost_price)
                                <tr>
                                    <td><strong>Cost Price:</strong></td>
                                    <td>{{ $product->currency }} {{ number_format($product->cost_price, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Stock Status:</strong></td>
                                    <td>
                                        <span class="badge 
                                            @if($product->stock_status === 'in_stock') badge-success
                                            @elseif($product->stock_status === 'out_of_stock') badge-danger
                                            @else badge-warning @endif">
                                            {{ ucfirst(str_replace('_', ' ', $product->stock_status)) }}
                                        </span>
                                    </td>
                                </tr>
                                @if($product->manage_stock)
                                <tr>
                                    <td><strong>Total Stock:</strong></td>
                                    <td>{{ $product->total_stock }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($product->description)
                    <div class="mt-4">
                        <h5>Description</h5>
                        <div class="border p-3 rounded">
                            {!! $product->description !!}
                        </div>
                    </div>
                    @endif

                    @if($product->productAttributes->count() > 0)
                    <div class="mt-4">
                        <h5>Product Attributes</h5>
                        <div class="row">
                            @foreach($product->productAttributes as $productAttribute)
                                <div class="col-md-6 mb-3">
                                    <div class="spec-item">
                                        <strong>{{ $productAttribute->attribute->name }}:</strong>
                                        <span class="ml-2">{!! $productAttribute->display_value !!}</span>
                                        @if($productAttribute->attribute->unit)
                                            <small class="text-muted">{{ $productAttribute->attribute->unit }}</small>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($product->variants->count() > 0)
                    <div class="mt-4">
                        <h5>Product Variants</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>SKU</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Default</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->variants as $variant)
                                        <tr>
                                            <td>{{ $variant->name ?: 'Default Variant' }}</td>
                                            <td><code>{{ $variant->sku ?: 'N/A' }}</code></td>
                                            <td>
                                                @if($variant->price)
                                                    {{ $product->currency }} {{ number_format($variant->price, 2) }}
                                                @else
                                                    <span class="text-muted">Uses base price</span>
                                                @endif
                                            </td>
                                            <td>{{ $variant->stock }}</td>
                                            <td>
                                                @if($variant->is_default)
                                                    <span class="badge badge-primary">Default</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($product->images->count() > 0)
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Product Images</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($product->images as $image)
                            <div class="col-md-3 text-center">
                                <img src="{{ asset('storage/' . $image->url) }}" 
                                     alt="{{ $image->alt_text }}" 
                                     class="product-image {{ $image->is_main ? 'main-image' : '' }}">
                                @if($image->is_main)
                                    <br><small class="badge badge-primary">Main Image</small>
                                @endif
                                <br><small class="text-muted">Sort: {{ $image->sort_order }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Physical Properties -->
            @if($product->weight || $product->dimensions || $product->warranty)
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Physical Properties</h3>
                </div>
                <div class="card-body">
                    @if($product->weight)
                        <div class="spec-item">
                            <strong>Weight:</strong> {{ $product->weight }} kg
                        </div>
                    @endif
                    @if($product->dimensions)
                        <div class="spec-item">
                            <strong>Dimensions:</strong> {{ $product->dimensions }}
                        </div>
                    @endif
                    @if($product->warranty)
                        <div class="spec-item">
                            <strong>Warranty:</strong> {{ $product->warranty }}
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Additional Information -->
            @if($product->manufacturer_part_no || $product->ean_upc)
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Additional Information</h3>
                </div>
                <div class="card-body">
                    @if($product->manufacturer_part_no)
                        <div class="spec-item">
                            <strong>Manufacturer Part No:</strong><br>
                            <code>{{ $product->manufacturer_part_no }}</code>
                        </div>
                    @endif
                    @if($product->ean_upc)
                        <div class="spec-item">
                            <strong>EAN/UPC:</strong><br>
                            <code>{{ $product->ean_upc }}</code>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- SEO Information -->
            @if($product->meta_title || $product->meta_description || $product->meta_keywords)
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">SEO Information</h3>
                </div>
                <div class="card-body">
                    @if($product->meta_title)
                        <div class="spec-item">
                            <strong>Meta Title:</strong><br>
                            <small>{{ $product->meta_title }}</small>
                        </div>
                    @endif
                    @if($product->meta_description)
                        <div class="spec-item">
                            <strong>Meta Description:</strong><br>
                            <small>{{ $product->meta_description }}</small>
                        </div>
                    @endif
                    @if($product->meta_keywords)
                        <div class="spec-item">
                            <strong>Meta Keywords:</strong><br>
                            <small>{{ $product->meta_keywords }}</small>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning btn-block">
                        <i class="fas fa-edit"></i> Edit Product
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-list"></i> Back to Products
                    </a>
                    <button type="button" class="btn btn-danger btn-block" onclick="deleteProduct({{ $product->id }})">
                        <i class="fas fa-trash"></i> Delete Product
                    </button>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="card card-dark">
                <div class="card-header">
                    <h3 class="card-title">Timestamps</h3>
                </div>
                <div class="card-body">
                    <div class="spec-item">
                        <strong>Created:</strong><br>
                        <small>{{ $product->created_at->format('M d, Y H:i:s') }}</small>
                    </div>
                    <div class="spec-item">
                        <strong>Updated:</strong><br>
                        <small>{{ $product->updated_at->format('M d, Y H:i:s') }}</small>
                    </div>
                </div>
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
                    <p><strong>Product:</strong> {{ $product->name }}</p>
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
</script>
@endpush