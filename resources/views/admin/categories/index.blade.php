@extends('admin.layout')

@section('title', 'Categories - Aragon RSPS Donation Admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">
            Categories Management
        </h2>
        <button onclick="showCreateForm()" 
           class="gradient-red px-6 py-3 rounded-lg font-medium hover:opacity-90 transition-opacity">
            Add New Category
        </button>
    </div>

    <div id="message-container"></div>

    <div id="category-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-dragon-surface rounded-lg shadow-lg p-6 w-full max-w-md border border-dragon-border">
                <div class="flex justify-between items-center mb-6">
                    <h3 id="modal-title" class="text-xl font-bold text-dragon-silver">Add New Category</h3>
                    <button onclick="closeModal()" class="text-dragon-silver-dark hover:text-dragon-silver">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="category-form" class="space-y-4">
                    @csrf
                    <input type="hidden" id="category-id" name="category_id">
                    <input type="hidden" id="form-method" name="_method" value="POST">
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-dragon-red mb-2">Category Name</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent"
                               required>
                        <div id="name_error" class="text-red-400 text-sm mt-1 hidden"></div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-dragon-red mb-2">Description (Optional)</label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3"
                                  class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent"></textarea>
                        <div id="description_error" class="text-red-400 text-sm mt-1 hidden"></div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               checked
                               class="w-4 h-4 text-dragon-red bg-dragon-black border-dragon-border rounded focus:ring-dragon-red focus:ring-2">
                        <label for="is_active" class="ml-2 text-sm font-medium text-dragon-silver-dark">Category is active</label>
                    </div>

                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="button" onclick="closeModal()" 
                                class="px-6 py-2 bg-dragon-border hover:bg-dragon-silver-dark text-dragon-silver rounded-lg transition duration-200">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 gradient-red hover:opacity-90 text-dragon-silver rounded-lg transition duration-200">
                            <span id="submit-text">Create Category</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="glass-effect rounded-xl overflow-hidden border border-dragon-border">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dragon-surface border-b border-dragon-border">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">ID</th>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">Category Name</th>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">Description</th>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">Products</th>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">Status</th>
                        <th class="px-6 py-4 text-left font-semibold text-dragon-red">Actions</th>
                    </tr>
                </thead>
                <tbody id="categories-table-body" class="divide-y divide-dragon-border">
                    @forelse($categories as $category)
                        <tr class="hover:bg-dragon-surface transition-colors" data-category-id="{{ $category->id }}">
                            <td class="px-6 py-4 text-dragon-silver">{{ $category->id }}</td>
                            <td class="px-6 py-4 font-medium text-dragon-silver">{{ $category->name }}</td>
                            <td class="px-6 py-4 text-dragon-silver-dark text-sm">
                                {{ $category->description ? Str::limit($category->description, 50) : '-' }}
                            </td>
                            <td class="px-6 py-4 text-dragon-silver">
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-600 text-blue-100">
                                    {{ $category->products_count }} product(s)
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if($category->is_active) bg-green-600 text-green-100
                                    @else bg-red-600 text-red-100
                                    @endif">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="editCategory({{ $category->id }})" 
                                            class="text-blue-400 hover:text-blue-300">Edit</button>
                                    <button onclick="deleteCategory({{ $category->id }})" 
                                            class="text-red-400 hover:text-red-300">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-dragon-silver-dark">
                                No categories found. <button onclick="showCreateForm()" 
                                                         class="text-dragon-red hover:underline">Create your first category</button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($categories->hasPages())
        <div class="flex justify-center">
            {{ $categories->links() }}
        </div>
    @endif
</div>

<script>
let isEditing = false;
let currentCategoryId = null;

function showCreateForm() {
    isEditing = false;
    currentCategoryId = null;
    document.getElementById('modal-title').textContent = 'Add New Category';
    document.getElementById('submit-text').textContent = 'Create Category';
    document.getElementById('form-method').value = 'POST';
    document.getElementById('category-form').reset();
    document.getElementById('is_active').checked = true;
    document.getElementById('category-modal').classList.remove('hidden');
    clearErrors();
}

function editCategory(categoryId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                      document.querySelector('input[name="_token"]')?.value;
    
    if (!csrfToken) {
        console.error('CSRF token not found');
        showMessage('Security token not found. Please refresh the page.', 'error');
        return;
    }

    fetch(`{{ url('/admin/categories') }}/${categoryId}/edit`, {
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
            currentCategoryId = categoryId;
            const category = data.category;
            
            document.getElementById('modal-title').textContent = 'Edit Category';
            document.getElementById('submit-text').textContent = 'Update Category';
            document.getElementById('form-method').value = 'PUT';
            document.getElementById('category-id').value = category.id;
            document.getElementById('name').value = category.name || '';
            document.getElementById('description').value = category.description || '';
            document.getElementById('is_active').checked = Boolean(category.is_active);
            
            document.getElementById('category-modal').classList.remove('hidden');
            clearErrors();
        } else {
            showMessage('Error loading category data', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error loading category data: ' + error.message, 'error');
    });
}

function deleteCategory(categoryId) {
    if (!confirm('Are you sure you want to delete this category?')) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                      document.querySelector('input[name="_token"]')?.value;

    fetch(`{{ url('/admin/categories') }}/${categoryId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
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
            const row = document.querySelector(`tr[data-category-id="${categoryId}"]`);
            if (row) {
                row.remove();
            }
            
            const tbody = document.getElementById('categories-table-body');
            if (tbody.children.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-dragon-silver-dark">
                            No categories found. <button onclick="showCreateForm()" 
                                                     class="text-dragon-red hover:underline">Create your first category</button>
                        </td>
                    </tr>
                `;
            }
        } else {
            showMessage(data.message || 'Error deleting category', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage(error.message || 'Error deleting category', 'error');
    });
}

function closeModal() {
    document.getElementById('category-modal').classList.add('hidden');
    document.getElementById('category-form').reset();
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

document.getElementById('category-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                      document.querySelector('input[name="_token"]')?.value;
    
    if (!csrfToken) {
        showMessage('Security token not found. Please refresh the page.', 'error');
        return;
    }
    
    const formData = new FormData(this);
    const url = isEditing ? `{{ url('/admin/categories') }}/${currentCategoryId}` : '{{ route("admin.categories.store") }}';
    
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (key !== '_method' && key !== 'category_id' && key !== '_token') {
            data[key] = value;
        }
    }
    
    data.is_active = document.getElementById('is_active').checked ? 1 : 0;
    
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

document.getElementById('category-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection
