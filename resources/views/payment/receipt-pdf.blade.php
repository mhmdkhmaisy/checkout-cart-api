<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt - Order #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #e8e8e8;
            margin: 0;
            padding: 20px;
            background-color: #0a0a0a;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #d40000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #d40000;
            margin-bottom: 10px;
        }
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #ff0000;
        }
        .order-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .info-section {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }
        .info-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
            color: #d40000;
        }
        .info-item {
            margin-bottom: 8px;
            color: #c0c0c0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
            color: #e8e8e8;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #065f46;
            color: #d1fae5;
        }
        .status-pending {
            background-color: #92400e;
            color: #fef3c7;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #1a1a1a;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #333333;
            padding: 12px;
            text-align: left;
        }
        .items-table th {
            background-color: #333333;
            font-weight: bold;
            color: #d40000;
        }
        .items-table td {
            color: #c0c0c0;
        }
        .total-row {
            background-color: #333333;
            font-weight: bold;
            color: #e8e8e8;
        }
        .instructions {
            background-color: #1a4d1a;
            border: 1px solid #065f46;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .instructions-title {
            font-weight: bold;
            color: #d1fae5;
            margin-bottom: 10px;
        }
        .command-code {
            background-color: #333333;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-weight: bold;
            color: #ff0000;
        }
        .footer {
            border-top: 1px solid #333333;
            padding-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #c0c0c0;
        }
        .coinbase-info {
            background-color: #1a2332;
            border: 1px solid #1e40af;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .coinbase-title {
            font-weight: bold;
            color: #bfdbfe;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">⚔️ {{ config('app.name', 'Aragon RSPS') }}</div>
        <div class="receipt-title">Payment Receipt</div>
        <div>Generated on {{ now()->format('F j, Y g:i A') }}</div>
    </div>

    <div class="order-info">
        <div class="info-section">
            <div class="info-title">Order Information</div>
            <div class="info-item">
                <span class="info-label">Order ID:</span>
                #{{ $order->id }}
            </div>
            <div class="info-item">
                <span class="info-label">Character Name:</span>
                {{ $order->username }}
            </div>
            <div class="info-item">
                <span class="info-label">Payment Method:</span>
                {{ ucfirst($order->payment_method) }}
            </div>
            <div class="info-item">
                <span class="info-label">Order Date:</span>
                {{ $order->created_at->format('M j, Y g:i A') }}
            </div>
        </div>
        
        <div class="info-section">
            <div class="info-title">Payment Information</div>
            <div class="info-item">
                <span class="info-label">Payment ID:</span>
                {{ $order->payment_id }}
            </div>
            <div class="info-item">
                <span class="info-label">Status:</span>
                @if($order->status === 'paid')
                    <span class="status-badge status-paid">Paid</span>
                @elseif($order->status === 'pending')
                    <span class="status-badge status-pending">Pending</span>
                @else
                    <span class="status-badge">{{ ucfirst($order->status) }}</span>
                @endif
            </div>
            <div class="info-item">
                <span class="info-label">Total Amount:</span>
                ${{ number_format($order->amount, 2) }} {{ $order->currency ?? 'USD' }}
            </div>
            @if($order->paypal_capture_id)
                <div class="info-item">
                    <span class="info-label">Capture ID:</span>
                    {{ $order->paypal_capture_id }}
                </div>
            @endif
        </div>
    </div>

    @if($order->status === 'pending')
        <div style="background-color: #92400e; border: 1px solid #f59e0b; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
            <div style="font-weight: bold; color: #fef3c7; margin-bottom: 10px;">⚠️ Payment Confirmation Pending</div>
            @if($order->payment_method === 'paypal')
                <p style="color: #fef3c7; margin: 0;">We are waiting for confirmation from PayPal. This usually takes a few minutes.</p>
            @else
                <p style="color: #fef3c7; margin: 0;">We are waiting for confirmation from Coinbase. This may take up to 15 minutes for blockchain confirmation.</p>
            @endif
        </div>
    @endif

    @if($order->payment_method === 'coinbase' && $trackerUrl)
        <div class="coinbase-info">
            <div class="coinbase-title">🔗 Coinbase Payment Tracker</div>
            <p style="margin: 0; color: #bfdbfe;">
                Track your payment status: <strong>{{ $trackerUrl }}</strong>
            </p>
        </div>
    @endif

    <table class="items-table">
        <thead>
            <tr>
                <th>Item Details</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->product)
                            <br><small>Product ID: {{ $item->product_id }}</small>
                        @endif
                    </td>
                    <td>{{ $item->qty_units }}</td>
                    <td>${{ number_format($item->price, 2) }}</td>
                    <td>${{ number_format($item->price * $item->qty_units, 2) }}</td>
                    <td>
                        @if($item->claimed)
                            ✅ Claimed
                        @else
                            🎁 Ready to Claim
                        @endif
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3"><strong>Total Amount</strong></td>
                <td><strong>${{ number_format($order->amount, 2) }} {{ $order->currency ?? 'USD' }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="instructions">
        <div class="instructions-title">🎮 How to Claim Your Items</div>
        <p><strong>1.</strong> Login to the game using the username: <strong>{{ $order->username }}</strong></p>
        <p><strong>2.</strong> Type the command: <span class="command-code">::claim</span></p>
        <p><strong>3.</strong> Your items will be automatically added to your inventory!</p>
    </div>

    <div class="footer">
        <p><strong>⚔️ {{ config('app.name', 'Aragon RSPS') }}</strong></p>
        <p>Thank you for your purchase! If you need assistance, please contact our support team.</p>
        <p>Email: support@aragon-rsps.com | Discord: {{ config('services.discord.invite_url', 'discord.gg/aragon') }}</p>
        <p>This receipt was generated automatically on {{ now()->format('F j, Y \\a\\t g:i A T') }}</p>
    </div>
</body>
</html>