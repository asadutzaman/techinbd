<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Display a listing of brands
     */
    public function index()
    {
        $brands = Brand::withCount('products')->paginate(15);
        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new brand
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Store a newly created brand
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'website' => 'nullable|url',
            'status' => 'boolean'
        ]);

        $data = $request->all();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . Str::slug($data['name']) . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('img/brands'), $logoName);
            $data['logo'] = $logoName;
        }

        // Handle status checkbox
        $data['status'] = $request->has('status') ? 1 : 0;

        Brand::create($data);

        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully!');
    }

    /**
     * Display the specified brand
     */
    public function show($id)
    {
        $brand = Brand::with('products')->findOrFail($id);
        return view('admin.brands.show', compact('brand'));
    }

    /**
     * Show the form for editing the specified brand
     */
    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified brand
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $id,
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'website' => 'nullable|url',
            'status' => 'boolean'
        ]);

        $brand = Brand::findOrFail($id);
        $data = $request->all();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($brand->logo && file_exists(public_path('img/brands/' . $brand->logo))) {
                unlink(public_path('img/brands/' . $brand->logo));
            }
            
            $logo = $request->file('logo');
            $logoName = time() . '_' . Str::slug($data['name']) . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('img/brands'), $logoName);
            $data['logo'] = $logoName;
        }

        // Handle status checkbox
        $data['status'] = $request->has('status') ? 1 : 0;

        $brand->update($data);

        return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully!');
    }

    /**
     * Remove the specified brand
     */
    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        
        // Delete logo if exists
        if ($brand->logo && file_exists(public_path('img/brands/' . $brand->logo))) {
            unlink(public_path('img/brands/' . $brand->logo));
        }
        
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully!');
    }
}