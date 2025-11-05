<div class="glass-card fade-in-up">
    @if($update->is_pinned)
        <div class="mb-2">
            <span class="badge badge-pinned">
                <i class="fas fa-thumbtack"></i> Pinned
            </span>
        </div>
    @endif
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
    
    <div class="text-muted mb-3" style="max-height: 200px; overflow: hidden; position: relative;">
        <div style="overflow: hidden;">
            {!! \App\Helpers\UpdateRenderer::renderPreview($update->content, 250) !!}
        </div>
        <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 40px; background: linear-gradient(to bottom, transparent, rgba(20, 16, 16, 0.92));"></div>
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
.badge-pinned {
    background: rgba(212, 0, 0, 0.2);
    color: #ff4444;
    border: 1px solid rgba(212, 0, 0, 0.4);
    font-weight: 600;
}
</style>
