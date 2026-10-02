{{-- Where the category sits: under a top-level category (one level deep), and its place in the menu --}}
@php($hasChildren = ($category->children_count ?? 0) > 0)
<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label for="parent_id">Parent category</label>
            <select class="form-control @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id" @disabled($hasChildren)>
                <option value="">None (a top-level category)</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" @selected((string) old('parent_id', $category?->parent_id) === (string) $parent->id)>{{ $parent->name }}</option>
                @endforeach
            </select>
            @error('parent_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">
                @if($hasChildren)
                    This category has subcategories, so it stays top-level.
                @else
                    A subcategory shows in its parent's menu, and its products are listed on the parent's page too.
                @endif
            </small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="sort_order">Sort order</label>
            <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $category?->sort_order ?? 0) }}">
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Lower numbers come first in the menu.</small>
        </div>
    </div>
</div>
