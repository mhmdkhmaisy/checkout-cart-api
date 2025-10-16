@extends('admin.layout')

@section('title', 'Products - Aragon RSPS Donation Admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">
            Products Management
        </h2>
        <a href="{{ route('admin.products.create') }}" 
           class="gradient-red px-6 py-3 rounded-lg font-medium hover:opacity-90 transition-opacity inline-block">
            <i class="fas fa-plus mr-2"></i>
            Add New Product
        </a>
    </div>

    <!-- Success/Error Messages -->
    <div id="message-container"></div>

    <!-- Create/Edit Form Modal -->
    <div id="product-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-dragon-surface rounded-lg shadow-lg p-6 w-full max-w-md border border-dragon-border">
                <div class="flex justify-between items-center mb-6">
                    <h3 id="modal-title" class="text-xl font-bold text-dragon-silver">Add New Product</h3>
                    <button onclick="closeModal()" class="text-dragon-silver-dark hover:text-dragon-silver">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="product-form" class="space-y-4">
                    @csrf
                    <input type="hidden" id="product-id" name="product_id">
                    <input type="hidden" id="form-method" name="_method" value="POST">
                    
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-dragon-red mb-2">Product Name</label>
                        <input type="text" 
                               id="product_name" 
                               name="product_name" 
                               class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent"
                               required>
                        <div id="product_name_error" class="text-red-400 text-sm mt-1 hidden"></div>
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-dragon-red mb-2">Category</label>
                        <select id="category_id" 
                                name="category_id"
                                class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent">
                            <option value="">No Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div id="category_id_error" class="text-red-400 text-sm mt-1 hidden"></div>
                    </div>

                    <div>
                        <label for="item_id" class="block text-sm font-medium text-dragon-red mb-2">Item ID</label>
                        <input type="number" 
                               id="item_id" 
                               name="item_id" 
                               class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent"
                               required>
                        <div id="item_id_error" class="text-red-400 text-sm mt-1 hidden"></div>
                    </div>

                    <div>
                        <label for="qty_unit" class="block text-sm font-medium text-dragon-red mb-2">Quantity Unit</label>
                        <input type="number" 
                               id="qty_unit" 
                               name="qty_unit" 
                               min="1"
                               class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent"
                               required>
                        <div id="qty_unit_error" class="text-red-400 text-sm mt-1 hidden"></div>
                    </div>

                    <div>
                        <label for="price" class="block text-sm font-medium text-dragon-red mb-2">Price ($)</label>
                        <input type="number" 
                               id="price" 
                               name="price" 
                               step="0.01" 
                               min="0.01"
                               class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent"
                               required>
                        <div id="price_error" class="text-red-400 text-sm mt-1 hidden"></div>
                    </div>

                    <div class="border-t border-dragon-border pt-4">
                        <div class="flex justify-between items-center mb-3">
                            <label class="block text-sm font-medium text-dragon-red">Bundle Items (Optional)</label>
                            <button type="button" onclick="addBundleItem()" 
                                    class="text-sm px-3 py-1 bg-dragon-red text-white rounded hover:opacity-90">
                                + Add Item
                            </button>
                        </div>
                        <p class="text-xs text-dragon-silver-dark mb-2">Add items to create a bundle/pack. Leave empty for single product.</p>
                        <div id="bundle-items-container" class="space-y-2">
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               checked
                               class="w-4 h-4 text-dragon-red bg-dragon-black border-dragon-border rounded focus:ring-dragon-red focus:ring-2">
                        <label for="is_active" class="ml-2 text-sm font-medium text-dragon-silver-dark">Product is active</label>
                    </div>

                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="button" onclick="closeModal()" 
                                class="px-6 py-2 bg-dragon-border hover:bg-dragon-silver-dark text-dragon-silver rounded-lg transition duration-200">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 gradient-red hover:opacity-90 text-dragon-silver rounded-lg transition duration-200">
                            <span id="submit-text">Create Product</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="glass-effect rounded-xl overflow-hidden border border-dragon-border">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dragon-surface border-b border-dragon-border">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">ID</th>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">Product Name</th>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">Category</th>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">Item ID</th>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">Qty Unit</th>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">Price</th>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">Type</th>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">Status</th>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">Actions</th>
                    </tr>
                </thead>
                <tbody id="products-table-body" class="divide-y divide-dragon-border">
                    @forelse($products as $product)
                        <tr class="hover:bg-dragon-surface transition-colors" data-product-id="{{ $product->id }}">
                            <td class="px-6 py-4 text-dragon-silver">{{ $product->id }}</td>
                            <td class="px-6 py-4 font-medium text-dragon-silver">{{ $product->product_name }}</td>
                            <td class="px-6 py-4 text-dragon-silver">
                                {{ $product->category ? $product->category->name : '-' }}
                            </td>
                            <td class="px-6 py-4 text-dragon-silver">{{ $product->item_id }}</td>
                            <td class="px-6 py-4 text-dragon-silver">{{ $product->qty_unit }}</td>
                            <td class="px-6 py-4 text-dragon-silver">${{ number_format($product->price, 2) }}</td>
                            <td class="px-6 py-4">
                                @if($product->bundleItems->count() > 0)
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-purple-600 text-purple-100">
                                        Bundle ({{ $product->bundleItems->count() }} items)
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-600 text-blue-100">
                                        Single
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if($product->is_active) bg-green-600 text-green-100
                                    @else bg-red-600 text-red-100
                                    @endif">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="editProduct({{ $product->id }})" 
                                            class="text-blue-400 hover:text-blue-300">Edit</button>
                                    <button onclick="deleteProduct({{ $product->id }})" 
                                            class="text-red-400 hover:text-red-300">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-dragon-silver-dark">
                                No products found. <button onclick="showCreateForm()" 
                                                         class="text-dragon-red hover:underline">Create your first product</button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="flex justify-center">
            {{ $products->links() }}
        </div>
    @endif
</div>

<script>
let isEditing = false;
let currentProductId = null;

function showCreateForm() {
    isEditing = false;
    currentProductId = null;
    document.getElementById('modal-title').textContent = 'Add New Product';
    document.getElementById('submit-text').textContent = 'Create Product';
    document.getElementById('form-method').value = 'POST';
    document.getElementById('product-form').reset();
    document.getElementById('is_active').checked = true;
    loadBundleItems([]);
    document.getElementById('product-modal').classList.remove('hidden');
    clearErrors();
}

function editProduct(productId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                      document.querySelector('input[name="_token"]')?.value;
    
    if (!csrfToken) {
        console.error('CSRF token not found');
        showMessage('Security token not found. Please refresh the page.', 'error');
        return;
    }

    fetch(`{{ url('/admin/products') }}/${productId}/edit`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            isEditing = true;
            currentProductId = productId;
            const product = data.product;
            
            document.getElementById('modal-title').textContent = 'Edit Product';
            document.getElementById('submit-text').textContent = 'Update Product';
            document.getElementById('form-method').value = 'PUT';
            document.getElementById('product-id').value = product.id;
            document.getElementById('product_name').value = product.product_name || '';
            document.getElementById('category_id').value = product.category_id || '';
            document.getElementById('item_id').value = product.item_id || '';
            document.getElementById('qty_unit').value = product.qty_unit || '';
            document.getElementById('price').value = product.price || '';
            document.getElementById('is_active').checked = Boolean(product.is_active);
            
            loadBundleItems(product.bundle_items || []);
            
            document.getElementById('product-modal').classList.remove('hidden');
            clearErrors();
        } else {
            showMessage('Error loading product data', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error loading product data: ' + error.message, 'error');
    });
}

function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product?')) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                      document.querySelector('input[name="_token"]')?.value;

    fetch(`{{ url('/admin/products') }}/${productId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            const row = document.querySelector(`tr[data-product-id="${productId}"]`);
            if (row) {
                row.remove();
            }
            
            // Check if table is empty
            const tbody = document.getElementById('products-table-body');
            if (tbody.children.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-dragon-silver-dark">
                            No products found. <button onclick="showCreateForm()" 
                                                     class="text-dragon-red hover:underline">Create your first product</button>
                        </td>
                    </tr>
                `;
            }
        } else {
            showMessage('Error deleting product', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error deleting product: ' + error.message, 'error');
    });
}

