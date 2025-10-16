@extends('layouts.public')

@section('title', 'Dragon\'s Store - Aragon RSPS')
@section('description', 'Browse and purchase items for Aragon RSPS')

@section('content')
<style>
.category-filter {
    background: rgba(26, 26, 26, 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--border-color);
    padding: 1rem 0;
    margin-bottom: 2rem;
    position: sticky;
    top: 70px;
    z-index: 999;
}

.category-btn {
    background: rgba(26, 26, 26, 0.8);
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    padding: 0.5rem 1rem;
    border-radius: 6px;
    margin: 0.25rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.category-btn:hover, .category-btn.active {
    background: var(--primary-color);
    color: var(--text-light);
    border-color: var(--primary-bright);
}

.view-toggle {
    display: flex;
    gap: 0.5rem;
}

.view-toggle-btn {
    background: rgba(26, 26, 26, 0.8);
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.view-toggle-btn:hover, .view-toggle-btn.active {
    background: var(--primary-color);
    color: var(--text-light);
    border-color: var(--primary-bright);
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.products-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 2rem;
}

.product-card {
    background: rgba(26, 26, 26, 0.8);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.product-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(212, 0, 0, 0.3);
}

.product-card.list-view {
    display: flex;
    gap: 1.5rem;
    align-items: center;
}

.product-image {
    width: 32px;
    height: 32px;
    object-fit: contain;
    background: rgba(10, 10, 10, 0.8);
    border-radius: 6px;
    padding: 4px;
}

.product-card.list-view .product-image {
    width: 48px;
    height: 48px;
}

.bundle-items {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
    display: none;
}

.bundle-items.expanded {
    display: block;
}

.bundle-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem;
    background: rgba(10, 10, 10, 0.8);
    border-radius: 6px;
    margin-bottom: 0.5rem;
}

.cart-sidebar {
    position: fixed;
    right: -400px;
    top: 0;
    width: 400px;
    height: 100vh;
    background: rgba(26, 26, 26, 0.98);
    backdrop-filter: blur(20px);
    border-left: 1px solid var(--border-color);
    transition: right 0.3s ease;
    z-index: 10000;
    overflow-y: auto;
    padding: 2rem;
}

.cart-sidebar.open {
    right: 0;
}

.cart-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    display: none;
}

.cart-overlay.active {
    display: block;
}

.cart-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background: rgba(10, 10, 10, 0.8);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    margin-bottom: 1rem;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quantity-btn {
    background: var(--border-color);
    color: var(--text-light);
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.quantity-btn:hover {
    background: var(--primary-color);
}

.cart-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: var(--primary-bright);
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
}

.user-form {
    background: rgba(26, 26, 26, 0.8);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}
</style>

<div class="fade-in-up">
    <!-- User Session Form -->
    <div class="user-form" id="user-form-section">
        <div id="username-form" style="{{ session('cart_user') ? 'display: none;' : '' }}">
            <h3 class="text-primary mb-3">
                <i class="fas fa-user"></i> Enter Your Username
            </h3>
            <p class="text-muted mb-3">Required to make purchases</p>
            
            <div class="form-group">
                <input type="text" 
                       id="cart-username" 
                       placeholder="Your in-game username"
                       class="form-input"
                       maxlength="50"
                       pattern="[A-Za-z0-9_]+"
                       value="{{ session('cart_user') }}">
            </div>
            
            <button onclick="setCartUser()" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-save"></i> Set Username
            </button>
            
            <div id="user-error" class="alert alert-error mt-2" style="display: none;"></div>
        </div>

        <div id="username-display" style="{{ session('cart_user') ? '' : 'display: none;' }}">
            <h3 class="text-primary mb-3">
                <i class="fas fa-user-check"></i> Shopping as
            </h3>
            <div class="mb-3">
                <span class="text-primary" style="font-size: 1.5rem; font-weight: 600;" id="current-cart-username">
                    {{ session('cart_user') }}
                </span>
            </div>
            <button onclick="changeCartUser()" class="btn btn-secondary">
                <i class="fas fa-user-edit"></i> Change Username
            </button>
        </div>
    </div>

    <!-- Category Filter -->
    <div class="category-filter">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <button class="category-btn active" data-category="all">
                        <i class="fas fa-th"></i> All Products
                    </button>
                    @foreach($categories as $category)
                        <button class="category-btn" data-category="{{ $category->id }}">
                            {{ $category->name }} ({{ $category->products_count }})
                        </button>
                    @endforeach
                </div>
                
                <div class="view-toggle">
                    <button class="view-toggle-btn active" data-view="grid">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-toggle-btn" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                    <button class="btn btn-primary" onclick="toggleCart()" style="position: relative;">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Grid/List -->
    <div class="container">
        <div id="products-container" class="products-grid">
            @foreach($products as $product)
                <div class="product-card" data-category="{{ $product->category_id ?? 'none' }}">
                    <div class="product-image-container" style="text-align: center; margin-bottom: 1rem;">
                        <img src="https://via.placeholder.com/32x32/d40000/e8e8e8?text=Item" 
                             alt="{{ $product->product_name }}" 
                             class="product-image">
                    </div>
                    
                    <div class="product-info" style="flex: 1;">
                        <h4 class="text-primary" style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">
                            {{ $product->product_name }}
                        </h4>
                        
                        <div class="text-muted" style="margin-bottom: 0.75rem;">
                            <i class="fas fa-box"></i> Quantity: {{ $product->qty_unit }}x
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <span class="text-primary" style="font-size: 1.5rem; font-weight: 700;">
                                ${{ number_format($product->price, 2) }}
                            </span>
                            
                            @if($product->bundleItems->count() > 0)
                                <button onclick="toggleBundle({{ $product->id }})" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-chevron-down" id="bundle-icon-{{ $product->id }}"></i> Show Items
                                </button>
                            @endif
                        </div>
                        
                        @if($product->bundleItems->count() > 0)
                            <div class="bundle-items" id="bundle-{{ $product->id }}">
                                <h5 class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.75rem;">Bundle Contains:</h5>
                                @foreach($product->bundleItems as $item)
                                    <div class="bundle-item">
                                        <img src="https://via.placeholder.com/24x24/d40000/e8e8e8?text=I" 
                                             alt="Bundle Item" 
                                             style="width: 24px; height: 24px;">
                                        <span class="text-light">Item ID: {{ $item->item_id }} ({{ $item->qty_unit }}x)</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        
                        <button onclick="addToCart({{ $product->id }})" 
                                class="btn btn-primary add-to-cart-btn" 
                                style="width: 100%; {{ session('cart_user') ? '' : 'display: none;' }}"
                                data-product-id="{{ $product->id }}">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Cart Overlay -->
<div class="cart-overlay" id="cart-overlay" onclick="toggleCart()"></div>

<!-- Cart Sidebar -->
<div class="cart-sidebar" id="cart-sidebar">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h3 class="text-primary" style="font-size: 1.5rem; font-weight: 700;">
            <i class="fas fa-shopping-cart"></i> Shopping Cart
        </h3>
        <button onclick="toggleCart()" class="btn btn-secondary btn-sm">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div id="cart-items">
        <p class="text-muted">Your cart is empty</p>
    </div>
    
    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <span style="font-size: 1.25rem; font-weight: 600;">Total:</span>
            <span class="text-primary" style="font-size: 1.75rem; font-weight: 700;" id="cart-total">$0.00</span>
        </div>
        
        <button onclick="checkout()" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;" id="checkout-btn" disabled>
            <i class="fas fa-credit-card"></i> Proceed to Checkout
        </button>
        
        <button onclick="clearCartItems()" class="btn btn-secondary" style="width: 100%;">
            <i class="fas fa-trash"></i> Clear Cart
        </button>
    </div>
</div>

@push('scripts')
<script>
let currentView = 'grid';
let currentCategory = 'all';
let currentUsername = '{{ session("cart_user") }}';

// Set Cart User
function setCartUser() {
    const username = $('#cart-username').val().trim();
    
    if (!username) {
        showError('Please enter a username');
        return;
    }
    
    if (!/^[A-Za-z0-9_]+$/.test(username)) {
        showError('Username can only contain letters, numbers, and underscores');
        return;
    }
    
    $.post('{{ route("store.set-user") }}', {
        username: username,
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        currentUsername = response.username;
        $('#current-cart-username').text(response.username);
        $('#username-form').hide();
        $('#username-display').show();
        $('.add-to-cart-btn').show();
    })
    .fail(function() {
        showError('Failed to set username. Please try again.');
    });
}

// Change Cart User
function changeCartUser() {
    $.post('{{ route("store.clear-user") }}', {
        _token: '{{ csrf_token() }}'
    })
    .done(function() {
        currentUsername = '';
        $('#cart-username').val('');
        $('#username-display').hide();
        $('#username-form').show();
        $('.add-to-cart-btn').hide();
        loadCart();
    });
}

