@extends('admin.layouts.app')

@section('title', 'Brand Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Brand Details: {{ $brand->name }}</h3>
                    <div>
                        <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit mr-1"></i>Edit Brand
                        </a>
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i>Back to Brands
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($brand->logo)
                                <div class="text-center mb-4">
                                    <img src="{{ asset('img/brands/' . $brand->logo) }}" 
                                         alt="{{ $brand->name }}" class="img-fluid" 
                                         style="max-width: 300px; max-height: 300px;">
                                </div>
                            @else
                                <div class="text-center mb-4">
                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 200px; height: 200px; margin: 0 auto;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                    <p class="text-muted mt-2">No logo uploaded</p>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-8">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="200">Brand Name</th>
                                    <td>{{ $brand->name }}</td>
                                </tr>
                                <tr>
                                    <th>Slug</th>
                                    <td><code>{{ $brand->slug }}</code></td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $brand->description ?: 'No description provided' }}</td>
                                </tr>
                                <tr>
                                    <th>Website</th>
                                    <td>
                                        @if($brand->website)
                                            <a href="{{ $brand->website }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                {{ $brand->website }} <i class="fas fa-external-link-alt ml-1"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">No website provided</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($brand->status)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Products Count</th>
                                    <td>
                                        <span class="badge badge-info">{{ $brand->products->count() }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created</th>
                                    <td>{{ $brand->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated</th>
                                    <td>{{ $brand->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($brand->products->count() > 0)
                        <hr>
                        <h5>Products using this brand ({{ $brand->products->count() }})</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($brand->products as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->category }}</td>
                                        <td>${{ number_format($product->display_price, 2) }}</td>
                                        <td>{{ $product->stock }}</td>
                                        <td>
                                            @if($product->status)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.products.edit', $product->id) }}" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection