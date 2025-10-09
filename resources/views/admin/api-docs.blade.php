@extends('admin.layout')

@section('title', 'API Documentation - Aragon RSPS Admin')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="glass-effect rounded-lg shadow-lg p-6 border border-dragon-border">
        <h1 class="text-3xl font-bold text-dragon-red dragon-text-glow mb-8">Dragon's API Documentation</h1>
        
        <!-- Authentication -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-dragon-red mb-4">Authentication</h2>
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <p class="text-dragon-silver-dark mb-2">All API requests require authentication using the <code class="bg-dragon-black px-2 py-1 rounded border border-dragon-border text-dragon-red">X-API-Key</code> header:</p>
                <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>X-API-Key: your-server-api-key</code></pre>
            </div>
        </div>

        <!-- Products API -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-dragon-red mb-4">Products API</h2>
            
            <!-- Get All Products -->
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">GET /api/products</h3>
                <p class="text-dragon-silver-dark mb-3">Retrieve all active products</p>
                
                <div class="mb-3">
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Request:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>GET /api/products
Headers:
  X-API-Key: your-server-api-key</code></pre>
                </div>
                
                <div>
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Response:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Dragon Scimitar",
      "price": "4.99",
      "is_active": true,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}</code></pre>
                </div>
            </div>
        </div>

        <!-- Checkout API -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-dragon-red mb-4">Checkout API</h2>
            
            <!-- Create Checkout -->
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">POST /api/checkout</h3>
                <p class="text-dragon-silver-dark mb-3">Create a new checkout session</p>
                
                <div class="mb-3">
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Request:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>POST /api/checkout
Headers:
  X-API-Key: your-server-api-key
  Content-Type: application/json

Body:
{
  "player_name": "DragonWarrior",
  "products": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ],
  "payment_method": "paypal"
}</code></pre>
                </div>
                
                <div>
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Response:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>{
  "success": true,
  "data": {
    "order_id": "abc123",
    "payment_url": "https://paypal.com/checkout/...",
    "total_amount": "9.98"
  }
}</code></pre>
                </div>
            </div>
        </div>

        <!-- Claim API -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-dragon-red mb-4">Claim API</h2>
            
            <!-- Check Claimable Items -->
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">GET /api/claim/{player_name}</h3>
                <p class="text-dragon-silver-dark mb-3">Check claimable items for a dragon warrior</p>
                
                <div class="mb-3">
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Request:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>GET /api/claim/DragonWarrior
Headers:
  X-API-Key: your-server-api-key</code></pre>
                </div>
                
                <div>
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Response:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>{
  "success": true,
  "data": {
    "player_name": "DragonWarrior",
    "claimable_items": [
      {
        "order_id": "abc123",
        "product_name": "Dragon Scimitar",
        "quantity": 2,
        "purchased_at": "2024-01-01T00:00:00.000000Z"
      }
    ]
  }
}</code></pre>
                </div>
            </div>

            <!-- Claim Items -->
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">POST /api/claim/{player_name}</h3>
                <p class="text-dragon-silver-dark mb-3">Claim items for a dragon warrior</p>
                
                <div class="mb-3">
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Request:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>POST /api/claim/DragonWarrior
Headers:
  X-API-Key: your-server-api-key
  Content-Type: application/json

Body:
{
  "order_ids": ["abc123", "def456"]
}</code></pre>
                </div>
                
                <div>
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Response:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>{
  "success": true,
  "data": {
    "claimed_orders": ["abc123", "def456"],
    "message": "Items successfully claimed by dragon warrior"
  }
}</code></pre>
                </div>
            </div>
        </div>

        <!-- Webhooks -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-dragon-red mb-4">Webhooks</h2>
            
            <!-- PayPal Webhook -->
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">POST /api/webhooks/paypal</h3>
                <p class="text-dragon-silver-dark mb-3">PayPal payment completion webhook</p>
                
                <div>
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Payload Example:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>{
  "event_type": "PAYMENT.CAPTURE.COMPLETED",
  "resource": {
    "id": "paypal_payment_id",
    "status": "COMPLETED",
    "amount": {
      "currency_code": "USD",
      "value": "9.98"
    }
  }
}</code></pre>
                </div>
            </div>

            <!-- Coinbase Webhook -->
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">POST /api/webhooks/coinbase</h3>
                <p class="text-dragon-silver-dark mb-3">Coinbase Commerce payment completion webhook</p>
                
                <div>
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Payload Example:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>{
  "event": {
    "type": "charge:confirmed",
    "data": {
      "id": "coinbase_charge_id",
      "code": "ABC123DEF",
      "pricing": {
        "local": {
          "amount": "9.98",
          "currency": "USD"
        }
      }
    }
  }
}</code></pre>
                </div>
            </div>
        </div>

        <!-- Error Responses -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-dragon-red mb-4">Error Responses</h2>
            
            <div class="bg-dragon-surface rounded-lg p-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">Common Error Formats</h3>
                
                <div class="mb-4">
                    <h4 class="text-lg font-medium text-red-300 mb-2">401 Unauthorized:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-red-300 overflow-x-auto border border-dragon-border"><code>{
  "success": false,
  "error": "Unauthorized",
  "message": "Invalid API key - Dragon's hoard is protected"
}</code></pre>
                </div>
                
                <div class="mb-4">
                    <h4 class="text-lg font-medium text-red-300 mb-2">422 Validation Error:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-red-300 overflow-x-auto border border-dragon-border"><code>{
  "success": false,
  "error": "Validation failed",
  "errors": {
    "player_name": ["The dragon warrior name field is required."],
    "products": ["The products field is required."]
  }
}</code></pre>
                </div>
                
                <div>
                    <h4 class="text-lg font-medium text-red-300 mb-2">500 Server Error:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-red-300 overflow-x-auto border border-dragon-border"><code>{
  "success": false,
  "error": "Internal server error",
  "message": "The dragon's server encountered an error"
}</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection