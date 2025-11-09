<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WikiPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WikiController extends Controller
{
    public function index()
    {
        $pages = WikiPage::orderBy('category')->orderBy('order')->orderBy('title')->get();
        $categories = WikiPage::getAllCategories();
        
        return view('admin.wiki.index', compact('pages', 'categories'));
    }

    public function create()
    {
        $categories = WikiPage::getAllCategories();
        return view('admin.wiki.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:wiki_pages,slug',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'content' => 'required|string',
            'order' => 'nullable|integer',
            'published' => 'boolean',
            'icon' => 'nullable|string|max:100'
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['published'] = $request->has('published');

        WikiPage::create($validated);

        return redirect()->route('admin.wiki.index')
            ->with('success', 'Wiki page created successfully');
    }

    public function edit(WikiPage $wikiPage)
    {
        $categories = WikiPage::getAllCategories();
        return view('admin.wiki.edit', compact('wikiPage', 'categories'));
    }

    public function update(Request $request, WikiPage $wikiPage)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:wiki_pages,slug,' . $wikiPage->id,
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'content' => 'required|string',
            'order' => 'nullable|integer',
            'published' => 'boolean',
            'icon' => 'nullable|string|max:100'
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['published'] = $request->has('published');

        $wikiPage->update($validated);

        return redirect()->route('admin.wiki.index')
            ->with('success', 'Wiki page updated successfully');
    }

    public function destroy(WikiPage $wikiPage)
    {
        $wikiPage->delete();

        return redirect()->route('admin.wiki.index')
            ->with('success', 'Wiki page deleted successfully');
    }

    public function togglePublish(WikiPage $wikiPage)
    {
        $wikiPage->update(['published' => !$wikiPage->published]);

        return redirect()->back()
            ->with('success', 'Wiki page publish status updated');
    }
}
