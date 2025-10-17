<div class="glass-card">
    <div class="tabs">
        <div class="tab-buttons mb-4" style="display: flex; gap: 0.5rem;">
            <button class="tab-btn active" data-tab="weekly">
                <i class="fas fa-calendar-week"></i> This Week
            </button>
            <button class="tab-btn" data-tab="monthly">
                <i class="fas fa-calendar-alt"></i> This Month
            </button>
        </div>

        <div id="weekly" class="tab-content active">
            @if($weekly->count() > 0)
                @foreach($weekly as $voter)
                    <div class="voter-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; margin-bottom: 0.5rem; background: rgba(13, 13, 13, 0.6); border-radius: 8px; border: 1px solid var(--border-color);">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span class="rank" style="font-size: 1.25rem; font-weight: bold; color: {{ $loop->iteration <= 3 ? 'var(--accent-gold)' : 'var(--text-muted)' }};">
                                #{{ $loop->iteration }}
                            </span>
                            <span class="text-primary font-semibold">{{ $voter->username }}</span>
                        </div>
                        <span class="badge badge-success">
                            <i class="fas fa-vote-yea"></i> {{ $voter->votes }} votes
                        </span>
                    </div>
                @endforeach
            @else
                <p class="text-center text-muted py-4">No votes this week yet. Be the first!</p>
            @endif
        </div>

        <div id="monthly" class="tab-content" style="display: none;">
            @if($monthly->count() > 0)
                @foreach($monthly as $voter)
                    <div class="voter-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; margin-bottom: 0.5rem; background: rgba(13, 13, 13, 0.6); border-radius: 8px; border: 1px solid var(--border-color);">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span class="rank" style="font-size: 1.25rem; font-weight: bold; color: {{ $loop->iteration <= 3 ? 'var(--accent-gold)' : 'var(--text-muted)' }};">
                                #{{ $loop->iteration }}
                            </span>
                            <span class="text-primary font-semibold">{{ $voter->username }}</span>
                        </div>
                        <span class="badge badge-success">
                            <i class="fas fa-vote-yea"></i> {{ $voter->votes }} votes
                        </span>
                    </div>
                @endforeach
            @else
                <p class="text-center text-muted py-4">No votes this month yet. Be the first!</p>
            @endif
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tabId = this.getAttribute('data-tab');
        
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
        
        this.classList.add('active');
        document.getElementById(tabId).style.display = 'block';
    });
});
</script>

<style>
.tab-btn {
    padding: 0.5rem 1rem;
    background: rgba(13, 13, 13, 0.6);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.3s ease;
}

.tab-btn:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.tab-btn.active {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}
</style>
