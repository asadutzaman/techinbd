@extends('admin.layouts.app')

@php
    $tz = config('shop.timezone');
    $images = $product->images;
    $main = $images->first();
    $default = $product->variants->first();
    $price = (float) $product->base_price;
    $was = (float) ($default?->compare_price ?? 0);
    $saving = $was > $price ? $was - $price : 0;
    $cost = (float) $product->cost_price;
    $margin = $cost > 0 ? $price - $cost : null;
    $stockBadge = ['in_stock' => 'success', 'out_of_stock' => 'danger', 'preorder' => 'warning'][$product->stock_status] ?? 'secondary';
    // The shop's Specification rows: the specs, then attribute values they don't already give. (Its "General"
    // group repeats the facts shown above, so it's left out here.)
    $specRows = $product->specGroups()['Specifications'] ?? [];
    $keyCount = count($product->keySpecs());
    $statusBadge = ['pending' => 'warning', 'processing' => 'info', 'shipped' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger'];
    $liveUrl = route('product.detail', $product);
    // How search engines see it: on the public address, whichever address this page was opened on
    $publicUrl = rtrim(config('app.url'), '/') . route('product.detail', $product, false);
@endphp

@section('title', $product->name . ' | ' . config('shop.name') . ' Admin')
@section('page-title', $product->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($product->name, 40) }}</li>
@endsection

