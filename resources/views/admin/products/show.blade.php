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
                                    <span class="badge badge-secondary">{{ ucfirst($product->category) }}</span>
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
            @if($product->productAttributes->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Product Attributes</h3>
                </div>
                <div class="card-body">
                    @foreach($product->productAttributes as $productAttribute)
                        <div class="mb-3">
                            <strong>{{ $productAttribute->attribute->name }}:</strong>
                            <br>
                            <span class="text-muted">{{ $productAttribute->value }}</span>
                        </div>
                    @endforeach
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
                </div>
            </div>
        </div>
    </div>
@endsection