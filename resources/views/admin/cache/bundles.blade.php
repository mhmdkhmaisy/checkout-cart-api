@extends('admin.layout')

@section('title', 'Cache Bundles')
@section('header', 'Cache Bundles Management')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="glass-effect rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-500/20 text-green-400">
                    <i class="fas fa-archive text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-dragon-silver-dark">Active Bundles</p>
                    <p class="text-2xl font-semibold text-dragon-silver">{{ $activeBundles }}</p>
                </div>
            </div>
        </div>

        <div class="glass-effect rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-500/20 text-red-400">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-dragon-silver-dark">Expired</p>
                    <p class="text-2xl font-semibold text-dragon-silver">{{ $expiredBundles }}</p>
                </div>
            </div>
        </div>

        <div class="glass-effect rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-500/20 text-blue-400">
                    <i class="fas fa-hdd text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-dragon-silver-dark">Total Size</p>
                    <p class="text-2xl font-semibold text-dragon-silver">
                        @php
                            $bytes = $totalSize;
                            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                            for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                                $bytes /= 1024;
                            }
                            echo round($bytes, 2) . ' ' . $units[$i];
                        @endphp
                    </p>
                </div>
            </div>
        </div>

        <div class="glass-effect rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-500/20 text-purple-400">
                    <i class="fas fa-compress-arrows-alt text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-dragon-silver-dark">Compression</p>
                    <p class="text-2xl font-semibold text-dragon-silver">tar.gz</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('admin.cache.bundles.clear-expired') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors"
                        onclick="return confirm('Are you sure you want to clear all expired bundles?')">
                    <i class="fas fa-broom mr-2"></i>Clear Expired ({{ $expiredBundles }})
                </button>
            </form>

            <form method="POST" action="{{ route('admin.cache.bundles.clear-all') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors"
                        onclick="return confirm('Are you sure you want to clear ALL bundles? This cannot be undone!')">
                    <i class="fas fa-trash mr-2"></i>Clear All
                </button>
            </form>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.cache.index') }}" class="px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                <i class="fas fa-file-archive mr-2"></i>Back to Files
            </a>
        </div>
    </div>

    <!-- Patch System Section -->
    <div class="glass-effect rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-dragon-border">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-dragon-silver">Delta Patch System</h3>
                    <p class="text-dragon-silver-dark text-sm">Incremental cache updates using patch-based versioning</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($latestVersion)
                        <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-full text-sm font-medium">
                            v{{ $latestVersion }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-dragon-black/30 rounded-lg p-4">
                    <div class="text-sm text-dragon-silver-dark">Latest Version</div>
                    <div class="text-xl font-semibold text-dragon-silver">{{ $latestVersion ?? 'None' }}</div>
                </div>
                <div class="bg-dragon-black/30 rounded-lg p-4">
                    <div class="text-sm text-dragon-silver-dark">Base Patches</div>
                    <div class="text-xl font-semibold text-dragon-silver">{{ $basePatches }}</div>
                </div>
                <div class="bg-dragon-black/30 rounded-lg p-4">
                    <div class="text-sm text-dragon-silver-dark">Incremental Patches</div>
                    <div class="text-xl font-semibold text-dragon-silver">{{ $incrementalPatches }}</div>
                </div>
                <div class="bg-dragon-black/30 rounded-lg p-4">
                    <div class="text-sm text-dragon-silver-dark">Total Patch Size</div>
                    <div class="text-xl font-semibold text-dragon-silver">
                        @php
                            $bytes = $totalPatchSize;
                            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                            for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                                $bytes /= 1024;
                            }
                            echo round($bytes, 2) . ' ' . $units[$i];
                        @endphp
                    </div>
                </div>
            </div>

            @if($canMerge)
                <div class="mb-6 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-yellow-400 mt-1 mr-3"></i>
                        <div class="flex-1">
                            <p class="text-yellow-400 font-medium">Merge Recommended</p>
                            <p class="text-dragon-silver-dark text-sm mt-1">
                                You have {{ $incrementalPatches }} incremental patches. Consider merging them into a new base version for optimal performance.
                            </p>
                        </div>
                        <form method="POST" action="{{ route('admin.cache.patches.merge') }}" class="ml-3">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors text-sm"
                                    onclick="return confirm('Merge all patches into a new base version?')">
                                <i class="fas fa-compress mr-2"></i>Merge Now
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if($patches->count() > 0)
                <div class="space-y-2">
                    <h4 class="text-sm font-medium text-dragon-silver-dark mb-3">Patch Chain</h4>
                    @foreach($patches as $patch)
                        <div class="flex items-center justify-between p-3 bg-dragon-black/30 rounded-lg hover:bg-dragon-black/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full {{ $patch->is_base ? 'bg-green-400' : 'bg-blue-400' }}"></div>
                                <div>
                                    <div class="text-sm font-medium text-dragon-silver">
                                        v{{ $patch->version }}
                                        @if($patch->is_base)
                                            <span class="ml-2 px-2 py-0.5 bg-green-500/20 text-green-400 rounded text-xs">Base</span>
                                        @else
                                            <span class="ml-2 px-2 py-0.5 bg-blue-500/20 text-blue-400 rounded text-xs">Delta</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-dragon-silver-dark">
                                        {{ $patch->file_count }} files · {{ $patch->formatted_size }} · {{ $patch->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.cache.patches.download', $patch) }}" 
                                   class="text-blue-400 hover:text-blue-300 p-2" title="Download Patch">
                                    <i class="fas fa-download"></i>
                                </a>
                                @if(!$patch->is_base)
                                    <form method="POST" action="{{ route('admin.cache.patches.delete', $patch) }}" 
                                          onsubmit="return confirm('Delete this patch?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 p-2" title="Delete Patch">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-code-branch text-4xl text-dragon-silver-dark mb-3"></i>
                    <p class="text-dragon-silver-dark">No patches created yet. Upload files to create the first patch.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Bundles Table -->
    <div class="glass-effect rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-dragon-border">
            <h3 class="text-lg font-semibold text-dragon-silver">Cache Bundles</h3>
            <p class="text-dragon-silver-dark text-sm">Compressed cache file bundles for efficient delivery</p>
        </div>

        @if($bundles->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-dragon-black/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Bundle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Files</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dragon-border">
                        @foreach($bundles as $bundle)
                            <tr class="hover:bg-dragon-black/30">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full {{ $bundle->isExpired() ? 'bg-red-500/20' : 'bg-green-500/20' }} flex items-center justify-center">
                                                <i class="fas fa-archive {{ $bundle->isExpired() ? 'text-red-400' : 'text-green-400' }} text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-dragon-silver">
                                                Bundle {{ substr($bundle->bundle_key, 0, 8) }}...
                                            </div>
                                            <div class="text-sm text-dragon-silver-dark">
                                                @if($bundle->existsOnDisk())
                                                    <span class="text-green-400"><i class="fas fa-check-circle mr-1"></i>Available</span>
                                                @else
                                                    <span class="text-red-400"><i class="fas fa-exclamation-triangle mr-1"></i>Missing</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-dragon-silver">{{ $bundle->file_count }} files</div>
                                    <div class="text-xs text-dragon-silver-dark">
                                        @if(count($bundle->file_list) <= 3)
                                            {{ implode(', ', array_slice($bundle->file_list, 0, 3)) }}
                                        @else
                                            {{ implode(', ', array_slice($bundle->file_list, 0, 2)) }} +{{ count($bundle->file_list) - 2 }} more
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-dragon-silver">
                                    {{ $bundle->formatted_size }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($bundle->isExpired())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/20 text-red-400">
                                            <i class="fas fa-clock mr-1"></i>Expired
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                                            <i class="fas fa-check mr-1"></i>Active
                                        </span>
                                    @endif
                                    <div class="text-xs text-dragon-silver-dark mt-1">
                                        {{ $bundle->time_until_expiry }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-dragon-silver-dark">
                                    <div>{{ $bundle->created_at->format('M j, Y') }}</div>
                                    <div class="text-xs">{{ $bundle->created_at->format('g:i A') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if($bundle->existsOnDisk() && !$bundle->isExpired())
                                            <a href="{{ route('admin.cache.bundles.download', $bundle) }}" 
                                               class="text-blue-400 hover:text-blue-300" title="Download Bundle">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @endif
                                        
                                        <button onclick="showBundleDetails('{{ $bundle->id }}')" 
                                                class="text-purple-400 hover:text-purple-300" title="View Details">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                        
                                        <form method="POST" action="{{ route('admin.cache.bundles.destroy', $bundle) }}" 
                                              onsubmit="return confirm('Are you sure you want to delete this bundle?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300" title="Delete Bundle">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-dragon-border">
                {{ $bundles->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <i class="fas fa-archive text-4xl text-dragon-silver-dark mb-4"></i>
                <h3 class="text-lg font-medium text-dragon-silver mb-2">No cache bundles created</h3>
                <p class="text-dragon-silver-dark mb-6">Bundles are created automatically when clients request cache downloads.</p>
                <a href="{{ route('admin.cache.index') }}" class="px-6 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                    <i class="fas fa-file-archive mr-2"></i>Manage Cache Files
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Bundle Details Modal -->
<div id="bundle-details-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-black border border-dragon-border rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-dragon-silver">Bundle Details</h3>
            <button onclick="hideBundleDetails()" class="text-dragon-silver-dark hover:text-dragon-silver">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div id="bundle-details-content">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

@push('scripts')
<script>
const bundleData = @json($bundles->keyBy('id'));

function showBundleDetails(bundleId) {
    const bundle = bundleData[bundleId];
    if (!bundle) return;

    const content = `
        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-dragon-silver-dark mb-1">Bundle Key</label>
                    <div class="text-sm font-mono text-dragon-silver bg-dragon-black/50 px-3 py-2 rounded">
                        ${bundle.bundle_key}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-dragon-silver-dark mb-1">Size</label>
                    <div class="text-sm text-dragon-silver bg-dragon-black/50 px-3 py-2 rounded">
                        ${bundle.formatted_size}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-dragon-silver-dark mb-1">Created</label>
                    <div class="text-sm text-dragon-silver bg-dragon-black/50 px-3 py-2 rounded">
                        ${new Date(bundle.created_at).toLocaleString()}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-dragon-silver-dark mb-1">Expires</label>
                    <div class="text-sm text-dragon-silver bg-dragon-black/50 px-3 py-2 rounded">
                        ${new Date(bundle.expires_at).toLocaleString()}
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-dragon-silver-dark mb-2">Included Files (${bundle.file_list.length})</label>
                <div class="bg-dragon-black/50 rounded-lg p-4 max-h-48 overflow-y-auto">
                    <div class="space-y-1">
                        ${bundle.file_list.map(file => `
                            <div class="text-sm text-dragon-silver font-mono">${file}</div>
                        `).join('')}
                    </div>
                </div>
            </div>

            <div class="flex justify-between pt-4">
                <button onclick="hideBundleDetails()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                    Close
                </button>
                ${bundle.exists_on_disk && !bundle.is_expired ? `
                    <a href="/admin/cache/bundles/${bundle.id}/download" class="px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Download Bundle
                    </a>
                ` : ''}
            </div>
        </div>
    `;

    document.getElementById('bundle-details-content').innerHTML = content;
    document.getElementById('bundle-details-modal').classList.remove('hidden');
}

function hideBundleDetails() {
    document.getElementById('bundle-details-modal').classList.add('hidden');
}
</script>
@endpush
@endsection