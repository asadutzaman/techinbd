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
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductOptimizedController extends Controller
{
    /**
     * The "was" price and the key specs, for both the create and edit forms.
     */
    private const SALE_AND_SPEC_RULES = [
        'compare_price' => 'nullable|numeric|gt:base_price',
        'specs' => 'nullable|array|max:40',
        'specs.*.label' => 'nullable|string|max:60',
        'specs.*.value' => 'nullable|string|max:255',
    ];

    private const SALE_AND_SPEC_MESSAGES = [
        'compare_price.gt' => 'The "was" price has to be higher than the price (leave it empty for no sale).',
    ];

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
            // Not unique: it's made from the name, and product links use the id
            'slug' => 'nullable|string|max:255',
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
            'featured' => 'boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string|max:255',
            'variants' => 'nullable|array',
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.is_default' => 'boolean',
        ] + self::SALE_AND_SPEC_RULES, self::SALE_AND_SPEC_MESSAGES);

        DB::beginTransaction();

        try {
            // Create product
            $productData = $request->only([
                'name', 'slug', 'sku', 'category_id', 'brand_id',
                'short_description', 'description', 'base_price', 'cost_price',
                'currency', 'manage_stock', 'stock_status', 'total_stock',
                'weight', 'dimensions', 'warranty', 'manufacturer_part_no',
                'ean_upc', 'meta_title', 'meta_description', 'meta_keywords', 'status', 'featured'
            ]);
            // An emptied stock field arrives as null, but the column is NOT NULL
            if (array_key_exists('total_stock', $productData)) {
                $productData['total_stock'] = (int) $productData['total_stock'];
            }
            // Unticked switches send nothing
            $productData['manage_stock'] = $request->boolean('manage_stock');
            $productData['featured'] = $request->boolean('featured');
            $productData['specs'] = $this->specsFrom($request);

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

            $this->saveComparePrice($product, $request->input('compare_price'));

            // Update search index
            $product->updateSearchIndex();

            DB::commit();

            return redirect()->route('admin.products.show', $product->id)
                           ->with('success', 'Product created. This is how it looks to the shop.');

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
            'images' => fn ($query) => $query->orderByDesc('is_main')->orderBy('sort_order')->orderBy('id'),
            'variants' => fn ($query) => $query->orderByDesc('is_default')->orderBy('price')->orderBy('id'),
            'productAttributes.attribute',
        ])->findOrFail($id);

        // Sales so far, leaving out cancelled orders
        $sales = OrderItem::where('product_id', $product->id)
            ->whereHas('order', fn ($order) => $order->where('status', '!=', 'cancelled'))
            ->selectRaw('coalesce(sum(quantity), 0) as units, coalesce(sum(total), 0) as revenue, count(distinct order_id) as orders')
            ->first();
        $recentOrders = Order::whereHas('orderItems', fn ($items) => $items->where('product_id', $product->id))
            ->latest()
            ->orderByDesc('id')
            ->take(5)
            ->get();

        return view('admin.products.show', compact('product', 'sales', 'recentOrders'));
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
            'slug' => 'nullable|string|max:255',
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
            'featured' => 'boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string|max:255',
        ] + self::SALE_AND_SPEC_RULES, self::SALE_AND_SPEC_MESSAGES);

        DB::beginTransaction();

        try {
            // Update product
            $productData = $request->only([
                'name', 'slug', 'sku', 'category_id', 'brand_id',
                'short_description', 'description', 'base_price', 'cost_price',
                'currency', 'manage_stock', 'stock_status', 'total_stock',
                'weight', 'dimensions', 'warranty', 'manufacturer_part_no',
                'ean_upc', 'meta_title', 'meta_description', 'meta_keywords', 'status', 'featured'
            ]);
            // An emptied stock field arrives as null, but the column is NOT NULL
            if (array_key_exists('total_stock', $productData)) {
                $productData['total_stock'] = (int) $productData['total_stock'];
            }
            // Unticked switches send nothing; without this they could never be turned off
            $productData['manage_stock'] = $request->boolean('manage_stock');
            $productData['featured'] = $request->boolean('featured');
            $productData['specs'] = $this->specsFrom($request);

            $product->update($productData);
            $this->saveComparePrice($product, $request->input('compare_price'));

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

            return redirect()->route('admin.products.show', $product->id)
                           ->with('success', 'Product saved.');

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
            // Delete associated images (originals and thumbnails) from storage
            foreach ($product->images as $image) {
                $image->deleteFiles();
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
                ->get()
                // Only an attribute made for this category can be required: a store-wide one (like the old
                // fashion "Size") would otherwise stop every laptop and phone from being saved.
                // (A block, not an arrow function: each() stops at the first callback that returns false.)
                ->each(function ($attribute) {
                    $attribute->required = $attribute->required && $attribute->category_id !== null;
                });

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
     * The key specs from the form as label => value, in the order shown. Rows missing either half are dropped.
     */
    private function specsFrom(Request $request): array
    {
        return collect($request->input('specs', []))
            ->filter(fn ($row) => is_array($row) && filled($row['label'] ?? null) && filled($row['value'] ?? null))
            ->mapWithKeys(fn ($row) => [trim($row['label']) => trim($row['value'])])
            ->all();
    }

    /**
     * The "was" price lives on the product's default option (compare_price): the shop reads it for the struck-out
     * price, the Save badge and Deals. A product without options gets a "Standard" one to hold it.
     */
    private function saveComparePrice(ProductOptimized $product, $comparePrice): void
    {
        $comparePrice = filled($comparePrice) ? round((float) $comparePrice, 2) : null;
        $default = $product->variants()->orderByDesc('is_default')->orderBy('id')->first();

        if ($default) {
            // Quietly: saving an option recounts the product's stock from its options, which would undo
            // a stock figure typed in the same form. The product's own save has already refreshed the shop.
            if ($default->compare_price === null ? $comparePrice !== null : (float) $default->compare_price !== $comparePrice) {
                $default->updateQuietly(['compare_price' => $comparePrice]);
            }

            return;
        }

        if ($comparePrice !== null) {
            $variant = new ProductVariantOptimized([
                'name' => 'Standard',
                'compare_price' => $comparePrice,
                'stock' => (int) $product->total_stock,
                'is_default' => true,
            ]);
            $variant->product()->associate($product);
            $variant->save();
        }
    }

    /**
     * Handle image uploads
     */
    private function handleImageUploads($product, $images)
    {
        // Check if product already has a main image
        $hasMainImage = $product->images()->where('is_main', true)->exists();
        
        // Get the current highest sort order
        $maxSortOrder = $product->images()->max('sort_order') ?? -1;
        
        foreach ($images as $index => $image) {
            $path = $image->store('products', 'public');

            $productImage = ProductImageOptimized::create([
                'product_id' => $product->id,
                'url' => $path,
                'alt_text' => $product->name,
                'sort_order' => $maxSortOrder + $index + 1,
                'is_main' => !$hasMainImage && $index === 0 // Only set first image as main if no main image exists
            ]);

            // Small WebP copy for product cards (uploads can be up to 2 MB)
            $productImage->generateVersions();
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
    public function deleteImage($imageId)
    {
        // The id comes from the URL (DELETE /admin/products/images/{imageId}); the page sends no body
        $image = ProductImageOptimized::findOrFail($imageId);
        
        // If this is the main image, set another image as main
        if ($image->is_main) {
            $nextMainImage = ProductImageOptimized::where('product_id', $image->product_id)
                                                  ->where('id', '!=', $image->id)
                                                  ->orderBy('sort_order')
                                                  ->first();
            if ($nextMainImage) {
                $nextMainImage->update(['is_main' => true]);
            }
        }
        
        // Delete original and thumbnail from storage
        $image->deleteFiles();

        $image->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Set image as main
     */
    public function setMainImage(Request $request)
    {
        $imageId = $request->input('image_id');
        $image = ProductImageOptimized::findOrFail($imageId);
        
        // The model's boot method will handle unsetting other main images
        $image->update(['is_main' => true]);
        
        return response()->json(['success' => true]);
    }
}