<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::with('category', 'bundleItems')->orderBy('product_name')->paginate(20);
        $categories = Category::orderBy('name')->get();
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_name' => 'required|string|max:100',
                'category_id' => 'nullable|exists:categories,id',
                'item_id' => 'required|integer',
                'qty_unit' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0.01',
                'is_active' => 'boolean'
            ]);

            $data = $validated;
            $data['is_active'] = $request->input('is_active', 0) ? 1 : 0;

            $product = \DB::transaction(function () use ($data, $request) {
                $product = Product::create($data);

                if ($request->has('bundle_items')) {
                    foreach ($request->bundle_items as $bundleItem) {
                        if (!empty($bundleItem['item_id']) && !empty($bundleItem['qty_unit'])) {
                            $product->bundleItems()->create([
                                'item_id' => (int) $bundleItem['item_id'],
                                'qty_unit' => (int) $bundleItem['qty_unit']
                            ]);
                        }
                    }
                }

                return $product;
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product created successfully.',
                    'product' => $product->load('bundleItems')
                ]);
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating product: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating product: ' . $e->getMessage());
        }
    }

    public function show(Product $product): View
    {
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View|JsonResponse
    {
        if (request()->ajax()) {
            $product->load('category', 'bundleItems');
            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        }
        
        $categories = Category::orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_name' => 'required|string|max:100',
                'category_id' => 'nullable|exists:categories,id',
                'item_id' => 'required|integer',
                'qty_unit' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0.01',
                'is_active' => 'boolean'
            ]);

            $data = $validated;
            $data['is_active'] = $request->input('is_active', 0) ? 1 : 0;

            \DB::transaction(function () use ($product, $data, $request) {
                $product->update($data);
                $product->bundleItems()->delete();

                if ($request->has('bundle_items')) {
                    foreach ($request->bundle_items as $bundleItem) {
                        if (!empty($bundleItem['item_id']) && !empty($bundleItem['qty_unit'])) {
                            $product->bundleItems()->create([
                                'item_id' => (int) $bundleItem['item_id'],
                                'qty_unit' => (int) $bundleItem['qty_unit']
                            ]);
                        }
                    }
                }
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product updated successfully.',
                    'product' => $product->load('bundleItems')
                ]);
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating product: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating product: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product): RedirectResponse|JsonResponse
    {
        try {
            if ($product->orderItems()->exists()) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete product. It has associated order items.'
                    ], 422);
                }

                return redirect()->route('admin.products.index')
                    ->with('error', 'Cannot delete product. It has associated order items.');
            }

            $product->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product deleted successfully.'
                ]);
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting product: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('admin.products.index')
                ->with('error', 'Error deleting product: ' . $e->getMessage());
        }
    }
}