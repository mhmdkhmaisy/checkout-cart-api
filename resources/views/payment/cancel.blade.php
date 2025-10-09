<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'dragon-black': '#0a0a0a',
                        'dragon-surface': '#1a1a1a',
                        'dragon-red': '#d40000',
                        'dragon-red-bright': '#ff0000',
                        'dragon-silver': '#e8e8e8',
                        'dragon-silver-dark': '#c0c0c0',
                        'dragon-border': '#333333'
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-red {
            background: linear-gradient(135deg, #ff0000, #d40000);
        }
        .dragon-glow {
            box-shadow: 0 0 20px rgba(212, 0, 0, 0.3);
        }
    </style>
</head>
<body class="bg-dragon-black text-dragon-silver min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-600 rounded-full mb-4 dragon-glow">
                <i class="fas fa-times text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-dragon-silver mb-2">Payment Cancelled</h1>
            <p class="text-dragon-silver-dark">Your payment was cancelled and no charges were made to the dragon's hoard.</p>
        </div>

        @if($error)
            <!-- Error State -->
            <div class="bg-red-600 border border-red-500 rounded-lg p-6 text-center mb-6">
                <i class="fas fa-exclamation-triangle text-red-200 text-3xl mb-4"></i>
                <h2 class="text-xl font-semibold text-red-100 mb-2">Order Not Found</h2>
                <p class="text-red-200">{{ $error }}</p>
                <p class="text-sm text-red-300 mt-2">Please contact support if you believe this is an error.</p>
            </div>
        @else
            <!-- Order Details -->
            <div class="bg-dragon-surface rounded-lg shadow-md overflow-hidden mb-6 border border-dragon-border dragon-glow">
                <div class="bg-dragon-black px-6 py-4 border-b border-dragon-border">
                    <h2 class="text-xl font-semibold text-dragon-red">Cancelled Order Details</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-sm font-medium text-dragon-red mb-2">Order Information</h3>
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
                            <h3 class="text-sm font-medium text-dragon-red mb-2">Status Information</h3>
                            <div class="space-y-2">
                                <p><span class="font-medium">Payment ID:</span> 
                                    <code class="bg-dragon-black px-2 py-1 rounded text-sm border border-dragon-border">{{ $order->payment_id }}</code>
                                </p>
                                <p><span class="font-medium">Status:</span> 
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-600 text-red-100">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </p>
                                <p><span class="font-medium">Order Date:</span> 
                                    {{ $order->created_at->format('M j, Y g:i A') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Cancellation Notice -->
                    <div class="bg-red-600 border border-red-500 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-red-200 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-medium text-red-100 mb-1">Payment Cancelled</h4>
                                <p class="text-red-200 text-sm">
                                    Your payment was cancelled and no charges have been made to your account. 
                                    The order has been marked as failed and no items will be delivered to the dragon's hoard.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-dragon-red mb-4">Items in Cancelled Order</h3>
                        <div class="bg-dragon-black rounded-lg overflow-hidden border border-dragon-border">
                            <table class="min-w-full">
                                <thead class="bg-dragon-surface border-b border-dragon-border">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-dragon-red uppercase">Item</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-dragon-red uppercase">Quantity</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-dragon-red uppercase">Price</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-dragon-red uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-dragon-border">
                                    @foreach($order->items as $item)
                                        <tr class="opacity-60">
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-dragon-silver">{{ $item->product_name }}</div>
                                                @if($item->product)
                                                    <div class="text-sm text-dragon-silver-dark">Product ID: {{ $item->product_id }}</div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-dragon-silver">{{ $item->qty_units }}</td>
                                            <td class="px-4 py-3 text-dragon-silver">${{ number_format($item->price, 2) }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-600 text-red-100">
                                                    <i class="fas fa-times mr-1"></i>
                                                    Cancelled
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- What's Next -->
                    <div class="bg-blue-600 border border-blue-500 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-lightbulb text-blue-200 mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-medium text-blue-100 mb-2">What's Next?</h4>
                                <div class="text-blue-200 text-sm space-y-1">
                                    <p>• You can try the payment again by returning to the dragon's store</p>
                                    <p>• No charges were made to your payment method</p>
                                    <p>• Your cart items are still available for purchase</p>
                                    <p>• Contact support if you experienced any issues during checkout</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/" 
                           class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Return to Dragon's Store
                        </a>
                        
                        <a href="#" 
                           class="inline-flex items-center justify-center px-6 py-3 gradient-red text-dragon-silver font-medium rounded-md hover:opacity-90 transition-opacity">
                            <i class="fas fa-redo mr-2"></i>
                            Try Payment Again
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Support Section -->
        <div class="bg-dragon-surface rounded-lg shadow-md p-6 text-center border border-dragon-border dragon-glow">
            <h3 class="text-lg font-medium text-dragon-red mb-2">Need Help?</h3>
            <p class="text-dragon-silver-dark mb-4">If you experienced issues during checkout or have questions about our dragon products, we're here to help!</p>
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
</body>
</html>