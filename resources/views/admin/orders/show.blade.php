@extends('admin.layout')

@section('title', 'Order Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold gradient-green bg-clip-text text-transparent">
                Order #{{ $order->id }}
            </h2>
            <p class="text-gray-400 mt-2">Order details and timeline</p>
        </div>
        <div class="flex space-x-4">
            <a href="{{ route('admin.orders.events', $order->id) }}" 
               class="gradient-green py-2 px-4 rounded-lg font-medium">
                View Timeline
            </a>
            <a href="{{ route('admin.orders.index') }}" 
               class="bg-gray-700 hover:bg-gray-600 py-2 px-4 rounded-lg font-medium">
                Back to Orders
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Order Information -->
        <div class="glass-effect rounded-xl p-6">
            <h3 class="text-xl font-bold text-green-primary mb-4">Order Information</h3>
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
                    <span class="text-gray-400">Server ID:</span>
                    <span class="text-white font-medium">{{ $order->server_id ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Payment Method:</span>
                    <span class="text-white font-medium capitalize">{{ $order->payment_method }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Payment ID:</span>
                    <span class="text-white font-medium text-sm break-all">{{ $order->payment_id ?? 'N/A' }}</span>
                </div>
                @if($order->paypal_capture_id)
                <div class="flex justify-between">
                    <span class="text-gray-400">PayPal Capture ID:</span>
                    <span class="text-white font-medium text-sm break-all">{{ $order->paypal_capture_id }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-400">Amount:</span>
                    <span class="text-white font-medium">${{ number_format($order->amount, 2) }} {{ $order->currency }}</span>
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
                    <span class="text-gray-400">Claim State:</span>
                    @php
                        $claimState = $order->claim_state;
                        $badgeClass = match($claimState) {
                            'fully_claimed' => 'bg-green-600 text-green-100',
                            'partially_claimed' => 'bg-yellow-600 text-yellow-100',
                            'unclaimed' => 'bg-gray-600 text-gray-100',
                            'no_items' => 'bg-red-600 text-red-100',
                            default => 'bg-gray-600 text-gray-100'
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                        {{ str_replace('_', ' ', ucfirst($claimState)) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Created:</span>
                    <span class="text-white font-medium">{{ $order->created_at->format('Y-m-d H:i:s') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Updated:</span>
                    <span class="text-white font-medium">{{ $order->updated_at->format('Y-m-d H:i:s') }}</span>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="glass-effect rounded-xl p-6">
            <h3 class="text-xl font-bold text-green-primary mb-4">Order Items</h3>
            @if($order->orderItems->count() > 0)
                <div class="space-y-3">
                    @foreach($order->orderItems as $item)
                        <div class="bg-dark-surface rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-medium text-white">{{ $item->product_name }}</h4>
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    {{ $item->claimed ? 'bg-green-600 text-green-100' : 'bg-gray-600 text-gray-100' }}">
                                    {{ $item->claimed ? 'Claimed' : 'Unclaimed' }}
                                </span>
                            </div>
                            <div class="grid grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-400">Price:</span>
                                    <span class="text-white ml-2">${{ number_format($item->price, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Qty Units:</span>
                                    <span class="text-white ml-2">{{ $item->qty_units }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Total Qty:</span>
                                    <span class="text-white ml-2">{{ $item->total_qty }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-gray-400 py-8">
                    No items found for this order
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Events Preview -->
    @if($order->events->count() > 0)
    <div class="glass-effect rounded-xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-green-primary">Recent Events</h3>
            <a href="{{ route('admin.orders.events', $order->id) }}" 
               class="text-blue-400 hover:text-blue-300">View Full Timeline</a>
        </div>
        <div class="space-y-3">
            @foreach($order->events()->latest()->take(5)->get() as $event)
                <div class="flex items-center space-x-4 p-3 bg-dark-surface rounded-lg">
                    <div class="w-3 h-3 rounded-full
                        @if($event->status === 'paid') bg-green-500
                        @elseif($event->status === 'pending') bg-yellow-500
                        @elseif($event->status === 'failed') bg-red-500
                        @elseif($event->status === 'refunded') bg-orange-500
                        @else bg-gray-500
                        @endif">
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-center">
                            <span class="text-white font-medium">
                                {{ str_replace(['_', ':'], [' ', ' '], ucwords(strtolower($event->event_type))) }}
                            </span>
                            <span class="text-sm text-gray-400">{{ $event->created_at->format('M j, H:i') }}</span>
                        </div>
                        <span class="px-2 py-1 rounded text-xs font-medium
                            @if($event->status === 'paid') bg-green-600 text-green-100
                            @elseif($event->status === 'pending') bg-yellow-600 text-yellow-100
                            @elseif($event->status === 'failed') bg-red-600 text-red-100
                            @elseif($event->status === 'refunded') bg-orange-600 text-orange-100
                            @else bg-gray-600 text-gray-100
                            @endif">
                            {{ ucfirst($event->status) }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection