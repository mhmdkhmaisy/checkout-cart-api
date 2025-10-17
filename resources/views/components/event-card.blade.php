<div class="glass-card fade-in-up" style="position: relative;">
    @if($event->image)
        <img src="{{ asset('storage/'.$event->image) }}" class="rounded-lg mb-3 w-full h-40 object-cover" alt="{{ $event->title }}">
    @else
        <div class="rounded-lg mb-3 w-full h-40 bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center">
            <i class="fas fa-calendar-alt text-4xl text-muted"></i>
        </div>
    @endif
    
    <h3 class="font-bold text-lg mb-2 text-primary">{{ $event->title }}</h3>
    <p class="text-sm text-muted mb-3">{{ ucfirst($event->type) }}</p>
    
    <div class="flex justify-between items-center mb-3">
        <span class="badge {{ $event->status == 'active' ? 'badge-success' : ($event->status == 'upcoming' ? 'badge-warning' : 'badge-secondary') }}">
            {{ ucfirst($event->status) }}
        </span>
        <span class="text-sm text-muted">
            @if($event->status == 'upcoming')
                Starts {{ $event->start_at->diffForHumans() }}
            @elseif($event->status == 'active' && $event->end_at)
                Ends {{ $event->end_at->diffForHumans() }}
            @else
                Ended {{ $event->end_at ? $event->end_at->diffForHumans() : '' }}
            @endif
        </span>
    </div>

    <button class="btn btn-primary w-full" onclick="showEventModal{{ $event->id }}()">
        <i class="fas fa-info-circle"></i> View Details
    </button>
</div>

<style>
.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success {
    background: rgba(34, 197, 94, 0.2);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.badge-warning {
    background: rgba(234, 179, 8, 0.2);
    color: #facc15;
    border: 1px solid rgba(234, 179, 8, 0.3);
}

.badge-secondary {
    background: rgba(107, 114, 128, 0.2);
    color: #9ca3af;
    border: 1px solid rgba(107, 114, 128, 0.3);
}
</style>

<div id="eventModal{{ $event->id }}" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
    <div class="glass-card" style="max-width: 600px; max-height: 90vh; overflow-y: auto; margin: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2 class="text-2xl font-bold text-primary">{{ $event->title }}</h2>
            <button onclick="closeEventModal{{ $event->id }}()" style="background: none; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        @if($event->image)
            <img src="{{ asset('storage/'.$event->image) }}" class="rounded-lg mb-4 w-full" alt="{{ $event->title }}">
        @endif
        
        <div class="mb-4">
            <h3 class="text-lg font-semibold mb-2 text-primary">Description</h3>
            <p style="white-space: pre-line;">{{ $event->description }}</p>
        </div>
        
        @if($event->rewards_array)
            <div class="mb-4">
                <h3 class="text-lg font-semibold mb-2 text-primary">Rewards</h3>
                <ul class="list-disc ml-6">
                    @foreach($event->rewards_array as $reward)
                        <li>{{ $reward }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="mt-4 pt-4" style="border-top: 1px solid var(--border-color);">
            <p class="text-sm text-muted">
                <strong>Starts:</strong> {{ $event->start_at->format('M d, Y H:i') }}
            </p>
            @if($event->end_at)
                <p class="text-sm text-muted">
                    <strong>Ends:</strong> {{ $event->end_at->format('M d, Y H:i') }}
                </p>
            @endif
        </div>
    </div>
</div>

<script>
function showEventModal{{ $event->id }}() {
    document.getElementById('eventModal{{ $event->id }}').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeEventModal{{ $event->id }}() {
    document.getElementById('eventModal{{ $event->id }}').style.display = 'none';
    document.body.style.overflow = 'auto';
}

document.getElementById('eventModal{{ $event->id }}').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEventModal{{ $event->id }}();
    }
});
</script>
