<?php

namespace App\Http\Controllers;

use App\Models\WikiPage;
use Illuminate\Http\Request;

class WikiController extends Controller
{
    public function index()
    {
        $pages = WikiPage::published()->ordered()->get();
        $categories = WikiPage::published()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
        
        return view('wiki.index', compact('pages', 'categories'));
    }

    public function show(WikiPage $wikiPage)
    {
        if (!$wikiPage->published) {
            abort(404);
        }

        $allPages = WikiPage::published()->ordered()->get();
        $categories = WikiPage::published()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
        
        $toc = $wikiPage->generateTableOfContents();

        return view('wiki.show', compact('wikiPage', 'allPages', 'categories', 'toc'));
    }
}
