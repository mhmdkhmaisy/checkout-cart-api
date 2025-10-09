@extends('admin.layout')

@section('title', 'Order Timeline - Aragon RSPS Admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">Order Timeline</h2>
            <p class="text-dragon-silver-dark mt-2">Event history for Order #{{ $order->id }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.orders.show', $order->id) }}" 
               class="px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Order
            </a>
            <a href="{{ route('admin.orders.logs') }}" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                <i class="fas fa-list mr-2"></i>
                All Logs
            </a>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="glass-effect rounded-xl p-6 border border-dragon-border">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="text-2xl font-bold text-dragon-red">#{{ $order->id }}</div>
                <div class="text-sm text-dragon-silver-dark">Order ID</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-dragon-silver">{{ $order->username }}</div>
                <div class="text-sm text-dragon-silver-dark">Player</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-400">${{ number_format($order->amount, 2) }}</div>
                <div class="text-sm text-dragon-silver-dark">Amount</div>
            </div>
            <div class="text-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($order->status === 'paid') bg-green-600 text-green-100
                    @elseif($order->status === 'pending') bg-yellow-600 text-yellow-100
                    @elseif($order->status === 'failed') bg-red-600 text-red-100
                    @else bg-gray-600 text-gray-100
                    @endif">
                    {{ ucfirst($order->status) }}
                </span>
                <div class="text-sm text-dragon-silver-dark mt-1">Status</div>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
        <div class="px-6 py-4 border-b border-dragon-border">
            <h3 class="text-xl font-bold text-dragon-red">Event Timeline</h3>
        </div>
        
        <div class="p-6">
            @if(isset($events) && $events->count() > 0)
                <div class="flow-root">
                    <ul class="-mb-8">
                        @foreach($events as $index => $event)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-dragon-red" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-dragon-black
                                                @if($event->status === 'paid') bg-green-600
                                                @elseif($event->status === 'pending') bg-yellow-600
                                                @elseif($event->status === 'failed') bg-red-600
                                                @elseif($event->status === 'cancelled') bg-gray-600
                                                @else bg-blue-600
                                                @endif">
                                                @if($event->status === 'paid')
                                                    <i class="fas fa-check text-white text-xs"></i>
                                                @elseif($event->status === 'pending')
                                                    <i class="fas fa-clock text-white text-xs"></i>
                                                @elseif($event->status === 'failed')
                                                    <i class="fas fa-times text-white text-xs"></i>
                                                @elseif($event->status === 'cancelled')
                                                    <i class="fas fa-ban text-white text-xs"></i>
                                                @else
                                                    <i class="fas fa-info text-white text-xs"></i>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-dragon-silver">
                                                        {{ isset($event->event_type) ? ucwords(str_replace('_', ' ', $event->event_type)) : (isset($event->type) ? ucwords(str_replace('_', ' ', $event->type)) : 'Event') }}
                                                    </p>
                                                    @if(isset($event->description) && $event->description)
                                                        <p class="text-sm text-dragon-silver-dark mt-1">{{ $event->description }}</p>
                                                    @endif
                                                    <div class="mt-1">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                            @if($event->status === 'paid') bg-green-600 text-green-100
                                                            @elseif($event->status === 'pending') bg-yellow-600 text-yellow-100
                                                            @elseif($event->status === 'failed') bg-red-600 text-red-100
                                                            @elseif($event->status === 'cancelled') bg-gray-600 text-gray-100
                                                            @else bg-blue-600 text-blue-100
                                                            @endif">
                                                            Status: {{ ucfirst($event->status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <time class="text-sm text-dragon-silver-dark">
                                                        {{ $event->created_at->format('M j, Y g:i A') }}
                                                    </time>
                                                    @if(isset($event->payload) && $event->payload)
                                                        <button onclick="togglePayload('payload-{{ $event->id }}')" 
                                                                class="inline-flex items-center px-2 py-1 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver text-xs rounded transition-colors">
                                                            <i class="fas fa-code mr-1"></i>
                                                            View Payload
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            @if(isset($event->payload) && $event->payload)
                                                <div id="payload-{{ $event->id }}" class="hidden mt-4 p-4 bg-dragon-black rounded-lg border border-dragon-border">
                                                    <div class="flex justify-between items-center mb-2">
                                                        <h4 class="text-sm font-medium text-dragon-red">Event Payload</h4>
                                                        <button onclick="copyPayload('payload-content-{{ $event->id }}')" 
                                                                class="text-xs text-dragon-silver-dark hover:text-dragon-silver">
                                                            <i class="fas fa-copy mr-1"></i>
                                                            Copy
                                                        </button>
                                                    </div>
                                                    <div class="overflow-x-auto">
                                                        <pre id="payload-content-{{ $event->id }}" class="text-xs text-dragon-silver whitespace-pre-wrap break-all max-w-full overflow-hidden">{{ is_string($event->payload) ? $event->payload : json_encode($event->payload, JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-timeline text-dragon-border text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-dragon-silver-dark mb-2">No Events Found</h3>
                    <p class="text-dragon-silver-dark">No timeline events have been recorded for this order yet.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Event Statistics -->
    @if(isset($events) && $events->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="glass-effect rounded-xl p-6 text-center border border-dragon-border">
                <div class="text-2xl font-bold text-dragon-red">{{ $events->count() }}</div>
                <div class="text-dragon-silver-dark mt-1">Total Events</div>
            </div>
            
            <div class="glass-effect rounded-xl p-6 text-center border border-dragon-border">
                <div class="text-2xl font-bold text-green-400">{{ $events->where('status', 'paid')->count() }}</div>
                <div class="text-dragon-silver-dark mt-1">Paid Events</div>
            </div>
            
            <div class="glass-effect rounded-xl p-6 text-center border border-dragon-border">
                <div class="text-2xl font-bold text-yellow-400">{{ $events->where('status', 'pending')->count() }}</div>
                <div class="text-dragon-silver-dark mt-1">Pending Events</div>
            </div>
            
            <div class="glass-effect rounded-xl p-6 text-center border border-dragon-border">
                <div class="text-2xl font-bold text-red-400">{{ $events->where('status', 'failed')->count() }}</div>
                <div class="text-dragon-silver-dark mt-1">Failed Events</div>
            </div>
        </div>
    @endif
</div>

<script>
function togglePayload(elementId) {
    const element = document.getElementById(elementId);
    if (element.classList.contains('hidden')) {
        element.classList.remove('hidden');
    } else {
        element.classList.add('hidden');
    }
}

function copyPayload(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            // Show success feedback
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check mr-1"></i>Copied!';
            button.classList.add('text-green-400');
            
            setTimeout(function() {
                button.innerHTML = originalText;
                button.classList.remove('text-green-400');
            }, 2000);
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        // Show success feedback
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-1"></i>Copied!';
        button.classList.add('text-green-400');
        
        setTimeout(function() {
            button.innerHTML = originalText;
            button.classList.remove('text-green-400');
        }, 2000);
    }
}
</script>
@endsection