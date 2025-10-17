@extends('admin.layout')

@section('title', 'Edit Product - Aragon RSPS Admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">
            Edit Product
        </h2>
        <a href="{{ route('admin.products.index') }}" 
           class="px-6 py-3 bg-dragon-border hover:bg-dragon-silver-dark text-dragon-silver rounded-lg transition duration-200">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Products
        </a>
    </div>

    <div id="message-container"></div>

    <form id="product-form" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        @method('PUT')

        <div class="lg:col-span-2 space-y-6">
            <div class="glass-effect rounded-xl p-6 border border-dragon-border">
                <h3 class="text-xl font-bold text-dragon-red mb-4">
                    <i class="fas fa-info-circle mr-2"></i>
                    Basic Information
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-dragon-red mb-2">
                            Product Name <span class="text-red-400">*</span>
                        </label>
                        <input type="text" 
                               id="product_name" 
                               name="product_name" 
                               value="{{ $product->product_name }}"
                               class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all"
                               placeholder="e.g., Dragon Package"
                               required>
                        <div id="product_name_error" class="text-red-400 text-sm mt-1 hidden"></div>
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-dragon-red mb-2">
                            Category
                        </label>
                        <div class="flex gap-2">
                            <select id="category_id" 
                                    name="category_id"
                                    class="flex-1 px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all">
                                <option value="">No Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" 
                                    onclick="showNewCategoryModal()"
                                    class="px-4 py-3 bg-dragon-red hover:opacity-90 text-white rounded-lg transition-all">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div id="category_id_error" class="text-red-400 text-sm mt-1 hidden"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="item_id" class="block text-sm font-medium text-dragon-red mb-2">
                                Item ID <span class="text-red-400">*</span>
                            </label>
                            <input type="number" 
                                   id="item_id" 
                                   name="item_id" 
                                   value="{{ $product->item_id }}"
                                   class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all"
                                   placeholder="e.g., 4151"
                                   required>
                            <div id="item_id_error" class="text-red-400 text-sm mt-1 hidden"></div>
                        </div>

                        <div>
                            <label for="qty_unit" class="block text-sm font-medium text-dragon-red mb-2">
                                Quantity <span class="text-red-400">*</span>
                            </label>
                            <input type="number" 
                                   id="qty_unit" 
                                   name="qty_unit" 
                                   value="{{ $product->qty_unit }}"
                                   min="1"
                                   class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all"
                                   placeholder="e.g., 1"
                                   required>
                            <div id="qty_unit_error" class="text-red-400 text-sm mt-1 hidden"></div>
                        </div>
                    </div>

                    <div>
                        <label for="price" class="block text-sm font-medium text-dragon-red mb-2">
                            Price (USD) <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-dragon-silver-dark">$</span>
                            <input type="number" 
                                   id="price" 
                                   name="price" 
                                   value="{{ $product->price }}"
                                   step="0.01" 
                                   min="0.01"
                                   class="w-full pl-8 pr-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all"
                                   placeholder="9.99"
                                   required>
                        </div>
                        <div id="price_error" class="text-red-400 text-sm mt-1 hidden"></div>
                    </div>
                </div>
            </div>

            <div class="glass-effect rounded-xl p-6 border border-dragon-border">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-dragon-red">
                        <i class="fas fa-layer-group mr-2"></i>
                        Bundle Items
                    </h3>
                    <button type="button" 
                            onclick="addBundleItem()" 
                            class="px-4 py-2 bg-dragon-red hover:opacity-90 text-white rounded-lg transition-all">
                        <i class="fas fa-plus mr-2"></i>
                        Add Item
                    </button>
                </div>
                <p class="text-sm text-dragon-silver-dark mb-4">
                    Create a bundle by adding multiple items. Leave empty for single product.
                </p>
                <div id="bundle-items-container" class="space-y-3">
                    @if($product->bundleItems->count() > 0)
                        @foreach($product->bundleItems as $index => $bundleItem)
                            <div class="flex gap-3 items-center bundle-item bg-dragon-black p-4 rounded-lg border border-dragon-border hover:border-dragon-red transition-all" data-index="{{ $index }}">
                                <div class="flex-1">
                                    <label class="block text-xs text-dragon-silver-dark mb-1">Item ID</label>
                                    <input type="number" 
                                           name="bundle_items[{{ $index }}][item_id]" 
                                           value="{{ $bundleItem->item_id }}"
                                           placeholder="e.g., 995" 
                                           class="w-full px-3 py-2 bg-dragon-surface border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red"
                                           required>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs text-dragon-silver-dark mb-1">Quantity</label>
                                    <input type="number" 
                                           name="bundle_items[{{ $index }}][qty_unit]" 
                                           value="{{ $bundleItem->qty_unit }}"
                                           placeholder="e.g., 1000000" 
                                           min="1"
                                           class="w-full px-3 py-2 bg-dragon-surface border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red"
                                           required>
                                </div>
                                <button type="button" onclick="removeBundleItem({{ $index }})" 
                                        class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all mt-5">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-8 text-dragon-silver-dark">
                            <i class="fas fa-cube text-4xl mb-2 opacity-50"></i>
                            <p>No bundle items added yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-effect rounded-xl p-6 border border-dragon-border sticky top-6">
                <h3 class="text-xl font-bold text-dragon-red mb-4">
                    <i class="fas fa-cog mr-2"></i>
                    Settings
                </h3>
                
                <div class="space-y-4">
                    <div class="flex items-center p-4 bg-dragon-black rounded-lg border border-dragon-border">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ $product->is_active ? 'checked' : '' }}
                               class="w-5 h-5 text-dragon-red bg-dragon-black border-dragon-border rounded focus:ring-dragon-red focus:ring-2">
                        <label for="is_active" class="ml-3 text-sm font-medium text-dragon-silver">
                            Product is Active
                            <span class="block text-xs text-dragon-silver-dark mt-1">Customers can see and purchase this product</span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-dragon-border">
                        <button type="submit" 
                                class="w-full gradient-red hover:opacity-90 text-white font-medium py-4 rounded-lg transition-all transform hover:scale-105">
                            <i class="fas fa-save mr-2"></i>
                            <span id="submit-text">Update Product</span>
                        </button>
                        <a href="{{ route('admin.products.index') }}" 
                           class="block w-full text-center px-6 py-3 bg-dragon-border hover:bg-dragon-silver-dark text-dragon-silver rounded-lg transition duration-200 mt-3">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="new-category-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-dragon-surface rounded-lg shadow-lg p-6 w-full max-w-md border border-dragon-border">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-dragon-silver">Quick Add Category</h3>
                <button onclick="closeNewCategoryModal()" class="text-dragon-silver-dark hover:text-dragon-silver">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="new-category-form" class="space-y-4">
                <div>
                    <label for="new_category_name" class="block text-sm font-medium text-dragon-red mb-2">Category Name</label>
                    <input type="text" 
                           id="new_category_name" 
                           class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red">
                    <div id="new_category_error" class="text-red-400 text-sm mt-1 hidden"></div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <button type="button" onclick="closeNewCategoryModal()" 
                            class="px-6 py-2 bg-dragon-border hover:bg-dragon-silver-dark text-dragon-silver rounded-lg transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 gradient-red hover:opacity-90 text-dragon-silver rounded-lg transition duration-200">
                        Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let bundleItemCounter = {{ $product->bundleItems->count() }};

