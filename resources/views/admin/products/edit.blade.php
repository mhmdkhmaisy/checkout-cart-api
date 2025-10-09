@extends('admin.layout')

@section('title', 'Edit Product - Aragon RSPS Admin')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="glass-effect rounded-lg shadow-lg p-6 border border-dragon-border">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-dragon-red dragon-text-glow">Edit Product</h1>
            <a href="{{ route('admin.products.index') }}" class="bg-dragon-border hover:bg-dragon-silver-dark text-dragon-silver px-4 py-2 rounded-lg transition duration-200">
                Back to Products
            </a>
        </div>

        <form action="{{ route('admin.products.update', $product) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label for="name" class="block text-sm font-medium text-dragon-red mb-2">Product Name</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ $product->product_name }}"
                       class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent"
                       required>
                @error('name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-dragon-red mb-2">Price ($)</label>
                <input type="number" 
                       id="price" 
                       name="price" 
                       value="{{ $product->price }}"
                       step="0.01" 
                       min="0"
                       class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent"
                       required>
                @error('price')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" 
                       id="is_active" 
                       name="is_active" 
                       value="1"
                       {{ $product->is_active ? 'checked' : '' }}
                       class="w-4 h-4 text-dragon-red bg-dragon-black border-dragon-border rounded focus:ring-dragon-red focus:ring-2">
                <label for="is_active" class="ml-2 text-sm font-medium text-dragon-silver-dark">Product is active</label>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.products.index') }}" 
                   class="px-6 py-2 bg-dragon-border hover:bg-dragon-silver-dark text-dragon-silver rounded-lg transition duration-200">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 gradient-red hover:opacity-90 text-dragon-silver rounded-lg transition duration-200">
                    Update Product
                </button>
            </div>
        </form>
    </div>
</div>
@endsection