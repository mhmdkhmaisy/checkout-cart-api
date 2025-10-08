@extends('admin.layout')

@section('title', 'Order Events Timeline')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold gradient-green bg-clip-text text-transparent">
                Order Events Timeline
            </h2>
            <p class="text-gray-400 mt-2">Complete event history for order #{{ $order->id }}</p>
        </div>
        <div class="flex space-x-4">
            <a href="{{ route('admin.orders.show', $order->id) }}" 
               class="bg-gray-700 hover:bg-gray-600 py-2 px-4 rounded-lg font-medium">
                Back to Order
            </a>
            <a href="{{ route('admin.orders.logs') }}" 
               class="bg-gray-700 hover:bg-gray-600 py-2 px-4 rounded-lg font-medium">
                All Logs
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Summary -->
        <div class="glass-effect rounded-xl p-6">
            <h3 class="text-xl font-bold text-green-primary mb-4">Order Summary</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-400">Order ID:</span>
                    <span class="text-white font-medium">#{{ $order->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Username:</span>
                    <span class="text-white font-medium">{{ $order->username }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Status:</span>
                    <span class="px-3 py-1 rounded-full text-xs font-medium
                        @if($order->status === 'paid') bg-green-600 text-green-100
                        @elseif($order->status === 'pending') bg-yellow-600 text-yellow-100
                        @elseif($order->status === 'failed') bg-red-600 text-red-100
                        @elseif($order->status === 'refunded') bg-orange-600 text-orange-100
                        @else bg-gray-600 text-gray-100
                        @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Amount:</span>
                    <span class="text-white font-medium">${{ number_format($order->amount, 2) }} {{ $order->currency }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Payment Method:</span>
                    <span class="text-white font-medium capitalize">{{ $order->payment_method }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Created:</span>
                    <span class="text-white font-medium">{{ $order->created_at->format('M j, Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Events Timeline -->
        <div class="lg:col-span-2 glass-effect rounded-xl p-6">
            <h3 class="text-xl font-bold text-green-primary mb-6">Event Timeline</h3>
            
            @if($events->count() > 0)
                <div class="relative">
                    <!-- Timeline line -->
                    <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-600"></div>
                    
                    <div class="space-y-6">
                        @foreach($events as $event)
                            <div class="relative flex items-start space-x-4">
                                <!-- Timeline marker -->
                                <div class="relative z-10 w-12 h-12 rounded-full flex items-center justify-center
                                    @if($event->status === 'paid') bg-green-600
                                    @elseif($event->status === 'pending') bg-yellow-600
                                    @elseif($event->status === 'failed') bg-red-600
                                    @elseif($event->status === 'refunded') bg-orange-600
                                    @else bg-gray-600
                                    @endif">
                                    @if($event->status === 'paid')
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @elseif($event->status === 'failed')
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    @elseif($event->status === 'pending')
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @endif
                                </div>
                                
                                <!-- Event content -->
                                <div class="flex-1 bg-dark-surface rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="text-lg font-medium text-white">
                                            {{ str_replace(['_', ':'], [' ', ' '], ucwords(strtolower($event->event_type))) }}
                                        </h4>
                                        <span class="text-sm text-gray-400">
                                            {{ $event->created_at->format('M j, Y H:i:s') }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-4 mb-3">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium
                                            @if($event->status === 'paid') bg-green-600 text-green-100
                                            @elseif($event->status === 'pending') bg-yellow-600 text-yellow-100
                                            @elseif($event->status === 'failed') bg-red-600 text-red-100
                                            @elseif($event->status === 'refunded') bg-orange-600 text-orange-100
                                            @else bg-gray-600 text-gray-100
                                            @endif">
                                            Status: {{ ucfirst($event->status) }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ $event->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    
                                    @if($event->payload && is_array($event->payload))
                                        <div class="mt-3">
                                            <button onclick="togglePayload('payload-{{ $event->id }}')" 
                                                    class="text-blue-400 hover:text-blue-300 text-sm">
                                                View Payload Details
                                            </button>
                                            <div id="payload-{{ $event->id }}" class="hidden mt-2 bg-gray-700 rounded p-3 text-xs font-mono overflow-x-auto">
                                                <pre class="text-gray-300">{{ json_encode($event->payload, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center text-gray-400 py-12">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-lg">No events found for this order</p>
                    <p class="text-sm mt-2">Events will appear here as webhook notifications are received</p>
                </div>
            @endif
        </div>
    </div>
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
</script>
@endsection