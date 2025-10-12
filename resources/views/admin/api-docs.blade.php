@extends('admin.layout')

@section('title', 'API Documentation - RSPS System Admin')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="glass-effect rounded-lg shadow-lg p-6 border border-dragon-border">
        <h1 class="text-3xl font-bold text-dragon-red dragon-text-glow mb-8">Complete API Documentation</h1>
        
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
                <p class="text-dragon-silver-dark mb-3">Check claimable items for a player</p>
                
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
        </div>

        <!-- Cache Management API -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-dragon-red mb-4">Cache Management API</h2>
            
            <!-- Get Cache Manifest -->
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">GET /api/cache/manifest</h3>
                <p class="text-dragon-silver-dark mb-3">Get complete cache manifest with directory structure</p>
                
                <div class="mb-3">
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Request:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>GET /api/cache/manifest</code></pre>
                </div>
                
                <div>
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Response:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>{
  "version": "20241011154332",
  "generated_at": "2024-10-11T15:43:32.367721Z",
  "total_files": 150,
  "total_directories": 25,
  "total_size": 52428800,
  "structure": {
    "preserve_paths": true,
    "directory_tree": [...],
    "flat_files": [...]
  },
  "metadata": {
    "format_version": "2.0",
    "supports_directory_structure": true
  }
}</code></pre>
                </div>
            </div>

            <!-- Download Cache Bundle -->
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">GET /api/cache/download</h3>
                <p class="text-dragon-silver-dark mb-3">Download cache files as compressed bundle with directory structure preserved</p>
                
                <div class="mb-3">
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Parameters:</h4>
                    <ul class="text-dragon-silver-dark list-disc list-inside mb-3">
                        <li><code>mode</code> - "full" (all files) or "selective" (specific files/paths)</li>
                        <li><code>files</code> - Comma-separated list of specific filenames</li>
                        <li><code>paths</code> - Comma-separated list of directory paths</li>
                        <li><code>preserve_structure</code> - "true" or "false" (default: true)</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Examples:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code># Download all files with structure
GET /api/cache/download?mode=full&preserve_structure=true

# Download specific directory
GET /api/cache/download?mode=selective&paths=models/weapons

# Download specific files (flattened)
GET /api/cache/download?mode=selective&files=config.dat,items.dat&preserve_structure=false</code></pre>
                </div>
            </div>

            <!-- Get Directory Tree -->
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">GET /api/cache/directory-tree</h3>
                <p class="text-dragon-silver-dark mb-3">Get hierarchical directory structure</p>
                
                <div>
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Response:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>{
  "success": true,
  "data": [
    {
      "type": "directory",
      "name": "models",
      "path": "models",
      "children": [
        {
          "type": "file",
          "name": "player.dat",
          "path": "models/player.dat",
          "size": 1024,
          "hash": "abc123..."
        }
      ]
    }
  ]
}</code></pre>
                </div>
            </div>

            <!-- Search Cache Files -->
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">GET /api/cache/search</h3>
                <p class="text-dragon-silver-dark mb-3">Search files and directories by name or path</p>
                
                <div class="mb-3">
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Parameters:</h4>
                    <ul class="text-dragon-silver-dark list-disc list-inside mb-3">
                        <li><code>q</code> - Search query (required)</li>
                        <li><code>type</code> - "all", "file", or "directory" (default: all)</li>
                        <li><code>extension</code> - Filter by file extension</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Example:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>GET /api/cache/search?q=weapon&type=file&extension=dat</code></pre>
                </div>
            </div>

            <!-- Cache Statistics -->
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">GET /api/cache/stats</h3>
                <p class="text-dragon-silver-dark mb-3">Get detailed cache statistics and analytics</p>
                
                <div>
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Response:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>{
  "total_files": 150,
  "total_directories": 25,
  "total_size": 52428800,
  "files_by_extension": {
    "dat": {"count": 50, "total_size": 25600000},
    "idx": {"count": 25, "total_size": 1280000}
  },
  "files_by_type": {
    "file": {"count": 150, "total_size": 52428800},
    "directory": {"count": 25, "total_size": 0}
  },
  "directory_depth_stats": {
    "0": 10,
    "1": 75,
    "2": 50,
    "3": 15
  }
}</code></pre>
                </div>
            </div>

            <!-- Download Individual File -->
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">GET /api/cache/file/{filename}</h3>
                <p class="text-dragon-silver-dark mb-3">Download a specific cache file</p>
                
                <div class="mb-3">
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Parameters:</h4>
                    <ul class="text-dragon-silver-dark list-disc list-inside mb-3">
                        <li><code>path</code> - Optional relative path for files with same names in different directories</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Examples:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code># Download file from root
