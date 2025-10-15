@extends('admin.layout')

@section('title', 'API Documentation - RSPS System Admin')

@section('content')
<style>
    .sidebar-nav {
        position: sticky;
        top: 20px;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
    }
    .sidebar-nav a {
        transition: all 0.2s ease;
    }
    .sidebar-nav a:hover {
        background: rgba(212, 0, 0, 0.1);
        border-left: 3px solid #d40000;
        padding-left: 1rem;
    }
    .sidebar-nav a.active {
        background: rgba(212, 0, 0, 0.15);
        border-left: 3px solid #d40000;
        color: #d40000;
        padding-left: 1rem;
    }
    .method-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 0.375rem;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .method-get { background: #10b981; color: white; }
    .method-post { background: #3b82f6; color: white; }
    .method-patch { background: #f59e0b; color: white; }
    .method-delete { background: #ef4444; color: white; }
    .method-any { background: #6366f1; color: white; }
    
    .endpoint-card {
        border-left: 3px solid #333333;
        transition: all 0.3s ease;
    }
    .endpoint-card:hover {
        border-left-color: #d40000;
        background: rgba(212, 0, 0, 0.05);
    }
    
    code {
        background: #0a0a0a;
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        border: 1px solid #333333;
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
    }
    
    pre code {
        display: block;
        padding: 1rem;
        overflow-x: auto;
        line-height: 1.5;
    }
    
    .param-required {
        color: #ef4444;
        font-weight: 600;
        font-size: 0.75rem;
    }
    
    .param-optional {
        color: #6b7280;
        font-weight: 600;
        font-size: 0.75rem;
    }
    
    .response-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
        border-bottom: 2px solid #333333;
    }
    
    .response-tab {
        padding: 0.5rem 1rem;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s ease;
    }
    
    .response-tab:hover {
        color: #d40000;
    }
    
    .response-tab.active {
        border-bottom-color: #d40000;
        color: #d40000;
    }
</style>

<div class="container mx-auto px-6 py-8">
    <div class="flex gap-8">
        <!-- Sidebar Navigation -->
        <div class="w-64 flex-shrink-0">
            <div class="sidebar-nav glass-effect rounded-lg p-4 border border-dragon-border">
                <h3 class="text-lg font-bold text-dragon-red mb-4 flex items-center">
                    <i class="fas fa-book mr-2"></i>API Reference
                </h3>
                <nav class="space-y-1">
                    <a href="#overview" class="block py-2 text-sm text-dragon-silver-dark hover:text-dragon-red">
                        <i class="fas fa-info-circle mr-2 w-4"></i>Overview
                    </a>
                    <a href="#authentication" class="block py-2 text-sm text-dragon-silver-dark hover:text-dragon-red">
                        <i class="fas fa-key mr-2 w-4"></i>Authentication
                    </a>
                    <a href="#products" class="block py-2 text-sm text-dragon-silver-dark hover:text-dragon-red">
                        <i class="fas fa-box mr-2 w-4"></i>Products
                    </a>
                    <a href="#checkout" class="block py-2 text-sm text-dragon-silver-dark hover:text-dragon-red">
                        <i class="fas fa-shopping-cart mr-2 w-4"></i>Checkout
                    </a>
                    <a href="#claim" class="block py-2 text-sm text-dragon-silver-dark hover:text-dragon-red">
                        <i class="fas fa-gift mr-2 w-4"></i>Claim System
                    </a>
                    <a href="#cache" class="block py-2 text-sm text-dragon-silver-dark hover:text-dragon-red">
                        <i class="fas fa-database mr-2 w-4"></i>Cache Management
                    </a>
                    <a href="#vote" class="block py-2 text-sm text-dragon-silver-dark hover:text-dragon-red">
                        <i class="fas fa-vote-yea mr-2 w-4"></i>Vote System
                    </a>
                    <a href="#client" class="block py-2 text-sm text-dragon-silver-dark hover:text-dragon-red">
                        <i class="fas fa-download mr-2 w-4"></i>Client Download
                    </a>
                    <a href="#webhooks" class="block py-2 text-sm text-dragon-silver-dark hover:text-dragon-red">
                        <i class="fas fa-webhook mr-2 w-4"></i>Webhooks
                    </a>
                    <a href="#admin-api" class="block py-2 text-sm text-dragon-silver-dark hover:text-dragon-red">
                        <i class="fas fa-user-shield mr-2 w-4"></i>Admin API
                    </a>
                    <a href="#errors" class="block py-2 text-sm text-dragon-silver-dark hover:text-dragon-red">
                        <i class="fas fa-exclamation-triangle mr-2 w-4"></i>Error Handling
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 min-w-0">
            <div class="glass-effect rounded-lg shadow-lg p-8 border border-dragon-border">
                
                <!-- Overview -->
                <section id="overview" class="mb-12">
                    <h1 class="text-4xl font-bold text-dragon-red dragon-text-glow mb-4 flex items-center">
                        <i class="fas fa-code mr-3"></i>API Documentation
                    </h1>
                    <p class="text-dragon-silver-dark text-lg mb-4">
                        Welcome to the RSPS System API documentation. This comprehensive guide provides detailed information about all available endpoints, request/response formats, and integration examples.
                    </p>
                    <div class="bg-blue-500/10 border-l-4 border-blue-500 p-4 rounded">
                        <p class="text-dragon-silver"><strong>Base URL:</strong> <code class="text-blue-400">{{ url('/api') }}</code></p>
                        <p class="text-dragon-silver mt-2"><strong>API Version:</strong> <code class="text-blue-400">v1</code></p>
                    </div>
                </section>

                <!-- Authentication -->
                <section id="authentication" class="mb-12">
                    <h2 class="text-3xl font-bold text-dragon-red mb-6 flex items-center">
                        <i class="fas fa-key mr-3"></i>Authentication
                    </h2>
                    <p class="text-dragon-silver-dark mb-4">
                        Most API endpoints require authentication using an API key. Include your API key in the request header:
                    </p>
                    <div class="bg-dragon-surface rounded-lg p-4 border border-dragon-border mb-4">
                        <h4 class="text-sm font-semibold text-dragon-silver mb-2 uppercase tracking-wide">Header</h4>
                        <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-green-400">X-API-Key: your-server-api-key</code></pre>
                    </div>
                    <div class="bg-yellow-500/10 border-l-4 border-yellow-500 p-4 rounded">
                        <p class="text-dragon-silver text-sm"><i class="fas fa-exclamation-triangle mr-2"></i><strong>Important:</strong> Keep your API key secure and never expose it in client-side code.</p>
                    </div>
                </section>

                <!-- Products API -->
                <section id="products" class="mb-12">
                    <h2 class="text-3xl font-bold text-dragon-red mb-6 flex items-center">
                        <i class="fas fa-box mr-3"></i>Products API
                    </h2>
                    
                    <!-- Get All Products -->
                    <div class="endpoint-card bg-dragon-surface rounded-lg p-6 mb-6 border border-dragon-border">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <span class="method-badge method-get">GET</span>
                                <code class="text-dragon-silver text-lg">/api/products</code>
                            </div>
                        </div>
                        
                        <p class="text-dragon-silver-dark mb-4">
                            Retrieves a list of all active products available for purchase. This endpoint returns product details including ID, name, pricing, and availability status.
                        </p>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Request Example</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-dragon-silver">GET {{ url('/api/products') }}

Headers:
  X-API-Key: your-server-api-key
  Accept: application/json</code></pre>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Response (200 OK)</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-green-400">{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Dragon Scimitar",
      "description": "A powerful curved sword",
      "price": "4.99",
      "currency": "USD",
      "is_active": true,
      "stock_quantity": 100,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Abyssal Whip",
      "description": "Legendary weapon from the Abyss",
      "price": "9.99",
      "currency": "USD",
      "is_active": true,
      "stock_quantity": 50,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "total": 2
}</code></pre>
                        </div>
                        
                        <div class="bg-dragon-black/50 rounded p-3 border border-dragon-border">
                            <h4 class="text-sm font-semibold text-dragon-silver-dark mb-2"><i class="fas fa-info-circle mr-2"></i>Notes</h4>
                            <ul class="text-sm text-dragon-silver-dark space-y-1 list-disc list-inside">
                                <li>Only active products (is_active = true) are returned</li>
                                <li>Prices are returned as strings to preserve decimal precision</li>
                                <li>Stock quantity indicates current availability</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Checkout API -->
                <section id="checkout" class="mb-12">
                    <h2 class="text-3xl font-bold text-dragon-red mb-6 flex items-center">
                        <i class="fas fa-shopping-cart mr-3"></i>Checkout API
                    </h2>
                    
                    <!-- Create Checkout Session -->
                    <div class="endpoint-card bg-dragon-surface rounded-lg p-6 mb-6 border border-dragon-border">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <span class="method-badge method-post">POST</span>
                                <code class="text-dragon-silver text-lg">/api/checkout</code>
                            </div>
                        </div>
                        
                        <p class="text-dragon-silver-dark mb-4">
                            Creates a new checkout session for purchasing products. This endpoint initiates the payment process and returns a payment URL for the selected payment method (PayPal or Coinbase).
                        </p>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Request Parameters</h4>
                            <div class="bg-dragon-black/50 rounded border border-dragon-border p-4 space-y-2">
                                <div class="flex items-start gap-3">
                                    <code class="text-blue-400">player_name</code>
                                    <span class="param-required">REQUIRED</span>
                                    <span class="text-dragon-silver-dark text-sm">string - In-game username of the player</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <code class="text-blue-400">products</code>
                                    <span class="param-required">REQUIRED</span>
                                    <span class="text-dragon-silver-dark text-sm">array - List of products with product_id and quantity</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <code class="text-blue-400">payment_method</code>
                                    <span class="param-required">REQUIRED</span>
                                    <span class="text-dragon-silver-dark text-sm">string - Payment method: "paypal" or "coinbase"</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <code class="text-blue-400">email</code>
                                    <span class="param-optional">OPTIONAL</span>
                                    <span class="text-dragon-silver-dark text-sm">string - Customer email for receipts</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Request Example</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-dragon-silver">POST {{ url('/api/checkout') }}

Headers:
  X-API-Key: your-server-api-key
  Content-Type: application/json
  Accept: application/json

Body:
{
  "player_name": "DragonWarrior",
  "email": "player@example.com",
  "products": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 3,
      "quantity": 1
    }
  ],
  "payment_method": "paypal"
}</code></pre>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Response (200 OK)</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-green-400">{
  "success": true,
  "data": {
    "order_id": "ORD-1234567890",
    "payment_url": "https://www.paypal.com/checkoutnow?token=...",
    "total_amount": "14.97",
    "currency": "USD",
    "expires_at": "2024-10-15T12:00:00Z",
    "items": [
      {
        "product_id": 1,
        "product_name": "Dragon Scimitar",
        "quantity": 2,
        "unit_price": "4.99",
        "subtotal": "9.98"
      },
      {
        "product_id": 3,
        "product_name": "Dragon Bones",
        "quantity": 1,
        "unit_price": "4.99",
        "subtotal": "4.99"
      }
    ]
  },
  "message": "Redirect customer to payment_url to complete purchase"
}</code></pre>
                        </div>
                        
                        <div class="bg-dragon-black/50 rounded p-3 border border-dragon-border">
                            <h4 class="text-sm font-semibold text-dragon-silver-dark mb-2"><i class="fas fa-lightbulb mr-2"></i>Integration Tips</h4>
                            <ul class="text-sm text-dragon-silver-dark space-y-1 list-disc list-inside">
                                <li>Redirect users to the <code>payment_url</code> to complete the transaction</li>
                                <li>Store the <code>order_id</code> for tracking order status</li>
                                <li>Payment sessions expire after 30 minutes by default</li>
                                <li>Webhooks will notify your system when payment is completed</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Success/Cancel Callbacks -->
                    <div class="endpoint-card bg-dragon-surface rounded-lg p-6 mb-6 border border-dragon-border">
                        <h3 class="text-xl font-semibold text-dragon-silver mb-3">Payment Callbacks</h3>
                        <p class="text-dragon-silver-dark mb-4">
                            After payment completion or cancellation, users are redirected to these callback URLs:
                        </p>
                        
                        <div class="space-y-4">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="method-badge method-get">GET</span>
                                    <code class="text-dragon-silver">/api/checkout/paypal/success</code>
                                </div>
                                <p class="text-sm text-dragon-silver-dark ml-20">PayPal payment successful callback</p>
                            </div>
                            
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="method-badge method-get">GET</span>
                                    <code class="text-dragon-silver">/api/checkout/paypal/cancel</code>
                                </div>
                                <p class="text-sm text-dragon-silver-dark ml-20">PayPal payment cancelled callback</p>
                            </div>
                            
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="method-badge method-get">GET</span>
                                    <code class="text-dragon-silver">/api/checkout/coinbase/success</code>
                                </div>
                                <p class="text-sm text-dragon-silver-dark ml-20">Coinbase payment successful callback</p>
                            </div>
                            
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="method-badge method-get">GET</span>
                                    <code class="text-dragon-silver">/api/checkout/coinbase/cancel</code>
                                </div>
                                <p class="text-sm text-dragon-silver-dark ml-20">Coinbase payment cancelled callback</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Claim API -->
                <section id="claim" class="mb-12">
                    <h2 class="text-3xl font-bold text-dragon-red mb-6 flex items-center">
                        <i class="fas fa-gift mr-3"></i>Claim System API
                    </h2>
                    
                    <div class="endpoint-card bg-dragon-surface rounded-lg p-6 mb-6 border border-dragon-border">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <span class="method-badge method-get">GET</span>
                                <code class="text-dragon-silver text-lg">/api/claim/{username}</code>
                            </div>
                        </div>
                        
                        <p class="text-dragon-silver-dark mb-4">
                            Checks if a player has any purchased items ready to claim. This endpoint should be called when a player logs into the game server to deliver purchased items.
                        </p>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Path Parameters</h4>
                            <div class="bg-dragon-black/50 rounded border border-dragon-border p-4">
                                <div class="flex items-start gap-3">
                                    <code class="text-blue-400">username</code>
                                    <span class="param-required">REQUIRED</span>
                                    <span class="text-dragon-silver-dark text-sm">string - In-game username of the player</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Request Example</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-dragon-silver">GET {{ url('/api/claim/DragonWarrior') }}

