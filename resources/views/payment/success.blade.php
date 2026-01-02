@extends('layouts.public')

@section('title', 'Payment Successful')

@push('styles')
<style>
    .success-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .success-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        border-radius: 50%;
        box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
        animation: pulse-success 2s ease-in-out infinite;
        margin-bottom: 1rem;
    }
    
    @keyframes pulse-success {
        0%, 100% { transform: scale(1); box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4); }
        50% { transform: scale(1.05); box-shadow: 0 8px 35px rgba(34, 197, 94, 0.6); }
    }
    
    .info-box {
        background: rgba(20, 16, 16, 0.7);
        border: 1px solid var(--border-color);
        border-left: 4px solid var(--primary-color);
        padding: 1.25rem;
        border-radius: 6px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: start;
        gap: 1rem;
    }
    
    .info-box.success {
        border-left-color: #22c55e;
        background: rgba(34, 197, 94, 0.08);
    }
    
    .info-box.warning {
        border-left-color: #f59e0b;
        background: rgba(245, 158, 11, 0.08);
    }
    
    .info-box.info {
        border-left-color: #3b82f6;
        background: rgba(59, 130, 246, 0.08);
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-badge.paid {
        background: rgba(34, 197, 94, 0.2);
        color: #22c55e;
    }
    
    .status-badge.pending {
        background: rgba(245, 158, 11, 0.2);
        color: #f59e0b;
    }
    
    .status-badge.ready {
        background: rgba(100, 116, 139, 0.2);
        color: #94a3b8;
    }
    
    .status-badge.claimed {
        background: rgba(34, 197, 94, 0.2);
        color: #22c55e;
    }
    
    .status-badge.cancelled {
        background: rgba(239, 68, 68, 0.2);
        color: #ef4444;
    }
    
    .order-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .order-table th {
        text-align: left;
        padding: 0.75rem 1rem;
        background: rgba(196, 30, 58, 0.1);
        border-bottom: 2px solid var(--border-color);
        color: var(--primary-color);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    
    .order-table td {
        padding: 0.875rem 1rem;
        border-bottom: 1px solid var(--border-color);
    }
    
    .order-table tr:hover td {
        background: rgba(196, 30, 58, 0.05);
    }
    
    .bundle-item {
        padding-left: 2rem;
        font-size: 0.875rem;
        color: var(--text-muted);
    }
    
    .bundle-item::before {
        content: '└─ ';
        margin-right: 0.5rem;
        color: var(--primary-color);
    }
    
    @media print {
        .no-print { display: none !important; }
        body { background: white !important; }
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto fade-in-up">
    <!-- Success Header -->
        <div class="success-header">
            <div class="success-badge">
                <i class="fas fa-check text-white" style="font-size: 2rem;"></i>
            </div>
            <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">Order Successful!</h1>
            <p class="text-muted" style="font-size: 1.125rem;">Thank you for choosing Aragon RSPS. Your order has been processed.</p>
        </div>

    @if($error)
        <!-- Error State -->
        <div class="glass-card" style="border-left: 4px solid #ef4444; background: rgba(239, 68, 68, 0.08);">
            <div style="text-align: center; padding: 1rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #ef4444; margin-bottom: 1rem;"></i>
                <h2 style="font-size: 1.5rem; font-weight: 700; color: #ef4444; margin-bottom: 0.5rem;">Order Not Found</h2>
                <p class="text-muted">{{ $error }}</p>
                <p class="text-sm text-muted" style="margin-top: 0.5rem;">Please contact support if you believe this is an error.</p>
            </div>
        </div>
    @else
        <!-- Order Details Card -->
        <div class="glass-card mb-4">
            <h2 class="text-primary" style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">
                <i class="fas fa-receipt mr-2"></i>Receipt Details
            </h2>
            
            <div class="grid grid-2 mb-4">
                <div>
                    <h3 class="text-primary font-bold mb-2" style="font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px;">Order Information</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <p><span style="color: var(--text-muted);">Order ID:</span> <strong>#{{ $order->id }}</strong></p>
                        <p><span style="color: var(--text-muted);">Username:</span> <strong>{{ $order->username }}</strong></p>
                        <p><span style="color: var(--text-muted);">Payment Method:</span> <strong class="capitalize">{{ $order->payment_method }}</strong></p>
                        <p><span style="color: var(--text-muted);">Total Amount:</span> <strong>${{ number_format($order->amount, 2) }} {{ $order->currency ?? 'USD' }}</strong></p>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-primary font-bold mb-2" style="font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px;">Payment Information</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <p><span style="color: var(--text-muted);">Payment ID:</span><br>
                            <code style="background: rgba(10, 10, 10, 0.6); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; display: inline-block; margin-top: 0.25rem;">{{ $order->payment_id }}</code>
                        </p>
                        <p><span style="color: var(--text-muted);">Status:</span> 
                            @if($order->status === 'paid')
                                <span class="status-badge paid"><i class="fas fa-check-circle"></i> Paid</span>
                            @elseif($order->status === 'pending')
                                <span class="status-badge pending"><i class="fas fa-clock"></i> Pending</span>
                            @else
                                <span class="status-badge">{{ ucfirst($order->status) }}</span>
                            @endif
                        </p>
                        <p><span style="color: var(--text-muted);">Order Date:</span> <strong>{{ $order->created_at->format('M j, Y g:i A') }}</strong></p>
                    </div>
                </div>
            </div>

            @if($order->status === 'pending')
                <div class="info-box warning">
                    <i class="fas fa-info-circle" style="font-size: 1.5rem; color: #f59e0b; flex-shrink: 0;"></i>
                    <div>
                        <h4 class="font-bold mb-1" style="color: #f59e0b;">Waiting for Payment Confirmation</h4>
                        @if($order->payment_method === 'paypal')
                            <p class="text-sm" style="color: #f59e0b;">We are waiting for confirmation from PayPal. This usually takes a few minutes.</p>
                        @else
                            <p class="text-sm" style="color: #f59e0b;">We are waiting for confirmation from Coinbase. This may take up to 15 minutes for blockchain confirmation.</p>
                        @endif
                    </div>
                </div>
            @endif

            @if($order->payment_method === 'coinbase' && isset($trackerUrl))
                <div class="info-box info">
                    <i class="fab fa-bitcoin" style="font-size: 1.5rem; color: #3b82f6; flex-shrink: 0;"></i>
                    <div style="flex: 1;">
                        <h4 class="font-bold mb-1" style="color: #3b82f6;">Track Your Coinbase Payment</h4>
                        <p class="text-sm mb-2" style="color: #3b82f6;">You can track your payment status directly on Coinbase Commerce:</p>
                        <a href="{{ $trackerUrl }}" target="_blank" class="btn btn-primary" style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                            <i class="fas fa-external-link-alt mr-2"></i>View Payment on Coinbase
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Items Purchased Card -->
        <div class="glass-card mb-4">
            <h3 class="text-primary" style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">
                <i class="fas fa-shopping-bag mr-2"></i>Items Purchased
            </h3>
            
            <div style="overflow-x: auto;">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="font-bold">{{ $item->product_name }}</div>
                                    @if($item->product && $item->product->bundleItems && $item->product->bundleItems->count() > 0)
                                        <div class="bundle-item" style="margin-top: 0.5rem;">
                                            Contains {{ $item->product->bundleItems->count() }} items:
                                            @foreach($item->product->bundleItems as $bundleItem)
                                                <div>Item ID: {{ $bundleItem->item_id }} (x{{ $bundleItem->qty_unit }})</div>
                                            @endforeach
                                        </div>
                                    @elseif($item->product)
                                        <div class="text-sm text-muted">Item ID: {{ $item->product->item_id }}</div>
                                    @endif
                                </td>
                                <td>{{ $item->qty_units }}x</td>
                                <td>${{ number_format($item->price, 2) }}</td>
                                <td>
                                    @if($item->claimed)
                                        <span class="status-badge claimed"><i class="fas fa-check"></i> Claimed</span>
                                    @else
                                        <span class="status-badge ready"><i class="fas fa-gift"></i> Ready</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Claim Instructions -->
        <div class="info-box success">
            <i class="fas fa-gift" style="font-size: 1.5rem; color: #22c55e; flex-shrink: 0;"></i>
            <div>
                <h4 class="font-bold mb-2" style="color: #22c55e;">How to Claim Your Items</h4>
                <div style="color: #22c55e; font-size: 0.875rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <p>1. Login to the game using the username: <strong>{{ $order->username }}</strong></p>
                    <p>2. Type the command: <code style="background: rgba(34, 197, 94, 0.2); padding: 0.25rem 0.5rem; border-radius: 4px;">::claim</code></p>
                    <p>3. Your items will be automatically added to your inventory!</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="no-print" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
            <a href="{{ route('payment.download-pdf', $order->id) }}" class="btn btn-primary">
                <i class="fas fa-download mr-2"></i>Download Receipt
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print mr-2"></i>Print Receipt
            </button>
            <a href="{{ route('store.index') }}" class="btn btn-outline">
                <i class="fas fa-store mr-2"></i>Back to Store
            </a>
        </div>

        <!-- Support Section -->
        <div class="glass-card text-center">
            <h3 class="text-primary font-bold mb-2" style="font-size: 1.125rem;">Need Help?</h3>
            <p class="text-muted mb-3">If you have any questions about your order or need assistance claiming your items, please contact our support team.</p>
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center;">
                <a href="mailto:support@aragon-rsps.com" class="btn btn-dark">
                    <i class="fas fa-envelope mr-2"></i>Email Support
                </a>
                <a href="{{ config('services.discord.invite_url', '#') }}" target="_blank" class="btn btn-dark">
                    <i class="fab fa-discord mr-2"></i>Discord Support
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
