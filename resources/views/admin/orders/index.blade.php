@extends('admin.layout')

@section('title', 'Orders - Aragon RSPS Donation Admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold gradient-crimson bg-clip-text text-transparent">
                    Orders Management
                </h2>
                <p class="text-metallic-gray mt-2">Monitor and manage donation orders</p>
            </div>
            <button onclick="openManualOrderModal()" class="gradient-crimson py-2 px-4 rounded-lg font-medium flex items-center gap-2 shadow-lg shadow-crimson-900/20">
                <i class="fas fa-plus"></i>
                Add Manual Order
            </button>
        </div>
    </div>

    <!-- Manual Order Modal -->
    <div id="manualOrderModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="glass-effect rounded-2xl w-full max-w-2xl border border-dragon/30 shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="bg-dragon-surface px-6 py-4 border-b border-dragon flex justify-between items-center">
                <h3 class="text-xl font-bold text-crimson-primary flex items-center gap-2">
                    <i class="fas fa-shopping-cart text-sm"></i>
                    Create Manual Order
                </h3>
                <button onclick="closeManualOrderModal()" class="text-metallic-gray hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.orders.store') }}" method="POST" class="p-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-crimson-primary mb-2">Character Username</label>
                        <input type="text" name="username" required class="w-full px-4 py-2.5 bg-dragon-surface border border-dragon rounded-xl text-metallic-silver focus:border-crimson-primary focus:ring-1 focus:ring-crimson-primary outline-none transition-all placeholder:text-metallic-gray/50" placeholder="e.g. AragonPlayer">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-crimson-primary mb-2">Total Amount ($)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-metallic-gray">$</span>
                            <input type="number" name="amount" step="0.01" required class="w-full pl-8 pr-4 py-2.5 bg-dragon-surface border border-dragon rounded-xl text-metallic-silver focus:border-crimson-primary focus:ring-1 focus:ring-crimson-primary outline-none transition-all" value="0.00">
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-crimson-primary mb-2">Payment Method</label>
                        <select name="payment_method" class="w-full px-4 py-2.5 bg-dragon-surface border border-dragon rounded-xl text-metallic-silver focus:border-crimson-primary focus:ring-1 focus:ring-crimson-primary outline-none transition-all">
                            <option value="paypal">PayPal (Simulated)</option>
                            <option value="manual">Manual/Cash</option>
                            <option value="crypto">Crypto</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-crimson-primary mb-3 flex items-center justify-between">
                        <span>Select Products</span>
                        <span class="text-[10px] uppercase tracking-wider text-metallic-gray font-bold">Multiple items allowed</span>
                    </label>
                    <div id="product-container" class="space-y-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                        <div class="product-row flex gap-3 p-3 bg-dragon-surface rounded-xl border border-dragon group hover:border-crimson-primary/30 transition-all">
                            <select name="products[0][id]" class="flex-1 bg-transparent border-none text-metallic-silver outline-none cursor-pointer">
                                @foreach(\App\Models\Product::active()->orderBy('product_name')->get() as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_name }} - ${{ number_format($product->price, 2) }}</option>
                                @endforeach
                            </select>
                            <div class="w-24 flex items-center bg-dragon-darker rounded-lg px-2 border border-dragon">
                                <span class="text-[10px] text-metallic-gray uppercase font-bold mr-2">Qty</span>
                                <input type="number" name="products[0][quantity]" value="1" min="1" class="w-full bg-transparent border-none text-metallic-silver text-center outline-none">
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="addProductRow()" class="mt-4 text-sm text-crimson-secondary hover:text-crimson-primary font-medium flex items-center gap-1.5 transition-colors group">
                        <i class="fas fa-plus-circle text-xs transition-transform group-hover:scale-110"></i>
                        Add another product
                    </button>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-dragon/30 mt-6">
                    <button type="button" onclick="closeManualOrderModal()" class="px-6 py-2.5 rounded-xl border border-dragon/50 text-metallic-silver hover:bg-dragon-surface transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="px-8 py-2.5 rounded-xl gradient-crimson font-bold shadow-lg shadow-crimson-900/20 hover:scale-[1.02] active:scale-95 transition-all">
                        Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let productCount = 1;
        const productList = @json(\App\Models\Product::active()->orderBy('product_name')->get());

        function openManualOrderModal() {
            document.getElementById('manualOrderModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeManualOrderModal() {
            document.getElementById('manualOrderModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function addProductRow() {
            const container = document.getElementById('product-container');
            const row = document.createElement('div');
            row.className = 'product-row flex gap-3 p-3 bg-dragon-surface rounded-xl border border-dragon group hover:border-crimson-primary/30 transition-all animate-in slide-in-from-top-2 duration-200';
            
            let options = '';
            productList.forEach(p => {
                options += `<option value="${p.id}">${p.product_name} - $${parseFloat(p.price).toFixed(2)}</option>`;
            });

            row.innerHTML = `
                <select name="products[${productCount}][id]" class="flex-1 bg-transparent border-none text-metallic-silver outline-none cursor-pointer">
                    ${options}
                </select>
                <div class="w-24 flex items-center bg-dragon-darker rounded-lg px-2 border border-dragon">
                    <span class="text-[10px] text-metallic-gray uppercase font-bold mr-2">Qty</span>
                    <input type="number" name="products[${productCount}][quantity]" value="1" min="1" class="w-full bg-transparent border-none text-metallic-silver text-center outline-none">
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-metallic-gray hover:text-red-500 transition-colors px-1">
                    <i class="fas fa-trash-alt text-sm"></i>
                </button>
            `;
            
            container.appendChild(row);
            productCount++;
        }

        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeManualOrderModal();
        });
    </script>

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