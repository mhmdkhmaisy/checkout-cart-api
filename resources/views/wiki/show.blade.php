@extends('layouts.public')

@section('title', $wikiPage->title . ' - Wiki')
@section('description', $wikiPage->description ?? 'Learn more about ' . $wikiPage->title)

@push('styles')
<style>
    .wiki-container {
        display: grid;
        grid-template-columns: 280px 1fr 200px;
        gap: 2rem;
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }
    
    .wiki-sidebar {
        position: sticky;
        top: 2rem;
        height: calc(100vh - 4rem);
        overflow-y: auto;
    }
    
    .wiki-content {
        min-width: 0;
    }
    
    .wiki-toc {
        position: sticky;
        top: 2rem;
        height: calc(100vh - 4rem);
        overflow-y: auto;
    }
    
    .wiki-nav-item {
        display: block;
        padding: 0.5rem 0.75rem;
        color: var(--text-muted);
        text-decoration: none;
        border-left: 2px solid transparent;
        transition: all 0.2s;
    }
    
    .wiki-nav-item:hover {
        color: var(--text-light);
        border-left-color: var(--primary-color);
    }
    
    .wiki-nav-item.active {
        color: var(--primary-color);
        border-left-color: var(--primary-color);
        background: rgba(196, 30, 58, 0.1);
    }
    
    .wiki-category {
        margin-bottom: 1.5rem;
    }
    
    .wiki-category-title {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-gold);
        margin-bottom: 0.5rem;
        padding: 0 0.75rem;
    }
    
    .toc-item {
        display: block;
        padding: 0.375rem 0;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.2s;
    }
    
    .toc-item:hover {
        color: var(--primary-color);
    }
    
    .toc-item.active {
        color: var(--primary-color);
        font-weight: 600;
    }
    
    .toc-item.level-3 {
        padding-left: 1rem;
        font-size: 0.8125rem;
    }
    
    .wiki-article {
        background: var(--card-background);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 3rem;
    }
    
    .wiki-article h1 {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--text-light);
        margin-bottom: 0.5rem;
    }
    
    .wiki-article h2 {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--text-light);
        margin-top: 3rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--border-color);
    }
    
    .wiki-article h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-light);
        margin-top: 2rem;
        margin-bottom: 1rem;
    }
    
    .wiki-article p {
        color: var(--text-muted);
        line-height: 1.75;
        margin-bottom: 1rem;
    }
    
    .wiki-article ul, .wiki-article ol {
        color: var(--text-muted);
        margin: 1rem 0 1rem 1.5rem;
    }
    
    .wiki-article li {
        margin-bottom: 0.5rem;
        line-height: 1.75;
    }
    
    .wiki-article code {
        background: var(--accent-color);
        padding: 0.125rem 0.375rem;
        border-radius: 3px;
        font-size: 0.875em;
        color: var(--accent-gold);
        font-family: 'Courier New', monospace;
    }
    
    .wiki-article pre {
        background: var(--accent-color);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 1rem;
        overflow-x: auto;
        margin: 1.5rem 0;
    }
    
    .wiki-article pre code {
        background: none;
        padding: 0;
        color: var(--text-light);
    }
    
    .wiki-alert {
        padding: 1rem 1.25rem;
        border-radius: 6px;
        margin: 1.5rem 0;
        border-left: 4px solid;
    }
    
    .wiki-alert.info {
        background: rgba(59, 130, 246, 0.1);
        border-left-color: #3b82f6;
        color: #60a5fa;
    }
    
    .wiki-alert.warning {
        background: rgba(245, 158, 11, 0.1);
        border-left-color: #f59e0b;
        color: #fbbf24;
    }
    
    .wiki-alert.success {
        background: rgba(34, 197, 94, 0.1);
        border-left-color: #22c55e;
        color: #4ade80;
    }
    
    .wiki-alert.danger {
        background: rgba(239, 68, 68, 0.1);
        border-left-color: #ef4444;
        color: #f87171;
    }
    
    @media (max-width: 1200px) {
        .wiki-container {
            grid-template-columns: 1fr;
        }
        .wiki-sidebar, .wiki-toc {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="wiki-container">
    <aside class="wiki-sidebar">
        <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-light); margin-bottom: 1.5rem;">
            <i class="fas fa-book mr-2 text-primary"></i>Wiki
        </h3>
        
        @foreach($categories as $category)
            <div class="wiki-category">
                <div class="wiki-category-title">{{ $category }}</div>
                @foreach($allPages->where('category', $category) as $page)
                    <a href="{{ route('wiki.show', $page) }}" 
                       class="wiki-nav-item {{ $page->id === $wikiPage->id ? 'active' : '' }}">
                        @if($page->icon)
                            <i class="{{ $page->icon }} mr-2"></i>
                        @endif
                        {{ $page->title }}
                    </a>
                @endforeach
            </div>
        @endforeach
        
        @if($allPages->whereIn('category', [null, ''])->count() > 0)
            <div class="wiki-category">
                <div class="wiki-category-title">Other</div>
                @foreach($allPages->whereIn('category', [null, '']) as $page)
                    <a href="{{ route('wiki.show', $page) }}" 
                       class="wiki-nav-item {{ $page->id === $wikiPage->id ? 'active' : '' }}">
                        @if($page->icon)
                            <i class="{{ $page->icon }} mr-2"></i>
                        @endif
                        {{ $page->title }}
                    </a>
                @endforeach
            </div>
        @endif
    </aside>
    
    <main class="wiki-content">
        <article class="wiki-article">
            <h1>{{ $wikiPage->title }}</h1>
            
            @if($wikiPage->description)
                <p style="font-size: 1.125rem; color: var(--text-muted); margin-bottom: 2rem;">
                    {{ $wikiPage->description }}
                </p>
            @endif
            
            <div class="wiki-body">
                {!! $wikiPage->content !!}
            </div>
        </article>
    </main>
    
    @if(count($toc) > 0)
        <aside class="wiki-toc">
            <h4 style="font-size: 0.875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-gold); margin-bottom: 1rem;">
                On This Page
            </h4>
            <nav>
                @foreach($toc as $item)
                    <a href="#{{ $item['id'] }}" 
                       class="toc-item level-{{ $item['level'] }}" 
                       data-target="{{ $item['id'] }}">
                        {{ $item['text'] }}
                    </a>
                @endforeach
            </nav>
        </aside>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tocLinks = document.querySelectorAll('.toc-item');
    const sections = Array.from(tocLinks).map(link => 
        document.getElementById(link.dataset.target)
    ).filter(Boolean);
    
    function setActiveSection() {
        let current = '';
        sections.forEach(section => {
            const rect = section.getBoundingClientRect();
            if (rect.top <= 100) {
                current = section.id;
            }
        });
        
        tocLinks.forEach(link => {
            link.classList.toggle('active', link.dataset.target === current);
        });
    }
    
    window.addEventListener('scroll', setActiveSection);
    setActiveSection();
    
    tocLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const target = document.getElementById(link.dataset.target);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
</script>
@endsection
