@extends('admin.layout')

@section('title', 'Edit Vote Site - Aragon RSPS Admin')
@section('page-title', 'Edit Vote Site')
@section('page-description', 'Modify voting site configuration')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass-effect rounded-xl p-8 border border-dragon-border">
        <form action="{{ route('admin.vote.update', $voteSite) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label for="title" class="block text-sm font-medium text-dragon-red mb-2">
                    Site Title <span class="text-red-400">*</span>
                </label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       value="{{ old('title', $voteSite->title) }}"
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
                       value="{{ old('site_id', $voteSite->site_id) }}"
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
                          required>{{ old('url', $voteSite->url) }}</textarea>
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
                       {{ old('active', $voteSite->active) ? 'checked' : '' }}
                       class="w-4 h-4 text-dragon-red bg-dragon-black border-dragon-border rounded focus:ring-dragon-red focus:ring-2">
                <label for="active" class="ml-2 text-sm text-dragon-silver">
                    Active (site will be visible to users)
                </label>
            </div>

            <!-- Site Statistics -->
            <div class="bg-dragon-surface rounded-lg p-4 border border-dragon-border">
                <h4 class="text-sm font-medium text-dragon-red mb-3">Site Statistics</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-dragon-silver-dark">Total Votes:</span>
                        <span class="text-dragon-silver font-medium">{{ $voteSite->votes()->count() }}</span>
                    </div>
                    <div>
                        <span class="text-dragon-silver-dark">Completed:</span>
                        <span class="text-green-400 font-medium">{{ $voteSite->votes()->completed()->count() }}</span>
                    </div>
                    <div>
                        <span class="text-dragon-silver-dark">Created:</span>
                        <span class="text-dragon-silver font-medium">{{ $voteSite->created_at->format('M j, Y') }}</span>
                    </div>
                    <div>
                        <span class="text-dragon-silver-dark">Last Updated:</span>
                        <span class="text-dragon-silver font-medium">{{ $voteSite->updated_at->format('M j, Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="flex space-x-4">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-dragon-red hover:bg-dragon-red-bright text-white font-medium rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Update Vote Site
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