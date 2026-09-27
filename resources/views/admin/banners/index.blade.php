@extends('admin.layouts.app')

@section('title', 'Home Banners')
@section('page-title', 'Home Banners')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Banners at the top of the home page</h3>
            <a href="{{ route('admin.banners.create') }}" class="btn btn-primary ml-auto">
                <i class="fas fa-plus mr-1"></i>Add banner
            </a>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Live banners show in this order at the top of the home page: slides in the main slider, and the first
                {{ \App\Models\Banner::SIDE_SLOTS }} side banners beside it. Times are in {{ config('shop.timezone') }}.
            </p>
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Banner</th>
                            <th>Title</th>
                            <th>Shows in</th>
                            <th>Link</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th style="width: 110px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($banners as $banner)
                            <tr>
                                <td>
                                    <img src="{{ $banner->src() }}" alt="" class="img-fluid banner-thumb {{ $banner->isSide() ? 'is-side' : '' }}">
                                </td>
                                <td>
                                    <strong>{{ $banner->title }}</strong>
                                    @if($banner->subtitle)
                                        <div class="text-muted small">{{ $banner->subtitle }}</div>
                                    @endif
                                    @unless($banner->show_text)
                                        <div class="text-muted small"><i class="fas fa-eye-slash mr-1"></i>Text not shown on the banner</div>
                                    @endunless
                                </td>
                                <td>{{ \App\Models\Banner::PLACEMENTS[$banner->placement] ?? $banner->placement }}</td>
                                <td>
                                    @if($banner->link_url)
                                        <a href="{{ $banner->link_url }}" target="_blank" rel="noopener">{{ Str::limit($banner->link_url, 40) }}</a>
                                    @else
                                        <span class="text-muted">No link</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($banner->starts_at || $banner->ends_at)
                                        @if($banner->starts_at)
                                            <div>From {{ $banner->starts_at->timezone(config('shop.timezone'))->format('j M Y, g:i A') }}</div>
                                        @endif
                                        @if($banner->ends_at)
                                            <div>Until {{ $banner->ends_at->timezone(config('shop.timezone'))->format('j M Y, g:i A') }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">Always</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badge = ['Live' => 'success', 'Scheduled' => 'info', 'Ended' => 'secondary', 'Hidden' => 'dark'][$banner->status_label];
                                    @endphp
                                    <span class="badge badge-{{ $badge }}">{{ $banner->status_label }}</span>
                                </td>
                                <td>{{ $banner->sort_order }}</td>
                                <td>
                                    <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this banner? Its images are deleted too.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No banners yet</h5>
                                    <p class="text-muted">Add a banner to show it at the top of the home page.</p>
                                    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus mr-1"></i>Add banner
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .banner-thumb {
        aspect-ratio: 3 / 1;
        object-fit: cover;
    }

    .banner-thumb.is-side {
        width: 67%;
        aspect-ratio: 2 / 1;
    }
</style>
@endpush