Headers:
  X-API-Key: your-server-api-key
  Accept: application/json</code></pre>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Response (200 OK) - With Items</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-green-400">{
  "success": true,
  "data": {
    "player_name": "DragonWarrior",
    "has_claimable_items": true,
    "claimable_items": [
      {
        "order_id": "ORD-1234567890",
        "product_id": 1,
        "product_name": "Dragon Scimitar",
        "quantity": 2,
        "purchased_at": "2024-10-15T10:30:00.000000Z",
        "metadata": {
          "item_id": 4151,
          "noted": false
        }
      },
      {
        "order_id": "ORD-1234567890",
        "product_id": 3,
        "product_name": "Dragon Bones",
        "quantity": 100,
        "purchased_at": "2024-10-15T10:30:00.000000Z",
        "metadata": {
          "item_id": 536,
          "noted": true
        }
      }
    ],
    "total_orders": 1
  },
  "message": "Player has claimable items pending delivery"
}</code></pre>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Response (200 OK) - No Items</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-dragon-silver">{
  "success": true,
  "data": {
    "player_name": "DragonWarrior",
    "has_claimable_items": false,
    "claimable_items": [],
    "total_orders": 0
  },
  "message": "No claimable items for this player"
}</code></pre>
                        </div>
                        
                        <div class="bg-dragon-black/50 rounded p-3 border border-dragon-border">
                            <h4 class="text-sm font-semibold text-dragon-silver-dark mb-2"><i class="fas fa-cog mr-2"></i>Implementation Guide</h4>
                            <ul class="text-sm text-dragon-silver-dark space-y-1 list-disc list-inside">
                                <li>Call this endpoint when a player logs in to your game server</li>
                                <li>Deliver items to the player's inventory or bank</li>
                                <li>Mark items as claimed after successful delivery</li>
                                <li>Handle inventory full scenarios gracefully</li>
                                <li>Log all claim attempts for audit purposes</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Cache Patch Management API -->
                <section id="cache" class="mb-12">
                    <h2 class="text-3xl font-bold text-dragon-red mb-6 flex items-center">
                        <i class="fas fa-database mr-3"></i>Cache Patch Management API
                    </h2>
                    <p class="text-dragon-silver-dark mb-6">
                        The Patch Management API provides intelligent incremental updates for game cache files. Using semantic versioning and delta patches, clients can efficiently download only changed files instead of the entire cache. These endpoints are designed for game launchers and clients that need efficient update mechanisms.
                    </p>
                    
                    <!-- Get Latest Version -->
                    <div class="endpoint-card bg-dragon-surface rounded-lg p-6 mb-6 border border-dragon-border">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <span class="method-badge method-get">GET</span>
                                <code class="text-dragon-silver text-lg">/admin/cache/patches/latest</code>
                            </div>
                        </div>
                        
                        <p class="text-dragon-silver-dark mb-4">
                            Returns the latest available patch version and a list of all patches. Use this to check what version is currently available.
                        </p>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Request Example</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-dragon-silver">GET {{ url('/admin/cache/patches/latest') }}</code></pre>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Response (200 OK)</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-green-400">{
  "success": true,
  "latest_version": "1.2.5",
  "patches": [
    {
      "id": 15,
      "version": "1.2.5",
      "is_base": false,
      "base_version": "1.2.0",
      "file_count": 23,
      "size": 5242880,
      "created_at": "2024-10-15T14:30:00Z"
    },
    {
      "id": 14,
      "version": "1.2.0",
      "is_base": true,
      "base_version": null,
      "file_count": 1547,
      "size": 157286400,
      "created_at": "2024-10-14T10:00:00Z"
    }
  ],
  "total_patches": 15
}</code></pre>
                        </div>
                        
                        <div class="bg-dragon-black/50 rounded p-3 border border-dragon-border">
                            <h4 class="text-sm font-semibold text-dragon-silver-dark mb-2"><i class="fas fa-info-circle mr-2"></i>Patch Types</h4>
                            <ul class="text-sm text-dragon-silver-dark space-y-1 list-disc list-inside">
                                <li><strong>Base Patch:</strong> Full cache snapshot containing all files</li>
                                <li><strong>Delta Patch:</strong> Incremental patch containing only changed/new files</li>
                                <li>Version format follows semantic versioning (major.minor.patch)</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Check for Updates -->
                    <div class="endpoint-card bg-dragon-surface rounded-lg p-6 mb-6 border border-dragon-border">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <span class="method-badge method-post">POST</span>
                                <code class="text-dragon-silver text-lg">/admin/cache/patches/check-updates</code>
                            </div>
                        </div>
                        
                        <p class="text-dragon-silver-dark mb-4">
                            Checks if updates are available by comparing the client's current version with the latest available version. Returns a list of patches needed to update.
                        </p>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Request Parameters</h4>
                            <div class="bg-dragon-black/50 rounded border border-dragon-border p-4 space-y-2">
                                <div class="flex items-start gap-3">
                                    <code class="text-blue-400">current_version</code>
                                    <span class="param-required">REQUIRED</span>
                                    <span class="text-dragon-silver-dark text-sm">string - Client's current cache version (e.g., "1.2.0")</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Request Example</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-dragon-silver">POST {{ url('/admin/cache/patches/check-updates') }}
