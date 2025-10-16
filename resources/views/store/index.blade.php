@extends('layouts.public')

@section('title', 'Dragon\'s Store - Aragon RSPS')
@section('description', 'Browse and purchase items for Aragon RSPS')

@section('content')
<style>
.store-container {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
    margin-top: 2rem;
}

.category-filter {
    background: rgba(26, 26, 26, 0.95);
    backdrop-filter: blur(10px);
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
    color: var(--text-muted);
    padding: 0.5rem 1rem;
    border-radius: 6px;
    margin: 0.25rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
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

.product-image {
    width: 48px;
    height: 48px;
    object-fit: contain;
    background: rgba(10, 10, 10, 0.8);
    border-radius: 6px;
    padding: 4px;
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

.bundle-item-image {
    width: 24px;
    height: 24px;
    object-fit: contain;
}

.basket-sidebar {
    position: sticky;
    top: 140px;
    height: fit-content;
    max-height: calc(100vh - 160px);
    overflow-y: auto;
}

.basket-card {
    background: rgba(26, 26, 26, 0.8);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.5rem;
}

.basket-header {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
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
    width: 28px;
    height: 28px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
}

.quantity-btn:hover {
    background: var(--primary-color);
}

.user-form {
    background: rgba(26, 26, 26, 0.8);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.5rem;
}

.total-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid var(--border-color);
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
                        <i class="fas fa-th"></i> All
                    </button>
                    @foreach($categories as $category)
                        <button class="category-btn" data-category="{{ $category->id }}">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
                
                <div class="view-toggle">
                    <button class="view-toggle-btn active" data-view="list">
                        <i class="fas fa-list"></i> List View
                    </button>
                    <button class="view-toggle-btn" data-view="grid">
                        <i class="fas fa-th"></i> Grid View
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
                <h3 class="text-primary mb-3" style="font-size: 1.25rem; margin-top: 1rem;">
                    <span id="category-title">Showing All result's</span>
                </h3>
                
                <div id="products-container" class="products-grid">
                    @foreach($products as $product)
                        <div class="product-card" data-category="{{ $product->category_id ?? 'none' }}">
                            <div style="display: flex; align-items: start; gap: 1rem;">
                                <img src="https://via.placeholder.com/48x48/d40000/e8e8e8?text=Item" 
                                     alt="{{ $product->product_name }}" 
                                     class="product-image">
                                
                                <div style="flex: 1;">
                                    <h4 class="text-primary" style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">
                                        {{ $product->product_name }}
                                    </h4>
                                    
                                    <p class="text-muted" style="font-size: 0.875rem; margin-bottom: 0.75rem;">
                                        {{ $product->product_description ?? 'A special bundle' }}
                                    </p>
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span class="text-primary" style="font-size: 1.25rem; font-weight: 700;">
                                            <i class="fas fa-gem"></i> {{ $product->qty_unit }} Gems
                                        </span>
                                        
                                        @if($product->bundleItems->count() > 0)
                                            <button onclick="toggleBundle({{ $product->id }})" class="category-btn" style="margin: 0;">
                                                <i class="fas fa-chevron-down" id="bundle-icon-{{ $product->id }}"></i> Show Items
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            @if($product->bundleItems->count() > 0)
                                <div class="bundle-items" id="bundle-{{ $product->id }}">
                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 0.75rem;">
                                        @foreach($product->bundleItems as $item)
                                            <div style="text-align: center;">
                                                <img src="https://via.placeholder.com/32x32/d40000/e8e8e8?text=I" 
                                                     alt="Item" 
                                                     class="bundle-item-image"
                                                     style="margin: 0 auto 0.25rem;">
                                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                    {{ $item->qty_unit }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            <div style="margin-top: 1rem;">
                                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;">
                                    <input type="number" 
                                           value="1" 
                                           min="1" 
                                           class="form-input quantity-input" 
                                           id="quantity-{{ $product->id }}"
                                           style="width: 80px; padding: 0.5rem;">
                                    <button onclick="addToCart({{ $product->id }})" 
                                            class="btn btn-primary add-to-cart-btn" 
                                            style="flex: 1; {{ session('cart_user') ? '' : 'display: none;' }}"
                                            data-product-id="{{ $product->id }}">
                                        Add to Basket
                                    </button>
                                </div>
                                @if($product->qty_unit == 0)
                                    <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; border-radius: 6px; padding: 0.5rem; text-align: center;">
                                        <span style="color: #ef4444; font-weight: 600;">No stock left</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Basket Sidebar -->
            <div class="basket-sidebar">
                <div class="basket-card">
                    <div class="basket-header">
                        <i class="fas fa-shopping-basket"></i> Basket
                    </div>
                    
                    <!-- Username Form or Display -->
                    <div id="basket-user-section">
                        <div id="username-form" style="{{ session('cart_user') ? 'display: none;' : '' }}">
                            <p class="text-muted mb-3" style="font-size: 0.875rem;">Enter your username to make purchases</p>
                            
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
                            <div style="background: rgba(10, 10, 10, 0.8); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                <div class="text-muted" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Shopping as:</div>
                                <div class="text-primary" style="font-weight: 600;" id="current-cart-username">
                                    {{ session('cart_user') }}
                                </div>
                                <button onclick="changeCartUser()" class="btn btn-secondary" style="width: 100%; margin-top: 0.5rem; padding: 0.5rem; font-size: 0.875rem;">
                                    <i class="fas fa-user-edit"></i> Change
                                </button>
                            </div>
                            
                            <!-- Cart Items -->
                            <div id="cart-items-section">
                                <div id="cart-items">
                                    <p class="text-muted" style="text-align: center; padding: 2rem 0;">Your basket is empty</p>
                                </div>
                                
                                <div class="total-section">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                        <span style="font-weight: 600;">Total Cost:</span>
                                        <span class="text-primary" style="font-size: 1.5rem; font-weight: 700;" id="cart-total">$0.00</span>
                                    </div>
                                    
                                    <button onclick="checkout()" class="btn btn-primary" style="width: 100%; margin-bottom: 0.5rem;" id="checkout-btn" disabled>
                                        <i class="fas fa-credit-card"></i> Checkout
                                    </button>
                                    
                                    <button onclick="clearCartItems()" class="btn btn-secondary" style="width: 100%;">
                                        <i class="fas fa-trash"></i> Clear Basket
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
        $('#category-title').text("Showing All result's");
    } else {
        const categoryName = $(this).text().trim();
        $('#category-title').text("Showing " + categoryName + " result's");
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
        container.css('grid-template-columns', 'repeat(auto-fill, minmax(280px, 1fr))');
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
        cartItems.html('<p class="text-muted" style="text-align: center; padding: 2rem 0;">Your basket is empty</p>');
        $('#cart-total').text('$0.00');
        checkoutBtn.prop('disabled', true);
        return;
    }
    
    let html = '';
    Object.values(cart).forEach(item => {
        html += `
            <div class="cart-item" style="display: block; margin-bottom: 0.75rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <div style="flex: 1;">
                        <div class="text-light" style="font-weight: 600; font-size: 0.9rem;">${item.name}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">$${parseFloat(item.price).toFixed(2)} each</div>
                    </div>
                    <button class="quantity-btn" onclick="removeFromCart(${item.id})" style="width: auto; padding: 0 0.5rem;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="quantity-controls" style="justify-content: space-between;">
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                        <span style="min-width: 32px; text-align: center; font-weight: 600; font-size: 0.9rem;">${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                    </div>
                    <div class="text-primary" style="font-weight: 700;">
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