function addBundleItem(itemId = '', qtyUnit = '') {
    const container = document.getElementById('bundle-items-container');
    
    if (container.querySelector('.text-center')) {
        container.innerHTML = '';
    }
    
    const index = bundleItemCounter++;
    
    const itemHtml = `
        <div class="flex gap-3 items-center bundle-item bg-dragon-black p-4 rounded-lg border border-dragon-border hover:border-dragon-red transition-all" data-index="${index}">
            <div class="flex-1">
                <label class="block text-xs text-dragon-silver-dark mb-1">Item ID</label>
                <input type="number" 
                       name="bundle_items[${index}][item_id]" 
                       value="${itemId}"
                       placeholder="e.g., 995" 
                       class="w-full px-3 py-2 bg-dragon-surface border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red"
                       required>
            </div>
            <div class="flex-1">
                <label class="block text-xs text-dragon-silver-dark mb-1">Quantity</label>
                <input type="number" 
                       name="bundle_items[${index}][qty_unit]" 
                       value="${qtyUnit}"
                       placeholder="e.g., 1000000" 
                       min="1"
                       class="w-full px-3 py-2 bg-dragon-surface border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red"
                       required>
            </div>
            <button type="button" onclick="removeBundleItem(${index})" 
                    class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all mt-5">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
}

function removeBundleItem(index) {
    const item = document.querySelector(`.bundle-item[data-index="${index}"]`);
    if (item) {
        item.remove();
    }
    
    const container = document.getElementById('bundle-items-container');
    if (container.children.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-dragon-silver-dark">
                <i class="fas fa-cube text-4xl mb-2 opacity-50"></i>
                <p>No bundle items added yet</p>
            </div>
        `;
    }
}