Content-Type: application/json

{
  "current_version": "1.2.0"
}</code></pre>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Response (200 OK) - Update Available</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-green-400">{
  "success": true,
  "update_available": true,
  "current_version": "1.2.0",
  "latest_version": "1.2.5",
  "patches_needed": [
    {
      "id": 12,
      "version": "1.2.1",
      "size": 1048576,
      "download_url": "/admin/cache/patches/12/download"
    },
    {
      "id": 13,
      "version": "1.2.3",
      "size": 2097152,
      "download_url": "/admin/cache/patches/13/download"
    },
    {
      "id": 15,
      "version": "1.2.5",
      "size": 5242880,
      "download_url": "/admin/cache/patches/15/download"
    }
  ],
  "total_download_size": 8388608,
  "combined_download_url": "/admin/cache/patches/download-combined?from_version=1.2.0"
}</code></pre>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Response (200 OK) - No Update Needed</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-dragon-silver">{
  "success": true,
  "update_available": false,
  "current_version": "1.2.5",
  "latest_version": "1.2.5",
  "message": "Client is up to date"
}</code></pre>
                        </div>
                        
                        <div class="bg-dragon-black/50 rounded p-3 border border-dragon-border">
                            <h4 class="text-sm font-semibold text-dragon-silver-dark mb-2"><i class="fas fa-lightbulb mr-2"></i>Update Strategy</h4>
                            <ul class="text-sm text-dragon-silver-dark space-y-1 list-disc list-inside">
                                <li>Download patches individually using the <code>download_url</code> for each patch</li>
                                <li>Or use <code>combined_download_url</code> to get all patches in one ZIP file</li>
                                <li>Apply patches in order from oldest to newest</li>
                                <li>Each patch contains only files that changed since the previous version</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Download Individual Patch -->
                    <div class="endpoint-card bg-dragon-surface rounded-lg p-6 mb-6 border border-dragon-border">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="method-badge method-get">GET</span>
                            <code class="text-dragon-silver text-lg">/admin/cache/patches/{patch_id}/download</code>
                        </div>
                        
                        <p class="text-dragon-silver-dark mb-4">
                            Downloads a specific patch file by ID. Returns a ZIP archive containing all changed files for that version.
                        </p>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Path Parameters</h4>
                            <div class="bg-dragon-black/50 rounded border border-dragon-border p-4">
                                <div class="flex items-start gap-3">
                                    <code class="text-blue-400">patch_id</code>
                                    <span class="param-required">REQUIRED</span>
                                    <span class="text-dragon-silver-dark text-sm">integer - The patch ID to download</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Example</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-dragon-silver">GET {{ url('/admin/cache/patches/15/download') }}</code></pre>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Response</h4>
                            <p class="text-dragon-silver-dark text-sm mb-2">Binary ZIP file download with headers:</p>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-dragon-silver">Content-Type: application/zip
