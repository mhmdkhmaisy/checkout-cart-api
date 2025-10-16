@extends('layouts.public')

@section('title', 'Dragon\'s Store - Aragon RSPS')
@section('description', 'Browse and purchase items for Aragon RSPS')

@section('content')
<style>
.store-header {
    background: linear-gradient(135deg, rgba(212, 0, 0, 0.1) 0%, transparent 100%);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.store-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
}

.store-container {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 2rem;
}

.category-filter {
    background: rgba(10, 10, 10, 0.98);
    backdrop-filter: blur(20px);
    border-bottom: 2px solid;
    border-image: linear-gradient(90deg, transparent, var(--primary-color), transparent) 1;
    padding: 1.25rem 0;
    margin-bottom: 0;
    position: sticky;
    top: 70px;
    z-index: 999;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
}

.category-btn {
    background: rgba(26, 26, 26, 0.8);
    border: 2px solid var(--border-color);
    color: var(--text-muted);
    padding: 0.6rem 1.2rem;
    border-radius: 6px;
    margin: 0.25rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
    font-weight: 600;
    letter-spacing: 0.3px;
}

.category-btn:hover {
    border-color: var(--primary-color);
    color: var(--text-light);
    box-shadow: 0 0 15px rgba(212, 0, 0, 0.3);
}

.category-btn.active {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-bright));
    color: var(--text-light);
    border-color: var(--primary-bright);
    box-shadow: 0 0 20px rgba(212, 0, 0, 0.5);
}

.view-toggle {
    display: flex;
    gap: 0.5rem;
}

.view-toggle-btn {
    background: rgba(26, 26, 26, 0.8);
    border: 2px solid var(--border-color);
    color: var(--text-muted);
    padding: 0.6rem 0.9rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.view-toggle-btn:hover, .view-toggle-btn.active {
    background: var(--primary-color);
    color: var(--text-light);
    border-color: var(--primary-bright);
    box-shadow: 0 0 15px rgba(212, 0, 0, 0.4);
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.product-card {
    background: rgba(26, 26, 26, 0.95);
    backdrop-filter: blur(10px);
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
}

.product-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(212, 0, 0, 0.5), transparent);
}

.product-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(212, 0, 0, 0.4);
}

.product-card:hover::before {
    background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
}

.product-image {
    width: 64px;
    height: 64px;
    object-fit: contain;
    background: rgba(10, 10, 10, 0.8);
    border-radius: 8px;
    padding: 8px;
    border: 2px solid var(--border-color);
}

.bundle-items {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid var(--border-color);
    display: none;
}

.bundle-items.expanded {
    display: block;
}

.bundle-item-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
    gap: 0.75rem;
}

.bundle-item {
    text-align: center;
    padding: 0.75rem;
    background: rgba(10, 10, 10, 0.6);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    transition: all 0.2s ease;
}

.bundle-item:hover {
    border-color: var(--primary-color);
    transform: scale(1.05);
}

.bundle-item-image {
    width: 32px;
    height: 32px;
    object-fit: contain;
    margin: 0 auto 0.25rem;
}

.basket-sidebar {
    position: sticky;
    top: 140px;
    height: fit-content;
    max-height: calc(100vh - 160px);
    overflow-y: auto;
}

.basket-card {
    background: rgba(26, 26, 26, 0.95);
    backdrop-filter: blur(20px);
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
    position: relative;
}

.basket-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
    border-radius: 8px 8px 0 0;
}

.basket-header {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cart-item {
    background: rgba(10, 10, 10, 0.8);
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.cart-item:hover {
    border-color: rgba(212, 0, 0, 0.5);
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
    font-size: 1rem;
    font-weight: 700;
}

.quantity-btn:hover {
    background: var(--primary-color);
    transform: scale(1.1);
}

.user-form {
    background: rgba(10, 10, 10, 0.8);
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
}

.total-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid var(--primary-color);
    background: rgba(212, 0, 0, 0.05);
    margin-left: -1.5rem;
    margin-right: -1.5rem;
    margin-bottom: -1.5rem;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
    padding-bottom: 1.5rem;
    border-radius: 0 0 6px 6px;
}

.price-tag {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-bright));
    color: var(--text-light);
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(212, 0, 0, 0.4);
}

.stock-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stock-badge.in-stock {
    background: rgba(34, 197, 94, 0.2);
    border: 1px solid #22c55e;
    color: #22c55e;
}

.stock-badge.out-of-stock {
    background: rgba(239, 68, 68, 0.2);
    border: 1px solid #ef4444;
    color: #ef4444;
}

@media (max-width: 1200px) {
    .store-container {
        grid-template-columns: 1fr;
    }
    
    .basket-sidebar {
        position: relative;
        top: 0;
        max-height: none;
    }
}
</style>

