@extends('admin.layout')

@section('title', 'Client Details')
@section('header', 'Client Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="glass-effect rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <i class="fas fa-{{ $client->os === 'windows' ? 'windows' : ($client->os === 'macos' ? 'apple' : ($client->os === 'linux' ? 'linux' : 'coffee')) }} text-2xl mr-3 text-dragon-red"></i>
                <div>
                    <h2 class="text-xl font-semibold text-dragon-silver">{{ $client->os_display }} v{{ $client->version }}</h2>
                    <p class="text-dragon-silver-dark text-sm">Client details and download information</p>
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.clients.edit', $client) }}" class="px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('admin.clients.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Client Info -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-dragon-silver border-b border-dragon-border pb-2">Client Information</h3>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-dragon-silver-dark text-sm">Platform:</span>
                        <div class="flex items-center mt-1">
                            <i class="fas fa-{{ $client->os === 'windows' ? 'windows' : ($client->os === 'macos' ? 'apple' : ($client->os === 'linux' ? 'linux' : 'coffee')) }} mr-2 text-dragon-silver-dark"></i>
                            <span class="text-dragon-silver">{{ $client->os_display }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-dragon-silver-dark text-sm">Version:</span>
                        <p class="text-dragon-silver font-medium">{{ $client->version }}</p>
                    </div>

                    <div>
                        <span class="text-dragon-silver-dark text-sm">Status:</span>
                        <div class="mt-1">
                            <span class="px-2 py-1 text-xs rounded {{ $client->enabled ? 'bg-green-600 text-green-100' : 'bg-gray-600 text-gray-100' }}">
                                {{ $client->enabled ? 'Active' : 'Disabled' }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="text-dragon-silver-dark text-sm">File Size:</span>
                        <p class="text-dragon-silver">{{ $client->formatted_size }}</p>
                    </div>

                    <div>
                        <span class="text-dragon-silver-dark text-sm">Original Filename:</span>
                        <p class="text-dragon-silver font-mono text-sm">{{ $client->original_filename }}</p>
                    </div>
                </div>
            </div>

            <!-- Technical Details -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-dragon-silver border-b border-dragon-border pb-2">Technical Details</h3>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-dragon-silver-dark text-sm">SHA-256 Hash:</span>
                        <p class="text-dragon-silver font-mono text-xs break-all">{{ $client->hash }}</p>
                    </div>

                    <div>
                        <span class="text-dragon-silver-dark text-sm">File Path:</span>
                        <p class="text-dragon-silver font-mono text-xs break-all">{{ $client->file_path }}</p>
                    </div>

                    <div>
                        <span class="text-dragon-silver-dark text-sm">Uploaded:</span>
                        <p class="text-dragon-silver">{{ $client->created_at->format('F j, Y \a\t g:i A') }}</p>
                        <p class="text-dragon-silver-dark text-xs">({{ $client->created_at->diffForHumans() }})</p>
                    </div>

                    <div>
                        <span class="text-dragon-silver-dark text-sm">Last Updated:</span>
                        <p class="text-dragon-silver">{{ $client->updated_at->format('F j, Y \a\t g:i A') }}</p>
                        <p class="text-dragon-silver-dark text-xs">({{ $client->updated_at->diffForHumans() }})</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Changelog -->
        @if($client->changelog)
            <div class="mt-6">
                <h3 class="text-lg font-medium text-dragon-silver border-b border-dragon-border pb-2 mb-4">Changelog</h3>
                <div class="bg-dragon-black p-4 rounded-lg border border-dragon-border">
                    <pre class="text-dragon-silver text-sm whitespace-pre-wrap">{{ $client->changelog }}</pre>
                </div>
            </div>
        @endif

        <!-- Download Information -->
        <div class="mt-6">
            <h3 class="text-lg font-medium text-dragon-silver border-b border-dragon-border pb-2 mb-4">Download Information</h3>
            
            @if($client->enabled)
                <div class="space-y-4">
                    <div>
                        <span class="text-dragon-silver-dark text-sm">Direct Download URL:</span>
                        <div class="flex mt-2">
                            <input type="text" class="flex-1 bg-dragon-black border border-dragon-border text-dragon-silver rounded-l-lg px-3 py-2 text-sm" 
                                   readonly value="{{ $client->download_url }}" id="downloadUrl">
                            <button class="px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-r-lg transition-colors" 
                                    onclick="copyToClipboard('downloadUrl')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <span class="text-dragon-silver-dark text-sm">Manifest Entry:</span>
                        <div class="flex mt-2">
                            <textarea class="flex-1 bg-dragon-black border border-dragon-border text-dragon-silver rounded-l-lg px-3 py-2 text-xs font-mono" 
                                      readonly rows="6" id="manifestEntry">{{ json_encode([
                                'os' => $client->os,
                                'version' => $client->version,
                                'url' => url('storage/clients/' . basename($client->file_path)),
                                'size' => $client->size,
                                'hash' => $client->hash
                            ], JSON_PRETTY_PRINT) }}</textarea>
                            <button class="px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-r-lg transition-colors self-start" 
                                    onclick="copyToClipboard('manifestEntry')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-4">
                        <a href="{{ $client->download_url }}" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors inline-flex items-center">
                            <i class="fas fa-download mr-2"></i>Download Client
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-yellow-900/20 border border-yellow-500/30 rounded-lg p-4">
                    <div class="flex items-center text-yellow-400">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span>This client is currently disabled and not available for download.</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand('copy');
    
    // Show feedback
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.remove('bg-dragon-red', 'hover:bg-dragon-red-bright');
    button.classList.add('bg-green-600');
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove('bg-green-600');
        button.classList.add('bg-dragon-red', 'hover:bg-dragon-red-bright');
    }, 2000);
}
</script>
@endpush
@endsection