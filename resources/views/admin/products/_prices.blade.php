{{--
    Price fields for the product create/edit forms. The "was" price is stored on the product's default option
    (compare_price); the shop shows it struck through with a "Save ৳…" badge when it's higher than the price.
    Expects $priceDefaults: ['base_price' => ..., 'compare_price' => ..., 'cost_price' => ...].
--}}
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="base_price">Price *</label>
            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                <input type="number" class="form-control @error('base_price') is-invalid @enderror"
                       id="base_price" name="base_price" value="{{ old('base_price', $priceDefaults['base_price']) }}"
                       placeholder="0" step="0.01" min="0" required>
                @error('base_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="compare_price">Was price <span class="text-muted font-weight-normal">(optional)</span></label>
            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                <input type="number" class="form-control @error('compare_price') is-invalid @enderror"
                       id="compare_price" name="compare_price" value="{{ old('compare_price', $priceDefaults['compare_price']) }}"
                       placeholder="Before the discount" step="0.01" min="0" aria-describedby="compare_price_help">
                @error('compare_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <small id="compare_price_help" class="form-text text-muted">Higher than the price: puts it on sale (Deals, Save badge). Empty: no sale.</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="cost_price">Cost price <span class="text-muted font-weight-normal">(private)</span></label>
            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                <input type="number" class="form-control @error('cost_price') is-invalid @enderror"
                       id="cost_price" name="cost_price" value="{{ old('cost_price', $priceDefaults['cost_price']) }}"
                       placeholder="What you paid" step="0.01" min="0">
                @error('cost_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>