<div class="fade-in-up">
    <!-- Store Header -->
    <div class="container">
        <div class="store-header">
            <h1 style="font-size: 2rem; font-weight: 800; color: var(--primary-color); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">
                <i class="fas fa-store"></i> Dragon's Store
            </h1>
            <p class="text-muted" style="font-size: 1rem;">Purchase premium items and bundles for your adventure</p>
        </div>
    </div>

    <!-- Category Filter -->
    <div class="category-filter">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <button class="category-btn active" data-category="all">
                        <i class="fas fa-th"></i> All Items
                    </button>
                    @foreach($categories as $category)
                        <button class="category-btn" data-category="{{ $category->id }}">
                            {{ $category->name }}
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
                </div>
            </div>
        </div>
    </div>

    <!-- Store Layout: Products + Basket -->
    <div class="container" style="margin-top: 2rem;">
        <div class="store-container">
            <!-- Products Section -->
            <div>
                <div style="margin-bottom: 1.5rem;">
                    <h3 class="text-primary" style="font-size: 1.1rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fas fa-box-open"></i> <span id="category-title">Showing All Items</span>
                    </h3>
                </div>
                
                <div id="products-container" class="products-grid">
                    @foreach($products as $product)
                        <div class="product-card" data-category="{{ $product->category_id ?? 'none' }}">
                            <div style="display: flex; align-items: start; gap: 1.25rem; margin-bottom: 1rem;">
                                <img src="https://via.placeholder.com/64x64/d40000/e8e8e8?text=Item" 
                                     alt="{{ $product->product_name }}" 
                                     class="product-image">
                                
                                <div style="flex: 1;">
                                    <h4 class="text-light" style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem; letter-spacing: 0.3px;">
                                        {{ $product->product_name }}
                                    </h4>
                                    
                                    @if($product->product_description)
                                        <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 0.75rem;">
                                            {{ $product->product_description }}
                                        </p>
                                    @endif
                                    
                                    <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 0.75rem;">
                                        <span class="price-tag">
                                            ${{ number_format($product->price, 2) }}
                                        </span>
                                        
                                        @if($product->qty_unit > 0)
                                            <span class="stock-badge in-stock">
                                                <i class="fas fa-check-circle"></i> In Stock
                                            </span>
                                        @else
                                            <span class="stock-badge out-of-stock">
                                                <i class="fas fa-times-circle"></i> Sold Out
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            @if($product->bundleItems->count() > 0)
                                <button onclick="toggleBundle({{ $product->id }})" class="btn btn-secondary" style="width: 100%; margin-bottom: 1rem; font-size: 0.85rem; padding: 0.5rem;">
                                    <i class="fas fa-chevron-down" id="bundle-icon-{{ $product->id }}"></i> View Bundle Contents ({{ $product->bundleItems->count() }} items)
                                </button>
                                
                                <div class="bundle-items" id="bundle-{{ $product->id }}">
                                    <div class="bundle-item-grid">
                                        @foreach($product->bundleItems as $item)
                                            <div class="bundle-item">
                                                <img src="https://via.placeholder.com/32x32/d40000/e8e8e8?text=I" 
                                                     alt="Item" 
                                                     class="bundle-item-image">
                                                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">
                                                    {{ $item->qty_unit }}x
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            <div style="display: flex; gap: 0.75rem; align-items: center; {{ $product->qty_unit > 0 ? '' : 'opacity: 0.5; pointer-events: none;' }}">
                                <input type="number" 
                                       value="1" 
                                       min="1" 
                                       class="form-input quantity-input" 
                                       id="quantity-{{ $product->id }}"
                                       style="width: 90px; padding: 0.6rem; text-align: center; font-weight: 700;">
                                <button onclick="addToCart({{ $product->id }})" 
                                        class="btn btn-primary add-to-cart-btn" 
                                        style="flex: 1; {{ session('cart_user') ? '' : 'display: none;' }}"
                                        data-product-id="{{ $product->id }}"
                                        {{ $product->qty_unit > 0 ? '' : 'disabled' }}>
                                    <i class="fas fa-cart-plus"></i> Add to Basket
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Basket Sidebar -->
            <div class="basket-sidebar">
                <div class="basket-card">
                    <div class="basket-header">
                        <i class="fas fa-shopping-basket"></i> Your Basket
                    </div>
                    
                    <!-- Username Form or Display -->
                    <div id="basket-user-section">
                        <div id="username-form" style="{{ session('cart_user') ? 'display: none;' : '' }}">
                            <div class="user-form">
                                <p class="text-muted mb-3" style="font-size: 0.9rem; text-align: center;">
                                    <i class="fas fa-info-circle"></i> Enter your in-game username to purchase items
                                </p>
                                
                                <div class="form-group" style="margin-bottom: 1rem;">
                                    <input type="text" 
                                           id="cart-username" 
                                           placeholder="Username"
                                           class="form-input"
                                           maxlength="50"
                                           pattern="[A-Za-z0-9_]+"
                                           value="{{ session('cart_user') }}"
                                           style="text-align: center; font-weight: 600;">
                                </div>
                                
                                <button onclick="setCartUser()" class="btn btn-primary" style="width: 100%;">
                                    <i class="fas fa-check"></i> Confirm Username
                                </button>
                                
                                <div id="user-error" class="alert alert-error mt-2" style="display: none;"></div>
                            </div>
                        </div>

                        <div id="username-display" style="{{ session('cart_user') ? '' : 'display: none;' }}">
                            <div style="background: rgba(10, 10, 10, 0.8); border: 2px solid var(--border-color); border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
                                <div class="text-muted" style="font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-user"></i> Shopping As:
                                </div>
                                <div class="text-primary" style="font-weight: 700; font-size: 1.1rem;" id="current-cart-username">
                                    {{ session('cart_user') }}
                                </div>
                                <button onclick="changeCartUser()" class="btn btn-secondary" style="width: 100%; margin-top: 0.75rem; padding: 0.5rem; font-size: 0.85rem;">
                                    <i class="fas fa-exchange-alt"></i> Change User
                                </button>
                            </div>
                            
                            <!-- Cart Items -->
                            <div id="cart-items-section">
                                <div id="cart-items">
                                    <div style="text-align: center; padding: 3rem 1rem; opacity: 0.5;">
                                        <i class="fas fa-shopping-basket" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                                        <p class="text-muted">Your basket is empty</p>
                                    </div>
                                </div>
                                
                                <div class="total-section">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                                        <span style="font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem;">Total Cost:</span>
                                        <span class="text-primary" style="font-size: 1.75rem; font-weight: 800;" id="cart-total">$0.00</span>
                                    </div>
                                    
                                    <button onclick="checkout()" class="btn btn-primary" style="width: 100%; margin-bottom: 0.75rem;" id="checkout-btn" disabled>
                                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                                    </button>
                                    
                                    <button onclick="clearCartItems()" class="btn btn-secondary" style="width: 100%;">
                                        <i class="fas fa-trash-alt"></i> Clear Basket
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
$('.category-btn[data-category]').click(function() {
    const category = $(this).data('category');
    currentCategory = category;
    
    $('.category-btn[data-category]').removeClass('active');
    $(this).addClass('active');
    
    // Update title
    if (category === 'all') {
        $('#category-title').text("Showing All Items");
    } else {
        const categoryName = $(this).text().trim();
        $('#category-title').text("Showing " + categoryName);
    }
    
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
        container.css('grid-template-columns', 'repeat(auto-fill, minmax(300px, 1fr))');
    } else {
        container.css('grid-template-columns', '1fr');
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
    
    const quantity = parseInt($(`#quantity-${productId}`).val()) || 1;
    
    $.post('{{ route("store.add-to-cart") }}', {
        product_id: productId,
        quantity: quantity,
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        loadCart();
    })
    .fail(function() {
        alert('Failed to add item to cart');
    });
}

// Load Cart
function loadCart() {
    $.get('{{ route("store.get-cart") }}')
        .done(function(response) {
            renderCart(response.cart, response.total);
        });
}

// Render Cart
function renderCart(cart, total) {
    const cartItems = $('#cart-items');
    const checkoutBtn = $('#checkout-btn');
    
    if (Object.keys(cart).length === 0) {
        cartItems.html(`
            <div style="text-align: center; padding: 3rem 1rem; opacity: 0.5;">
                <i class="fas fa-shopping-basket" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                <p class="text-muted">Your basket is empty</p>
            </div>
        `);
        $('#cart-total').text('$0.00');
        checkoutBtn.prop('disabled', true);
        return;
    }
    
    let html = '';
    Object.values(cart).forEach(item => {
        html += `
            <div class="cart-item">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                    <div style="flex: 1;">
                        <div class="text-light" style="font-weight: 700; font-size: 0.95rem; margin-bottom: 0.25rem;">${item.name}</div>
                        <div class="text-muted" style="font-size: 0.8rem;">$${parseFloat(item.price).toFixed(2)} each</div>
                    </div>
                    <button class="quantity-btn" onclick="removeFromCart(${item.id})" style="width: auto; padding: 0 0.6rem;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span style="min-width: 40px; text-align: center; font-weight: 700; font-size: 1rem;">${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="text-primary" style="font-weight: 800; font-size: 1.1rem;">
                        $${(parseFloat(item.price) * item.quantity).toFixed(2)}
                    </div>
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
    
    // Enable enter key for username
    $('#cart-username').on('keypress', function(e) {
        if (e.which === 13) {
            setCartUser();
        }
    });
});
</script>
@endpush
@endsection
