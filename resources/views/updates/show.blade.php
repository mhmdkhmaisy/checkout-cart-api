@extends('layouts.public')

@section('title', $update->title . ' - Aragon RSPS')
@section('description', Str::limit(strip_tags(\App\Helpers\UpdateRenderer::render($update->content)), 160))

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="fade-in-up" style="max-width: 900px; margin: 0 auto;">
    <div class="mb-4">
        <a href="{{ route('updates') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Updates
        </a>
    </div>

    <div class="glass-card">
        <div class="mb-4">
            <div class="flex justify-between items-start mb-3">
                <h1 class="text-primary" style="font-size: 2.5rem; font-weight: 800;">
                    {{ $update->title }}
                </h1>
                @if($update->client_update)
                    <span class="badge badge-info" style="font-size: 0.9rem;">
                        <i class="fas fa-download"></i> Client Update
                    </span>
                @endif
            </div>
            
            <p class="text-muted" style="font-size: 1rem;">
                <i class="far fa-clock"></i> Published {{ $update->created_at->format('F j, Y \a\t g:i A') }}
                <span class="mx-2">•</span>
                {{ $update->created_at->diffForHumans() }}
            </p>
        </div>

        <div class="content" style="line-height: 1.8;">
            {!! \App\Helpers\UpdateRenderer::render($update->content) !!}
        </div>

        @if($update->client_update)
            <div class="mt-5 pt-4" style="border-top: 1px solid var(--border-color);">
                <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 12px; padding: 1.5rem;">
                    <h3 class="text-primary mb-3" style="font-size: 1.25rem; font-weight: 600;">
                        <i class="fas fa-download"></i> Client Update Required
                    </h3>
                    <p class="mb-3">
                        This update requires a client update. Please restart your launcher to download the latest version automatically.
                    </p>
                    <a href="{{ route('play') }}" class="btn btn-primary">
                        <i class="fas fa-download"></i> Download Launcher
                    </a>
                </div>
            </div>
        @endif
    </div>

    @if($update->hotfixes && $update->hotfixes->count() > 0)
        @foreach($update->hotfixes as $hotfix)
            <div class="glass-card mt-4">
                <div class="mb-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="badge" style="background: rgba(168, 85, 247, 0.2); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.3); padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600;">
                            <i class="fas fa-wrench"></i> Hotfix
                        </span>
                        <h2 class="text-primary" style="font-size: 1.75rem; font-weight: 700;">
                            {{ $hotfix->title }}
                        </h2>
                    </div>
                    
                    <p class="text-muted" style="font-size: 0.9rem;">
                        <i class="far fa-clock"></i> Added {{ $hotfix->created_at->format('F j, Y \a\t g:i A') }}
                        <span class="mx-2">•</span>
                        {{ $hotfix->created_at->diffForHumans() }}
                    </p>
                </div>

                <div class="content" style="line-height: 1.8;">
                    {!! \App\Helpers\UpdateRenderer::render($hotfix->content) !!}
                </div>
            </div>
        @endforeach
    @endif

    <div class="mt-4 text-center">
        <a href="{{ route('updates') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to All Updates
        </a>
    </div>
</div>

<style>
.badge-info {
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
    border: 1px solid rgba(59, 130, 246, 0.3);
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
}

.content {
    color: var(--text-light);
}

.content h1, .content h2, .content h3, .content h4, .content h5, .content h6 {
    color: var(--primary-color);
}

.content a {
    color: var(--accent-gold);
    text-decoration: underline;
}

.content a:hover {
    color: var(--primary-bright);
}
</style>
@endsection