function showNewCategoryModal() {
    document.getElementById('new-category-modal').classList.remove('hidden');
    document.getElementById('new_category_name').focus();
}

function closeNewCategoryModal() {
    document.getElementById('new-category-modal').classList.add('hidden');
    document.getElementById('new-category-form').reset();
    document.getElementById('new_category_error').classList.add('hidden');
}

document.getElementById('new-category-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const categoryName = document.getElementById('new_category_name').value;
    
    fetch('/admin/categories', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            name: categoryName,
            is_active: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('category_id');
            const option = new Option(data.category.name, data.category.id, true, true);
            select.add(option);
            closeNewCategoryModal();
            showMessage('Category created successfully!', 'success');
        } else {
            document.getElementById('new_category_error').textContent = 'Error creating category';
            document.getElementById('new_category_error').classList.remove('hidden');
        }
    })
    .catch(error => {
        document.getElementById('new_category_error').textContent = 'Error: ' + error.message;
        document.getElementById('new_category_error').classList.remove('hidden');
    });
});

function clearErrors() {
    document.querySelectorAll('[id$="_error"]').forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
    });
}

function showErrors(errors) {
    clearErrors();
    Object.keys(errors).forEach(field => {
        const errorDiv = document.getElementById(`${field}_error`);
        if (errorDiv) {
            errorDiv.textContent = errors[field][0];
            errorDiv.classList.remove('hidden');
        }
    });
}

function showMessage(message, type) {
    const container = document.getElementById('message-container');
    const alertClass = type === 'success' ? 'bg-green-600' : 'bg-red-600';
    
    container.innerHTML = `
        <div class="${alertClass} text-dragon-silver px-6 py-4 rounded-lg mb-4">
            ${message}
        </div>
    `;
    
    setTimeout(() => {
        container.innerHTML = '';
    }, 5000);
}

document.getElementById('product-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        showMessage('Security token not found. Please refresh the page.', 'error');
        return;
    }
    
    const formData = new FormData(this);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        if (key !== '_token' && key !== '_method') {
            data[key] = value;
        }
    }
    
    data.is_active = document.getElementById('is_active').checked ? 1 : 0;
    data._method = 'PUT';
    
    const bundleItems = [];
    document.querySelectorAll('.bundle-item').forEach((item, index) => {
        const itemId = item.querySelector('input[name*="[item_id]"]')?.value;
        const qtyUnit = item.querySelector('input[name*="[qty_unit]"]')?.value;
        if (itemId && qtyUnit) {
            bundleItems.push({
                item_id: parseInt(itemId),
                qty_unit: parseInt(qtyUnit)
            });
        }
    });
    
    if (bundleItems.length > 0) {
        data.bundle_items = bundleItems;
    }

    fetch('/admin/products/{{ $product->id }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            setTimeout(() => {
                window.location.href = '/admin/products';
            }, 1000);
        } else {
            showMessage('An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (error.errors) {
            showErrors(error.errors);
        } else {
            showMessage('An error occurred: ' + (error.message || 'Unknown error'), 'error');
        }
    });
});

document.getElementById('new-category-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeNewCategoryModal();
    }
});
</script>
@endsection
