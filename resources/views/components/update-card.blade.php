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
    
    <div class="text-muted mb-3" style="max-height: 100px; overflow: hidden; position: relative;">
        <div style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.6;">
            {{ \App\Helpers\UpdateRenderer::extractPlainText($update->content, 180) }}
        </div>
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