GET /api/cache/file/config.dat

# Download file from specific path
GET /api/cache/file/player.dat?path=models/characters/player.dat</code></pre>
                </div>
            </div>
        </div>

        <!-- Vote System API -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-dragon-red mb-4">Vote System API</h2>
            
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">Vote Endpoints</h3>
                <p class="text-dragon-silver-dark mb-3">Public voting system integration</p>
                
                <div>
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Available Routes:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>GET  /vote                    - Vote homepage
POST /vote/set-username      - Set voting username
POST /vote/{site}           - Submit vote to specific site
ANY  /vote/callback         - Vote callback handler
GET  /vote/stats            - Vote statistics
GET  /vote/user-votes       - Get user's vote history</code></pre>
                </div>
            </div>
        </div>

        <!-- Client Download API -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-dragon-red mb-4">Client Download API</h2>
            
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">Client Management</h3>
                <p class="text-dragon-silver-dark mb-3">Game client distribution system</p>
                
                <div>
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Endpoints:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>GET /download/{os}/{version}  - Download client for specific OS/version
GET /manifest.json           - Client version manifest
GET /play                    - Play page with client downloads</code></pre>
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

        <!-- Admin API -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-dragon-red mb-4">Admin API</h2>
            
            <div class="bg-dragon-surface rounded-lg p-4 mb-4 border border-dragon-border">
                <h3 class="text-xl font-semibold text-dragon-silver mb-2">Administrative Endpoints</h3>
                <p class="text-dragon-silver-dark mb-3">Server administration and monitoring</p>
                
                <div>
                    <h4 class="text-lg font-medium text-dragon-red mb-2">Available Routes:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-dragon-silver overflow-x-auto border border-dragon-border"><code>GET   /api/admin/orders/logs           - Order activity logs
GET   /api/admin/orders/{id}/events    - Specific order events
GET   /api/admin/orders/stats          - Order statistics
PATCH /api/admin/orders/{id}/status    - Update order status</code></pre>
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
  "message": "Invalid API key"
}</code></pre>
                </div>
                
                <div class="mb-4">
                    <h4 class="text-lg font-medium text-red-300 mb-2">404 Not Found:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-red-300 overflow-x-auto border border-dragon-border"><code>{
  "success": false,
  "error": "Not Found",
  "message": "Resource not found"
}</code></pre>
                </div>
                
                <div class="mb-4">
                    <h4 class="text-lg font-medium text-red-300 mb-2">422 Validation Error:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-red-300 overflow-x-auto border border-dragon-border"><code>{
  "success": false,
  "error": "Validation failed",
  "errors": {
    "player_name": ["The player name field is required."],
    "products": ["The products field is required."]
  }
}</code></pre>
                </div>
                
                <div>
                    <h4 class="text-lg font-medium text-red-300 mb-2">500 Server Error:</h4>
                    <pre class="bg-dragon-black p-3 rounded text-red-300 overflow-x-auto border border-dragon-border"><code>{
  "success": false,
  "error": "Internal server error",
  "message": "Server encountered an error"
}</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection