<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        
        if (!in_array($perPage, [10, 15, 25, 50])) {
            $perPage = 15;
        }
        
        $categories = \App\Models\Category::withCount('products')->orderBy('created_at', 'desc')->paginate($perPage);
        $categories->appends($request->query());
        
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'boolean',
            'is_menu' => 'boolean',
            'is_featured' => 'boolean'
        ]);

        $data = $request->all();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('img'), $imageName);
            $data['image'] = $imageName;
        }

        // Handle checkboxes
        $data['status'] = $request->has('status') ? 1 : 0;
        $data['is_menu'] = $request->has('is_menu') ? 1 : 0;
        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;

        \App\Models\Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully!');
    }

    public function edit($id)
    {
        $category = \App\Models\Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'boolean',
            'is_menu' => 'boolean',
            'is_featured' => 'boolean'
        ]);

        $category = \App\Models\Category::findOrFail($id);
        $data = $request->all();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image && file_exists(public_path('img/' . $category->image))) {
                unlink(public_path('img/' . $category->image));
            }
            
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('img'), $imageName);
            $data['image'] = $imageName;
        }

        // Handle checkboxes
        $data['status'] = $request->has('status') ? 1 : 0;
        $data['is_menu'] = $request->has('is_menu') ? 1 : 0;
        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully!');
    }

    public function destroy($id)
    {
        $category = \App\Models\Category::findOrFail($id);
        
        // Check if category has products
        $productCount = \App\Models\Product::where('category', $category->name)->count();
        if ($productCount > 0) {
            return redirect()->route('admin.categories.index')->with('error', 'Cannot delete category. It has ' . $productCount . ' products associated with it.');
        }
        
        // Delete category image if exists
        if ($category->image && file_exists(public_path('img/' . $category->image))) {
            unlink(public_path('img/' . $category->image));
        }
        
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully!');
    }
}
