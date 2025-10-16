@extends('layouts.public')

@section('title', 'Dragon\'s Store - Aragon RSPS')
@section('description', 'Browse and purchase items for Aragon RSPS')

@section('content')
<style>
.store-container {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
    margin-top: 1.5rem;
}

.category-filter {
    background: rgba(10, 10, 10, 0.98);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--border-color);
    padding: 1rem 0;
    margin-bottom: 0;
    position: sticky;
    top: 70px;
    z-index: 999;
}

.category-btn {
    background: rgba(26, 26, 26, 0.8);
    border: 1px solid var(--border-color);
    color: var(--text-light);
    padding: 0.5rem 1rem;
    border-radius: 6px;
    margin: 0.25rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.85rem;
    font-weight: 600;
}

.category-btn:hover {
    border-color: var(--primary-color);
    background: rgba(212, 0, 0, 0.1);
}

.category-btn.active {
    background: var(--primary-color);
    color: var(--text-light);
    border-color: var(--primary-color);
}

.view-toggle-btn {
    background: rgba(26, 26, 26, 0.8);
    border: 1px solid var(--border-color);
    color: var(--text-light);
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.view-toggle-btn:hover, .view-toggle-btn.active {
    background: var(--primary-color);
    border-color: var(--primary-color);
}

.section-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--primary-color);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 1.25rem;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.25rem;
}

.product-card {
    background: rgba(20, 20, 20, 0.95);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    border-color: var(--primary-color);
    background: rgba(26, 26, 26, 0.95);
}

.category-tag {
    position: absolute;
    top: -1px;
    right: -1px;
    background: var(--primary-color);
    color: var(--text-light);
    padding: 0.25rem 0.75rem;
    border-radius: 0 7px 0 8px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.bundle-badge {
    position: absolute;
    top: -1px;
    left: -1px;
    background: rgba(212, 175, 55, 0.9);
    color: #000;
    padding: 0.25rem 0.6rem;
    border-radius: 7px 0 8px 0;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.product-image {
    width: 56px;
    height: 56px;
    object-fit: contain;
    background: rgba(10, 10, 10, 0.8);
    border-radius: 6px;
    padding: 8px;
    margin: 0.5rem auto 0.75rem;
    display: block;
}

.product-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-light);
    margin-bottom: 0.75rem;
    text-align: center;
    line-height: 1.3;
}

.product-price {
    background: var(--primary-color);
    color: var(--text-light);
    padding: 0.5rem 0.8rem;
    border-radius: 6px;
    font-size: 1.1rem;
    font-weight: 800;
    text-align: center;
    margin-bottom: 0.5rem;
}

.stock-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    padding: 0.3rem 0.6rem;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 0.75rem;
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

.bundle-items {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--border-color);
    display: none;
}

.bundle-items.expanded {
    display: block;
}

.bundle-toggle {
    background: transparent;
    border: 1px solid var(--border-color);
    color: var(--text-light);
    padding: 0.5rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.7rem;
    font-weight: 600;
    width: 100%;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
}

.bundle-toggle:hover {
    border-color: var(--primary-color);
    background: rgba(212, 0, 0, 0.1);
}

.bundle-item-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
    gap: 0.5rem;
}

.bundle-item {
    text-align: center;
    padding: 0.5rem;
    background: rgba(10, 10, 10, 0.6);
    border: 1px solid var(--border-color);
    border-radius: 4px;
}

.bundle-item img {
    width: 24px;
    height: 24px;
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
    background: rgba(20, 20, 20, 0.95);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1.25rem;
}

.basket-header {
    font-size: 1rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-transform: uppercase;
}

.cart-item {
    background: rgba(10, 10, 10, 0.8);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    padding: 0.75rem;
    margin-bottom: 0.75rem;
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
    width: 28px;
    height: 28px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.85rem;
    font-weight: 700;
}

.quantity-btn:hover {
    background: var(--primary-color);
}

.user-form {
    background: rgba(10, 10, 10, 0.8);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    padding: 1rem;
}

.total-section {
    margin-top: 1.25rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--primary-color);
}

