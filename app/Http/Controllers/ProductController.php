<?php

namespace App\Http\Controllers;

use App\Models\ProductOptimized;

class ProductController extends Controller
{
    /** Products listed beside (or, on small screens, below) the product */
    private const RELATED_COUNT = 8;

    public function show($id)
    {
        $product = ProductOptimized::with([
            'brand',
            'category',
            // The main image leads the gallery
            'images' => fn ($query) => $query->orderByDesc('is_main')->orderBy('sort_order')->orderBy('id'),
            'variants' => fn ($query) => $query->orderBy('is_default', 'desc')->orderBy('price'),
            'productAttributes.attribute',
        ])->where('status', 1)->findOrFail($id);

        // Same category, featured first, then newest
        $relatedProducts = ProductOptimized::with('mainImage')
            ->withMax('variants', 'compare_price')
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('featured')
            ->latest()
            ->orderByDesc('id')
            ->take(self::RELATED_COUNT)
            ->get();

        return view('product-detail', compact('product', 'relatedProducts'));
    }
}
