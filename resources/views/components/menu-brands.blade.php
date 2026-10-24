@props(['category', 'brands'])
{{-- A category's brands in the category bar: "All {category}", then each brand's page of it, in columns of up to 12 --}}
@php($columns = min(3, (int) ceil(($brands->count() + 1) / 12)))
<ul {{ $attributes->class(['sf-brand-list', 'is-cols-' . $columns => $columns > 1]) }} aria-label="{{ $category->name }} brands">
    <li><a href="{{ route('shop.category', $category) }}">All {{ $category->name }}</a></li>
    @foreach($brands as $brand)
        <li><a href="{{ route('shop.category', ['category' => $category, 'brand' => $brand->id]) }}">{{ $brand->name }}</a></li>
    @endforeach
</ul>
