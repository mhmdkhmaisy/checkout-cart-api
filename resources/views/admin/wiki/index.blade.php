@extends('admin.layout')

@section('header', 'Wiki Pages')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-dragon-silver">Manage Wiki Pages</h2>
            <p class="text-dragon-silver-dark mt-1">Create and organize your RSPS wiki documentation</p>
        </div>
        <a href="{{ route('admin.wiki.create') }}" class="bg-dragon-red hover:bg-dragon-red-dark text-white px-4 py-2 rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i>New Wiki Page
        </a>
    </div>

    @if($pages->count() > 0)
        @foreach($categories as $category)
            <div class="bg-dragon-surface border border-dragon-border rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-dragon-accent border-b border-dragon-border">
                    <h3 class="text-lg font-semibold text-dragon-silver">
                        <i class="fas fa-folder mr-2 text-dragon-gold"></i>
                        {{ $category ?: 'Uncategorized' }}
                    </h3>
                </div>
                <div class="divide-y divide-dragon-border">
                    @foreach($pages->where('category', $category) as $page)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-dragon-accent transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    @if($page->icon)
                                        <i class="{{ $page->icon }} text-dragon-gold"></i>
                                    @endif
                                    <div>
                                        <h4 class="text-lg font-medium text-dragon-silver">{{ $page->title }}</h4>
                                        <p class="text-sm text-dragon-silver-dark">/wiki/{{ $page->slug }}</p>
                                        @if($page->description)
                                            <p class="text-sm text-dragon-silver-dark mt-1">{{ Str::limit($page->description, 100) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-dragon-silver-dark">Order: {{ $page->order }}</span>
                                <form action="{{ route('admin.wiki.toggle-publish', $page) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1 rounded text-sm {{ $page->published ? 'bg-green-600 text-white' : 'bg-gray-600 text-gray-300' }}">
                                        <i class="fas {{ $page->published ? 'fa-eye' : 'fa-eye-slash' }} mr-1"></i>
                                        {{ $page->published ? 'Published' : 'Draft' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.wiki.edit', $page) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.wiki.destroy', $page) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        
        @php
            $uncategorizedPages = $pages->filter(function($page) {
                return empty($page->category);
            });
        @endphp
        
        @if($uncategorizedPages->count() > 0)
            <div class="bg-dragon-surface border border-dragon-border rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-dragon-accent border-b border-dragon-border">
                    <h3 class="text-lg font-semibold text-dragon-silver">
                        <i class="fas fa-folder mr-2 text-dragon-gold"></i>
                        Uncategorized
                    </h3>
                </div>
                <div class="divide-y divide-dragon-border">
                    @foreach($uncategorizedPages as $page)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-dragon-accent transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    @if($page->icon)
                                        <i class="{{ $page->icon }} text-dragon-gold"></i>
                                    @endif
                                    <div>
                                        <h4 class="text-lg font-medium text-dragon-silver">{{ $page->title }}</h4>
                                        <p class="text-sm text-dragon-silver-dark">/wiki/{{ $page->slug }}</p>
                                        @if($page->description)
                                            <p class="text-sm text-dragon-silver-dark mt-1">{{ Str::limit($page->description, 100) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-dragon-silver-dark">Order: {{ $page->order }}</span>
                                <form action="{{ route('admin.wiki.toggle-publish', $page) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1 rounded text-sm {{ $page->published ? 'bg-green-600 text-white' : 'bg-gray-600 text-gray-300' }}">
                                        <i class="fas {{ $page->published ? 'fa-eye' : 'fa-eye-slash' }} mr-1"></i>
                                        {{ $page->published ? 'Published' : 'Draft' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.wiki.edit', $page) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.wiki.destroy', $page) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <div class="bg-dragon-surface border border-dragon-border rounded-lg p-12 text-center">
            <i class="fas fa-book text-6xl text-dragon-silver-dark mb-4"></i>
            <h3 class="text-xl font-semibold text-dragon-silver mb-2">No Wiki Pages Yet</h3>
            <p class="text-dragon-silver-dark mb-6">Create your first wiki page to get started documenting your RSPS</p>
            <a href="{{ route('admin.wiki.create') }}" class="bg-dragon-red hover:bg-dragon-red-dark text-white px-6 py-3 rounded-lg inline-flex items-center transition-colors">
                <i class="fas fa-plus mr-2"></i>Create First Wiki Page
            </a>
        </div>
    @endif
</div>
@endsection
