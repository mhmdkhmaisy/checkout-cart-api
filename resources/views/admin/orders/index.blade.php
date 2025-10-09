@extends('admin.layout')

@section('title', 'Orders - Aragon RSPS Donation Admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h2 class="text-3xl font-bold gradient-crimson bg-clip-text text-transparent">
            Orders Management
        </h2>
        <p class="text-metallic-gray mt-2">Monitor and manage donation orders</p>
    </div>

    <!-- Filters -->
    <div class="glass-effect rounded-xl p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-crimson-primary mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 bg-dragon-surface border border-dragon rounded-lg text-metallic-silver">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-crimson-primary mb-2">Claim State</label>
                <select name="claim_state" class="w-full px-3 py-2 bg-dragon-surface border border-dragon rounded-lg text-metallic-silver">
                    <option value="">All States</option>
                    <option value="not_claimed" {{ request('claim_state') === 'not_claimed' ? 'selected' : '' }}>Not Claimed</option>
                    <option value="claimed" {{ request('claim_state') === 'claimed' ? 'selected' : '' }}>Claimed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-crimson-primary mb-2">Username</label>
                <input type="text" name="username" value="{{ request('username') }}" 
                       class="w-full px-3 py-2 bg-dragon-surface border border-dragon rounded-lg text-metallic-silver"
                       placeholder="Search username...">
            </div>
            <div>
                <label class="block text-sm font-medium text-crimson-primary mb-2">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full px-3 py-2 bg-dragon-surface border border-dragon rounded-lg text-metallic-silver">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full gradient-crimson py-2 px-4 rounded-lg font-medium">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="glass-effect rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dragon-surface border-b border-dragon">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-crimson-primary">ID</th>
                        <th class="px-6 py-4 text-left font-semibold text-crimson-primary">Username</th>
                        <th class="px-6 py-4 text-left font-semibold text-crimson-primary">Amount</th>
                        <th class="px-6 py-4 text-left font-semibold text-crimson-primary">Payment Method</th>
                        <th class="px-6 py-4 text-left font-semibold text-crimson-primary">Status</th>
                        <th class="px-6 py-4 text-left font-semibold text-crimson-primary">Claim State</th>
                        <th class="px-6 py-4 text-left font-semibold text-crimson-primary">Date</th>
                        <th class="px-6 py-4 text-left font-semibold text-crimson-primary">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dragon">
                    @forelse($orders as $order)
                        <tr class="hover:bg-dragon-surface transition-colors">
                            <td class="px-6 py-4 text-metallic-silver">#{{ $order->id }}</td>
                            <td class="px-6 py-4 font-medium text-metallic-silver">{{ $order->username }}</td>
                            <td class="px-6 py-4 text-metallic-silver">${{ number_format($order->amount, 2) }}</td>
                            <td class="px-6 py-4 capitalize text-metallic-silver">{{ $order->payment_method }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if($order->status === 'paid') bg-green-600 text-green-100
                                    @elseif($order->status === 'pending') bg-yellow-600 text-yellow-100
                                    @elseif($order->status === 'failed') bg-red-600 text-red-100
                                    @else bg-gray-600 text-gray-100
                                    @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if($order->claim_state === 'claimed') bg-blue-600 text-blue-100
                                    @else bg-orange-600 text-orange-100
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $order->claim_state)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-metallic-gray">
                                {{ $order->created_at->format('M j, Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.orders.show', $order) }}" 
                                   class="text-crimson-secondary hover:text-crimson-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-metallic-gray">
                                No orders found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="flex justify-center">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection