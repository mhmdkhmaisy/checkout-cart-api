@extends('admin.layout')

@section('title', 'Edit Client')
@section('header', 'Edit Client')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass-effect rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-semibold text-dragon-silver">Edit {{ $client->os_display }} v{{ $client->version }}</h2>
                <p class="text-dragon-silver-dark text-sm">Update client information and settings</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.clients.show', $client) }}" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-eye mr-1"></i>View
                </a>
                <a href="{{ route('admin.clients.index') }}" class="px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i>Back
                </a>
            </div>
        </div>

        <form action="{{ route('admin.clients.update', $client) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Platform (Read-only) -->
            <div>
                <label class="block text-sm font-medium text-dragon-silver mb-2">Platform</label>
                <div class="flex items-center p-3 bg-dragon-black border border-dragon-border rounded-lg">
                    <i class="{{ $client->os === 'windows' ? 'fab fa-windows' : ($client->os === 'minimal' ? 'fas fa-feather-alt' : 'fas fa-coffee') }} mr-2 text-dragon-silver-dark"></i>
                    <span class="text-dragon-silver">{{ $client->os_display }}</span>
                </div>
                <p class="text-dragon-silver-dark text-sm mt-1">Platform cannot be changed after upload.</p>
            </div>

            <!-- File Info (Read-only) -->
            <div>
                <label class="block text-sm font-medium text-dragon-silver mb-2">File Information</label>
                <div class="p-3 bg-dragon-black border border-dragon-border rounded-lg">
                    <span class="text-dragon-silver font-medium">{{ $client->original_filename }}</span>
                    <span class="text-dragon-silver-dark ml-2">({{ $client->formatted_size }})</span>
                </div>
                <p class="text-dragon-silver-dark text-sm mt-1">To change the file, upload a new client version.</p>
            </div>

            <!-- Version -->
            <div>
                <label for="version" class="block text-sm font-medium text-dragon-silver mb-2">Version *</label>
                <input type="text" class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-3 py-2 focus:outline-none focus:border-dragon-red @error('version') border-red-500 @enderror" 
                       id="version" name="version" value="{{ old('version', $client->version) }}" 
                       required pattern="^\d+\.\d+\.\d+$">
                @error('version')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dragon-silver-dark text-sm mt-1">Format: major.minor.patch (e.g., 0.1.3)</p>
            </div>

            <!-- Changelog -->
            <div>
                <label for="changelog" class="block text-sm font-medium text-dragon-silver mb-2">Changelog</label>
                <textarea class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-3 py-2 focus:outline-none focus:border-dragon-red @error('changelog') border-red-500 @enderror" 
                          id="changelog" name="changelog" rows="6" 
                          placeholder="What's new in this version?">{{ old('changelog', $client->changelog) }}</textarea>
                @error('changelog')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dragon-silver-dark text-sm mt-1">Describe what's new or changed in this version.</p>
            </div>

            <!-- Enable Client -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" class="sr-only peer" id="enabled" name="enabled" 
                           value="1" {{ old('enabled', $client->enabled) ? 'checked' : '' }}>
                    <div class="relative w-11 h-6 bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-dragon-red"></div>
                    <span class="ml-3 text-dragon-silver">Enable this client</span>
                </label>
                <p class="text-dragon-silver-dark text-sm mt-1">
                    If checked, this will become the active client for {{ $client->os_display }} and disable any other versions.
                </p>
            </div>

            <!-- Current Status Info -->
            <div class="bg-blue-900/20 border border-blue-500/30 rounded-lg p-4">
                <h3 class="flex items-center text-blue-400 font-medium mb-3">
                    <i class="fas fa-info-circle mr-2"></i>Current Status
                </h3>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center">
                        <span class="text-dragon-silver-dark">Status:</span>
                        <span class="ml-2 px-2 py-1 text-xs rounded {{ $client->enabled ? 'bg-green-600 text-green-100' : 'bg-gray-600 text-gray-100' }}">
                            {{ $client->enabled ? 'Active' : 'Disabled' }}
                        </span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-dragon-silver-dark">SHA-256:</span>
                        <code class="ml-2 text-dragon-silver text-xs">{{ substr($client->hash, 0, 16) }}...</code>
                    </li>
                    <li class="flex items-center">
                        <span class="text-dragon-silver-dark">Uploaded:</span>
                        <span class="ml-2 text-dragon-silver">{{ $client->created_at->format('M j, Y') }}</span>
                    </li>
                </ul>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-3 pt-6">
                <a href="{{ route('admin.clients.show', $client) }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i>Update Client
                </button>
            </div>
        </form>

        <!-- Separate Delete Form (outside the update form) -->
        <div class="mt-8 pt-6 border-t border-dragon-border">
            <div class="bg-red-900/20 border border-red-500/30 rounded-lg p-4">
                <h3 class="flex items-center text-red-400 font-medium mb-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Danger Zone
                </h3>
                <p class="text-dragon-silver-dark text-sm mb-4">
                    Once you delete this client, there is no going back. Please be certain.
                </p>
                <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors" 
                            onclick="return confirm('Are you sure you want to delete this client? This action cannot be undone.')">
                        <i class="fas fa-trash mr-2"></i>Delete Client
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection