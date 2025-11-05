<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Update;
use Illuminate\Http\Request;

class UpdateApiController extends Controller
{
    /**
     * Get the latest 3 published updates
     * Returns: id, title, slug, date
     */
    public function latest()
    {
        $updates = Update::published()
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get(['id', 'title', 'slug', 'published_at as date', 'excerpt', 'featured_image', 'category', 'is_featured', 'is_pinned'])
            ->map(function ($update) {
                return [
                    'id' => $update->id,
                    'title' => $update->title,
                    'slug' => $update->slug,
                    'date' => $update->date?->toISOString(),
                    'excerpt' => $update->excerpt,
                    'featured_image' => $update->featured_image,
                    'category' => $update->category,
                    'is_featured' => $update->is_featured,
                    'is_pinned' => $update->is_pinned,
                ];
            });

        return response()->json([
            'success' => true,
            'updates' => $updates
        ])->header('Cache-Control', 'public, max-age=300');
    }
    
    /**
     * Get all published updates with pagination
     */
    public function index(Request $request)
    {
        $perPage = min($request->input('per_page', 10), 50);
        
        $query = Update::published();
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        
        // Filter featured only
        if ($request->boolean('featured')) {
            $query->featured();
        }
        
        $updates = $query->orderBy('is_pinned', 'desc')
                        ->orderBy('published_at', 'desc')
                        ->paginate($perPage);

        return response()->json([
            'success' => true,
            'updates' => $updates->items(),
            'pagination' => [
                'current_page' => $updates->currentPage(),
                'last_page' => $updates->lastPage(),
                'per_page' => $updates->perPage(),
                'total' => $updates->total(),
            ]
        ])->header('Cache-Control', 'public, max-age=180');
    }
    
    /**
     * Get a single update by slug
     */
    public function show($slug)
    {
        $update = Update::published()
            ->where('slug', $slug)
            ->firstOrFail();
        
        // Increment view count
        $update->incrementViews();
        
        return response()->json([
            'success' => true,
            'update' => $update
        ])->header('Cache-Control', 'public, max-age=300');
    }
}
