@extends('admin.layout')

@section('title', 'Create Vote Site - Aragon RSPS Admin')
@section('page-title', 'Create Vote Site')
@section('page-description', 'Add a new voting site')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass-effect rounded-xl p-8 border border-dragon-border">
        <form action="{{ route('admin.vote.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label for="title" class="block text-sm font-medium text-dragon-red mb-2">
                    Site Title <span class="text-red-400">*</span>
                </label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       value="{{ old('title') }}"
                       class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:ring-2 focus:ring-dragon-red focus:border-transparent @error('title') border-red-500 @enderror"
                       placeholder="e.g., RuneLocus"
                       required>
                @error('title')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="site_id" class="block text-sm font-medium text-dragon-red mb-2">
                    Site ID <span class="text-red-400">*</span>
                </label>
                <input type="text" 
                       id="site_id" 
                       name="site_id" 
                       value="{{ old('site_id') }}"
                       class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:ring-2 focus:ring-dragon-red focus:border-transparent @error('site_id') border-red-500 @enderror"
                       placeholder="Your site ID from the toplist"
                       required>
                @error('site_id')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="url" class="block text-sm font-medium text-dragon-red mb-2">
                    Vote URL <span class="text-red-400">*</span>
                </label>
                <textarea id="url" 
                          name="url" 
                          rows="3"
                          class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:ring-2 focus:ring-dragon-red focus:border-transparent @error('url') border-red-500 @enderror"
                          placeholder="https://example.com/vote?id={sid}&incentive={incentive}"
                          required>{{ old('url') }}</textarea>
                @error('url')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-dragon-silver-dark">
                    Use <code class="bg-dragon-surface px-1 rounded">{sid}</code> for site ID and <code class="bg-dragon-surface px-1 rounded">{incentive}</code> for callback UID
                </p>
            </div>

            <div class="flex items-center">
                <input type="checkbox" 
                       id="active" 
                       name="active" 
                       value="1"
                       {{ old('active', true) ? 'checked' : '' }}
                       class="w-4 h-4 text-dragon-red bg-dragon-black border-dragon-border rounded focus:ring-dragon-red focus:ring-2">
                <label for="active" class="ml-2 text-sm text-dragon-silver">
                    Active (site will be visible to users)
                </label>
            </div>

            <div class="flex space-x-4">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-dragon-red hover:bg-dragon-red-bright text-white font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Create Vote Site
                </button>
                <a href="{{ route('admin.vote.index') }}" 
                   class="flex-1 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg text-center transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