@media (max-width: 1024px) {
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
                
                <div style="display: flex; gap: 0.5rem;">
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
    <div class="container">
        <div class="store-container">
            <!-- Products Section -->
            <div>
                <div class="section-title">
                    <i class="fas fa-box"></i> <span id="category-title">SHOWING ALL ITEMS</span>
                </div>
                
                <div id="products-container" class="products-grid">
                    @foreach($products as $product)
                        <div class="product-card" data-category="{{ $product->category_id ?? 'none' }}">
                            @if($product->bundleItems->count() > 0)
                                <span class="bundle-badge">
                                    <i class="fas fa-box"></i> BUNDLE
                                </span>
                            @endif
                            
                            @if($product->category)
                                <span class="category-tag">{{ $product->category->name }}</span>
                            @endif
                            
                            <img src="https://via.placeholder.com/56x56/d40000/e8e8e8?text={{ substr($product->product_name, 0, 1) }}" 
                                 alt="{{ $product->product_name }}" 
                                 class="product-image">
                            
                            <div class="product-name">{{ $product->product_name }}</div>
                            
                            <div class="product-price">${{ number_format($product->price, 2) }}</div>
                            
                            <div class="stock-badge {{ $product->qty_unit > 0 ? 'in-stock' : 'out-of-stock' }}">
                                <i class="fas fa-circle"></i>
                                {{ $product->qty_unit > 0 ? 'IN STOCK' : 'OUT OF STOCK' }}
                            </div>
                            
                            @if($product->bundleItems->count() > 0)
                                <button onclick="toggleBundle({{ $product->id }})" class="bundle-toggle">
                                    <i class="fas fa-chevron-down" id="bundle-icon-{{ $product->id }}"></i> 
                                    View Contents ({{ $product->bundleItems->count() }})
                                </button>
                                
                                <div class="bundle-items" id="bundle-{{ $product->id }}">
                                    <div class="bundle-item-grid">
                                        @foreach($product->bundleItems as $item)
                                            <div class="bundle-item">
                                                <img src="https://via.placeholder.com/24x24/d40000/e8e8e8?text=I" alt="Item">
                                                <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600;">
                                                    {{ $item->qty_unit }}x
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            <div style="display: flex; gap: 0.5rem; align-items: center; margin-top: auto; {{ $product->qty_unit > 0 ? '' : 'opacity: 0.5; pointer-events: none;' }}">
                                <input type="number" 
                                       value="1" 
                                       min="1" 
                                       class="form-input quantity-input" 
                                       id="quantity-{{ $product->id }}"
                                       style="width: 55px; padding: 0.5rem 0.25rem; text-align: center; font-weight: 700; font-size: 0.9rem;">
                                <button onclick="addToCart({{ $product->id }})" 
                                        class="btn btn-primary add-to-cart-btn" 
                                        style="flex: 1; padding: 0.55rem 0.5rem; font-size: 0.7rem; {{ session('cart_user') ? '' : 'display: none;' }}"
                                        data-product-id="{{ $product->id }}"
                                        {{ $product->qty_unit > 0 ? '' : 'disabled' }}>
                                    <i class="fas fa-cart-plus"></i> ADD TO BASKET
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
                        <i class="fas fa-shopping-basket"></i> YOUR BASKET
                    </div>
                    
                    <!-- Username Form or Display -->
                    <div id="basket-user-section">
                        <div id="username-form" style="{{ session('cart_user') ? 'display: none;' : '' }}">
                            <div class="user-form">
                                <div style="text-align: center; margin-bottom: 1rem;">
                                    <i class="fas fa-user" style="font-size: 2rem; color: var(--text-muted); margin-bottom: 0.5rem;"></i>
                                    <p class="text-muted" style="font-size: 0.85rem;">SHOPPING AS:</p>
                                </div>
                                
                                <div class="form-group" style="margin-bottom: 0.75rem;">
                                    <input type="text" 
                                           id="cart-username" 
                                           placeholder="Username"
                                           class="form-input"
                                           maxlength="50"
                                           pattern="[A-Za-z0-9_]+"
                                           value="{{ session('cart_user') }}"
                                           style="text-align: center; font-weight: 600; font-size: 0.9rem;">
                                </div>
                                
                                <button onclick="setCartUser()" class="btn btn-primary" style="width: 100%; padding: 0.6rem; font-size: 0.8rem;">
                                    <i class="fas fa-check"></i> CHANGE USER
                                </button>
                                
                                <div id="user-error" class="alert alert-error mt-2" style="display: none; font-size: 0.8rem;"></div>
                            </div>
                        </div>

                        <div id="username-display" style="{{ session('cart_user') ? '' : 'display: none;' }}">
                            <div style="background: rgba(10, 10, 10, 0.8); border: 1px solid var(--border-color); border-radius: 6px; padding: 0.75rem; margin-bottom: 1rem; text-align: center;">
                                <div class="text-muted" style="font-size: 0.7rem; margin-bottom: 0.25rem; text-transform: uppercase;">
                                    <i class="fas fa-user"></i> SHOPPING AS:
                                </div>
                                <div class="text-primary" style="font-weight: 700; font-size: 1rem;" id="current-cart-username">
                                    {{ session('cart_user') }}
                                </div>
                                <button onclick="changeCartUser()" class="btn btn-secondary" style="width: 100%; margin-top: 0.5rem; padding: 0.4rem; font-size: 0.7rem;">
                                    <i class="fas fa-exchange-alt"></i> CHANGE USER
                                </button>
                            </div>
                            
                            <!-- Cart Items -->
                            <div id="cart-items-section">
                                <div id="cart-items">
                                    <div style="text-align: center; padding: 2rem 1rem; opacity: 0.5;">
                                        <i class="fas fa-shopping-basket" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 0.5rem;"></i>
                                        <p class="text-muted" style="font-size: 0.85rem;">Your basket is empty</p>
                                    </div>
                                </div>
                                
                                <div class="total-section">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                        <span style="font-weight: 700; text-transform: uppercase; font-size: 0.8rem;">TOTAL COST:</span>
                                        <span class="text-primary" style="font-size: 1.5rem; font-weight: 800;" id="cart-total">$0.00</span>
                                    </div>
                                    
                                    <button onclick="checkout()" class="btn btn-primary" style="width: 100%; margin-bottom: 0.5rem; padding: 0.7rem; font-size: 0.8rem;" id="checkout-btn" disabled>
                                        <i class="fas fa-credit-card"></i> PROCEED TO CHECKOUT
                                    </button>
                                    
                                    <button onclick="showClearBasketModal()" class="btn btn-secondary" style="width: 100%; padding: 0.6rem; font-size: 0.75rem;">
                                        <i class="fas fa-trash-alt"></i> CLEAR BASKET
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

<!-- Confirmation Modal -->
<div id="confirmation-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-black border border-dragon-border rounded-lg p-8 max-w-md w-full mx-4" style="background: rgba(10, 10, 10, 0.98); border: 1px solid var(--border-color);">
        <div class="flex items-center mb-6">
            <div class="p-3 rounded-full bg-red-500/20 text-red-400 mr-4">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-semibold" style="color: var(--text-light);" id="confirm-title">Confirm Action</h3>
                <p style="color: var(--text-muted);" id="confirm-message">Are you sure?</p>
            </div>
        </div>
        
        <div class="flex justify-end gap-3">
            <button onclick="hideConfirmationModal()" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                Cancel
            </button>
            <button id="confirm-action-btn" onclick="executeConfirmation()" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                Confirm
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentView = 'grid';
let currentCategory = 'all';
let currentUsername = '{{ session("cart_user") }}';
let confirmationCallback = null;

// Confirmation Modal Functions
function showConfirmationModal(title, message, callback, actionText = 'Confirm') {
    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-message').textContent = message;
    document.getElementById('confirm-action-btn').textContent = actionText;
    confirmationCallback = callback;
    document.getElementById('confirmation-modal').classList.remove('hidden');
}

function hideConfirmationModal() {
    document.getElementById('confirmation-modal').classList.add('hidden');
    confirmationCallback = null;
}

function executeConfirmation() {
    if (confirmationCallback) {
        confirmationCallback();
        hideConfirmationModal();
    }
}

function showClearBasketModal() {
    showConfirmationModal(
        'Clear Basket',
        'Are you sure you want to clear all items from your basket?',
        clearCartItems,
        'Clear Basket'
    );
}

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
        $('#category-title').text("SHOWING ALL ITEMS");
    } else {
        const categoryName = $(this).text().trim();
        $('#category-title').text("SHOWING " + categoryName.toUpperCase());
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
        container.css('grid-template-columns', 'repeat(auto-fill, minmax(220px, 1fr))');
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

// Add to Cart - FIX: Parse quantity as integer to prevent concatenation
function addToCart(productId) {
    if (!currentUsername) {
        showError('Please set your username first');
        return;
    }
    
    const quantityInput = $(`#quantity-${productId}`);
    const quantity = parseInt(quantityInput.val(), 10) || 1;
    
    $.post('{{ route("store.add-to-cart") }}', {
        product_id: productId,
        quantity: quantity,
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        // Reset quantity input to 1 after adding to cart
        quantityInput.val(1);
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
            <div style="text-align: center; padding: 2rem 1rem; opacity: 0.5;">
                <i class="fas fa-shopping-basket" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 0.5rem;"></i>
                <p class="text-muted" style="font-size: 0.85rem;">Your basket is empty</p>
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
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                    <div style="flex: 1;">
                        <div class="text-light" style="font-weight: 700; font-size: 0.85rem; margin-bottom: 0.15rem;">${item.name}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">$${parseFloat(item.price).toFixed(2)} each</div>
                    </div>
                    <button class="quantity-btn" onclick="removeFromCart(${item.id})" style="width: auto; padding: 0 0.5rem;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span style="min-width: 35px; text-align: center; font-weight: 700; font-size: 0.9rem;">${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="text-primary" style="font-weight: 800; font-size: 1rem;">
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

// Clear Cart - Now without confirm, using modal
function clearCartItems() {
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
