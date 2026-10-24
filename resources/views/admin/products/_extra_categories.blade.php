{{--
    "Also show in" for the product create/edit forms: other categories that list the product as well as its
    own (a gaming keyboard in Gaming Keyboard). Its own category is skipped when saving.
    Expects $categories (Category::nestedList()) and $extraCategoryIds.
--}}
@php($checked = array_map('intval', (array) old('extra_categories', $extraCategoryIds)))
<div class="form-group mb-0">
    <label id="extra_categories_label">Also show in <span class="text-muted font-weight-normal">(optional)</span></label>
    <div class="border rounded px-2 py-1 @error('extra_categories.*') is-invalid @enderror" style="max-height: 220px; overflow-y: auto;"
         role="group" aria-labelledby="extra_categories_label" aria-describedby="extra_categories_help">
        @foreach($categories as $category)
            <div class="custom-control custom-checkbox {{ $category->parent_id ? 'ml-3' : '' }}">
                <input type="checkbox" class="custom-control-input" id="extra_category_{{ $category->id }}"
                       name="extra_categories[]" value="{{ $category->id }}" {{ in_array($category->id, $checked, true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="extra_category_{{ $category->id }}">{{ $category->name }}</label>
            </div>
        @endforeach
    </div>
    @error('extra_categories.*')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted" id="extra_categories_help">
        Listed there as well as in its own category, for example a gaming keyboard in Gaming Keyboard.
    </small>
</div>
