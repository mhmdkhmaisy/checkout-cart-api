<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::orderBy('product_name')->paginate(20);
        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'product_name' => 'required|string|max:100',
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
            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        }
        
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $request->validate([
            'product_name' => 'required|string|max:100',
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