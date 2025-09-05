<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    /**
     * Display a listing of attributes
     */
    public function index()
    {
        $attributes = Attribute::with(['category', 'attributeValues'])
                              ->withCount('attributeValues')
                              ->paginate(15);
        return view('admin.attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new attribute
     */
    public function create()
    {
        $categories = Category::where('status', true)->orderBy('name')->get();
        return view('admin.attributes.create', compact('categories'));
    }

    /**
     * Store a newly created attribute
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,number,select,boolean,textarea',
            'required' => 'boolean',
            'filterable' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean'
        ]);

        $data = $request->all();
        
        // Handle checkboxes
        $data['required'] = $request->has('required') ? 1 : 0;
        $data['filterable'] = $request->has('filterable') ? 1 : 0;
        $data['status'] = $request->has('status') ? 1 : 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        Attribute::create($data);

        return redirect()->route('admin.attributes.index')->with('success', 'Attribute created successfully!');
    }

    /**
     * Display the specified attribute
     */
    public function show($id)
    {
        $attribute = Attribute::with(['category', 'attributeValues'])->findOrFail($id);
        return view('admin.attributes.show', compact('attribute'));
    }

    /**
     * Show the form for editing the specified attribute
     */
    public function edit($id)
    {
        $attribute = Attribute::findOrFail($id);
        $categories = Category::where('status', true)->orderBy('name')->get();
        return view('admin.attributes.edit', compact('attribute', 'categories'));
    }

    /**
     * Update the specified attribute
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,number,select,boolean,textarea',
            'required' => 'boolean',
            'filterable' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean'
        ]);

        $attribute = Attribute::findOrFail($id);
        $data = $request->all();
        
        // Handle checkboxes
        $data['required'] = $request->has('required') ? 1 : 0;
        $data['filterable'] = $request->has('filterable') ? 1 : 0;
        $data['status'] = $request->has('status') ? 1 : 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $attribute->update($data);

        return redirect()->route('admin.attributes.index')->with('success', 'Attribute updated successfully!');
    }

    /**
     * Remove the specified attribute
     */
    public function destroy($id)
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->delete();

        return redirect()->route('admin.attributes.index')->with('success', 'Attribute deleted successfully!');
    }

    /**
     * Manage attribute values
     */
    public function manageValues($id)
    {
        $attribute = Attribute::with('attributeValues')->findOrFail($id);
        return view('admin.attributes.values', compact('attribute'));
    }

    /**
     * Store attribute value
     */
    public function storeValue(Request $request, $id)
    {
        $request->validate([
            'value' => 'required|string|max:255',
            'display_value' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean'
        ]);

        $attribute = Attribute::findOrFail($id);
        $data = $request->all();
        $data['attribute_id'] = $id;
        $data['status'] = $request->has('status') ? 1 : 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        AttributeValue::create($data);

        return redirect()->route('admin.attributes.values', $id)->with('success', 'Attribute value added successfully!');
    }

    /**
     * Update attribute value
     */
    public function updateValue(Request $request, $attributeId, $valueId)
    {
        $request->validate([
            'value' => 'required|string|max:255',
            'display_value' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean'
        ]);

        $value = AttributeValue::where('attribute_id', $attributeId)->findOrFail($valueId);
        $data = $request->all();
        $data['status'] = $request->has('status') ? 1 : 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $value->update($data);

        return redirect()->route('admin.attributes.values', $attributeId)->with('success', 'Attribute value updated successfully!');
    }

    /**
     * Delete attribute value
     */
    public function destroyValue($attributeId, $valueId)
    {
        $value = AttributeValue::where('attribute_id', $attributeId)->findOrFail($valueId);
        $value->delete();

        return redirect()->route('admin.attributes.values', $attributeId)->with('success', 'Attribute value deleted successfully!');
    }
}