@push('styles')
<style>
    .product-gallery-main {
        display: flex;
        align-items: center;
        justify-content: center;
        aspect-ratio: 1 / 1;
        padding: 12px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: #FFFFFF;
    }

    .product-gallery-main img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .product-gallery-thumbs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 10px 0 0;
        padding: 0;
        list-style: none;
    }

    .product-gallery-thumbs button {
        width: 60px;
        height: 60px;
        padding: 3px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        background: #FFFFFF;
    }

    .product-gallery-thumbs button[aria-current="true"] {
        border-color: #007bff;
        box-shadow: 0 0 0 1px #007bff;
    }

    .product-gallery-thumbs img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .product-price {
        font-size: 28px;
        font-weight: 700;
        color: #DC2626;
    }

    .product-facts th {
        width: 34%;
        font-weight: 600;
        color: #6c757d;
    }

    .product-facts td,
    .product-facts th {
        padding: .45rem .75rem;
    }

    .spec-table .spec-group th {
        background: #f4f6f9;
    }

    .spec-table th[scope="row"] {
        width: 34%;
        font-weight: 500;
        color: #6c757d;
    }

    .search-preview {
        font-family: Arial, sans-serif;
    }

    .search-preview-url {
        font-size: 12px;
        color: #202124;
        word-break: break-all;
    }

    .search-preview-title {
        margin: 2px 0;
        font-size: 18px;
        line-height: 1.3;
        color: #1a0dab;
    }

    .search-preview-text {
        font-size: 13px;
        line-height: 1.5;
        color: #4d5156;
    }

    .product-description {
        max-height: 420px;
        overflow: auto;
    }

    .product-description img {
        max-width: 100%;
        height: auto;
    }
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <!-- Overview -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5 mb-3 mb-md-0">
                            @if($main)
                                <div class="product-gallery-main">
                                    <img src="{{ $main->gallery_url }}" alt="{{ $main->alt_text ?: $product->name }}" data-gallery-main>
                                </div>
                                @if($images->count() > 1)
                                    <ul class="product-gallery-thumbs" aria-label="Product images">
                                        @foreach($images as $image)
                                            <li>
                                                <button type="button" data-gallery-thumb="{{ $image->gallery_url }}" aria-current="{{ $loop->first ? 'true' : 'false' }}"
                                                        aria-label="Show image {{ $loop->iteration }}{{ $image->is_main ? ' (main)' : '' }}">
                                                    <img src="{{ $image->card_url }}" alt="" loading="lazy">
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @else
                                <div class="product-gallery-main text-muted flex-column">
                                    <i class="fas fa-image fa-3x mb-2"></i>
                                    No images yet. <a href="{{ route('admin.products.edit', $product->id) }}">Add some</a>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-7">
                            <p class="mb-2">
                                <span class="badge badge-{{ $product->status ? 'success' : 'secondary' }}">{{ $product->status ? 'Active' : 'Draft (hidden from the shop)' }}</span>
                                <span class="badge badge-{{ $stockBadge }}">{{ ucfirst(str_replace('_', ' ', $product->stock_status)) }}</span>
                                @if($product->featured)
                                    <span class="badge badge-warning"><i class="fas fa-star mr-1"></i>Featured</span>
                                @endif
                            </p>

                            <div class="mb-3">
                                <span class="product-price"><x-price :amount="$price" :currency="$product->currency" /></span>
                                @if($saving)
                                    <s class="text-muted ml-2"><x-price :amount="$was" :currency="$product->currency" /></s>
                                    <span class="badge badge-success ml-1">Save <x-price :amount="$saving" :currency="$product->currency" /> ({{ round($saving / $was * 100) }}%)</span>
                                @endif
                            </div>

                            <table class="table table-sm product-facts mb-3">
                                <tr><th>SKU</th><td><code>{{ $product->sku ?: '—' }}</code></td></tr>
                                <tr><th>Model</th><td>{{ $product->manufacturer_part_no ?: '—' }}</td></tr>
                                <tr><th>Barcode</th><td>{{ $product->ean_upc ?: '—' }}</td></tr>
                                <tr>
                                    <th>Category</th>
                                    <td>
                                        @if($product->category)
                                            <a href="{{ route('admin.products.index', ['category' => $product->category_id]) }}">{{ $product->category->name }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Brand</th>
                                    <td>
                                        @if($product->brand)
                                            <a href="{{ route('admin.products.index', ['brand' => $product->brand_id]) }}">{{ $product->brand->name }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Stock</th>
                                    <td>{{ $product->manage_stock ? $product->total_stock . ' in stock' : 'Not tracked' }}</td>
                                </tr>
                                <tr>
                                    <th>Cost / margin</th>
                                    <td>
                                        @if($margin !== null)
                                            <x-price :amount="$cost" :currency="$product->currency" /> ·
                                            <span class="{{ $margin < 0 ? 'text-danger' : 'text-success' }}"><x-price :amount="$margin" :currency="$product->currency" /> ({{ round($margin / max($price, 0.01) * 100) }}%)</span>
                                        @else
                                            <span class="text-muted">No cost price</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <div>
                                @if($product->status)
                                    <a href="{{ $liveUrl }}" class="btn btn-outline-primary" target="_blank" rel="noopener"><i class="fas fa-external-link-alt mr-1"></i> View on the shop</a>
                                @endif
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning"><i class="fas fa-edit mr-1"></i> Edit</a>
                                <button type="button" class="btn btn-outline-danger float-right" data-toggle="modal" data-target="#deleteModal"><i class="fas fa-trash mr-1"></i> Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specs, as the shop's Specification table lists them -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list-ul mr-1"></i> Specification</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.products.edit', $product->id) }}#specs" class="btn btn-tool">Edit specs</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(empty($product->specs))
                        <p class="text-muted p-3 mb-0">No key specs yet. <a href="{{ route('admin.products.edit', $product->id) }}#specs">Add them</a> so shoppers can compare at a glance.</p>
                    @endif
                    @if($specRows)
                        <table class="table table-sm spec-table mb-0">
                            @foreach($specRows as $label => $value)
                                <tr>
                                    <th scope="row" class="pl-3">{{ $label }}</th>
                                    <td>
                                        {{ $value }}
                                        @if($loop->index < $keyCount)
                                            <span class="badge badge-light border ml-1" title="Shown on product cards and at the top of the product page">key feature</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                </div>
            </div>

            @if($product->variants->count() > 1)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-sliders-h mr-1"></i> Options</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th class="pl-3">Option</th><th>SKU</th><th class="text-right">Price</th><th class="text-right">Was</th><th class="text-right">Stock</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($product->variants as $variant)
                                    <tr>
                                        <td class="pl-3">{{ $variant->name ?: 'Standard' }}</td>
                                        <td><code>{{ $variant->sku }}</code></td>
                                        <td class="text-right"><x-price :amount="$variant->price ?? $product->base_price" :currency="$product->currency" /></td>
                                        <td class="text-right">@if($variant->compare_price)<s class="text-muted"><x-price :amount="$variant->compare_price" :currency="$product->currency" /></s>@else — @endif</td>
                                        <td class="text-right">{{ $variant->stock }}</td>
                                        <td>@if($variant->is_default)<span class="badge badge-primary">Default</span>@endif</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if(filled(strip_tags((string) $product->description)))
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-align-left mr-1"></i> Description</h3>
                    </div>
                    <div class="card-body product-description">
                        @if($product->short_description)
                            <p class="lead">{{ $product->short_description }}</p>
                        @endif
                        {!! $product->description !!}
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Sales -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Sales</h3>
                    <div class="card-tools small text-muted pt-1">cancelled orders left out</div>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h4 mb-0">{{ (int) $sales->units }}</div>
                            <small class="text-muted">sold</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0">{{ (int) $sales->orders }}</div>
                            <small class="text-muted">{{ Str::plural('order', (int) $sales->orders) }}</small>
                        </div>
                        <div class="col-4">
                            <div class="h5 mb-0 pt-1"><x-price :amount="$sales->revenue" :currency="$product->currency" /></div>
                            <small class="text-muted">revenue</small>
                        </div>
                    </div>
                    @if($recentOrders->isNotEmpty())
                        <hr>
                        <h6 class="font-weight-bold">Latest orders</h6>
                        <ul class="list-unstyled mb-0">
                            @foreach($recentOrders as $order)
                                <li class="d-flex justify-content-between align-items-center py-1">
                                    <span>
                                        <a href="{{ route('admin.orders.show', $order->id) }}">{{ $order->order_number }}</a>
                                        <small class="text-muted">{{ $order->created_at->timezone($tz)->format('d M') }}</small>
                                    </span>
                                    <span class="badge badge-{{ $statusBadge[$order->status] ?? 'secondary' }}">{{ $order->status_label }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Search preview -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fab fa-google mr-1"></i> Search preview</h3>
                </div>
                <div class="card-body search-preview">
                    <div class="search-preview-url">{{ $publicUrl }}</div>
                    <div class="search-preview-title">{{ Str::limit($product->meta_title ?: $product->name . ' | ' . config('shop.name'), 60) }}</div>
                    <div class="search-preview-text">{{ Str::limit($product->meta_description ?: ($product->short_description ?: config('shop.description')), 160) }}</div>
                    @if(! $product->meta_title || ! $product->meta_description)
                        <p class="small text-muted mt-2 mb-0">
                            {{ ! $product->meta_title ? 'No SEO title, so the product name is used.' : '' }}
                            {{ ! $product->meta_description ? 'No SEO description, so the short description is used.' : '' }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Shipping details -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-truck mr-1"></i> Shipping and warranty</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm product-facts mb-0">
                        <tr><th class="pl-3">Weight</th><td>{{ $product->weight > 0 ? rtrim(rtrim((string) $product->weight, '0'), '.') . ' kg' : '—' }}</td></tr>
                        <tr><th class="pl-3">Dimensions</th><td>{{ $product->dimensions ?: '—' }}</td></tr>
                        <tr><th class="pl-3">Warranty</th><td>{{ $product->warranty ?: '—' }}</td></tr>
                    </table>
                </div>
            </div>

            <!-- Record -->
            <div class="card">
                <div class="card-body small text-muted">
                    ID {{ $product->id }} · slug <code>{{ $product->slug }}</code><br>
                    Added {{ $product->created_at->timezone($tz)->format('d M Y, g:i A') }}<br>
                    Last changed {{ $product->updated_at->timezone($tz)->format('d M Y, g:i A') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Delete confirmation -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalTitle">Delete this product?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1"><strong>{{ $product->name }}</strong> and its images will be removed. This can't be undone.</p>
                    <p class="text-muted small mb-0">Past orders keep the product's name and price.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Keep it</button>
                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline">
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
    // Gallery: a thumbnail shows its image in the large frame
    document.querySelectorAll('[data-gallery-thumb]').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            document.querySelector('[data-gallery-main]').src = thumb.dataset.galleryThumb;
            document.querySelectorAll('[data-gallery-thumb]').forEach(function (other) {
                other.setAttribute('aria-current', other === thumb ? 'true' : 'false');
            });
        });
    });
</script>
@endpush
