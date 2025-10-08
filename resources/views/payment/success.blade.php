<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <i class="fas fa-check text-green-600 text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
            <p class="text-gray-600">Thank you for your purchase. Your order has been processed.</p>
        </div>

        @if($error)
            <!-- Error State -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-4"></i>
                <h2 class="text-xl font-semibold text-red-800 mb-2">Order Not Found</h2>
                <p class="text-red-600">{{ $error }}</p>
                <p class="text-sm text-red-500 mt-2">Please contact support if you believe this is an error.</p>
            </div>
        @else
            <!-- Order Details -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-gray-50 px-6 py-4 border-b">
                    <h2 class="text-xl font-semibold text-gray-900">Order Details</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Order Information</h3>
                            <div class="space-y-2">
                                <p><span class="font-medium">Order ID:</span> #{{ $order->id }}</p>
                                <p><span class="font-medium">Username:</span> {{ $order->username }}</p>
                                <p><span class="font-medium">Payment Method:</span> 
                                    <span class="capitalize">{{ $order->payment_method }}</span>
                                </p>
                                <p><span class="font-medium">Total Amount:</span> 
                                    ${{ number_format($order->amount, 2) }} {{ $order->currency ?? 'USD' }}
                                </p>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Payment Information</h3>
                            <div class="space-y-2">
                                <p><span class="font-medium">Payment ID:</span> 
                                    <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $order->payment_id }}</code>
                                </p>
                                <p><span class="font-medium">Status:</span> 
                                    @if($order->status === 'paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Paid
                                        </span>
                                    @elseif($order->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending Confirmation
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    @endif
                                </p>
                                <p><span class="font-medium">Order Date:</span> 
                                    {{ $order->created_at->format('M j, Y g:i A') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Status-specific Messages -->
                    @if($order->status === 'pending')
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-yellow-500 mt-1 mr-3"></i>
                                <div>
                                    <h4 class="font-medium text-yellow-800 mb-1">Waiting for Payment Confirmation</h4>
                                    @if($order->payment_method === 'paypal')
                                        <p class="text-yellow-700 text-sm">We are waiting for confirmation from PayPal. This usually takes a few minutes.</p>
                                    @else
                                        <p class="text-yellow-700 text-sm">We are waiting for confirmation from Coinbase. This may take up to 15 minutes for blockchain confirmation.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Coinbase Tracker Link -->
                    @if($order->payment_method === 'coinbase' && $trackerUrl)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <div class="flex items-start">
                                <i class="fab fa-bitcoin text-blue-500 mt-1 mr-3"></i>
                                <div class="flex-1">
                                    <h4 class="font-medium text-blue-800 mb-1">Track Your Coinbase Payment</h4>
                                    <p class="text-blue-700 text-sm mb-3">You can track your payment status directly on Coinbase Commerce:</p>
                                    <a href="{{ $trackerUrl }}" target="_blank" 
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-external-link-alt mr-2"></i>
                                        View Payment on Coinbase
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Items List -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Items Purchased</h3>
                        <div class="bg-gray-50 rounded-lg overflow-hidden">
                            <table class="min-w-full">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-gray-900">{{ $item->product_name }}</div>
                                                @if($item->product)
                                                    <div class="text-sm text-gray-500">Product ID: {{ $item->product_id }}</div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-gray-900">{{ $item->qty_units }}</td>
                                            <td class="px-4 py-3 text-gray-900">${{ number_format($item->price, 2) }}</td>
                                            <td class="px-4 py-3">
                                                @if($item->claimed)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check mr-1"></i>
                                                        Claimed
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        <i class="fas fa-gift mr-1"></i>
                                                        Ready to Claim
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Claim Instructions -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-gift text-green-500 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-medium text-green-800 mb-2">How to Claim Your Items</h4>
                                <div class="text-green-700 text-sm space-y-1">
                                    <p>1. Login to the game using the username: <strong>{{ $order->username }}</strong></p>
                                    <p>2. Type the command: <code class="bg-green-100 px-2 py-1 rounded">::claim</code></p>
                                    <p>3. Your items will be automatically added to your account!</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('payment.download-pdf', $order->id) }}" 
                           class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-download mr-2"></i>
                            Download Receipt (PDF)
                        </a>
                        
                        <button onclick="window.print()" 
                                class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white font-medium rounded-md hover:bg-gray-700 transition-colors">
                            <i class="fas fa-print mr-2"></i>
                            Print Receipt
                        </button>
                        
                        <a href="/" 
                           class="inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white font-medium rounded-md hover:bg-green-700 transition-colors">
                            <i class="fas fa-home mr-2"></i>
                            Back to Store
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Support Section -->
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Need Help?</h3>
            <p class="text-gray-600 mb-4">If you have any questions about your order or need assistance claiming your items, please contact our support team.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="mailto:support@example.com" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-envelope mr-2"></i>
                    Email Support
                </a>
                <a href="#" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                    <i class="fab fa-discord mr-2"></i>
                    Discord Support
                </a>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .shadow-md { box-shadow: none !important; }
        }
    </style>
</body>
</html>