<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Each top-level category followed by its subcategories. A shop has few enough categories to list them all.
     */
    public function index()
    {
        $all = Category::withCount(['products', 'children'])->orderBy('sort_order')->orderBy('name')->get();
        $children = $all->whereNotNull('parent_id')->groupBy('parent_id');

        $categories = $all->whereNull('parent_id')
            ->flatMap(fn (Category $category) => [$category, ...$children->get($category->id, [])]);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create', ['parents' => $this->parentOptions()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'parent_id' => ['nullable', 'integer', $this->topLevelRule()],
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'is_menu' => 'boolean',
            'is_featured' => 'boolean'
        ]);

        $data = $this->fields($request);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('img'), $imageName);
            $data['image'] = $imageName;
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully!');
    }

    public function edit($id)
    {
        $category = Category::withCount('children')->findOrFail($id);

        return view('admin.categories.edit', ['category' => $category, 'parents' => $this->parentOptions($category)]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::withCount('children')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Categories are one level deep: a category with subcategories stays top-level
            'parent_id' => ['nullable', 'integer', Rule::notIn([$category->id]), $this->topLevelRule(),
                Rule::prohibitedIf($category->children_count > 0)],
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'boolean',
            'is_menu' => 'boolean',
            'is_featured' => 'boolean'
        ], [
            'parent_id.prohibited' => 'This category has subcategories, so it can\'t go under another category.',
        ]);

        $data = $this->fields($request);

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

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully!');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Check if category has products
        $productCount = $category->products()->count();
        if ($productCount > 0) {
            return redirect()->route('admin.categories.index')->with('error', 'Cannot delete category. It has ' . $productCount . ' products associated with it.');
        }

        // Deleting it would delete its subcategories too, and take their products out of any category
        $childCount = $category->children()->count();
        if ($childCount > 0) {
            return redirect()->route('admin.categories.index')->with('error', 'Cannot delete category. It has ' . $childCount . ' ' . str('subcategory')->plural($childCount) . '; move or delete them first.');
        }

        // Delete category image if exists
        if ($category->image && file_exists(public_path('img/' . $category->image))) {
            unlink(public_path('img/' . $category->image));
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully!');
    }

    /**
     * The form's saved fields. Unticked switches aren't submitted, so they're read as booleans.
     */
    private function fields(Request $request): array
    {
        return [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'parent_id' => $request->input('parent_id') ?: null,
            'sort_order' => (int) $request->input('sort_order', 0),
            'status' => $request->boolean('status'),
            'is_menu' => $request->boolean('is_menu'),
            'is_featured' => $request->boolean('is_featured'),
        ];
    }

    /**
     * A parent must be a top-level category.
     */
    private function topLevelRule()
    {
        return Rule::exists('categories', 'id')->whereNull('parent_id');
    }

    /**
     * Top-level categories a category can go under (not itself).
     */
    private function parentOptions(?Category $category = null)
    {
        return Category::whereNull('parent_id')
            ->when($category, fn ($query) => $query->whereKeyNot($category->id))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