Content-Disposition: attachment; filename="cache-patch-1.2.5.zip"
Content-Length: 5242880</code></pre>
                        </div>
                    </div>
                    
                    <!-- Download Combined Patches -->
                    <div class="endpoint-card bg-dragon-surface rounded-lg p-6 mb-6 border border-dragon-border">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="method-badge method-post">POST</span>
                            <code class="text-dragon-silver text-lg">/admin/cache/patches/download-combined</code>
                        </div>
                        
                        <p class="text-dragon-silver-dark mb-4">
                            Downloads multiple patches combined into a single ZIP file. This is more efficient than downloading patches individually when multiple updates are needed.
                        </p>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Query Parameters</h4>
                            <div class="bg-dragon-black/50 rounded border border-dragon-border p-4 space-y-2">
                                <div class="flex items-start gap-3">
                                    <code class="text-blue-400">from_version</code>
                                    <span class="param-required">REQUIRED</span>
                                    <span class="text-dragon-silver-dark text-sm">string - Starting version (e.g., "1.2.0")</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <code class="text-blue-400">to_version</code>
                                    <span class="param-optional">OPTIONAL</span>
                                    <span class="text-dragon-silver-dark text-sm">string - Target version (defaults to latest)</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Example</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-dragon-silver">POST {{ url('/admin/cache/patches/download-combined?from_version=1.2.0') }}</code></pre>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Response</h4>
                            <p class="text-dragon-silver-dark text-sm mb-2">Binary ZIP file containing all patches from the specified version to latest:</p>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-dragon-silver">Content-Type: application/zip
