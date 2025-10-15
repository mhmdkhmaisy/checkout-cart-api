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
        return view('admin.products.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'product_name' => 'required|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'item_id' => 'required|integer',
            'qty_unit' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0.01',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $product = Product::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'product' => $product
            ]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
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
        $request->validate([
            'product_name' => 'required|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'item_id' => 'required|integer',
            'qty_unit' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0.01',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $product->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'product' => $product
            ]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse|JsonResponse
    {
        $product->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.'
            ]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}