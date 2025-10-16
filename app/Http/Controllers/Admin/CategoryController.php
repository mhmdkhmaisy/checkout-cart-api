<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->orderBy('name')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $category = Category::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => $category
            ]);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category): JsonResponse
    {
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        }
        
        return response()->json(['success' => false], 400);
    }

    public function update(Request $request, Category $category): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $category->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'category' => $category
            ]);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse|JsonResponse
    {
        $productCount = $category->products()->count();
        
        if ($productCount > 0) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete category. It has {$productCount} product(s) associated with it."
                ], 422);
            }
            
            return redirect()->route('admin.categories.index')
                ->with('error', "Cannot delete category. It has {$productCount} product(s) associated with it.");
        }

        $category->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ]);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
