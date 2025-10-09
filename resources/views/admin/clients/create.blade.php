@extends('admin.layout')

@section('title', 'Upload New Client')
@section('header', 'Upload New Client')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass-effect rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-semibold text-dragon-silver">Upload New Client</h2>
                <p class="text-dragon-silver-dark text-sm">Add a new game client for a specific platform</p>
            </div>
            <a href="{{ route('admin.clients.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Clients
            </a>
        </div>

        <form action="{{ route('admin.clients.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <!-- Platform Selection -->
            <div>
                <label for="os" class="block text-sm font-medium text-dragon-silver mb-2">Platform *</label>
                <select name="os" id="os" class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-3 py-2 focus:outline-none focus:border-dragon-red @error('os') border-red-500 @enderror" required>
                    <option value="">Select Platform</option>
                    @foreach($osTypes as $value => $label)
                        <option value="{{ $value }}" 
                                data-extension="{{ App\Models\Client::FILE_EXTENSIONS[$value] }}"
                                {{ old('os') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('os')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dragon-silver-dark text-sm mt-1">Select the target platform for this client build.</p>
            </div>

            <!-- File Upload -->
            <div>
                <label for="file" class="block text-sm font-medium text-dragon-silver mb-2">Client File *</label>
                <div class="relative">
                    <input type="file" class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-3 py-2 focus:outline-none focus:border-dragon-red @error('file') border-red-500 @enderror" 
                           id="file" name="file" required accept=".exe,.dmg,.AppImage,.jar">
                </div>
                @error('file')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dragon-silver-dark text-sm mt-1" id="file-help">
                    Maximum file size: 500MB. Expected extension will be shown when you select a platform.
                </p>
            </div>

            <!-- Version -->
            <div>
                <label for="version" class="block text-sm font-medium text-dragon-silver mb-2">Version</label>
                <input type="text" class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-3 py-2 focus:outline-none focus:border-dragon-red @error('version') border-red-500 @enderror" 
                       id="version" name="version" value="{{ old('version') }}" 
                       placeholder="e.g., 0.1.3" pattern="^\d+\.\d+\.\d+$">
                @error('version')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dragon-silver-dark text-sm mt-1">
                    Leave empty to auto-increment from the latest version. Format: major.minor.patch (e.g., 0.1.3)
                </p>
            </div>

            <!-- Changelog -->
            <div>
                <label for="changelog" class="block text-sm font-medium text-dragon-silver mb-2">Changelog</label>
                <textarea class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-3 py-2 focus:outline-none focus:border-dragon-red @error('changelog') border-red-500 @enderror" 
                          id="changelog" name="changelog" rows="4" 
                          placeholder="What's new in this version?">{{ old('changelog') }}</textarea>
                @error('changelog')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dragon-silver-dark text-sm mt-1">Optional. Describe what's new or changed in this version.</p>
            </div>

            <!-- Enable Client -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" class="sr-only peer" id="enabled" name="enabled" 
                           value="1" {{ old('enabled', true) ? 'checked' : '' }}>
                    <div class="relative w-11 h-6 bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-dragon-red"></div>
                    <span class="ml-3 text-dragon-silver">Enable this client immediately</span>
                </label>
                <p class="text-dragon-silver-dark text-sm mt-1">
                    If checked, this will become the active client for the selected platform and disable any previous versions.
                </p>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-between pt-6">
                <a href="{{ route('admin.clients.index') }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                    <i class="fas fa-upload mr-2"></i>Upload Client
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Update file extension help text when platform changes
    $('#os').change(function() {
        const selectedOption = $(this).find(':selected');
        const extension = selectedOption.data('extension');
        
        if (extension) {
            $('#file-help').html(`Maximum file size: 500MB. Expected extension: <strong>${extension}</strong>`);
            $('#file').attr('accept', extension);
        } else {
            $('#file-help').text('Maximum file size: 500MB. Expected extension will be shown when you select a platform.');
            $('#file').attr('accept', '.exe,.dmg,.AppImage,.jar');
        }
    });

    // Auto-generate version suggestion
    $('#os').change(function() {
        const os = $(this).val();
        if (os && !$('#version').val()) {
            $('#version').attr('placeholder', 'Auto-generated (e.g., 0.1.3)');
        }
    });
});
</script>
@endpush
@endsection