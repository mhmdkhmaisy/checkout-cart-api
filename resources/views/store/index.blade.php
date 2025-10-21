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
    background: rgba(20, 16, 16, 0.92);
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
        <!-- Active Promotions Banner Carousel -->
        @if($promotions->count() > 0)
        <div style="margin-bottom: 2rem;">
            <div class="section-title">
                <i class="fas fa-gift"></i> ACTIVE PROMOTIONS
            </div>

            <!-- Banner Carousel -->
            <div style="position: relative; margin-bottom: 2rem;">
                <div id="banner-carousel" style="width: 100%; height: 150px; border-radius: 12px; overflow: hidden; position: relative; background: rgba(20, 20, 20, 0.95); border: 2px solid var(--border-gold); box-shadow: 0 0 30px rgba(212, 165, 116, 0.3);">
                    @foreach($promotions as $index => $promo)
                    <div class="banner-slide" data-index="{{ $index }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: {{ $index === 0 ? 'flex' : 'none' }}; align-items: center; justify-content: center; padding: 2rem; background: linear-gradient(135deg, rgba(212, 165, 116, 0.08) 0%, rgba(196, 30, 58, 0.08) 100%); animation: edgeGlow 3s ease-in-out infinite alternate;">
                        <div style="text-align: center;">
                            <div style="font-family: 'Press Start 2P', monospace; font-size: 1.5rem; color: #FFD700; text-shadow: 2px 2px 4px rgba(0,0,0,0.8), 0 0 10px rgba(255, 215, 0, 0.5); margin-bottom: 0.5rem; letter-spacing: 2px;">
                                {{ strtoupper($promo->title) }}
                            </div>
                            <div style="font-size: 0.9rem; color: var(--text-muted); max-width: 600px; margin: 0 auto;">
                                {{ $promo->description }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($promotions->count() > 1)
                <!-- Navigation Controls -->
                <button id="banner-prev" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: rgba(196, 30, 58, 0.8); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; z-index: 10; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button id="banner-next" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: rgba(196, 30, 58, 0.8); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; z-index: 10; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-chevron-right"></i>
                </button>

                <!-- Indicators -->
                <div style="position: absolute; bottom: 15px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 10;">
                    @foreach($promotions as $index => $promo)
                    <button class="banner-indicator" data-index="{{ $index }}" style="width: 10px; height: 10px; border-radius: 50%; background: {{ $index === 0 ? 'rgba(255, 215, 0, 0.9)' : 'rgba(255, 255, 255, 0.3)' }}; border: none; cursor: pointer; transition: all 0.3s ease;"></button>
                    @endforeach
                </div>
                @endif
            </div>

            <style>
                @import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');

                @keyframes edgeGlow {
                    0% {
                        box-shadow: inset 0 0 20px rgba(255, 182, 193, 0.3), inset 0 0 40px rgba(255, 182, 193, 0.1);
                    }
                    100% {
                        box-shadow: inset 0 0 30px rgba(255, 182, 193, 0.5), inset 0 0 60px rgba(255, 182, 193, 0.2);
                    }
                }

                #banner-prev:hover, #banner-next:hover {
                    background: rgba(196, 30, 58, 1);
                    transform: translateY(-50%) scale(1.1);
                }

                .banner-indicator.active {
                    background: rgba(255, 215, 0, 0.9) !important;
                    transform: scale(1.3);
                }
            </style>

            <!-- Promotion Details Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1rem;">
                @foreach($promotions as $promo)
                @php
                    $progress = collect($userProgress)->firstWhere('promo.id', $promo->id);
                @endphp
                <div style="background: linear-gradient(135deg, rgba(212, 165, 116, 0.08) 0%, rgba(196, 30, 58, 0.08) 100%); border: 1px solid var(--border-gold); border-radius: 8px; padding: 1.25rem; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -10px; right: -10px; background: var(--accent-gold); color: #000; padding: 0.25rem 2rem; transform: rotate(25deg); font-size: 0.65rem; font-weight: 800; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                        {{ strtoupper($promo->bonus_type) }}
                    </div>
                    
                    <div style="margin-bottom: 0.75rem;">
                        <h3 style="color: var(--text-gold); font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem;">
                            <i class="fas fa-trophy"></i> {{ $promo->title }}
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.4;">{{ $promo->description }}</p>
                    </div>
                    
                    <div style="background: rgba(0, 0, 0, 0.3); border-radius: 6px; padding: 0.75rem; margin-bottom: 0.75rem;">
                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.25rem; text-transform: uppercase;">
                            Requirements:
                        </div>
                        <div style="color: var(--text-light); font-weight: 600; font-size: 0.9rem;">
                            <i class="fas fa-dollar-sign"></i> Spend ${{ number_format($promo->min_amount, 2) }} or more
                        </div>
                    </div>
                    
                    @if($progress && session('cart_user'))
                    <div style="margin-bottom: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Your Progress:</span>
                            <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-gold);">
                                ${{ number_format($progress['progress_amount'], 2) }} / ${{ number_format($promo->min_amount, 2) }}
                            </span>
                        </div>
                        <div style="background: rgba(0, 0, 0, 0.4); border-radius: 4px; height: 8px; overflow: hidden;">
                            <div style="background: linear-gradient(90deg, var(--accent-gold) 0%, var(--accent-ember) 100%); height: 100%; width: {{ min(100, $progress['progress_percent']) }}%; transition: width 0.3s ease;"></div>
                        </div>
                        @if($progress['can_claim'])
                        <button onclick="claimPromotion({{ $promo->id }})" class="btn btn-primary" style="width: 100%; margin-top: 0.75rem; background: var(--accent-gold); color: #000; font-weight: 800; padding: 0.6rem; font-size: 0.8rem;">
                            <i class="fas fa-gift"></i> CLAIM REWARD
                        </button>
                        @endif
                    </div>
                    @endif
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.7rem; color: var(--text-muted); border-top: 1px solid rgba(212, 165, 116, 0.2); padding-top: 0.5rem;">
                        <span><i class="fas fa-clock"></i> {{ $promo->time_remaining }}</span>
                        @if($promo->global_claim_limit)
                        <span><i class="fas fa-users"></i> {{ $promo->claimed_global }} / {{ $promo->global_claim_limit }} claimed</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

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
                                    
                                    <!-- Terms & Conditions Checkbox -->
                                    <div style="margin-bottom: 1rem; padding: 0.75rem; background: rgba(26, 26, 26, 0.8); border: 1px solid var(--border-color); border-radius: 6px;">
                                        <label style="display: flex; align-items: start; cursor: pointer; font-size: 0.75rem;">
                                            <input type="checkbox" id="terms-checkbox" style="margin-right: 0.5rem; margin-top: 0.15rem; cursor: pointer;">
                                            <span style="color: var(--text-muted); line-height: 1.4;">
                                                I agree to the <a href='{{ route("store.terms") }}' target="_blank" style="color: var(--primary-color); text-decoration: underline;">Terms & Conditions</a> and understand all sales are final with no refunds.
                                            </span>
                                        </label>
                                    </div>
                                    
                                    <button onclick="checkout('paypal')" class="btn btn-primary" style="width: 100%; margin-bottom: 0.5rem; padding: 0.7rem; font-size: 0.8rem; background: #0070ba; box-shadow: none;" id="paypal-checkout-btn" disabled>
                                        <i class="fab fa-paypal"></i> CHECKOUT WITH PAYPAL
                                    </button>
                                    
                                    <button onclick="checkout('coinbase')" class="btn btn-primary" style="width: 100%; margin-bottom: 0.5rem; padding: 0.7rem; font-size: 0.8rem; background: #0052ff; box-shadow: none;" id="coinbase-checkout-btn" disabled>
                                        <i class="fab fa-bitcoin"></i> CHECKOUT WITH COINBASE
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
<div id="confirmation-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.85); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: rgba(10, 10, 10, 0.98); border: 1px solid var(--border-color); border-radius: 8px; padding: 2rem; max-width: 500px; width: 90%; margin: 0 auto;">
        <div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
            <div style="padding: 0.75rem; border-radius: 50%; background: rgba(239, 68, 68, 0.2); color: #ef4444; margin-right: 1rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <h3 id="confirm-title" style="font-size: 1.25rem; font-weight: 700; color: var(--text-light); margin-bottom: 0.25rem;">Confirm Action</h3>
                <p id="confirm-message" style="color: var(--text-muted); font-size: 0.9rem;">Are you sure?</p>
            </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button onclick="hideConfirmationModal()" style="padding: 0.6rem 1.5rem; background: #4b5563; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.3s ease;">
                Cancel
            </button>
            <button id="confirm-action-btn" onclick="executeConfirmation()" style="padding: 0.6rem 1.5rem; background: #dc2626; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.3s ease;">
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
    const modal = document.getElementById('confirmation-modal');
    modal.style.display = 'flex';
}

function hideConfirmationModal() {
    const modal = document.getElementById('confirmation-modal');
    modal.style.display = 'none';
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
    
    $.post('/store/set-user', {
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
    $.post('/store/clear-user', {
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
    
    $.post('/store/add-to-cart', {
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
    $.get('/store/cart')
        .done(function(response) {
            renderCart(response.cart, response.total);
        });
}

// Render Cart
function renderCart(cart, total) {
    const cartItems = $('#cart-items');
    const paypalBtn = $('#paypal-checkout-btn');
    const coinbaseBtn = $('#coinbase-checkout-btn');
    
    if (Object.keys(cart).length === 0) {
        cartItems.html(`
            <div style="text-align: center; padding: 2rem 1rem; opacity: 0.5;">
                <i class="fas fa-shopping-basket" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 0.5rem;"></i>
                <p class="text-muted" style="font-size: 0.85rem;">Your basket is empty</p>
            </div>
        `);
        $('#cart-total').text('$0.00');
        if (paypalBtn.length) paypalBtn.prop('disabled', true);
        if (coinbaseBtn.length) coinbaseBtn.prop('disabled', true);
        return;
    }
    
    let html = '';
    Object.values(cart).forEach(item => {
        const qty = parseInt(item.quantity);
        const minusQty = qty - 1;
        const plusQty = qty + 1;
        
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
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${minusQty})">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span style="min-width: 35px; text-align: center; font-weight: 700; font-size: 0.9rem;">${qty}</span>
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${plusQty})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="text-primary" style="font-weight: 800; font-size: 1rem;">
                        $${(parseFloat(item.price) * qty).toFixed(2)}
                    </div>
                </div>
            </div>
        `;
    });
    
    cartItems.html(html);
    $('#cart-total').text('$' + total);
    if (paypalBtn.length) paypalBtn.prop('disabled', false);
    if (coinbaseBtn.length) coinbaseBtn.prop('disabled', false);
}

// Update Quantity - FIX: Ensure quantity is parsed as integer
function updateQuantity(productId, quantity) {
    const qty = parseInt(quantity, 10);
    
    $.post('/store/update-cart', {
        product_id: productId,
        quantity: qty,
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        renderCart(response.cart, response.total);
    });
}

// Remove from Cart
function removeFromCart(productId) {
    $.ajax({
        url: '/store/remove-from-cart/' + productId,
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
    $.post('/store/clear-cart', {
        _token: '{{ csrf_token() }}'
    })
    .done(function() {
        loadCart();
    });
}

// Checkout with PayPal or Coinbase
async function checkout(paymentMethod) {
    if (!currentUsername) {
        alert('Please set your username first');
        return;
    }
    
    // Check if terms checkbox is checked
    const termsCheckbox = document.getElementById('terms-checkbox');
    if (!termsCheckbox.checked) {
        alert('Please agree to the Terms & Conditions before proceeding to checkout');
        return;
    }
    
    // Get cart items
    const cartResponse = await $.get('/store/cart');
    const cart = cartResponse.cart;
    const total = cartResponse.total;
    
    if (Object.keys(cart).length === 0) {
        alert('Your basket is empty');
        return;
    }
    
    // Prepare checkout data
    const checkoutData = {
        user_id: currentUsername,
        payment_method: paymentMethod,
        items: Object.values(cart).map(item => ({
            product_id: parseInt(item.id),
            name: item.name,
            price: parseFloat(item.price),
            quantity: parseInt(item.quantity)
        })),
        currency: 'USD'
    };
    
    try {
        // Show loading state
        const btnId = paymentMethod === 'paypal' ? '#paypal-checkout-btn' : '#coinbase-checkout-btn';
        const originalText = $(btnId).html();
        $(btnId).html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);
        
        // Make API call to checkout endpoint
        const response = await $.ajax({
            url: '/api/checkout',
            type: 'POST',
            data: JSON.stringify(checkoutData),
            contentType: 'application/json',
            dataType: 'json'
        });
        
        // Restore button
        $(btnId).html(originalText).prop('disabled', false);
        
        // If checkout successful and has payment URL, open in new tab
        if (response.success && (response.payment_url || response.checkout_url)) {
            const paymentUrl = response.payment_url || response.checkout_url;
            window.open(paymentUrl, '_blank');
            
            // Optionally clear cart after successful checkout
            // clearCartItems();
        } else {
            alert('Checkout failed: ' + (response.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Checkout error:', error);
        
        // Restore button
        const btnId = paymentMethod === 'paypal' ? '#paypal-checkout-btn' : '#coinbase-checkout-btn';
        const btnText = paymentMethod === 'paypal' ? '<i class="fab fa-paypal"></i> CHECKOUT WITH PAYPAL' : '<i class="fab fa-bitcoin"></i> CHECKOUT WITH COINBASE';
        $(btnId).html(btnText).prop('disabled', false);
        
        let errorMessage = 'Checkout failed: ';
        if (error.responseJSON && error.responseJSON.error) {
            errorMessage += error.responseJSON.error;
        } else if (error.responseJSON && error.responseJSON.message) {
            errorMessage += error.responseJSON.message;
        } else {
            errorMessage += 'Please try again';
        }
        
        alert(errorMessage);
    }
}

// Claim promotion reward
async function claimPromotion(promotionId) {
    if (!currentUsername) {
        alert('Please set your username first to claim rewards');
        return;
    }
    
    try {
        const response = await $.ajax({
            url: `/promotions/${promotionId}/claim`,
            type: 'POST',
            data: JSON.stringify({ username: currentUsername }),
            contentType: 'application/json',
            dataType: 'json'
        });
        
        if (response.success) {
            alert('🎉 ' + response.message + '\n\nRewards:\n' + response.rewards.map(r => `- ${r.item_name} x${r.item_amount}`).join('\n'));
            // Reload the page to refresh promotion status
            location.reload();
        } else {
            alert('Failed to claim promotion: ' + (response.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Claim error:', error);
        let errorMessage = 'Failed to claim promotion: ';
        if (error.responseJSON && error.responseJSON.message) {
            errorMessage += error.responseJSON.message;
        } else {
            errorMessage += 'Please try again';
        }
        alert(errorMessage);
    }
}

// Banner Carousel Logic
let currentBannerIndex = 0;
let bannerAutoplayInterval = null;
const bannerSlides = document.querySelectorAll('.banner-slide');
const bannerIndicators = document.querySelectorAll('.banner-indicator');

function showBannerSlide(index) {
    bannerSlides.forEach((slide, i) => {
        slide.style.display = i === index ? 'flex' : 'none';
    });

    bannerIndicators.forEach((indicator, i) => {
        if (i === index) {
            indicator.classList.add('active');
            indicator.style.background = 'rgba(255, 215, 0, 0.9)';
        } else {
            indicator.classList.remove('active');
            indicator.style.background = 'rgba(255, 255, 255, 0.3)';
        }
    });

    currentBannerIndex = index;
}

function nextBannerSlide() {
    const nextIndex = (currentBannerIndex + 1) % bannerSlides.length;
    showBannerSlide(nextIndex);
}

function prevBannerSlide() {
    const prevIndex = (currentBannerIndex - 1 + bannerSlides.length) % bannerSlides.length;
    showBannerSlide(prevIndex);
}

function startBannerAutoplay() {
    if (bannerSlides.length > 1) {
        bannerAutoplayInterval = setInterval(nextBannerSlide, 5000);
    }
}

function stopBannerAutoplay() {
    if (bannerAutoplayInterval) {
        clearInterval(bannerAutoplayInterval);
    }
}

// Initialize banner carousel
if (bannerSlides.length > 0) {
    const prevBtn = document.getElementById('banner-prev');
    const nextBtn = document.getElementById('banner-next');

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            stopBannerAutoplay();
            prevBannerSlide();
            startBannerAutoplay();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            stopBannerAutoplay();
            nextBannerSlide();
            startBannerAutoplay();
        });
    }

    bannerIndicators.forEach((indicator, index) => {
        indicator.addEventListener('click', function() {
            stopBannerAutoplay();
            showBannerSlide(index);
            startBannerAutoplay();
        });
    });

    startBannerAutoplay();
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