function closeModal() {
    document.getElementById('product-modal').classList.add('hidden');
    document.getElementById('product-form').reset();
    clearErrors();
}

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

let bundleItemCounter = 0;

function addBundleItem(itemId = '', qtyUnit = '') {
    const container = document.getElementById('bundle-items-container');
    const index = bundleItemCounter++;
    
    const itemHtml = `
        <div class="flex gap-2 items-start bundle-item" data-index="${index}">
            <div class="flex-1">
                <input type="number" 
                       name="bundle_items[${index}][item_id]" 
                       value="${itemId}"
                       placeholder="Item ID" 
                       class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent"
                       required>
            </div>
            <div class="flex-1">
                <input type="number" 
                       name="bundle_items[${index}][qty_unit]" 
                       value="${qtyUnit}"
                       placeholder="Qty Unit" 
                       min="1"
                       class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent"
                       required>
            </div>
            <button type="button" onclick="removeBundleItem(${index})" 
                    class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Remove
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
}

function loadBundleItems(bundleItems) {
    const container = document.getElementById('bundle-items-container');
    container.innerHTML = '';
    bundleItemCounter = 0;
    
    if (bundleItems && bundleItems.length > 0) {
        bundleItems.forEach(item => {
            addBundleItem(item.item_id, item.qty_unit);
        });
    }
}

document.getElementById('product-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                      document.querySelector('input[name="_token"]')?.value;
    
    if (!csrfToken) {
        showMessage('Security token not found. Please refresh the page.', 'error');
        return;
    }
    
    const formData = new FormData(this);
    const url = isEditing ? `{{ url('/admin/products') }}/${currentProductId}` : '{{ route("admin.products.store") }}';
    
    // Convert FormData to regular object
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (key !== '_method' && key !== 'product_id' && key !== '_token') {
            data[key] = value;
        }
    }
    
    // Handle checkbox
    data.is_active = document.getElementById('is_active').checked ? 1 : 0;
    
    // Collect bundle items
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
    
    // Add method for updates
    if (isEditing) {
        data._method = 'PUT';
    }

    fetch(url, {
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
            closeModal();
            // Reload page to update table
            setTimeout(() => {
                window.location.reload();
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

// Close modal when clicking outside
document.getElementById('product-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection