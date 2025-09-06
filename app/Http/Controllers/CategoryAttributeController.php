<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Http\Request;

class CategoryAttributeController extends Controller
{
    /**
     * Get attributes for a specific category
     */
    public function getAttributes($categoryId)
    {
        try {
            $category = Category::find($categoryId);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $attributes = Attribute::with(['activeValues' => function($query) {
                    $query->orderBy('sort_order');
                }])
                ->where('category_id', $categoryId)
                ->where('status', true)
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'success' => true,
                'attributes' => $attributes,
                'category' => $category->name
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load attributes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}