Content-Disposition: attachment; filename="patches_1.2.0_to_1.2.5.zip"
Content-Length: 8388608</code></pre>
                        </div>
                        
                        <div class="bg-blue-500/10 border-l-4 border-blue-500 p-4 rounded">
                            <p class="text-dragon-silver text-sm"><i class="fas fa-rocket mr-2"></i><strong>Performance Tip:</strong> Use combined downloads when updating from older versions. The system automatically merges all delta patches into a single download.</p>
                        </div>
                    </div>
                </section>

                <!-- Vote System API -->
                <section id="vote" class="mb-12">
                    <h2 class="text-3xl font-bold text-dragon-red mb-6 flex items-center">
                        <i class="fas fa-vote-yea mr-3"></i>Vote System API
                    </h2>
                    
                    <div class="bg-dragon-surface rounded-lg p-6 border border-dragon-border">
                        <p class="text-dragon-silver-dark mb-4">
                            The voting system allows players to vote for your server on various toplist sites and receive rewards.
                        </p>
                        
                        <h4 class="text-lg font-semibold text-dragon-silver mb-3">Available Endpoints</h4>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <span class="method-badge method-get">GET</span>
                                <div>
                                    <code class="text-dragon-silver">/vote</code>
                                    <p class="text-sm text-dragon-silver-dark mt-1">Vote homepage with available voting sites</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="method-badge method-post">POST</span>
                                <div>
                                    <code class="text-dragon-silver">/vote/set-username</code>
                                    <p class="text-sm text-dragon-silver-dark mt-1">Set voting username for session</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="method-badge method-post">POST</span>
                                <div>
                                    <code class="text-dragon-silver">/vote/{site}</code>
                                    <p class="text-sm text-dragon-silver-dark mt-1">Submit vote to specific toplist site</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="method-badge method-any">ANY</span>
                                <div>
                                    <code class="text-dragon-silver">/vote/callback</code>
                                    <p class="text-sm text-dragon-silver-dark mt-1">Callback handler for toplist confirmations</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="method-badge method-get">GET</span>
                                <div>
                                    <code class="text-dragon-silver">/vote/stats</code>
                                    <p class="text-sm text-dragon-silver-dark mt-1">Get voting statistics and leaderboards</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="method-badge method-get">GET</span>
                                <div>
                                    <code class="text-dragon-silver">/vote/user-votes</code>
                                    <p class="text-sm text-dragon-silver-dark mt-1">Get user's voting history and available votes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Client Download API -->
                <section id="client" class="mb-12">
                    <h2 class="text-3xl font-bold text-dragon-red mb-6 flex items-center">
                        <i class="fas fa-download mr-3"></i>Client Download API
                    </h2>
                    
                    <div class="bg-dragon-surface rounded-lg p-6 border border-dragon-border">
                        <p class="text-dragon-silver-dark mb-4">
                            Endpoints for distributing game clients to players across different platforms.
                        </p>
                        
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <span class="method-badge method-get">GET</span>
                                <div>
                                    <code class="text-dragon-silver">/download/{os}/{version}</code>
                                    <p class="text-sm text-dragon-silver-dark mt-1">Download client for specific OS and version (e.g., /download/windows/1.0.0)</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="method-badge method-get">GET</span>
                                <div>
                                    <code class="text-dragon-silver">/manifest.json</code>
                                    <p class="text-sm text-dragon-silver-dark mt-1">Client version manifest with latest versions for each platform</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="method-badge method-get">GET</span>
                                <div>
                                    <code class="text-dragon-silver">/play</code>
                                    <p class="text-sm text-dragon-silver-dark mt-1">Play page with client download links</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Webhooks -->
                <section id="webhooks" class="mb-12">
                    <h2 class="text-3xl font-bold text-dragon-red mb-6 flex items-center">
                        <i class="fas fa-webhook mr-3"></i>Webhooks
                    </h2>
                    <p class="text-dragon-silver-dark mb-6">
                        Webhook endpoints that receive payment notifications from payment processors. These endpoints are called automatically by PayPal and Coinbase when payment events occur.
                    </p>
                    
                    <!-- PayPal Webhook -->
                    <div class="endpoint-card bg-dragon-surface rounded-lg p-6 mb-6 border border-dragon-border">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="method-badge method-post">POST</span>
                            <code class="text-dragon-silver text-lg">/api/webhooks/paypal</code>
                        </div>
                        
                        <p class="text-dragon-silver-dark mb-4">
                            Receives PayPal payment completion notifications. Configure this URL in your PayPal Developer Dashboard.
                        </p>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Webhook URL</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-blue-400">{{ url('/api/webhooks/paypal') }}</code></pre>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Event Types</h4>
                            <ul class="text-sm text-dragon-silver-dark space-y-1 list-disc list-inside bg-dragon-black/50 rounded p-4 border border-dragon-border">
                                <li><code>PAYMENT.CAPTURE.COMPLETED</code> - Payment successfully captured</li>
                                <li><code>PAYMENT.CAPTURE.DENIED</code> - Payment denied or failed</li>
                                <li><code>CHECKOUT.ORDER.APPROVED</code> - Order approved by customer</li>
                            </ul>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Example Payload</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-dragon-silver">{
  "id": "WH-1A234567B8901234C-567890123",
  "event_type": "PAYMENT.CAPTURE.COMPLETED",
  "resource_type": "capture",
  "resource": {
    "id": "CAPTURE-123ABC",
    "status": "COMPLETED",
    "amount": {
      "currency_code": "USD",
      "value": "14.97"
    },
    "custom_id": "ORD-1234567890"
  },
  "create_time": "2024-10-15T12:00:00Z"
}</code></pre>
                        </div>
                        
                        <div class="bg-yellow-500/10 border-l-4 border-yellow-500 p-4 rounded">
                            <p class="text-dragon-silver text-sm"><i class="fas fa-shield-alt mr-2"></i><strong>Security:</strong> PayPal webhooks are verified using signature validation to ensure authenticity.</p>
                        </div>
                    </div>
                    
                    <!-- Coinbase Webhook -->
                    <div class="endpoint-card bg-dragon-surface rounded-lg p-6 mb-6 border border-dragon-border">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="method-badge method-post">POST</span>
                            <code class="text-dragon-silver text-lg">/api/webhooks/coinbase</code>
                        </div>
                        
                        <p class="text-dragon-silver-dark mb-4">
                            Receives Coinbase Commerce payment notifications. Configure this URL in your Coinbase Commerce settings.
                        </p>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Webhook URL</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-blue-400">{{ url('/api/webhooks/coinbase') }}</code></pre>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Event Types</h4>
                            <ul class="text-sm text-dragon-silver-dark space-y-1 list-disc list-inside bg-dragon-black/50 rounded p-4 border border-dragon-border">
                                <li><code>charge:confirmed</code> - Payment confirmed on blockchain</li>
                                <li><code>charge:failed</code> - Payment failed or expired</li>
                                <li><code>charge:pending</code> - Payment detected but awaiting confirmations</li>
                            </ul>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-dragon-red mb-2 uppercase tracking-wide">Example Payload</h4>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-dragon-silver">{
  "event": {
    "id": "event-123abc",
    "type": "charge:confirmed",
    "data": {
      "id": "charge-abc123",
      "code": "ABC123DEF",
      "metadata": {
        "order_id": "ORD-1234567890"
      },
      "pricing": {
        "local": {
          "amount": "14.97",
          "currency": "USD"
        },
        "bitcoin": {
          "amount": "0.00035",
          "currency": "BTC"
        }
      },
      "confirmed_at": "2024-10-15T12:00:00Z"
    }
  }
}</code></pre>
                        </div>
                        
                        <div class="bg-yellow-500/10 border-l-4 border-yellow-500 p-4 rounded">
                            <p class="text-dragon-silver text-sm"><i class="fas fa-shield-alt mr-2"></i><strong>Security:</strong> Coinbase webhooks are verified using HMAC signature in the X-CC-Webhook-Signature header.</p>
                        </div>
                    </div>
                </section>

                <!-- Admin API -->
                <section id="admin-api" class="mb-12">
                    <h2 class="text-3xl font-bold text-dragon-red mb-6 flex items-center">
                        <i class="fas fa-user-shield mr-3"></i>Admin API
                    </h2>
                    <p class="text-dragon-silver-dark mb-6">
                        Administrative endpoints for server management, monitoring, and order processing.
                    </p>
                    
                    <div class="bg-dragon-surface rounded-lg p-6 border border-dragon-border">
                        <div class="space-y-4">
                            <div>
                                <div class="flex items-start gap-3 mb-2">
                                    <span class="method-badge method-get">GET</span>
                                    <code class="text-dragon-silver">/api/admin/orders/logs</code>
                                </div>
                                <p class="text-sm text-dragon-silver-dark ml-20">Retrieve order activity logs with filtering and pagination</p>
                            </div>
                            
                            <div>
                                <div class="flex items-start gap-3 mb-2">
                                    <span class="method-badge method-get">GET</span>
                                    <code class="text-dragon-silver">/api/admin/orders/{id}/events</code>
                                </div>
                                <p class="text-sm text-dragon-silver-dark ml-20">Get detailed event history for a specific order</p>
                            </div>
                            
                            <div>
                                <div class="flex items-start gap-3 mb-2">
                                    <span class="method-badge method-get">GET</span>
                                    <code class="text-dragon-silver">/api/admin/orders/stats</code>
                                </div>
                                <p class="text-sm text-dragon-silver-dark ml-20">Get order statistics, revenue metrics, and analytics</p>
                            </div>
                            
                            <div>
                                <div class="flex items-start gap-3 mb-2">
                                    <span class="method-badge method-patch">PATCH</span>
                                    <code class="text-dragon-silver">/api/admin/orders/{id}/status</code>
                                </div>
                                <p class="text-sm text-dragon-silver-dark ml-20">Update order status (pending, processing, completed, failed)</p>
                            </div>
                        </div>
                        
                        <div class="mt-6 bg-dragon-black/50 rounded p-4 border border-dragon-border">
                            <h4 class="text-sm font-semibold text-dragon-silver-dark mb-2"><i class="fas fa-lock mr-2"></i>Authentication Required</h4>
                            <p class="text-sm text-dragon-silver-dark">All admin endpoints require elevated API key permissions with admin access.</p>
                        </div>
                    </div>
                </section>

                <!-- Error Handling -->
                <section id="errors" class="mb-12">
                    <h2 class="text-3xl font-bold text-dragon-red mb-6 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-3"></i>Error Handling
                    </h2>
                    <p class="text-dragon-silver-dark mb-6">
                        The API uses conventional HTTP status codes and returns error details in JSON format.
                    </p>
                    
                    <div class="space-y-6">
                        <!-- 401 Unauthorized -->
                        <div class="endpoint-card bg-dragon-surface rounded-lg p-6 border border-red-500/30">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="px-3 py-1 bg-red-500 text-white rounded font-bold text-sm">401</span>
                                <h3 class="text-xl font-semibold text-red-400">Unauthorized</h3>
                            </div>
                            <p class="text-dragon-silver-dark text-sm mb-3">API key is missing, invalid, or lacks required permissions.</p>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-red-400">{
  "success": false,
  "error": "Unauthorized",
  "message": "Invalid or missing API key"
}</code></pre>
                        </div>
                        
                        <!-- 404 Not Found -->
                        <div class="endpoint-card bg-dragon-surface rounded-lg p-6 border border-yellow-500/30">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="px-3 py-1 bg-yellow-500 text-white rounded font-bold text-sm">404</span>
                                <h3 class="text-xl font-semibold text-yellow-400">Not Found</h3>
                            </div>
                            <p class="text-dragon-silver-dark text-sm mb-3">The requested resource does not exist.</p>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-yellow-400">{
  "success": false,
  "error": "Not Found",
  "message": "The requested resource could not be found"
}</code></pre>
                        </div>
                        
                        <!-- 422 Validation Error -->
                        <div class="endpoint-card bg-dragon-surface rounded-lg p-6 border border-orange-500/30">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="px-3 py-1 bg-orange-500 text-white rounded font-bold text-sm">422</span>
                                <h3 class="text-xl font-semibold text-orange-400">Validation Error</h3>
                            </div>
                            <p class="text-dragon-silver-dark text-sm mb-3">Request validation failed. Check the errors object for field-specific details.</p>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-orange-400">{
  "success": false,
  "error": "Validation failed",
  "message": "The given data was invalid",
  "errors": {
    "player_name": [
      "The player name field is required."
    ],
    "products": [
      "The products field is required."
    ],
    "payment_method": [
      "The selected payment method is invalid."
    ]
  }
}</code></pre>
                        </div>
                        
                        <!-- 500 Server Error -->
                        <div class="endpoint-card bg-dragon-surface rounded-lg p-6 border border-red-600/30">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="px-3 py-1 bg-red-600 text-white rounded font-bold text-sm">500</span>
                                <h3 class="text-xl font-semibold text-red-500">Internal Server Error</h3>
                            </div>
                            <p class="text-dragon-silver-dark text-sm mb-3">An unexpected error occurred on the server. Contact support if this persists.</p>
                            <pre class="bg-dragon-black rounded border border-dragon-border"><code class="text-red-500">{
  "success": false,
  "error": "Internal server error",
  "message": "An unexpected error occurred. Please try again later.",
  "support_id": "ERR-20241015-ABC123"
}</code></pre>
                        </div>
                    </div>
                    
                    <div class="mt-6 bg-blue-500/10 border-l-4 border-blue-500 p-4 rounded">
                        <h4 class="text-dragon-silver font-semibold mb-2">Best Practices</h4>
                        <ul class="text-sm text-dragon-silver-dark space-y-1 list-disc list-inside">
                            <li>Always check the <code>success</code> field in the response</li>
                            <li>Log error messages with support IDs for troubleshooting</li>
                            <li>Implement retry logic with exponential backoff for 500 errors</li>
                            <li>Validate input data client-side before API calls to reduce 422 errors</li>
                            <li>Cache manifest and configuration data to reduce API calls</li>
                        </ul>
                    </div>
                </section>

            </div>
        </div>
    </div>