// Show Error
function showError(message) {
    $('#user-error').text(message).show();
    setTimeout(() => $('#user-error').hide(), 3000);
}

// Category Filter
$('.category-btn').click(function() {
    const category = $(this).data('category');
    currentCategory = category;
    
    $('.category-btn').removeClass('active');
    $(this).addClass('active');
    
    filterProducts();
});

// View Toggle
$('.view-toggle-btn').click(function() {
    const view = $(this).data('view');
    currentView = view;
    
    $('.view-toggle-btn').removeClass('active');
    $(this).addClass('active');
    
    const container = $('#products-container');
    if (view === 'grid') {
        container.removeClass('products-list').addClass('products-grid');
        $('.product-card').removeClass('list-view');
    } else {
        container.removeClass('products-grid').addClass('products-list');
        $('.product-card').addClass('list-view');
    }
});

// Filter Products
function filterProducts() {
    $('.product-card').each(function() {
        const cardCategory = $(this).data('category');
        
        if (currentCategory === 'all' || cardCategory == currentCategory) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

// Toggle Bundle
function toggleBundle(productId) {
    const bundle = $(`#bundle-${productId}`);
    const icon = $(`#bundle-icon-${productId}`);
    
    bundle.toggleClass('expanded');
    
    if (bundle.hasClass('expanded')) {
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
    } else {
        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
    }
}

// Add to Cart
function addToCart(productId) {
    if (!currentUsername) {
        showError('Please set your username first');
        return;
    }
    
    $.post('{{ route("store.add-to-cart") }}', {
        product_id: productId,
        quantity: 1,
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        loadCart();
        updateCartBadge(response.cart_count);
    })
    .fail(function() {
        alert('Failed to add item to cart');
    });
}

// Toggle Cart
function toggleCart() {
    $('#cart-sidebar').toggleClass('open');
    $('#cart-overlay').toggleClass('active');
    if ($('#cart-sidebar').hasClass('open')) {
        loadCart();
    }
}

// Load Cart
function loadCart() {
    $.get('{{ route("store.get-cart") }}')
        .done(function(response) {
            updateCartBadge(response.cart_count);
            renderCart(response.cart, response.total);
        });
}

// Update Cart Badge
function updateCartBadge(count) {
    const badge = $('#cart-badge');
    if (count > 0) {
        badge.text(count).show();
    } else {
        badge.hide();
    }
}

// Render Cart
function renderCart(cart, total) {
    const cartItems = $('#cart-items');
    const checkoutBtn = $('#checkout-btn');
    
    if (Object.keys(cart).length === 0) {
        cartItems.html('<p class="text-muted">Your cart is empty</p>');
        $('#cart-total').text('$0.00');
        checkoutBtn.prop('disabled', true);
        return;
    }
    
    let html = '';
    Object.values(cart).forEach(item => {
        html += `
            <div class="cart-item">
                <div style="flex: 1;">
                    <h5 class="text-light" style="margin-bottom: 0.5rem;">${item.name}</h5>
                    <p class="text-muted" style="font-size: 0.875rem;">$${parseFloat(item.price).toFixed(2)} each</p>
                </div>
                <div class="quantity-controls">
                    <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                    <span style="min-width: 32px; text-align: center; font-weight: 600;">${item.quantity}</span>
                    <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                    <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.id})" style="margin-left: 0.5rem;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    cartItems.html(html);
    $('#cart-total').text('$' + total);
    checkoutBtn.prop('disabled', false);
}

// Update Quantity
function updateQuantity(productId, quantity) {
    $.post('{{ route("store.update-cart") }}', {
        product_id: productId,
        quantity: quantity,
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        updateCartBadge(response.cart_count);
        renderCart(response.cart, response.total);
    });
}

// Remove from Cart
function removeFromCart(productId) {
    $.ajax({
        url: '{{ route("store.remove-from-cart", ":id") }}'.replace(':id', productId),
        type: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}'
        }
    })
    .done(function(response) {
        updateCartBadge(response.cart_count);
        renderCart(response.cart, response.total);
    });
}

// Clear Cart
function clearCartItems() {
    if (!confirm('Are you sure you want to clear your cart?')) return;
    
    $.post('{{ route("store.clear-cart") }}', {
        _token: '{{ csrf_token() }}'
    })
    .done(function() {
        loadCart();
    });
}

// Checkout
function checkout() {
    window.location.href = '/test-checkout.html';
}

// Load cart on page load
$(document).ready(function() {
    loadCart();
});
</script>
@endpush
@endsection
