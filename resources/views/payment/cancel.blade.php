@extends('layouts.public')

@section('title', 'Payment Cancelled')

@push('styles')
<style>
    .cancel-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .cancel-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border-radius: 50%;
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
        margin-bottom: 1rem;
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
    
    .info-box.error {
        border-left-color: #ef4444;
        background: rgba(239, 68, 68, 0.08);
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
    
    .order-table tbody tr {
        opacity: 0.6;
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
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto fade-in-up">
    <!-- Cancel Header -->
    <div class="cancel-header">
        <div class="cancel-badge">
            <i class="fas fa-times text-white" style="font-size: 2rem;"></i>
        </div>
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">Payment Cancelled</h1>
        <p class="text-muted" style="font-size: 1.125rem;">Your payment was cancelled and no charges were made.</p>
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
        <!-- Cancellation Notice -->
        <div class="info-box error mb-4">
            <i class="fas fa-info-circle" style="font-size: 1.5rem; color: #ef4444; flex-shrink: 0;"></i>
            <div>
                <h4 class="font-bold mb-1" style="color: #ef4444;">Payment Cancelled</h4>
                <p class="text-sm" style="color: #ef4444;">Your payment was cancelled and no charges have been made to your account. The order has been marked as failed and no items will be delivered.</p>
            </div>
        </div>

        <!-- Order Details Card -->
        <div class="glass-card mb-4">
            <h2 class="text-primary" style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">
                <i class="fas fa-receipt mr-2"></i>Cancelled Order Details
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
                    <h3 class="text-primary font-bold mb-2" style="font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px;">Status Information</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <p><span style="color: var(--text-muted);">Payment ID:</span><br>
                            <code style="background: rgba(10, 10, 10, 0.6); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; display: inline-block; margin-top: 0.25rem;">{{ $order->payment_id }}</code>
                        </p>
                        <p><span style="color: var(--text-muted);">Status:</span> 
                            <span class="status-badge cancelled"><i class="fas fa-times-circle"></i> {{ ucfirst($order->status) }}</span>
                        </p>
                        <p><span style="color: var(--text-muted);">Order Date:</span> <strong>{{ $order->created_at->format('M j, Y g:i A') }}</strong></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items in Cancelled Order -->
        <div class="glass-card mb-4">
            <h3 class="text-primary" style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">
                <i class="fas fa-shopping-bag mr-2"></i>Items in Cancelled Order
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
                                    <span class="status-badge cancelled"><i class="fas fa-times"></i> Cancelled</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- What's Next -->
        <div class="info-box info mb-4">
            <i class="fas fa-lightbulb" style="font-size: 1.5rem; color: #3b82f6; flex-shrink: 0;"></i>
            <div>
                <h4 class="font-bold mb-2" style="color: #3b82f6;">What's Next?</h4>
                <div style="color: #3b82f6; font-size: 0.875rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <p>• You can try the payment again by returning to the store</p>
                    <p>• No charges were made to your payment method</p>
                    <p>• Your cart items are still available for purchase</p>
                    <p>• Contact support if you experienced any issues during checkout</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
            <a href="{{ route('store.index') }}" class="btn btn-primary">
                <i class="fas fa-shopping-cart mr-2"></i>Return to Store
            </a>
            <a href="{{ route('store.index') }}" class="btn btn-outline">
                <i class="fas fa-redo mr-2"></i>Try Payment Again
            </a>
        </div>

        <!-- Support Section -->
        <div class="glass-card text-center">
            <h3 class="text-primary font-bold mb-2" style="font-size: 1.125rem;">Need Help?</h3>
            <p class="text-muted mb-3">If you experienced issues during checkout or have questions about our products, we're here to help!</p>
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center;">
                <a href="mailto:support@example.com" class="btn btn-dark">
                    <i class="fas fa-envelope mr-2"></i>Email Support
                </a>
                <a href="#" class="btn btn-dark">
                    <i class="fab fa-discord mr-2"></i>Discord Support
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
