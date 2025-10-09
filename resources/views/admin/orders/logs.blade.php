@extends('admin.layout')

@section('title', 'Payment Logs - Aragon RSPS Admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">Dragon's Payment Logs</h2>
            <p class="text-dragon-silver-dark mt-2">Monitor all payment transactions and events</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="refreshLogs()" class="px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                <i class="fas fa-sync-alt mr-2"></i>
                Refresh
            </button>
            <button onclick="exportLogs()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                <i class="fas fa-download mr-2"></i>
                Export
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-effect rounded-xl p-6 border border-dragon-border">
        <h3 class="text-lg font-semibold text-dragon-red mb-4">Filter Logs</h3>
        <form method="GET" action="{{ route('admin.orders.logs') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-dragon-silver-dark mb-2">Order ID</label>
                <input type="text" name="order_id" value="{{ request('order_id') }}" 
                       class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-md text-dragon-silver focus:ring-2 focus:ring-dragon-red focus:border-transparent"
                       placeholder="Enter order ID">
            </div>
            <div>
                <label class="block text-sm font-medium text-dragon-silver-dark mb-2">Payment Method</label>
                <select name="payment_method" class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-md text-dragon-silver focus:ring-2 focus:ring-dragon-red focus:border-transparent">
                    <option value="">All Methods</option>
                    <option value="paypal" {{ request('payment_method') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                    <option value="coinbase" {{ request('payment_method') === 'coinbase' ? 'selected' : '' }}>Coinbase</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-dragon-silver-dark mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-md text-dragon-silver focus:ring-2 focus:ring-dragon-red focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-md transition-colors">
                    <i class="fas fa-search mr-2"></i>
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
        <div class="px-6 py-4 border-b border-dragon-border">
            <h3 class="text-xl font-bold text-dragon-red">Payment Transaction Logs</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-dragon-surface border-b border-dragon-border">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Player</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Last Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dragon-border">
                    @forelse($logs ?? [] as $log)
                        <tr class="hover:bg-dragon-surface transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-dragon-red rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-receipt text-dragon-silver text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-dragon-silver">#{{ $log->order_id ?? $log->id }}</div>
                                        <div class="text-sm text-dragon-silver-dark">{{ $log->payment_id ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-dragon-silver">{{ $log->username ?? 'N/A' }}</div>
                                <div class="text-sm text-dragon-silver-dark">{{ isset($log->items) ? $log->items->count() : 0 }} items</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-dragon-red">${{ number_format($log->amount ?? 0, 2) }}</div>
                                <div class="text-sm text-dragon-silver-dark">{{ $log->currency ?? 'USD' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if(str_contains($log->last_event ?? '', 'COMPLETED') || str_contains($log->last_event ?? '', 'confirmed')) bg-green-600 text-green-100
                                    @elseif(str_contains($log->last_event ?? '', 'APPROVED') || str_contains($log->last_event ?? '', 'pending')) bg-blue-600 text-blue-100
                                    @elseif(str_contains($log->last_event ?? '', 'failed') || str_contains($log->last_event ?? '', 'DENIED')) bg-red-600 text-red-100
                                    @else bg-gray-600 text-gray-100
                                    @endif">
                                    {{ str_replace(['_', ':'], [' ', ' '], ucwords(strtolower($log->last_event ?? 'Unknown'))) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if(isset($log->status) && $log->status === 'paid') bg-green-600 text-green-100
                                    @elseif(isset($log->status) && $log->status === 'pending') bg-yellow-600 text-yellow-100
                                    @elseif(isset($log->status) && $log->status === 'failed') bg-red-600 text-red-100
                                    @else bg-gray-600 text-gray-100
                                    @endif">
                                    @if(isset($log->status) && $log->status === 'paid')
                                        <i class="fas fa-check-circle mr-1"></i>
                                    @elseif(isset($log->status) && $log->status === 'pending')
                                        <i class="fas fa-clock mr-1"></i>
                                    @elseif(isset($log->status) && $log->status === 'failed')
                                        <i class="fas fa-times-circle mr-1"></i>
                                    @endif
                                    {{ ucfirst($log->status ?? 'Unknown') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-dragon-silver">{{ $log->created_at->format('M j, Y') }}</div>
                                <div class="text-sm text-dragon-silver-dark">{{ $log->created_at->format('g:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.orders.show', $log->order_id ?? $log->id) }}" 
                                       class="inline-flex items-center px-3 py-1 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver text-xs rounded-md transition-colors">
                                        <i class="fas fa-eye mr-1"></i>
                                        View
                                    </a>
                                    <a href="{{ route('admin.orders.events', $log->order_id ?? $log->id) }}" 
                                       class="inline-flex items-center px-3 py-1 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver text-xs rounded-md transition-colors">
                                        <i class="fas fa-timeline mr-1"></i>
                                        Timeline
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-search text-dragon-border text-4xl mb-4"></i>
                                    <h3 class="text-lg font-medium text-dragon-silver-dark mb-2">No logs found</h3>
                                    <p class="text-dragon-silver-dark">Try adjusting your search filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(isset($logs) && $logs->hasPages())
            <div class="px-6 py-4 border-t border-dragon-border">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="glass-effect rounded-xl p-6 border border-dragon-border text-center">
            <div class="text-2xl font-bold text-dragon-red">{{ $stats['total_orders'] ?? 0 }}</div>
            <div class="text-sm text-dragon-silver-dark mt-1">Total Orders</div>
        </div>
        <div class="glass-effect rounded-xl p-6 border border-dragon-border text-center">
            <div class="text-2xl font-bold text-green-400">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</div>
            <div class="text-sm text-dragon-silver-dark mt-1">Total Revenue</div>
        </div>
        <div class="glass-effect rounded-xl p-6 border border-dragon-border text-center">
            <div class="text-2xl font-bold text-yellow-400">{{ $stats['pending_orders'] ?? 0 }}</div>
            <div class="text-sm text-dragon-silver-dark mt-1">Pending Orders</div>
        </div>
        <div class="glass-effect rounded-xl p-6 border border-dragon-border text-center">
            <div class="text-2xl font-bold text-red-400">{{ $stats['failed_orders'] ?? 0 }}</div>
            <div class="text-sm text-dragon-silver-dark mt-1">Failed Orders</div>
        </div>
    </div>
</div>

<script>
function refreshLogs() {
    window.location.reload();
}

function exportLogs() {
    // Add export functionality here
    alert('Export functionality coming soon!');
}
</script>
@endsection