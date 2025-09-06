<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Attribute;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        
        $products = Product::with([
            'brand:id,name',
            'category:id,name',
            'productAttributes' => function($query) {
                $query->select('id', 'product_id', 'attribute_id', 'attribute_value_id', 'value')
                      ->with([
                          'attribute:id,name,type',
                          'attributeValue:id,value,display_value'
                      ]);
            }
        ])
        ->select('id', 'name', 'price', 'sale_price', 'image', 'category_id', 'brand_id', 'created_at')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
        
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('status', true)->orderBy('name')->get();
        $brands = Brand::where('status', true)->orderBy('name')->get();
        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'status' => $request->has('status') ? true : false,
            'featured' => $request->has('featured') ? true : false,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'boolean',
            'featured' => 'boolean',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string'
        ]);

        $data = $request->all();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('img'), $imageName);
            $data['image'] = $imageName;
        }

        $product = Product::create($data);

        // Handle attributes
        if ($request->has('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $attributeId => $value) {
                if (!empty($value)) {
                    ProductAttribute::create([
                        'product_id' => $product->id,
                        'attribute_id' => $attributeId,
                        'value' => $value
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully!');
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::with('productAttributes.attribute')->findOrFail($id);
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
        $request->merge([
            'status' => $request->has('status') ? true : false,
            'featured' => $request->has('featured') ? true : false,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'boolean',
            'featured' => 'boolean',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string'
        ]);

        $product = Product::findOrFail($id);
        $data = $request->all();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && file_exists(public_path('img/' . $product->image))) {
                unlink(public_path('img/' . $product->image));
            }
            
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('img'), $imageName);
            $data['image'] = $imageName;
        }

        $product->update($data);

        // Handle attributes - delete existing and create new ones
        $product->productAttributes()->delete();
        
        if ($request->has('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $attributeId => $value) {
                if (!empty($value)) {
                    ProductAttribute::create([
                        'product_id' => $product->id,
                        'attribute_id' => $attributeId,
                        'value' => $value
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // Delete product image if exists
        if ($product->image && file_exists(public_path('img/' . $product->image))) {
            unlink(public_path('img/' . $product->image));
        }
        
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully!');
    }

    /**
     * Get attributes for a specific category (AJAX endpoint)
     */
    public function getAttributesByCategory(Request $request)
    {
        $categoryId = $request->get('category_id');
        
        if (!$categoryId) {
            return response()->json([]);
        }
        
        $attributes = Attribute::where('category_id', $categoryId)
                              ->where('status', true)
                              ->with('activeAttributeValues')
                              ->orderBy('sort_order')
                              ->get();
        
        return response()->json($attributes);
    }
}
