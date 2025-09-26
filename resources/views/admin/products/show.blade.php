@extends('admin.layouts.app')

@section('title', 'Product Details - MultiShop Admin')
@section('page-title', 'Product Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">{{ $product->name }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Product Information</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Product
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($product->image)
                                <img src="{{ asset('img/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h4>{{ $product->name }}</h4>
                            <p class="text-muted">{{ $product->description }}</p>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>Category:</strong> 
                                    @if($product->category)
                                        <span class="badge badge-secondary">{{ $product->category->name }}</span>
                                    @else
                                        <span class="text-muted">No category</span>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <strong>Brand:</strong> 
                                    @if($product->brand)
                                        <span class="badge badge-info">{{ $product->brand->name }}</span>
                                    @else
                                        <span class="text-muted">No brand</span>
                                    @endif
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>Price:</strong>
                                    @if($product->sale_price)
                                        <span class="text-success font-weight-bold">${{ number_format($product->sale_price, 2) }}</span>
                                        <br><small class="text-muted"><del>${{ number_format($product->price, 2) }}</del></small>
                                    @else
                                        <span class="font-weight-bold">${{ number_format($product->price, 2) }}</span>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <strong>Stock:</strong>
                                    @if($product->stock > 10)
                                        <span class="badge badge-success">{{ $product->stock }} in stock</span>
                                    @elseif($product->stock > 0)
                                        <span class="badge badge-warning">{{ $product->stock }} in stock</span>
                                    @else
                                        <span class="badge badge-danger">Out of Stock</span>
                                    @endif
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>Status:</strong>
                                    @if($product->status)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <strong>Featured:</strong>
                                    @if($product->featured)
                                        <span class="badge badge-warning"><i class="fas fa-star"></i> Featured</span>
                                    @else
                                        <span class="badge badge-light">Regular</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Product Attributes</h3>
                    <div>
                        @if($product->category)
                            <small class="text-muted">Category: {{ $product->category->name }}</small>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($product->productAttributes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
                                <tbody>
                                    @foreach($product->productAttributes as $productAttribute)
                                    <tr>
                                        <td class="font-weight-bold" style="width: 40%;">{{ $productAttribute->attribute->name }}:</td>
                                        <td>
                                            @if($productAttribute->attribute->type === 'boolean')
                                                @if($productAttribute->value == '1' || strtolower($productAttribute->value) === 'yes' || strtolower($productAttribute->value) === 'true')
                                                    <span class="badge badge-success">Yes</span>
                                                @else
                                                    <span class="badge badge-secondary">No</span>
                                                @endif
                                            @elseif($productAttribute->attribute->type === 'select')
                                                <span class="badge badge-info">{{ $productAttribute->value }}</span>
                                            @elseif($productAttribute->attribute->type === 'number')
                                                <code>{{ $productAttribute->value }}</code>
                                            @else
                                                {{ $productAttribute->value }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                {{ $product->productAttributes->count() }} attribute(s) defined for this product
                            </small>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-tags fa-2x text-muted mb-3"></i>
                            <h6 class="text-muted">No Attributes Defined</h6>
                            <p class="text-muted mb-3">This product doesn't have any custom attributes yet.</p>
                            @if($product->category)
                                <p class="text-muted">
                                    <small>
                                        <i class="fas fa-lightbulb"></i> 
                                        You can add attributes by editing this product and selecting from the available 
                                        <strong>{{ $product->category->name }}</strong> category attributes.
                                    </small>
                                </p>
                            @endif
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit mr-1"></i>Edit Product
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            @if($product->category && $product->category->attributes->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Available Category Attributes</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <small>Attributes available for <strong>{{ $product->category->name }}</strong> category:</small>
                    </p>
                    
                    @php
                        $usedAttributeIds = $product->productAttributes->pluck('attribute_id')->toArray();
                    @endphp
                    
                    <div class="row">
                        @foreach($product->category->attributes->where('status', true) as $attribute)
                        <div class="col-12 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge badge-{{ in_array($attribute->id, $usedAttributeIds) ? 'success' : 'light' }}">
                                        {{ $attribute->name }}
                                    </span>
                                    <small class="text-muted">({{ ucfirst($attribute->type) }})</small>
                                    @if($attribute->required)
                                        <span class="badge badge-danger badge-sm">Required</span>
                                    @endif
                                </div>
                                <div>
                                    @if(in_array($attribute->id, $usedAttributeIds))
                                        <i class="fas fa-check text-success" title="Used"></i>
                                    @else
                                        <i class="fas fa-minus text-muted" title="Not used"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-3 pt-3 border-top">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit mr-1"></i>Edit Attributes
                        </a>
                        @if($product->category)
                            <a href="{{ route('admin.attributes.index') }}?category={{ $product->category->id }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-cog mr-1"></i>Manage Category Attributes
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Product Meta</h3>
                </div>
                <div class="card-body">
                    <p><strong>Created:</strong> {{ $product->created_at->format('M d, Y \a\t g:i A') }}</p>
                    <p><strong>Updated:</strong> {{ $product->updated_at->format('M d, Y \a\t g:i A') }}</p>
                    <p><strong>ID:</strong> #{{ $product->id }}</p>
                    @if($product->category)
                        <p><strong>Category ID:</strong> {{ $product->category->id }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@push(
'styles')
<style>
.attribute-badge {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

.attribute-used {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.attribute-unused {
    background-color: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.attribute-required {
    position: relative;
}

.attribute-required::after {
    content: "*";
    color: #dc3545;
    font-weight: bold;
    margin-left: 2px;
}

.color-swatch {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 3px;
    border: 1px solid #dee2e6;
    vertical-align: middle;
    margin-right: 5px;
}
</style>
@endpush