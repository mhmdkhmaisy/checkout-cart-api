@extends('admin.layout')

@section('title', 'Edit Product')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Edit Product</h1>
            <a href="{{ route('admin.products.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
                Back to Products
            </a>
        </div>

        <form action="{{ route('admin.products.update', $product) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Product Name</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ $product->product_name }}"
                       class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       required>
                @error('name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-gray-300 mb-2">Price ($)</label>
                <input type="number" 
                       id="price" 
                       name="price" 
                       value="{{ $product->price }}"
                       step="0.01" 
                       min="0"
                       class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                       class="w-4 h-4 text-green-600 bg-gray-700 border-gray-600 rounded focus:ring-green-500 focus:ring-2">
                <label for="is_active" class="ml-2 text-sm font-medium text-gray-300">Product is active</label>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.products.index') }}" 
                   class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition duration-200">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg transition duration-200">
                    Update Product
                </button>
            </div>
        </form>
    </div>
</div>
@endsection