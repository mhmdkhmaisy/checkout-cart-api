<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Update;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function index(Request $request)
    {
        $query = Update::query();
        
        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'published') {
                $query->where('is_published', true);
            } elseif ($status === 'draft') {
                $query->where('is_published', false);
            } elseif ($status === 'featured') {
                $query->where('is_featured', true);
            } elseif ($status === 'pinned') {
                $query->where('is_pinned', true);
            }
        }
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        
        // Sort by pinned first, then created_at
        $updates = $query->orderBy('is_pinned', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);
        
        // Get statistics
        $stats = [
            'total' => Update::count(),
            'published' => Update::where('is_published', true)->count(),
            'draft' => Update::where('is_published', false)->count(),
            'featured' => Update::where('is_featured', true)->count(),
            'pinned' => Update::where('is_pinned', true)->count(),
        ];
        
        // Get categories for filter
        $categories = Update::whereNotNull('category')
                            ->distinct()
                            ->pluck('category')
                            ->filter();
        
        return view('admin.updates.index', compact('updates', 'stats', 'categories'));
    }

    public function create()
    {
        return view('admin.updates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'author' => 'nullable|string|max:100',
            'meta_description' => 'nullable|string|max:160',
            'client_update' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $validated['client_update'] = $request->has('client_update');
        $validated['is_published'] = $request->has('is_published');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_pinned'] = $request->has('is_pinned');

        $update = Update::create($validated);

        return redirect()->route('admin.updates.index')
            ->with('success', 'Update created successfully.');
    }

    public function edit(Update $update)
    {
        return view('admin.updates.edit', compact('update'));
    }

    public function update(Request $request, Update $update)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'author' => 'nullable|string|max:100',
            'meta_description' => 'nullable|string|max:160',
            'client_update' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $validated['client_update'] = $request->has('client_update');
        $validated['is_published'] = $request->has('is_published');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_pinned'] = $request->has('is_pinned');

        $update->update($validated);

        return redirect()->route('admin.updates.index')
            ->with('success', 'Update updated successfully.');
    }

    public function destroy(Update $update)
    {
        $update->delete();

        return redirect()->route('admin.updates.index')
            ->with('success', 'Update deleted successfully.');
    }
    
    public function togglePublish(Update $update)
    {
        $update->is_published = !$update->is_published;
        if ($update->is_published && empty($update->published_at)) {
            $update->published_at = now();
        }
        $update->save();
        
        $status = $update->is_published ? 'published' : 'unpublished';
        return redirect()->back()->with('success', "Update {$status} successfully.");
    }
    
    public function toggleFeatured(Update $update)
    {
        $update->is_featured = !$update->is_featured;
        $update->save();
        
        $status = $update->is_featured ? 'marked as featured' : 'unmarked as featured';
        return redirect()->back()->with('success', "Update {$status} successfully.");
    }
    
    public function togglePinned(Update $update)
    {
        $update->is_pinned = !$update->is_pinned;
        $update->save();
        
        $status = $update->is_pinned ? 'pinned' : 'unpinned';
        return redirect()->back()->with('success', "Update {$status} successfully.");
    }
}
