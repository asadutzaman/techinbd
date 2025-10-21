<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeOptimized;
use App\Models\AttributeValueOptimized;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttributeOptimizedController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $category = $request->get('category');
        $type = $request->get('type');

        $query = AttributeOptimized::with(['category:id,name'])
            ->select(['id', 'name', 'slug', 'type', 'category_id', 'required', 'filterable', 'status', 'sort_order', 'unit']);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($category) {
            if ($category === 'global') {
                $query->whereNull('category_id');
            } else {
                $query->where('category_id', $category);
            }
        }

        if ($type) {
            $query->where('type', $type);
        }

        $attributes = $query->orderBy('sort_order')->orderBy('name')->paginate($perPage);

        $categories = Category::where('status', true)->orderBy('name')->get(['id', 'name']);
        $types = ['text', 'number', 'select', 'boolean', 'textarea', 'color', 'date'];

        return view('admin.attributes.index', compact('attributes', 'categories', 'types'));
    }

    public function create()
    {
        $categories = Category::where('status', true)->orderBy('name')->get();
        $types = [
            'text' => 'Text',
            'number' => 'Number',
            'select' => 'Select (Dropdown)',
            'boolean' => 'Boolean (Yes/No)',
            'textarea' => 'Textarea',
            'color' => 'Color',
            'date' => 'Date'
        ];

        return view('admin.attributes.create', compact('categories', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:attributes_optimized,slug',
            'type' => 'required|in:text,number,select,boolean,textarea,color,date',
            'category_id' => 'nullable|exists:categories,id',
            'required' => 'boolean',
            'filterable' => 'boolean',
            'searchable' => 'boolean',
            'comparable' => 'boolean',
            'unit' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean'
        ]);

        $attribute = AttributeOptimized::create($request->all());

        return redirect()->route('admin.attributes.index')
                        ->with('success', 'Attribute created successfully!');
    }

    public function show($id)
    {
        $attribute = AttributeOptimized::with(['category', 'values' => function($query) {
            $query->orderBy('sort_order');
        }])->findOrFail($id);

        return view('admin.attributes.show', compact('attribute'));
    }

    public function edit($id)
    {
        $attribute = AttributeOptimized::findOrFail($id);
        $categories = Category::where('status', true)->orderBy('name')->get();
        $types = [
            'text' => 'Text',
            'number' => 'Number',
            'select' => 'Select (Dropdown)',
            'boolean' => 'Boolean (Yes/No)',
            'textarea' => 'Textarea',
            'color' => 'Color',
            'date' => 'Date'
        ];

        return view('admin.attributes.edit', compact('attribute', 'categories', 'types'));
    }

    public function update(Request $request, $id)
    {
        $attribute = AttributeOptimized::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:attributes_optimized,slug,' . $id,
            'type' => 'required|in:text,number,select,boolean,textarea,color,date',
            'category_id' => 'nullable|exists:categories,id',
            'required' => 'boolean',
            'filterable' => 'boolean',
            'searchable' => 'boolean',
            'comparable' => 'boolean',
            'unit' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean'
        ]);

        $attribute->update($request->all());

        return redirect()->route('admin.attributes.index')
                        ->with('success', 'Attribute updated successfully!');
    }

    public function destroy($id)
    {
        $attribute = AttributeOptimized::findOrFail($id);
        
        // Check if attribute is being used by products
        if ($attribute->productAttributes()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete attribute that is being used by products.']);
        }

        $attribute->delete();

        return redirect()->route('admin.attributes.index')
                        ->with('success', 'Attribute deleted successfully!');
    }

    public function manageValues($id)
    {
        $attribute = AttributeOptimized::with(['values' => function($query) {
            $query->orderBy('sort_order');
        }])->findOrFail($id);

        if (!in_array($attribute->type, ['select', 'color'])) {
            return redirect()->route('admin.attributes.index')
                           ->withErrors(['error' => 'Only select and color attributes can have predefined values.']);
        }

        return view('admin.attributes.values', compact('attribute'));
    }

    public function storeValue(Request $request, $id)
    {
        $attribute = AttributeOptimized::findOrFail($id);

        $request->validate([
            'value' => 'required|string|max:255',
            'display_value' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean'
        ]);

        // Check for duplicate values
        $exists = AttributeValueOptimized::where('attribute_id', $id)
                                        ->where('value', $request->value)
                                        ->exists();

        if ($exists) {
            return back()->withErrors(['value' => 'This value already exists for this attribute.']);
        }

        AttributeValueOptimized::create([
            'attribute_id' => $id,
            'value' => $request->value,
            'display_value' => $request->display_value,
            'color_code' => $request->color_code,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->has('status')
        ]);

        return back()->with('success', 'Attribute value added successfully!');
    }

    public function updateValue(Request $request, $attributeId, $valueId)
    {
        $value = AttributeValueOptimized::where('attribute_id', $attributeId)
                                       ->findOrFail($valueId);

        $request->validate([
            'value' => 'required|string|max:255',
            'display_value' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean'
        ]);

        // Check for duplicate values (excluding current)
        $exists = AttributeValueOptimized::where('attribute_id', $attributeId)
                                        ->where('value', $request->value)
                                        ->where('id', '!=', $valueId)
                                        ->exists();

        if ($exists) {
            return back()->withErrors(['value' => 'This value already exists for this attribute.']);
        }

        $value->update([
            'value' => $request->value,
            'display_value' => $request->display_value,
            'color_code' => $request->color_code,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->has('status')
        ]);

        return back()->with('success', 'Attribute value updated successfully!');
    }

    public function destroyValue($attributeId, $valueId)
    {
        $value = AttributeValueOptimized::where('attribute_id', $attributeId)
                                       ->findOrFail($valueId);

        $value->delete();

        return back()->with('success', 'Attribute value deleted successfully!');
    }
}