</div>

<script>
// Smooth scrolling for navigation links
document.querySelectorAll('.sidebar-nav a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Update active state
            document.querySelectorAll('.sidebar-nav a').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        }
    });
});

// Highlight active section on scroll
const sections = document.querySelectorAll('section[id]');
const navLinks = document.querySelectorAll('.sidebar-nav a');

window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        if (pageYOffset >= (sectionTop - 200)) {
            current = section.getAttribute('id');
        }
    });

    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + current) {
            link.classList.add('active');
        }
    });
});

// Copy code to clipboard functionality
document.querySelectorAll('pre code').forEach(block => {
    block.style.position = 'relative';
    const button = document.createElement('button');
    button.innerHTML = '<i class="fas fa-copy"></i>';
    button.className = 'absolute top-2 right-2 px-3 py-1 bg-dragon-red hover:bg-dragon-red-bright text-white rounded text-xs transition-colors';
    button.style.position = 'absolute';
    button.style.top = '0.5rem';
    button.style.right = '0.5rem';
    
    button.addEventListener('click', () => {
        navigator.clipboard.writeText(block.textContent);
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => {
            button.innerHTML = '<i class="fas fa-copy"></i>';
        }, 2000);
    });
    
    block.parentElement.style.position = 'relative';
    block.parentElement.appendChild(button);
});
</script>
@endsection
