<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductOptimized;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = ProductOptimized::with([
            'brand',
            'category',
            'images' => function($query) {
                $query->orderBy('sort_order');
            },
            'variants' => function($query) {
                $query->orderBy('is_default', 'desc')->orderBy('price');
            },
            'productAttributes.attribute',
            'productAttributes.attributeValue'
        ])->where('status', 1)->findOrFail($id);

        // Get related products from the same category
        $relatedProducts = ProductOptimized::with(['brand', 'mainImage'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 1)
            ->inStock()
            ->take(4)
            ->get();

        return view('product-detail', compact('product', 'relatedProducts'));
    }
}
