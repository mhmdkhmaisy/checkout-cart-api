@extends('admin.layout')

@section('title', 'Dashboard - Aragon RSPS Donation Admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">
                Dragon's Hoard Dashboard
            </h2>
            <p class="text-dragon-silver-dark mt-2">Welcome to the Aragon RSPS admin panel</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-dragon-silver-dark">{{ now()->format('F j, Y') }}</p>
            <p class="text-lg font-semibold text-dragon-silver">{{ now()->format('g:i A') }}</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <!-- Total Revenue -->
        <div class="glass-effect rounded-xl p-6 text-center border border-dragon-border">
            <div class="text-3xl font-bold text-dragon-red">${{ number_format($totalRevenue ?? 0, 2) }}</div>
            <div class="text-dragon-silver-dark mt-2">Total Revenue</div>
        </div>
        
        <!-- Total Orders -->
        <div class="glass-effect rounded-xl p-6 text-center border border-dragon-border">
            <div class="text-3xl font-bold text-blue-400">{{ $totalOrders ?? 0 }}</div>
            <div class="text-dragon-silver-dark mt-2">Total Orders</div>
        </div>
        
        <!-- Paid Orders -->
        <div class="glass-effect rounded-xl p-6 text-center border border-dragon-border">
            <div class="text-3xl font-bold text-green-400">{{ $paidOrders ?? 0 }}</div>
            <div class="text-dragon-silver-dark mt-2">Paid Orders</div>
        </div>
        
        <!-- Pending Orders -->
        <div class="glass-effect rounded-xl p-6 text-center border border-dragon-border">
            <div class="text-3xl font-bold text-yellow-400">{{ $pendingOrders ?? 0 }}</div>
            <div class="text-dragon-silver-dark mt-2">Pending Orders</div>
        </div>
        
        <!-- Unclaimed Orders -->
        <div class="glass-effect rounded-xl p-6 text-center border border-dragon-border">
            <div class="text-3xl font-bold text-orange-400">{{ $unclaimedOrders ?? 0 }}</div>
            <div class="text-dragon-silver-dark mt-2">Unclaimed Orders</div>
        </div>
    </div>

    <!-- Charts and Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="glass-effect rounded-xl p-6 border border-dragon-border">
            <h3 class="text-xl font-semibold mb-4 text-dragon-red">Recent Orders</h3>
            <div class="space-y-3">
                @forelse($recentOrders ?? [] as $order)
                    <div class="flex justify-between items-center p-3 bg-dragon-surface rounded-lg border border-dragon-border">
                        <div>
                            <div class="font-medium text-dragon-silver">{{ $order->username }}</div>
                            <div class="text-sm text-dragon-silver-dark">Order #{{ $order->id }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-medium text-dragon-silver">${{ number_format($order->amount, 2) }}</div>
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
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-dragon-border mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-dragon-silver-dark">No orders yet</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Top Products -->
        <div class="glass-effect rounded-xl p-6 border border-dragon-border">
            <h3 class="text-xl font-semibold mb-4 text-dragon-red">Top Products</h3>
            <div class="space-y-3">
                @forelse($topProducts ?? [] as $product)
                    <div class="flex justify-between items-center p-3 bg-dragon-surface rounded-lg border border-dragon-border">
                        <div class="font-medium text-dragon-silver">{{ $product->product_name }}</div>
                        <div class="text-dragon-red font-medium">{{ $product->total_sold }} sold</div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-dragon-border mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <p class="text-dragon-silver-dark">No sales data yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Monthly Revenue Chart -->
    @if(isset($monthlyRevenue) && $monthlyRevenue->count() > 0)
    <div class="glass-effect rounded-xl p-6 border border-dragon-border">
        <h3 class="text-xl font-semibold mb-4 text-dragon-red">Monthly Revenue</h3>
        <div class="h-64 flex items-end space-x-2">
            @foreach($monthlyRevenue as $month)
                <div class="flex-1 flex flex-col items-center">
                    <div class="bg-gradient-to-t from-dragon-red to-dragon-red-bright rounded-t w-full" 
                         style="height: {{ ($month->revenue / $monthlyRevenue->max('revenue')) * 200 }}px;">
                    </div>
                    <div class="text-xs text-dragon-silver-dark mt-2">
                        {{ date('M', mktime(0, 0, 0, $month->month, 1)) }}
                    </div>
                    <div class="text-xs text-dragon-red">${{ number_format($month->revenue, 0) }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="glass-effect rounded-xl p-6 border border-dragon-border">
        <h3 class="text-xl font-bold text-dragon-red mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.products.index') }}" 
               class="flex items-center p-4 bg-dragon-surface rounded-lg border border-dragon-border hover:bg-dragon-border transition-colors group">
                <div class="p-2 bg-dragon-red rounded-lg mr-4 group-hover:bg-dragon-red-bright transition-colors">
                    <svg class="w-5 h-5 text-dragon-silver" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-dragon-silver">Manage Products</p>
                    <p class="text-sm text-dragon-silver-dark">Add, edit, or remove products</p>
                </div>
            </a>

            <a href="{{ route('admin.orders.index') }}" 
               class="flex items-center p-4 bg-dragon-surface rounded-lg border border-dragon-border hover:bg-dragon-border transition-colors group">
                <div class="p-2 bg-dragon-red rounded-lg mr-4 group-hover:bg-dragon-red-bright transition-colors">
                    <svg class="w-5 h-5 text-dragon-silver" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-dragon-silver">View Orders</p>
                    <p class="text-sm text-dragon-silver-dark">Monitor order status and history</p>
                </div>
            </a>

            <a href="{{ route('admin.orders.logs') }}" 
               class="flex items-center p-4 bg-dragon-surface rounded-lg border border-dragon-border hover:bg-dragon-border transition-colors group">
                <div class="p-2 bg-dragon-red rounded-lg mr-4 group-hover:bg-dragon-red-bright transition-colors">
                    <svg class="w-5 h-5 text-dragon-silver" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-dragon-silver">Payment Logs</p>
                    <p class="text-sm text-dragon-silver-dark">Review payment transactions</p>
                </div>
            </a>

            <a href="{{ route('admin.api-docs') }}" 
               class="flex items-center p-4 bg-dragon-surface rounded-lg border border-dragon-border hover:bg-dragon-border transition-colors group">
                <div class="p-2 bg-dragon-red rounded-lg mr-4 group-hover:bg-dragon-red-bright transition-colors">
                    <svg class="w-5 h-5 text-dragon-silver" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-dragon-silver">API Documentation</p>
                    <p class="text-sm text-dragon-silver-dark">Integration guides and examples</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection