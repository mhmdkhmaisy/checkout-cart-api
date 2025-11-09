@extends('layouts.public')

@section('title', 'Wiki - Knowledge Base')
@section('description', 'Browse our comprehensive wiki to learn everything about our RSPS')

@section('content')
<div class="fade-in-up">
    <div class="text-center mb-5">
        <h1 class="text-primary" style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem;">
            <i class="fas fa-book"></i> Wiki
        </h1>
        <p class="text-muted" style="font-size: 1.25rem; max-width: 800px; margin: 0 auto;">
            Your complete guide to everything in Aragon RSPS
        </p>
    </div>

    @if($pages->count() > 0)
        @foreach($categories as $category)
            <section class="mb-5">
                <h2 class="text-primary" style="font-size: 2rem; font-weight: 700; margin-bottom: 1.5rem;">
                    <i class="fas fa-folder mr-2"></i>{{ $category }}
                </h2>
                
                <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
                    @foreach($pages->where('category', $category) as $page)
                        <a href="{{ route('wiki.show', $page) }}" class="glass-card hover-scale" style="text-decoration: none;">
                            <div class="flex items-start gap-3">
                                @if($page->icon)
                                    <div class="text-primary" style="font-size: 2rem;">
                                        <i class="{{ $page->icon }}"></i>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <h3 class="text-primary" style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">
                                        {{ $page->title }}
                                    </h3>
                                    @if($page->description)
                                        <p class="text-muted" style="font-size: 0.875rem; line-height: 1.6;">
                                            {{ $page->description }}
                                        </p>
                                    @endif
                                </div>
                                <div>
                                    <i class="fas fa-chevron-right text-primary"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
        
        @if($pages->whereIn('category', [null, ''])->count() > 0)
            <section class="mb-5">
                <h2 class="text-primary" style="font-size: 2rem; font-weight: 700; margin-bottom: 1.5rem;">
                    <i class="fas fa-folder mr-2"></i>Other Pages
                </h2>
                
                <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
                    @foreach($pages->whereIn('category', [null, '']) as $page)
                        <a href="{{ route('wiki.show', $page) }}" class="glass-card hover-scale" style="text-decoration: none;">
                            <div class="flex items-start gap-3">
                                @if($page->icon)
                                    <div class="text-primary" style="font-size: 2rem;">
                                        <i class="{{ $page->icon }}"></i>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <h3 class="text-primary" style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">
                                        {{ $page->title }}
                                    </h3>
                                    @if($page->description)
                                        <p class="text-muted" style="font-size: 0.875rem; line-height: 1.6;">
                                            {{ $page->description }}
                                        </p>
                                    @endif
                                </div>
                                <div>
                                    <i class="fas fa-chevron-right text-primary"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    @else
        <div class="glass-card text-center" style="padding: 4rem;">
            <i class="fas fa-book text-primary" style="font-size: 4rem; margin-bottom: 1.5rem; display: block;"></i>
            <h2 class="text-primary" style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">
                Wiki Coming Soon
            </h2>
            <p class="text-muted" style="font-size: 1.125rem;">
                We're working on creating comprehensive guides for you
            </p>
        </div>
    @endif
</div>
@endsection
