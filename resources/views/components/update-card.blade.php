<div class="glass-card fade-in-up">
    <div class="flex justify-between items-start mb-3">
        <h3 class="text-lg font-bold text-primary">{{ $update->title }}</h3>
        @if($update->client_update)
            <span class="badge badge-info">
                <i class="fas fa-download"></i> Client Update
            </span>
        @endif
    </div>
    
    <p class="text-sm text-muted mb-3">
        <i class="far fa-clock"></i> {{ $update->created_at->diffForHumans() }}
    </p>
    
    <div class="text-muted mb-3" style="max-height: 100px; overflow: hidden;">
        {!! Str::limit(strip_tags(\App\Helpers\UpdateRenderer::render($update->content)), 150) !!}
    </div>
    
    <a href="{{ route('updates.show', $update->slug) }}" class="btn btn-outline w-full">
        <i class="fas fa-book-open"></i> Read More
    </a>
</div>

<style>
.badge-info {
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
    border: 1px solid rgba(59, 130, 246, 0.3);
}
</style>
