<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductOptimized;
use App\Models\ProductVariantOptimized;
use App\Models\ProductImageOptimized;
use App\Models\ProductAttributeOptimized;
use App\Models\Category;
use App\Models\Brand;
use App\Models\AttributeOptimized;
use App\Models\AttributeValueOptimized;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductOptimizedController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $category = $request->get('category');
        $brand = $request->get('brand');
        $status = $request->get('status');

        $query = ProductOptimized::with([
            'brand:id,name',
            'category:id,name',
            'mainImage'
        ])->select([
            'id', 'name', 'sku', 'slug', 'short_description', 
            'base_price', 'total_stock', 'stock_status',
            'category_id', 'brand_id', 'status', 'created_at'
        ]);

        // Apply filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category_id', $category);
        }

        if ($brand) {
            $query->where('brand_id', $brand);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Get filter options
        $categories = Category::where('status', true)->orderBy('name')->get(['id', 'name']);
        $brands = Brand::where('status', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = Category::where('status', true)->orderBy('name')->get();
        $brands = Brand::where('status', true)->orderBy('name')->get();
        
        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products_optimized,slug',
            'sku' => 'nullable|string|max:100|unique:products_optimized,sku',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'short_description' => 'nullable|string|max:512',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'manage_stock' => 'boolean',
            'stock_status' => 'required|in:in_stock,out_of_stock,preorder',
            'total_stock' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'warranty' => 'nullable|string|max:128',
            'manufacturer_part_no' => 'nullable|string|max:128',
            'ean_upc' => 'nullable|string|max:64',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:512',
            'meta_keywords' => 'nullable|string|max:512',
            'status' => 'required|integer|in:0,1',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string|max:255',
            'variants' => 'nullable|array',
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.is_default' => 'boolean'
        ]);

        DB::beginTransaction();
        
        try {
            // Create product
            $productData = $request->only([
                'name', 'slug', 'sku', 'category_id', 'brand_id',
                'short_description', 'description', 'base_price', 'cost_price',
                'currency', 'manage_stock', 'stock_status', 'total_stock',
                'weight', 'dimensions', 'warranty', 'manufacturer_part_no',
                'ean_upc', 'meta_title', 'meta_description', 'meta_keywords', 'status'
            ]);

            $product = ProductOptimized::create($productData);

            // Handle images
            if ($request->hasFile('images')) {
                $this->handleImageUploads($product, $request->file('images'));
            }

            // Handle attributes
            if ($request->has('attributes')) {
                $this->handleProductAttributes($product, $request->input('attributes'));
            }

            // Handle variants
            if ($request->has('variants')) {
                $this->handleProductVariants($product, $request->input('variants'));
            }

            // Update search index
            $product->updateSearchIndex();

            DB::commit();

            return redirect()->route('admin.products.index')
                           ->with('success', 'Product created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    public function show($id)
    {
        $product = ProductOptimized::with([
            'brand',
            'category',
            'categories',
            'images' => function($query) {
                $query->orderBy('sort_order');
            },
            'variants' => function($query) {
                $query->orderBy('is_default', 'desc');
            },
            'productAttributes.attribute',
            'productAttributes.attributeValue'
        ])->findOrFail($id);

        return view('admin.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = ProductOptimized::with([
            'images',
            'variants',
            'productAttributes.attribute',
            'productAttributes.attributeValue'
        ])->findOrFail($id);

        $categories = Category::where('status', true)->orderBy('name')->get();
        $brands = Brand::where('status', true)->orderBy('name')->get();

        // Get current product attributes as key-value pairs
        $currentAttributes = [];
        foreach ($product->productAttributes as $productAttribute) {
            $currentAttributes[$productAttribute->attribute_id] = $productAttribute->value;
        }

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'currentAttributes'));
    }

    public function update(Request $request, $id)
    {
        $product = ProductOptimized::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products_optimized,slug,' . $id,
            'sku' => 'nullable|string|max:100|unique:products_optimized,sku,' . $id,
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'short_description' => 'nullable|string|max:512',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'manage_stock' => 'boolean',
            'stock_status' => 'required|in:in_stock,out_of_stock,preorder',
            'total_stock' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'warranty' => 'nullable|string|max:128',
            'manufacturer_part_no' => 'nullable|string|max:128',
            'ean_upc' => 'nullable|string|max:64',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:512',
            'meta_keywords' => 'nullable|string|max:512',
            'status' => 'required|integer|in:0,1',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();

        try {
            // Update product
            $productData = $request->only([
                'name', 'slug', 'sku', 'category_id', 'brand_id',
                'short_description', 'description', 'base_price', 'cost_price',
                'currency', 'manage_stock', 'stock_status', 'total_stock',
                'weight', 'dimensions', 'warranty', 'manufacturer_part_no',
                'ean_upc', 'meta_title', 'meta_description', 'meta_keywords', 'status'
            ]);

            $product->update($productData);

            // Handle new images
            if ($request->hasFile('images')) {
                $this->handleImageUploads($product, $request->file('images'));
            }

            // Handle attributes - delete existing and create new ones
            $product->productAttributes()->delete();
            if ($request->has('attributes')) {
                $this->handleProductAttributes($product, $request->input('attributes'));
            }

            // Update search index
            $product->updateSearchIndex();

            DB::commit();

            return redirect()->route('admin.products.index')
                           ->with('success', 'Product updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    public function destroy($id)
    {
        $product = ProductOptimized::findOrFail($id);

        DB::beginTransaction();

        try {
            // Delete associated images from storage
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->url)) {
                    Storage::disk('public')->delete($image->url);
                }
            }

            $product->delete();

            DB::commit();

            return redirect()->route('admin.products.index')
                           ->with('success', 'Product deleted successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to delete product: ' . $e->getMessage()]);
        }
    }

    /**
     * Get attributes for a specific category (AJAX endpoint)
     */
    public function getCategoryAttributes(Request $request, $categoryId)
    {
        try {
            $category = Category::findOrFail($categoryId);
            
            $attributes = AttributeOptimized::with(['activeValues' => function($query) {
                    $query->orderBy('sort_order');
                }])
                ->forCategory($categoryId)
                ->active()
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

    /**
     * Handle image uploads
     */
    private function handleImageUploads($product, $images)
    {
        foreach ($images as $index => $image) {
            $path = $image->store('products', 'public');
            
            ProductImageOptimized::create([
                'product_id' => $product->id,
                'url' => $path,
                'alt_text' => $product->name,
                'sort_order' => $index,
                'is_main' => $index === 0 // First image is main
            ]);
        }
    }

    /**
     * Handle product attributes
     */
    private function handleProductAttributes($product, $attributes)
    {
        foreach ($attributes as $attributeId => $value) {
            if (!empty($value)) {
                // Check if this is a predefined value
                $attributeValue = AttributeValueOptimized::where('attribute_id', $attributeId)
                                                        ->where('value', $value)
                                                        ->first();

                ProductAttributeOptimized::create([
                    'product_id' => $product->id,
                    'attribute_id' => $attributeId,
                    'attribute_value_id' => $attributeValue?->id,
                    'value' => $value
                ]);
            }
        }
    }

    /**
     * Handle product variants
     */
    private function handleProductVariants($product, $variants)
    {
        foreach ($variants as $variantData) {
            if (!empty($variantData['name']) || !empty($variantData['sku'])) {
                ProductVariantOptimized::create([
                    'product_id' => $product->id,
                    'name' => $variantData['name'] ?? null,
                    'sku' => $variantData['sku'] ?? null,
                    'price' => $variantData['price'] ?? null,
                    'stock' => $variantData['stock'] ?? 0,
                    'is_default' => $variantData['is_default'] ?? false
                ]);
            }
        }
    }

    /**
     * Delete image
     */
    public function deleteImage(Request $request)
    {
        $imageId = $request->input('image_id');
        $image = ProductImageOptimized::findOrFail($imageId);
        
        // Delete from storage
        if (Storage::disk('public')->exists($image->url)) {
            Storage::disk('public')->delete($image->url);
        }
        
        $image->delete();
        
        return response()->json(['success' => true]);
    }
}