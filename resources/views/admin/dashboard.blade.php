@extends('admin.layout')

@section('title', 'Dashboard - RSPS Donation Admin')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="text-center">
        <h2 class="text-4xl font-bold gradient-green bg-clip-text text-transparent mb-2">
            Admin Dashboard
        </h2>
        <p class="text-gray-400">Monitor your RSPS donation system performance</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <div class="glass-effect rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-green-primary">${{ number_format($totalRevenue, 2) }}</div>
            <div class="text-gray-400 mt-2">Total Revenue</div>
        </div>
        <div class="glass-effect rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-blue-400">{{ $totalOrders }}</div>
            <div class="text-gray-400 mt-2">Total Orders</div>
        </div>
        <div class="glass-effect rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-green-400">{{ $paidOrders }}</div>
            <div class="text-gray-400 mt-2">Paid Orders</div>
        </div>
        <div class="glass-effect rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-yellow-400">{{ $pendingOrders }}</div>
            <div class="text-gray-400 mt-2">Pending Orders</div>
        </div>
        <div class="glass-effect rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-orange-400">{{ $unclaimedOrders }}</div>
            <div class="text-gray-400 mt-2">Unclaimed Orders</div>
        </div>
    </div>

    <!-- Charts and Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Orders -->
        <div class="glass-effect rounded-xl p-6">
            <h3 class="text-xl font-semibold mb-4 text-green-primary">Recent Orders</h3>
            <div class="space-y-3">
                @forelse($recentOrders as $order)
                    <div class="flex justify-between items-center p-3 bg-dark-surface rounded-lg">
                        <div>
                            <div class="font-medium">{{ $order->username }}</div>
                            <div class="text-sm text-gray-400">Order #{{ $order->id }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-medium">${{ number_format($order->amount, 2) }}</div>
                            <div class="text-sm">
                                <span class="px-2 py-1 rounded-full text-xs
                                    @if($order->status === 'paid') bg-green-600 text-green-100
                                    @elseif($order->status === 'pending') bg-yellow-600 text-yellow-100
                                    @else bg-red-600 text-red-100
                                    @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400">No orders yet</p>
                @endforelse
            </div>
        </div>

        <!-- Top Products -->
        <div class="glass-effect rounded-xl p-6">
            <h3 class="text-xl font-semibold mb-4 text-green-primary">Top Products</h3>
            <div class="space-y-3">
                @forelse($topProducts as $product)
                    <div class="flex justify-between items-center p-3 bg-dark-surface rounded-lg">
                        <div class="font-medium">{{ $product->product_name }}</div>
                        <div class="text-green-400 font-medium">{{ $product->total_sold }} sold</div>
                    </div>
                @empty
                    <p class="text-gray-400">No sales data yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Monthly Revenue Chart -->
    @if($monthlyRevenue->count() > 0)
    <div class="glass-effect rounded-xl p-6">
        <h3 class="text-xl font-semibold mb-4 text-green-primary">Monthly Revenue</h3>
        <div class="h-64 flex items-end space-x-2">
            @foreach($monthlyRevenue as $month)
                <div class="flex-1 flex flex-col items-center">
                    <div class="gradient-green rounded-t w-full" 
                         style="height: {{ ($month->revenue / $monthlyRevenue->max('revenue')) * 200 }}px;">
                    </div>
                    <div class="text-xs text-gray-400 mt-2">
                        {{ date('M', mktime(0, 0, 0, $month->month, 1)) }}
                    </div>
                    <div class="text-xs text-green-400">${{ number_format($month->revenue, 0) }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection