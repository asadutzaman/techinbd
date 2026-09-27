@php
    $localTime = fn ($value) => $value?->copy()->timezone(config('shop.timezone'))->format('Y-m-d\TH:i');
    $placement = old('placement', $banner->placement ?? 'slider');
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-lg-7">
        <div class="form-group">
            <label for="placement">Where it shows</label>
            <select class="form-control" id="placement" name="placement">
                @foreach(\App\Models\Banner::PLACEMENTS as $value => $label)
                    <option value="{{ $value }}" {{ $placement === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <small class="form-text text-muted">
                Slides take turns in the large slider. The first {{ \App\Models\Banner::SIDE_SLOTS }} live side banners
                show in the column to its right on computers, and side by side under it on phones.
            </small>
        </div>

        <div class="form-group">
            <label for="image">Banner image @unless($banner->exists)<span class="text-danger">*</span>@endunless</label>
            @if($banner->exists)
                <img src="{{ $banner->src() }}" alt="" class="img-fluid d-block mb-2 banner-shape {{ $banner->isSide() ? 'is-side' : '' }}">
            @endif
            <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/webp" {{ $banner->exists ? '' : 'required' }}>
            <small class="form-text text-muted" data-placement="slider" @if($placement !== 'slider') hidden @endif>
                Shown 3 times as wide as it is tall on computers and tablets. Upload at least 1920 × 640 px
                (JPG, PNG or WebP, up to 5 MB). Other shapes are trimmed evenly from the edges.
                The slide arrows sit in the middle of the left and right edges and the pause button in the
                bottom-right corner, so keep text and products away from those spots.
                @if($banner->exists) Leave empty to keep the current image. @endif
            </small>
            <small class="form-text text-muted" data-placement="side" @if($placement !== 'side') hidden @endif>
                Shown twice as wide as it is tall, on every screen. Upload at least 1000 × 500 px
                (JPG, PNG or WebP, up to 5 MB). Other shapes are trimmed evenly from the edges.
                On phones it is only about 175 px wide, so keep any text large.
                @if($banner->exists) Leave empty to keep the current image. @endif
            </small>
            <img class="img-fluid mt-2 image-preview banner-shape {{ $placement === 'side' ? 'is-side' : '' }}" data-for="image" alt="" hidden>
        </div>

        <div class="form-group" data-placement="slider" @if($placement !== 'slider') hidden @endif>
            <label for="mobile_image">Phone image <span class="text-muted font-weight-normal">(optional)</span></label>
            @if($banner->mobile_image_path)
                <img src="{{ $banner->src('mobile') }}" alt="" class="d-block mb-2" style="max-width: 320px; width: 100%; aspect-ratio: 2 / 1; object-fit: cover;">
                <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" class="custom-control-input" id="remove_mobile_image" name="remove_mobile_image" value="1">
                    <label class="custom-control-label" for="remove_mobile_image">Remove the phone image and use the banner image on phones</label>
                </div>
            @endif
            <input type="file" class="form-control-file" id="mobile_image" name="mobile_image" accept="image/jpeg,image/png,image/webp">
            <small class="form-text text-muted">
                Shown twice as wide as it is tall on phones. Upload 1200 × 600 px. Without it, phones show the
                banner image trimmed at the sides, so add one if your banner has text near the edges.
            </small>
            <img class="mt-2 image-preview" data-for="mobile_image" alt="" hidden style="max-width: 320px; width: 100%; aspect-ratio: 2 / 1; object-fit: cover;">
        </div>
    </div>

    <div class="col-lg-5">
        <div class="form-group">
            <label for="title">Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="title" name="title" maxlength="120" required
                   value="{{ old('title', $banner->title) }}" placeholder="Graphics cards, in stock">
            <small class="form-text text-muted">Also describes the image for screen readers.</small>
        </div>

        <div class="form-group" data-placement="slider" @if($placement !== 'slider') hidden @endif>
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="show_text" name="show_text" value="1"
                       {{ old('show_text', $banner->show_text) ? 'checked' : '' }}>
                <label class="custom-control-label" for="show_text">Show the title, subtitle and button on the banner</label>
            </div>
            <small class="form-text text-muted">Turn off when the text is already designed into the image.</small>
        </div>

        <small class="form-text text-muted mt-n2 mb-3" data-placement="side" @if($placement !== 'side') hidden @endif>
            Side banners show the image only, so design any text into it.
        </small>

        <div class="form-group" data-placement="slider" @if($placement !== 'slider') hidden @endif>
            <label for="subtitle">Subtitle</label>
            <input type="text" class="form-control" id="subtitle" name="subtitle" maxlength="255"
                   value="{{ old('subtitle', $banner->subtitle) }}" placeholder="RTX 50 series cards from ASUS, MSI and Gigabyte">
        </div>

        <div class="form-group" data-placement="slider" @if($placement !== 'slider') hidden @endif>
            <label for="button_text">Button text</label>
            <input type="text" class="form-control" id="button_text" name="button_text" maxlength="40"
                   value="{{ old('button_text', $banner->button_text) }}" placeholder="Shop now">
        </div>

        <div class="form-group">
            <label for="link_url">Link</label>
            <input type="text" class="form-control" id="link_url" name="link_url" maxlength="500"
                   value="{{ old('link_url', $banner->link_url) }}" placeholder="/shop?sale=1">
            <small class="form-text text-muted">
                Where the banner leads: a page on this site starting with "/", or a full web address.
                Leave empty for a banner that isn't clickable.
            </small>
        </div>

        <div class="form-row">
            <div class="form-group col-sm-6">
                <label for="starts_at">Show from</label>
                <input type="datetime-local" class="form-control" id="starts_at" name="starts_at"
                       value="{{ old('starts_at', $localTime($banner->starts_at)) }}">
            </div>
            <div class="form-group col-sm-6">
                <label for="ends_at">Show until</label>
                <input type="datetime-local" class="form-control" id="ends_at" name="ends_at"
                       value="{{ old('ends_at', $localTime($banner->ends_at)) }}">
            </div>
        </div>
        <small class="form-text text-muted mt-n2 mb-3">Optional, in {{ config('shop.timezone') }} time. Leave both empty to show the banner until you turn it off.</small>

        <div class="form-row">
            <div class="form-group col-sm-6">
                <label for="sort_order">Order</label>
                <input type="number" class="form-control" id="sort_order" name="sort_order" min="0"
                       value="{{ old('sort_order', $banner->sort_order ?? 0) }}">
                <small class="form-text text-muted">Lower numbers show first.</small>
            </div>
            <div class="form-group col-sm-6 d-flex align-items-center">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                           {{ old('is_active', $banner->is_active) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_active">Active</label>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Preview a newly chosen image before saving
    $('#image, #mobile_image').on('change', function () {
        var preview = $('.image-preview[data-for="' + this.id + '"]');
        var file = this.files[0];
        if (!file) {
            preview.attr('hidden', true);
            return;
        }
        preview.attr('src', URL.createObjectURL(file)).removeAttr('hidden');
    });

    // Slides and side banners are different shapes, and only slides take a phone image
    $('#placement').on('change', function () {
        var side = this.value === 'side';
        $('[data-placement]').each(function () {
            $(this).prop('hidden', $(this).data('placement') !== (side ? 'side' : 'slider'));
        });
        $('.banner-shape').toggleClass('is-side', side);
    });
</script>
@endpush

@push('styles')
<style>
    /* Previews in the shape the banner is cropped to */
    .banner-shape {
        aspect-ratio: 3 / 1;
        object-fit: cover;
    }

    .banner-shape.is-side {
        max-width: 400px;
        aspect-ratio: 2 / 1;
    }
</style>
@endpush
