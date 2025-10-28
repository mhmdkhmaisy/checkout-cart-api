@extends('admin.layout')

@section('title', 'Cache Management')
@section('header', 'Cache Files Management')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-effect rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-dragon-red/20 text-dragon-red">
                    <i class="fas fa-file-archive text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-dragon-silver-dark">Total Files</p>
                    <p class="text-2xl font-semibold text-dragon-silver">{{ $totalFiles }}</p>
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
                <div class="p-3 rounded-full bg-green-500/20 text-green-400">
                    <i class="fas fa-sync-alt text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-dragon-silver-dark">Last Updated</p>
                    <p class="text-lg font-semibold text-dragon-silver">
                        {{ $files->first()?->updated_at?->diffForHumans() ?? 'Never' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Patch Management Interface -->
    <div class="glass-effect rounded-lg overflow-hidden">
        <!-- Toolbar -->
        <div class="px-6 py-4 border-b border-dragon-border bg-dragon-black/30">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <h3 class="text-lg font-semibold text-dragon-silver">Patch Management</h3>
                    @if($latestVersion)
                        <span class="px-3 py-1 bg-dragon-red/20 text-dragon-red rounded-full text-sm font-medium">
                            <i class="fas fa-code-branch mr-1"></i>v{{ $latestVersion }}
                        </span>
                    @endif
                </div>
                <div class="flex gap-3">
                    <div class="relative inline-block">
                        <button onclick="toggleUploadMenu()" id="upload-menu-btn" class="px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors flex items-center">
                            <i class="fas fa-upload mr-2"></i>Upload Files
                            <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div id="upload-menu" class="hidden absolute left-0 mt-2 w-72 bg-dragon-black border border-dragon-border rounded-lg shadow-xl z-50">
                            <button onclick="showChunkedUploadModal(); hideUploadMenu();" class="w-full px-4 py-3 text-left hover:bg-dragon-red/20 transition-colors border-b border-dragon-border">
                                <div class="flex items-center">
                                    <i class="fas fa-bolt text-yellow-400 mr-3 text-lg"></i>
                                    <div>
                                        <p class="text-dragon-silver font-medium">Chunked Upload (Recommended)</p>
                                        <p class="text-xs text-dragon-silver-dark">Fast, resumable, for large files</p>
                                    </div>
                                </div>
                            </button>
                            <button onclick="showUploadModal(); hideUploadMenu();" class="w-full px-4 py-3 text-left hover:bg-dragon-red/20 transition-colors border-b border-dragon-border">
                                <div class="flex items-center">
                                    <i class="fas fa-upload text-blue-400 mr-3 text-lg"></i>
                                    <div>
                                        <p class="text-dragon-silver font-medium">Standard Upload</p>
                                        <p class="text-xs text-dragon-silver-dark">Traditional upload method</p>
                                    </div>
                                </div>
                            </button>
                            <button onclick="showZipPatchModal(); hideUploadMenu();" class="w-full px-4 py-3 text-left hover:bg-dragon-red/20 transition-colors">
                                <div class="flex items-center">
                                    <i class="fas fa-file-archive text-purple-400 mr-3 text-lg"></i>
                                    <div>
                                        <p class="text-dragon-silver font-medium">ZIP → Extract → Patch</p>
                                        <p class="text-xs text-dragon-silver-dark">Upload .zip, auto-extract & create patch</p>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                    @if($canMerge)
                        <button onclick="mergePatches()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                            <i class="fas fa-object-group mr-2"></i>Merge Patches
                        </button>
                    @endif
                    <button onclick="showDeleteAllModal()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-trash mr-2"></i>Clear All
                    </button>
                    <button onclick="location.reload()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Patch Comparison Tool -->
        @if($patches->count() >= 2)
        <div class="px-6 py-4 border-b border-dragon-border bg-dragon-black/20">
            <div class="flex items-center gap-4 flex-wrap">
                <div class="flex items-center gap-2">
                    <i class="fas fa-code-compare text-purple-400"></i>
                    <span class="text-dragon-silver font-medium">Compare Patches:</span>
                </div>
                <select id="compare-patch-from" class="bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 outline-none">
                    <option value="">Select base patch...</option>
                    @foreach($patches as $patch)
                        <option value="{{ $patch->id }}">v{{ $patch->version }} ({{ $patch->is_base ? 'Base' : 'Delta' }})</option>
                    @endforeach
                </select>
                <i class="fas fa-arrow-right text-dragon-silver-dark"></i>
                <select id="compare-patch-to" class="bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 outline-none">
                    <option value="">Select target patch...</option>
                    @foreach($patches as $patch)
                        <option value="{{ $patch->id }}">v{{ $patch->version }} ({{ $patch->is_base ? 'Base' : 'Delta' }})</option>
                    @endforeach
                </select>
                <button onclick="comparePatchesAction()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-code-compare mr-2"></i>Compare
                </button>
            </div>
        </div>
        @endif

        <!-- Patch History & Upload Zone -->
        <div id="patch-viewer" class="min-h-[400px]">
            <!-- Drop Zone for Drag & Drop -->
            <div id="drop-zone" class="relative">
                <div id="drop-overlay" class="absolute inset-0 bg-dragon-red/20 border-2 border-dashed border-dragon-red rounded-lg flex items-center justify-center z-10 hidden">
                    <div class="text-center">
                        <i class="fas fa-upload text-4xl text-dragon-red mb-4"></i>
                        <p class="text-xl text-dragon-silver">Drop files here to upload</p>
                        <p class="text-dragon-silver-dark">Supports all file types and folders</p>
                    </div>
                </div>

                <!-- Patch History Timeline -->
                <div class="p-6">
                    @if($patches->count() > 0)
                        <div class="space-y-4">
                            @foreach($patches as $patch)
                                @php
                                    $bytes = $patch->size;
                                    $units = ['B', 'KB', 'MB', 'GB'];
                                    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                                        $bytes /= 1024;
                                    }
                                    $formattedSize = round($bytes, 2) . ' ' . $units[$i];
                                @endphp
                                <div class="bg-dragon-black/20 rounded-lg p-5 hover:bg-dragon-black/40 transition-colors border border-dragon-border">
                                    <div class="flex items-start justify-between">
                                        <!-- Patch Info -->
                                        <div class="flex items-start gap-4 flex-1">
                                            <!-- Version Badge -->
                                            <div class="flex-shrink-0">
                                                @if($patch->is_base)
                                                    <div class="w-12 h-12 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                                        <i class="fas fa-cube text-2xl text-purple-400"></i>
                                                    </div>
                                                @else
                                                    <div class="w-12 h-12 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                                        <i class="fas fa-layer-group text-2xl text-blue-400"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <!-- Details -->
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <h4 class="text-lg font-semibold text-dragon-silver">
                                                        v{{ $patch->version }}
                                                    </h4>
                                                    @if($patch->is_base)
                                                        <span class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded text-xs font-medium">
                                                            BASE PATCH
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-1 bg-blue-500/20 text-blue-400 rounded text-xs font-medium">
                                                            DELTA PATCH
                                                        </span>
                                                    @endif
                                                    @if($patch->version === $latestVersion)
                                                        <span class="px-2 py-1 bg-green-500/20 text-green-400 rounded text-xs font-medium">
                                                            <i class="fas fa-check-circle mr-1"></i>CURRENT
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                                    <div>
                                                        <span class="text-dragon-silver-dark">Files:</span>
                                                        <span class="text-dragon-silver ml-1 font-medium">{{ $patch->file_count }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-dragon-silver-dark">Size:</span>
                                                        <span class="text-dragon-silver ml-1 font-medium">{{ $formattedSize }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-dragon-silver-dark">Created:</span>
                                                        <span class="text-dragon-silver ml-1 font-medium">{{ $patch->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    @if(!$patch->is_base && $patch->base_version)
                                                        <div>
                                                            <span class="text-dragon-silver-dark">Based on:</span>
                                                            <span class="text-dragon-silver ml-1 font-medium">v{{ $patch->base_version }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="flex gap-2 ml-4">
                                            <button onclick="viewPatchData({{ json_encode($patch) }})" 
                                                    class="p-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors" 
                                                    title="View Data">
                                                <i class="fas fa-folder-tree"></i>
                                            </button>
                                            <a href="{{ route('admin.cache.patches.download', $patch) }}" 
                                               class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors inline-flex items-center justify-center" 
                                               title="Download Patch">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            @if(!$patch->is_base)
                                                <form method="POST" action="{{ route('admin.cache.patches.delete', $patch) }}" 
                                                      onsubmit="return confirm('Are you sure you want to delete patch v{{ $patch->version }}?\n\nThis action cannot be undone.')" 
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="p-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors" 
                                                            title="Delete Patch">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-16">
                            <div class="w-24 h-24 mx-auto bg-dragon-red/20 rounded-full flex items-center justify-center mb-6">
                                <i class="fas fa-code-branch text-5xl text-dragon-red"></i>
                            </div>
                            <h3 class="text-2xl font-semibold text-dragon-silver mb-3">No Patches Available</h3>
                            <p class="text-dragon-silver-dark mb-8 max-w-md mx-auto">
                                Upload cache files to automatically generate your first patch. The system will create base patches and incremental updates as you upload new content.
                            </p>
                            <button onclick="showUploadModal()" class="px-8 py-3 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors font-medium">
                                <i class="fas fa-upload mr-2"></i>Upload Your First Files
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Patch System Stats -->

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
</div>

<!-- Patch Data Modal -->
<div id="patch-data-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-black border border-dragon-border rounded-lg p-8 max-w-6xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
                <h3 class="text-xl font-semibold text-dragon-silver">Patch Data</h3>
                <span id="patch-data-version" class="px-3 py-1 bg-dragon-red/20 text-dragon-red rounded-full text-sm font-medium"></span>
            </div>
            <button onclick="hidePatchDataModal()" class="text-dragon-silver-dark hover:text-dragon-silver">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="glass-effect rounded-lg p-4">
                <h4 class="text-sm font-medium text-dragon-silver-dark mb-3">Patch Information</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-dragon-silver-dark">Type:</span>
                        <span id="patch-data-type" class="text-dragon-silver font-medium"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dragon-silver-dark">File Count:</span>
                        <span id="patch-data-file-count" class="text-dragon-silver font-medium"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dragon-silver-dark">Size:</span>
                        <span id="patch-data-size" class="text-dragon-silver font-medium"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dragon-silver-dark">Created:</span>
                        <span id="patch-data-created" class="text-dragon-silver font-medium"></span>
                    </div>
                    <div class="flex justify-between" id="patch-data-base-version-container">
                        <span class="text-dragon-silver-dark">Based on:</span>
                        <span id="patch-data-base-version" class="text-dragon-silver font-medium"></span>
                    </div>
                </div>
            </div>
            
            <div class="glass-effect rounded-lg p-4">
                <h4 class="text-sm font-medium text-dragon-silver-dark mb-3">Storage</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-dragon-silver-dark">Path:</span>
                        <span id="patch-data-path" class="text-dragon-silver font-medium text-xs truncate max-w-xs" title=""></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-effect rounded-lg p-4 flex-1 overflow-hidden flex flex-col">
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-sm font-medium text-dragon-silver-dark">File Structure</h4>
                <div class="flex gap-2">
                    <button onclick="generateChangelog()" class="text-xs px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded transition-colors">
                        <i class="fas fa-list mr-1"></i>Changelog
                    </button>
                    <button onclick="verifyIntegrity()" class="text-xs px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white rounded transition-colors">
                        <i class="fas fa-shield-alt mr-1"></i>Verify
                    </button>
                    <button onclick="expandAllDirectories()" class="text-xs px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors">
                        <i class="fas fa-folder-open mr-1"></i>Expand All
                    </button>
                    <button onclick="collapseAllDirectories()" class="text-xs px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white rounded transition-colors">
                        <i class="fas fa-folder mr-1"></i>Collapse All
                    </button>
                </div>
            </div>
            <div id="patch-data-tree" class="flex-1 overflow-y-auto bg-dragon-black/30 rounded p-4 font-mono text-sm">
                <!-- Directory tree will be populated here -->
            </div>
        </div>
    </div>
</div>

<!-- Patch Comparison/Diff Modal -->
<div id="patch-diff-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-black border border-dragon-border rounded-lg p-8 max-w-7xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
                <h3 class="text-xl font-semibold text-dragon-silver">Patch Comparison</h3>
                <span id="diff-patches-info" class="text-sm text-dragon-silver-dark"></span>
            </div>
            <button onclick="hidePatchDiffModal()" class="text-dragon-silver-dark hover:text-dragon-silver">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="glass-effect rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-400" id="diff-added-count">0</div>
                <div class="text-xs text-dragon-silver-dark mt-1">Added Files</div>
            </div>
            <div class="glass-effect rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-yellow-400" id="diff-modified-count">0</div>
                <div class="text-xs text-dragon-silver-dark mt-1">Modified Files</div>
            </div>
            <div class="glass-effect rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-red-400" id="diff-removed-count">0</div>
                <div class="text-xs text-dragon-silver-dark mt-1">Removed Files</div>
            </div>
            <div class="glass-effect rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-400" id="diff-total-count">0</div>
                <div class="text-xs text-dragon-silver-dark mt-1">Total Changes</div>
            </div>
        </div>

        <div class="flex-1 overflow-hidden max-h-[500px]">
            <div class="h-full overflow-y-auto bg-dragon-black/30 rounded p-4">
                <div id="diff-results" class="space-y-2">
                    <!-- Diff results will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Changelog Modal -->
<div id="changelog-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-black border border-dragon-border rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
                <h3 class="text-xl font-semibold text-dragon-silver">Changelog</h3>
                <span id="changelog-version" class="px-3 py-1 bg-green-500/20 text-green-400 rounded-full text-sm font-medium"></span>
            </div>
            <button onclick="hideChangelogModal()" class="text-dragon-silver-dark hover:text-dragon-silver">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto bg-dragon-black/30 rounded p-4">
                <div id="changelog-content" class="space-y-3">
                    <!-- Changelog will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- File History Modal -->
<div id="file-history-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-black border border-dragon-border rounded-lg p-8 max-w-5xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
                <h3 class="text-xl font-semibold text-dragon-silver">File Change History</h3>
                <span id="file-history-name" class="text-sm text-dragon-silver-dark font-mono"></span>
            </div>
            <button onclick="hideFileHistoryModal()" class="text-dragon-silver-dark hover:text-dragon-silver">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto">
                <div id="file-history-timeline" class="space-y-4">
                    <!-- File history timeline will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Integrity Verification Modal -->
<div id="integrity-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-black border border-dragon-border rounded-lg p-8 max-w-3xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
                <h3 class="text-xl font-semibold text-dragon-silver">Integrity Verification</h3>
                <span id="integrity-version" class="px-3 py-1 bg-yellow-500/20 text-yellow-400 rounded-full text-sm font-medium"></span>
            </div>
            <button onclick="hideIntegrityModal()" class="text-dragon-silver-dark hover:text-dragon-silver">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div id="integrity-progress" class="mb-6 hidden">
            <div class="flex items-center gap-3 mb-2">
                <i class="fas fa-spinner fa-spin text-yellow-400"></i>
                <span class="text-dragon-silver">Verifying checksums...</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-2">
                <div id="integrity-progress-bar" class="bg-yellow-400 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>

        <div class="flex-1 overflow-hidden">
            <div id="integrity-results" class="h-full overflow-y-auto bg-dragon-black/30 rounded p-4">
                <!-- Integrity results will be populated here -->
            </div>
        </div>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div id="confirmation-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-black border border-dragon-border rounded-lg p-8 max-w-md w-full mx-4">
        <div class="flex items-center mb-6">
            <div class="p-3 rounded-full bg-red-500/20 text-red-400 mr-4">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-semibold text-dragon-silver" id="confirm-title">Confirm Action</h3>
                <p class="text-dragon-silver-dark" id="confirm-message">Are you sure?</p>
            </div>
        </div>
        
        <div class="flex justify-end gap-3">
            <button onclick="hideConfirmationModal()" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                Cancel
            </button>
            <button id="confirm-action-btn" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                Delete
            </button>
        </div>
    </div>
</div>

<!-- Responsive Context Menu -->
<div id="context-menu" class="fixed bg-dragon-black border border-dragon-border rounded-lg shadow-lg py-2 z-50 hidden min-w-[180px]">
    <button onclick="openSelectedFile()" class="w-full px-4 py-2 text-left text-dragon-silver hover:bg-dragon-red/20 transition-colors flex items-center">
        <i class="fas fa-folder-open mr-3 w-4"></i>Open
    </button>
    <button onclick="downloadSelectedFile()" class="w-full px-4 py-2 text-left text-dragon-silver hover:bg-dragon-red/20 transition-colors flex items-center">
        <i class="fas fa-download mr-3 w-4"></i>Download
    </button>
    <button id="extract-option" onclick="extractSelectedFile()" class="w-full px-4 py-2 text-left text-dragon-silver hover:bg-dragon-red/20 transition-colors flex items-center hidden">
        <i class="fas fa-file-archive mr-3 w-4"></i>Extract Here
    </button>
    <button onclick="renameSelectedFile()" class="w-full px-4 py-2 text-left text-dragon-silver hover:bg-dragon-red/20 transition-colors flex items-center">
        <i class="fas fa-edit mr-3 w-4"></i>Rename
    </button>
    <hr class="border-dragon-border my-1">
    <button onclick="showDeleteContextModal()" class="w-full px-4 py-2 text-left text-red-400 hover:bg-red-500/20 transition-colors flex items-center">
        <i class="fas fa-trash mr-3 w-4"></i>Delete
    </button>
</div>

<!-- Extract Progress Modal -->
<div id="extract-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-black border border-dragon-border rounded-lg p-8 max-w-2xl w-full mx-4">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-dragon-silver">Extracting Archive</h3>
            <button onclick="hideExtractModal()" class="text-dragon-silver-dark hover:text-dragon-silver">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="space-y-4">
            <div class="flex items-center">
                <i class="fas fa-file-archive text-2xl text-purple-400 mr-4"></i>
                <div>
                    <p class="text-dragon-silver font-medium" id="extract-filename">archive.zip</p>
                    <p class="text-dragon-silver-dark text-sm" id="extract-status">Preparing extraction...</p>
                </div>
            </div>

            <div class="w-full bg-gray-700 rounded-full h-3">
                <div id="extract-progress-bar" class="bg-gradient-to-r from-purple-600 to-purple-400 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>

            <div class="flex justify-between text-sm text-dragon-silver-dark">
                <span id="extract-progress-text">0%</span>
                <span id="extract-files-count">0 files extracted</span>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Upload Modal -->
<div id="upload-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-black border border-dragon-border rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-dragon-silver">Upload Cache Files & Folders</h3>
            <button onclick="hideUploadModal()" class="text-dragon-silver-dark hover:text-dragon-silver">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Unified Upload Section -->
        <div class="glass-effect rounded-lg p-8 mb-6">
            <div id="unified-drop-zone" class="border-2 border-dashed border-dragon-border rounded-lg p-12 text-center transition-colors hover:border-dragon-red">
                <i class="fas fa-cloud-upload-alt text-5xl text-dragon-silver-dark mb-4"></i>
                <h4 class="text-xl font-medium text-dragon-silver mb-3">
                    Drop files or folders here
                </h4>
                <p class="text-dragon-silver-dark mb-6">
                    Supports all file types, directories, and archives
                </p>
                <div class="flex justify-center gap-4 mb-4">
                    <button type="button" onclick="document.getElementById('unified-files-input').click()" 
                            class="px-6 py-3 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors inline-flex items-center">
                        <i class="fas fa-file mr-2"></i>Browse Files
                    </button>
                    <button type="button" onclick="document.getElementById('unified-folder-input').click()" 
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors inline-flex items-center">
                        <i class="fas fa-folder mr-2"></i>Browse Folders
                    </button>
                </div>
                <input type="file" id="unified-files-input" multiple class="hidden">
                <input type="file" id="unified-folder-input" webkitdirectory directory multiple class="hidden">
                <p class="text-xs text-dragon-silver-dark mt-4">
                    <i class="fas fa-info-circle mr-1"></i>
                    TAR archives (.tar, .tar.gz, .tgz) will be auto-extracted • Max 1GB per file
                </p>
            </div>
        </div>

        <!-- Upload Options -->
        <div class="glass-effect rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" id="preserve-structure" checked class="mr-2">
                    <span class="text-dragon-silver">Preserve directory structure</span>
                </label>
                <div class="text-sm text-dragon-silver-dark">
                    <span id="selected-files-count">0 files selected</span>
                </div>
            </div>
        </div>

        <!-- Upload Progress -->
        <div id="upload-progress-container" class="hidden">
            <div class="glass-effect rounded-lg p-6 mb-6">
                <h4 class="text-lg font-medium text-dragon-silver mb-4">Upload Progress</h4>
                
                <!-- Overall Progress -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-dragon-silver-dark">Overall Progress</span>
                        <span id="overall-progress-text" class="text-dragon-silver">0%</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-3">
                        <div id="overall-progress-bar" class="bg-gradient-to-r from-dragon-red to-dragon-red-bright h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <div class="flex justify-between text-sm text-dragon-silver-dark mt-2">
                        <span id="upload-stats">0 / 0 files</span>
                        <span id="upload-speed">0 KB/s</span>
                    </div>
                </div>

                <!-- TAR Extraction Progress -->
                <div id="tar-extraction-progress" class="hidden mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-dragon-silver-dark">TAR Extraction Progress</span>
                        <span id="extraction-progress-text" class="text-dragon-silver">0%</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-3">
                        <div id="extraction-progress-bar" class="bg-gradient-to-r from-purple-600 to-purple-400 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <div class="text-sm text-dragon-silver-dark mt-2">
                        <span id="extraction-status">Preparing extraction...</span>
                    </div>
                </div>

                <!-- Individual File Progress -->
                <div id="file-progress-list" class="space-y-3 max-h-64 overflow-y-auto"></div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between">
            <button onclick="hideUploadModal()" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                Cancel
            </button>
            <div class="flex gap-3">
                <button id="clear-queue-btn" onclick="clearUploadQueue()" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors hidden">
                    <i class="fas fa-trash mr-2"></i>Clear All
                </button>
                <button id="start-upload-btn" onclick="startBatchUpload()" class="px-6 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors" disabled>
                    <i class="fas fa-upload mr-2"></i>Start Upload
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Chunked Upload Modal -->
<div id="chunked-upload-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-black border border-dragon-border rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-dragon-silver flex items-center">
                <i class="fas fa-bolt text-yellow-400 mr-3"></i>
                Chunked Upload - Fast & Resumable
            </h3>
            <button onclick="hideChunkedUploadModal()" class="text-dragon-silver-dark hover:text-dragon-silver">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Unified Upload Section -->
        <div class="glass-effect rounded-lg p-8 mb-6">
            <div id="chunked-drop-zone" class="border-2 border-dashed border-yellow-500/50 rounded-lg p-12 text-center transition-colors hover:border-yellow-400">
                <i class="fas fa-rocket text-5xl text-yellow-400 mb-4"></i>
                <h4 class="text-xl font-medium text-dragon-silver mb-3">
                    Drop files or folders here
                </h4>
                <p class="text-dragon-silver-dark mb-2">
                    <i class="fas fa-check text-green-400 mr-1"></i> Best for slow or unreliable connections
                </p>
                <p class="text-dragon-silver-dark mb-6">
                    <i class="fas fa-check text-green-400 mr-1"></i> Supports all file types and directories
                </p>
                <div class="flex justify-center gap-4 mb-4">
                    <button type="button" onclick="document.getElementById('chunked-files-input').click()" 
                            class="px-6 py-3 bg-yellow-600 hover:bg-yellow-500 text-black font-medium rounded-lg transition-colors inline-flex items-center">
                        <i class="fas fa-file mr-2"></i>Browse Files
                    </button>
                    <button type="button" onclick="document.getElementById('chunked-folder-input').click()" 
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors inline-flex items-center">
                        <i class="fas fa-folder mr-2"></i>Browse Folders
                    </button>
                </div>
                <input type="file" id="chunked-files-input" multiple class="hidden">
                <input type="file" id="chunked-folder-input" webkitdirectory directory multiple class="hidden">
                <p class="text-xs text-dragon-silver-dark mt-4">
                    <i class="fas fa-info-circle mr-1"></i>
                    Files are split into chunks and uploaded progressively • Ideal for large files and slow connections
                </p>
            </div>
        </div>

        <!-- Upload Options -->
        <div class="glass-effect rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" id="chunked-preserve-structure" checked class="mr-2">
                    <span class="text-dragon-silver">Preserve directory structure</span>
                </label>
                <div class="text-sm text-dragon-silver-dark">
                    <span id="chunked-selected-files-count">0 files selected</span>
                </div>
            </div>
        </div>

        <!-- Upload Progress -->
        <div id="chunked-upload-progress-container" class="hidden">
            <div class="glass-effect rounded-lg p-6 mb-6">
                <h4 class="text-lg font-medium text-dragon-silver mb-4">Upload Progress</h4>
                
                <!-- Overall Progress -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-dragon-silver-dark">Overall Progress</span>
                        <span id="chunked-overall-progress-text" class="text-dragon-silver">0%</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-3">
                        <div id="chunked-overall-progress-bar" class="bg-gradient-to-r from-yellow-600 to-yellow-400 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <div class="flex justify-between text-sm text-dragon-silver-dark mt-2">
                        <span id="chunked-upload-stats">0 / 0 files</span>
                        <span id="chunked-upload-speed">0 KB/s</span>
                    </div>
                </div>

                <!-- Individual File Progress -->
                <div id="chunked-file-progress-list" class="space-y-3 max-h-64 overflow-y-auto"></div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between">
            <button onclick="hideChunkedUploadModal()" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                Cancel
            </button>
            <div class="flex gap-3">
                <button id="chunked-clear-queue-btn" onclick="clearChunkedUploadQueue()" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors hidden">
                    <i class="fas fa-trash mr-2"></i>Clear All
                </button>
                <button id="chunked-start-upload-btn" onclick="startChunkedBatchUpload()" class="px-6 py-2 bg-yellow-600 hover:bg-yellow-500 text-black font-medium rounded-lg transition-colors" disabled>
                    <i class="fas fa-rocket mr-2"></i>Start Chunked Upload
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ZIP → Extract → Patch Modal -->
<div id="zip-patch-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-black border border-dragon-border rounded-lg p-8 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-dragon-silver flex items-center">
                <i class="fas fa-file-archive text-purple-400 mr-3"></i>
                ZIP → Extract → Patch
            </h3>
            <button onclick="hideZipPatchModal()" class="text-dragon-silver-dark hover:text-dragon-silver">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Upload Section -->
        <div class="glass-effect rounded-lg p-8 mb-6">
            <div id="zip-drop-zone" class="border-2 border-dashed border-purple-500/50 rounded-lg p-12 text-center transition-colors hover:border-purple-400">
                <i class="fas fa-file-zipper text-5xl text-purple-400 mb-4"></i>
                <h4 class="text-xl font-medium text-dragon-silver mb-3">
                    Drop a .zip file here
                </h4>
                <p class="text-dragon-silver-dark mb-2">
                    <i class="fas fa-check text-green-400 mr-1"></i> Automatically extracts contents
                </p>
                <p class="text-dragon-silver-dark mb-2">
                    <i class="fas fa-check text-green-400 mr-1"></i> Generates patch from extracted files
                </p>
                <p class="text-dragon-silver-dark mb-6">
                    <i class="fas fa-check text-green-400 mr-1"></i> Cleans up temporary files
                </p>
                <div class="flex justify-center gap-4 mb-4">
                    <button type="button" onclick="document.getElementById('zip-file-input').click()" 
                            class="px-6 py-3 bg-purple-600 hover:bg-purple-500 text-white font-medium rounded-lg transition-colors inline-flex items-center">
                        <i class="fas fa-file-zipper mr-2"></i>Select ZIP File
                    </button>
                </div>
                <input type="file" id="zip-file-input" accept=".zip" class="hidden">
                <p class="text-xs text-dragon-silver-dark mt-4">
                    <i class="fas fa-info-circle mr-1"></i>
                    Only .zip files are supported for this method
                </p>
            </div>
            
            <div id="zip-file-selected" class="hidden mt-4 p-4 bg-purple-500/10 border border-purple-500/30 rounded-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-file-zipper text-purple-400 mr-3 text-2xl"></i>
                        <div>
                            <p class="text-dragon-silver font-medium" id="zip-file-name"></p>
                            <p class="text-dragon-silver-dark text-sm" id="zip-file-size"></p>
                        </div>
                    </div>
                    <button onclick="clearZipFile()" class="text-red-400 hover:text-red-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Progress -->
        <div id="zip-patch-progress-container" class="hidden">
            <div class="glass-effect rounded-lg p-6 mb-6">
                <h4 class="text-lg font-medium text-dragon-silver mb-4">Processing</h4>
                <div class="space-y-4">
                    <div class="flex items-center" id="zip-upload-status">
                        <i class="fas fa-circle-notch fa-spin text-purple-400 mr-3"></i>
                        <span class="text-dragon-silver">Uploading ZIP file...</span>
                    </div>
                    <div class="flex items-center text-dragon-silver-dark" id="zip-extract-status">
                        <i class="far fa-circle text-dragon-silver-dark mr-3"></i>
                        <span>Extracting files...</span>
                    </div>
                    <div class="flex items-center text-dragon-silver-dark" id="zip-patch-status">
                        <i class="far fa-circle text-dragon-silver-dark mr-3"></i>
                        <span>Generating patch...</span>
                    </div>
                    <div class="flex items-center text-dragon-silver-dark" id="zip-cleanup-status">
                        <i class="far fa-circle text-dragon-silver-dark mr-3"></i>
                        <span>Cleaning up...</span>
                    </div>
                </div>
                <div id="zip-patch-result" class="hidden mt-6 p-4 rounded-lg"></div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between">
            <button onclick="hideZipPatchModal()" id="zip-cancel-btn" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                Cancel
            </button>
            <button id="zip-start-btn" onclick="startZipPatchUpload()" class="px-6 py-2 bg-purple-600 hover:bg-purple-500 text-white font-medium rounded-lg transition-colors" disabled>
                <i class="fas fa-rocket mr-2"></i>Start Process
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedFiles = [];
let uploadQueue = [];
let isUploading = false;
let uploadStartTime = 0;
let completedUploads = 0;
let totalUploads = 0;
let modalClosing = false;
let uploadCompleted = false;
let currentView = 'grid';
let selectedFileItems = new Set();
let contextMenuTarget = null;
let confirmationCallback = null;
let currentNavigationPath = '{{ $currentPath }}';

// Chunked upload state
let chunkedSelectedFiles = [];
let chunkedIsUploading = false;
let chunkedUploadStartTime = 0;
let chunkedCompletedUploads = 0;
let chunkedTotalUploads = 0;
let chunkedModalClosing = false;
let chunkedUploadCompleted = false;

// ZIP → Patch state
let zipSelectedFile = null;
let zipIsProcessing = false;

// Extractable file extensions
const extractableExtensions = ['zip', 'tar', 'gz', 'rar', '7z', 'tgz'];

// Custom Confirmation Modal Functions
function showConfirmationModal(title, message, callback, actionText = 'Delete') {
    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-message').textContent = message;
    document.getElementById('confirm-action-btn').textContent = actionText;
    confirmationCallback = callback;
    document.getElementById('confirmation-modal').classList.remove('hidden');
}

function hideConfirmationModal() {
    document.getElementById('confirmation-modal').classList.add('hidden');
    confirmationCallback = null;
}

function executeConfirmation() {
    if (confirmationCallback) {
        confirmationCallback();
        hideConfirmationModal();
    }
}

// Enhanced Delete Functions
function showDeleteModal(fileId, fileName) {
    showConfirmationModal(
        'Delete File',
        `Are you sure you want to delete "${fileName}"? This action cannot be undone.`,
        () => deleteFile(fileId)
    );
}

function showDeleteAllModal() {
    const patchCount = {{ $patches->count() }};
    if (patchCount === 0) {
        alert('No patches to clear.');
        return;
    }
    showConfirmationModal(
        'Clear All Patches',
        `Are you sure you want to clear ALL ${patchCount} patches? This action cannot be undone and will permanently remove all patches and reset the system.`,
        () => clearAllPatches(),
        'Clear All'
    );
}

function showDeleteSelectedModal() {
    const count = selectedFileItems.size;
    showConfirmationModal(
        'Delete Selected Files',
        `Are you sure you want to delete ${count} selected files? This action cannot be undone.`,
        () => deleteSelected(),
        'Delete Selected'
    );
}

function showDeleteContextModal() {
    if (contextMenuTarget) {
        const fileName = contextMenuTarget.getAttribute('data-file-name');
        const fileId = contextMenuTarget.getAttribute('data-file-id');
        showDeleteModal(fileId, fileName);
    }
    hideContextMenu();
}

// Clear All Patches Function
async function clearAllPatches() {
    try {
        const response = await fetch('/admin/cache/patches/clear-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Success: ' + result.message);
            location.reload();
        } else {
            alert('Error: ' + (result.message || 'Failed to clear patches'));
        }
    } catch (error) {
        console.error('Clear all patches error:', error);
        alert('Network error occurred while clearing patches');
    }
}

// View Toggle Functions
function toggleView(view) {
    currentView = view;
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');
    const gridBtn = document.getElementById('grid-view-btn');
    const listBtn = document.getElementById('list-view-btn');
    
    if (view === 'grid') {
        gridView.classList.remove('hidden');
        listView.classList.add('hidden');
        gridBtn.classList.add('bg-dragon-red/20', 'text-dragon-red');
        gridBtn.classList.remove('hover:bg-dragon-red/20', 'text-dragon-silver-dark');
        listBtn.classList.remove('bg-dragon-red/20', 'text-dragon-red');
        listBtn.classList.add('hover:bg-dragon-red/20', 'text-dragon-silver-dark');
    } else {
        gridView.classList.add('hidden');
        listView.classList.remove('hidden');
        listBtn.classList.add('bg-dragon-red/20', 'text-dragon-red');
        listBtn.classList.remove('hover:bg-dragon-red/20', 'text-dragon-silver-dark');
        gridBtn.classList.remove('bg-dragon-red/20', 'text-dragon-red');
        gridBtn.classList.add('hover:bg-dragon-red/20', 'text-dragon-silver-dark');
    }
}

// File Selection Functions
function selectFile(element) {
    const fileId = element.getAttribute('data-file-id');
    
    if (element.classList.contains('selected')) {
        element.classList.remove('selected', 'bg-dragon-red/20');
        selectedFileItems.delete(fileId);
    } else {
        element.classList.add('selected', 'bg-dragon-red/20');
        selectedFileItems.add(fileId);
    }
    
    updateBulkActionsDisplay();
}

function clearSelection() {
    selectedFileItems.clear();
    document.querySelectorAll('.file-item, tr[data-file-id]').forEach(el => {
        el.classList.remove('selected', 'bg-dragon-red/20');
    });
    document.querySelectorAll('.file-checkbox').forEach(cb => cb.checked = false);
    updateBulkActionsDisplay();
}

function updateBulkActionsDisplay() {
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    
    // Guard: Check if elements exist before accessing
    if (!bulkActions || !selectedCount) {
        return;
    }
    
    if (selectedFileItems.size > 0) {
        bulkActions.classList.remove('hidden');
        selectedCount.textContent = selectedFileItems.size;
    } else {
        bulkActions.classList.add('hidden');
    }
}

// Context Menu Functions with Responsive Positioning
function showContextMenu(event, element) {
    event.preventDefault();
    contextMenuTarget = element;
    
    const contextMenu = document.getElementById('context-menu');
    const extractOption = document.getElementById('extract-option');
    
    // Check if file is extractable
    const fileExtension = element.getAttribute('data-file-extension');
    const fileType = element.getAttribute('data-file-type');
    
    if (fileType === 'file' && extractableExtensions.includes(fileExtension)) {
        extractOption.classList.remove('hidden');
    } else {
        extractOption.classList.add('hidden');
    }
    
    // Show context menu first to get dimensions
    contextMenu.classList.remove('hidden');
    
    // Get menu dimensions
    const menuRect = contextMenu.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    
    // Calculate position
    let left = event.pageX;
    let top = event.pageY;
    
    // Adjust horizontal position if menu would overflow
    if (left + menuRect.width > viewportWidth) {
        left = viewportWidth - menuRect.width - 10; // 10px margin
    }
    
    // Adjust vertical position if menu would overflow
    if (top + menuRect.height > viewportHeight) {
        top = viewportHeight - menuRect.height - 10; // 10px margin
    }
    
    // Ensure menu doesn't go off the left or top edge
    left = Math.max(10, left);
    top = Math.max(10, top);
    
    contextMenu.style.left = left + 'px';
    contextMenu.style.top = top + 'px';
    
    // Hide context menu when clicking elsewhere
    setTimeout(() => {
        document.addEventListener('click', hideContextMenu, { once: true });
    }, 10);
}

function hideContextMenu() {
    document.getElementById('context-menu').classList.add('hidden');
    contextMenuTarget = null;
}

// File Operations
function openFile(element) {
    const fileType = element.getAttribute('data-file-type');
    
    // OPEN only works for directories/folders
    if (fileType === 'directory') {
        // Navigate to directory using navigation path
        const navigationPath = element.getAttribute('data-navigation-path') || element.getAttribute('data-relative-path');
        navigateTo(navigationPath);
    }
    // Files cannot be "opened" - use download instead
}

function openSelectedFile() {
    if (contextMenuTarget) {
        const fileType = contextMenuTarget.getAttribute('data-file-type');
        // Only open if it's a directory
        if (fileType === 'directory') {
            openFile(contextMenuTarget);
        }
    }
    hideContextMenu();
}

function downloadFile(fileId) {
    // Find the file by ID and download it
    const file = @json($files->items()).find(f => f.id == fileId);
    if (file && file.file_type === 'file') {
        let downloadUrl = `/api/cache/file/${encodeURIComponent(file.filename)}`;
        if (file.relative_path) {
            downloadUrl += `?path=${encodeURIComponent(file.relative_path)}`;
        }
        window.open(downloadUrl, '_blank');
    }
}

function downloadSelectedFile() {
    if (contextMenuTarget) {
        const fileId = contextMenuTarget.getAttribute('data-file-id');
        downloadFile(fileId);
    }
    hideContextMenu();
}

// Extract Functions
function extractSelectedFile() {
    if (contextMenuTarget) {
        const fileId = contextMenuTarget.getAttribute('data-file-id');
        const fileName = contextMenuTarget.getAttribute('data-file-name');
        const fileExtension = contextMenuTarget.getAttribute('data-file-extension');
        
        if (extractableExtensions.includes(fileExtension)) {
            extractFile(fileId, fileName);
        } else {
            showConfirmationModal(
                'Unsupported File Type',
                'This file type cannot be extracted.',
                () => {},
                'OK'
            );
        }
    }
    hideContextMenu();
}

async function extractFile(fileId, fileName) {
    // Show extract modal
    document.getElementById('extract-modal').classList.remove('hidden');
    document.getElementById('extract-filename').textContent = fileName;
    document.getElementById('extract-status').textContent = 'Preparing extraction...';
    document.getElementById('extract-progress-bar').style.width = '0%';
    document.getElementById('extract-progress-text').textContent = '0%';
    document.getElementById('extract-files-count').textContent = '0 files extracted';
    
    try {
        const formData = new FormData();
        formData.append('file_id', fileId);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        const response = await fetch('/admin/cache/extract-file', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Start polling for extraction progress
            pollFileExtractionProgress(result.extraction_id, fileName);
        } else {
            document.getElementById('extract-status').textContent = 'Extraction failed: ' + result.message;
            setTimeout(() => {
                hideExtractModal();
            }, 3000);
        }
    } catch (error) {
        document.getElementById('extract-status').textContent = 'Network error occurred';
        console.error('Extract error:', error);
        setTimeout(() => {
            hideExtractModal();
        }, 3000);
    }
}

async function pollFileExtractionProgress(extractionId, fileName) {
    const pollInterval = setInterval(async () => {
        try {
            const response = await fetch('/admin/cache/extraction-progress?id=' + extractionId);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.status === 'completed') {
                clearInterval(pollInterval);
                document.getElementById('extract-progress-bar').style.width = '100%';
                document.getElementById('extract-progress-text').textContent = '100%';
                document.getElementById('extract-status').textContent = 'Extraction completed successfully!';
                document.getElementById('extract-files-count').textContent = `${data.files_count} files extracted`;
                
                setTimeout(() => {
                    hideExtractModal();
                    location.reload(); // Refresh to show extracted files
                }, 2000);
            } else if (data.status === 'failed') {
                clearInterval(pollInterval);
                document.getElementById('extract-status').textContent = 'Extraction failed: ' + (data.error || 'Unknown error');
                setTimeout(() => {
                    hideExtractModal();
                }, 3000);
            } else {
                // Update extraction progress
                const percent = data.total > 0 ? Math.round((data.processed / data.total) * 100) : 0;
                document.getElementById('extract-progress-bar').style.width = percent + '%';
                document.getElementById('extract-progress-text').textContent = percent + '%';
                document.getElementById('extract-status').textContent = `Extracting... ${data.processed}/${data.total} files`;
                document.getElementById('extract-files-count').textContent = `${data.processed} files extracted`;
            }
        } catch (error) {
            console.error('Extraction polling error:', error);
            clearInterval(pollInterval);
            document.getElementById('extract-status').textContent = 'Extraction monitoring failed';
            setTimeout(() => {
                hideExtractModal();
            }, 3000);
        }
    }, 1000);
}

function hideExtractModal() {
    document.getElementById('extract-modal').classList.add('hidden');
}

function deleteFile(fileId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/cache/${fileId}`;
    form.innerHTML = `
        @csrf
        @method('DELETE')
    `;
    document.body.appendChild(form);
    form.submit();
}

function renameSelectedFile() {
    if (contextMenuTarget) {
        const currentName = contextMenuTarget.getAttribute('data-file-name');
        const newName = prompt('Enter new name:', currentName);
        if (newName && newName !== currentName) {
            // Implement rename functionality
            showConfirmationModal(
                'Feature Coming Soon',
                'Rename functionality will be implemented in the next update.',
                () => {},
                'OK'
            );
        }
    }
    hideContextMenu();
}

// Navigation Functions
function navigateTo(path) {
    // Clean up the path
    path = (path || '').replace(/^\/+|\/+$/g, '');

    // Update current navigation path
    currentNavigationPath = path;

    // Reload page with new path parameter
    const url = new URL(window.location);
    if (path) {
        url.searchParams.set('path', path);
    } else {
        url.searchParams.delete('path');
    }
    window.location.href = url.toString();
}

function refreshFiles() {
    location.reload();
}

function createFolder() {
    const folderName = prompt('Enter folder name:');
    if (folderName) {
        // Implement folder creation
        showConfirmationModal(
            'Feature Coming Soon',
            'Folder creation functionality will be implemented in the next update.',
            () => {},
            'OK'
        );
    }
}

// Bulk Operations
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('select-all');
    const fileCheckboxes = document.querySelectorAll('.file-checkbox');
    
    fileCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
        const row = checkbox.closest('.file-item, tr[data-file-id]');
        if (selectAllCheckbox.checked) {
            row.classList.add('selected', 'bg-dragon-red/20');
            selectedFileItems.add(checkbox.value);
        } else {
            row.classList.remove('selected', 'bg-dragon-red/20');
            selectedFileItems.delete(checkbox.value);
        }
    });
    
    updateBulkActionsDisplay();
}

function updateBulkActions() {
    const selectedCheckboxes = document.querySelectorAll('.file-checkbox:checked');
    selectedFileItems.clear();
    
    selectedCheckboxes.forEach(cb => {
        selectedFileItems.add(cb.value);
        const row = cb.closest('.file-item, tr[data-file-id]');
        row.classList.add('selected', 'bg-dragon-red/20');
    });
    
    document.querySelectorAll('.file-checkbox:not(:checked)').forEach(cb => {
        const row = cb.closest('.file-item, tr[data-file-id]');
        row.classList.remove('selected', 'bg-dragon-red/20');
    });
    
    updateBulkActionsDisplay();
}

function downloadSelected() {
    if (selectedFileItems.size === 0) {
        showConfirmationModal(
            'No Selection',
            'Please select files to download.',
            () => {},
            'OK'
        );
        return;
    }
    
    // Download selected files
    selectedFileItems.forEach(fileId => {
        downloadFile(fileId);
    });
}

async function deleteSelected() {
    if (selectedFileItems.size === 0) {
        showConfirmationModal(
            'No Selection',
            'Please select files to delete.',
            () => {},
            'OK'
        );
        return;
    }
    
    try {
        const formData = new FormData();
        selectedFileItems.forEach(id => formData.append('file_ids[]', id));
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        const response = await fetch('/admin/cache/bulk-delete', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Remove deleted items from view
            selectedFileItems.forEach(id => {
                document.querySelectorAll(`[data-file-id="${id}"]`).forEach(el => el.remove());
            });
            
            showConfirmationModal(
                'Success',
                result.message,
                () => {},
                'OK'
            );
            clearSelection();
            
            // Reload if no files left
            if (document.querySelectorAll('[data-file-id]').length === 0) {
                location.reload();
            }
        } else {
            showConfirmationModal(
                'Error',
                'Error: ' + result.message,
                () => {},
                'OK'
            );
        }
    } catch (error) {
        showConfirmationModal(
            'Network Error',
            'Network error occurred while deleting files.',
            () => {},
            'OK'
        );
        console.error('Bulk delete error:', error);
    }
}

// Drag & Drop for File Browser
function setupFileBrowserDragDrop() {
    const dropZone = document.getElementById('drop-zone');
    const dropOverlay = document.getElementById('drop-overlay');
    
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropOverlay.classList.remove('hidden');
    });
    
    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        if (!dropZone.contains(e.relatedTarget)) {
            dropOverlay.classList.add('hidden');
        }
    });
    
    dropZone.addEventListener('drop', async (e) => {
        e.preventDefault();
        dropOverlay.classList.add('hidden');
        
        const items = Array.from(e.dataTransfer.items);
        
        // Check if any folders are being dropped
        let hasFolder = false;
        if (items.length > 0 && items[0].webkitGetAsEntry) {
            for (const item of items) {
                const entry = item.webkitGetAsEntry();
                if (entry && entry.isDirectory) {
                    hasFolder = true;
                    break;
                }
            }
        }
        
        if (hasFolder) {
            // Show error message for folder drag & drop
            showConfirmationModal(
                'Folders Not Supported in Drag & Drop',
                'Due to browser security restrictions, folders cannot be dragged and dropped. Please use the "Browse Folders" button instead.',
                () => {},
                'OK'
            );
            return;
        }
        
        const files = Array.from(e.dataTransfer.files);
        if (files.length > 0) {
            // Auto-open upload modal and add files
            showUploadModal();
            handleFileSelection(files, 'files');
        }
    });
}

// Modal functions
function showUploadModal() {
    modalClosing = false;
    uploadCompleted = false;
    document.getElementById('upload-modal').classList.remove('hidden');
    resetUploadState();
}

function hideUploadModal() {
    if (isUploading && !modalClosing && !uploadCompleted) {
        if (!confirm('Upload in progress. Are you sure you want to cancel?')) {
            return;
        }
    }
    modalClosing = true;
    document.getElementById('upload-modal').classList.add('hidden');
    resetUploadState();
}

function resetUploadState() {
    selectedFiles = [];
    uploadQueue = [];
    isUploading = false;
    uploadCompleted = false;
    completedUploads = 0;
    totalUploads = 0;
    updateSelectedFilesCount();
    document.getElementById('upload-progress-container').classList.add('hidden');
    document.getElementById('tar-extraction-progress').classList.add('hidden');
    document.getElementById('file-progress-list').innerHTML = '';
    document.getElementById('clear-queue-btn').classList.add('hidden');
    document.getElementById('start-upload-btn').disabled = true;
}

// Prevent unwanted beforeunload alerts
window.addEventListener('beforeunload', function(e) {
    if (isUploading && !modalClosing && !uploadCompleted) {
        e.preventDefault();
        e.returnValue = 'Upload in progress. Are you sure you want to leave?';
        return e.returnValue;
    }
});

// Unified Drag & Drop functionality
const unifiedDropZone = document.getElementById('unified-drop-zone');
const unifiedFilesInput = document.getElementById('unified-files-input');
const unifiedFolderInput = document.getElementById('unified-folder-input');

setupDropZone(unifiedDropZone, (files) => {
    handleUnifiedFileSelection(Array.from(files));
});

unifiedFilesInput.addEventListener('change', (e) => {
    handleUnifiedFileSelection(Array.from(e.target.files));
});

unifiedFolderInput.addEventListener('change', (e) => {
    handleUnifiedFileSelection(Array.from(e.target.files));
});

function setupDropZone(dropZone, onFilesDropped) {
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-dragon-red', 'bg-dragon-red/5');
    });

    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        if (!dropZone.contains(e.relatedTarget)) {
            dropZone.classList.remove('border-dragon-red', 'bg-dragon-red/5');
        }
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-dragon-red', 'bg-dragon-red/5');
        
        const files = e.dataTransfer.files;
        onFilesDropped(files);
    });
}

function handleUnifiedFileSelection(files) {
    if (files.length === 0) return;
    
    // Add files to selection with automatic type detection
    files.forEach(file => {
        const exists = selectedFiles.some(f => 
            f.name === file.name && 
            f.size === file.size && 
            f.lastModified === file.lastModified
        );
        
        if (!exists) {
            // Auto-detect file type
            const fileName = file.name.toLowerCase();
            const webkitPath = file.webkitRelativePath || '';
            
            if (fileName.endsWith('.tar') || fileName.endsWith('.tar.gz') || fileName.endsWith('.tgz')) {
                file.uploadType = 'tar';
            } else if (webkitPath && webkitPath.includes('/')) {
                file.uploadType = 'folders';
            } else {
                file.uploadType = 'files';
            }
            
            selectedFiles.push(file);
        }
    });
    
    updateSelectedFilesCount();
    document.getElementById('start-upload-btn').disabled = selectedFiles.length === 0;
}

function handleFileSelection(files, type) {
    if (files.length === 0) return;
    
    // Add files to selection (avoid duplicates)
    files.forEach(file => {
        const exists = selectedFiles.some(f => 
            f.name === file.name && 
            f.size === file.size && 
            f.lastModified === file.lastModified
        );
        
        if (!exists) {
            file.uploadType = type;
            selectedFiles.push(file);
        }
    });
    
    updateSelectedFilesCount();
    document.getElementById('start-upload-btn').disabled = selectedFiles.length === 0;
}

function updateSelectedFilesCount() {
    const count = selectedFiles.length;
    const totalSize = selectedFiles.reduce((sum, file) => sum + file.size, 0);
    document.getElementById('selected-files-count').textContent = 
        `${count} files selected (${formatBytes(totalSize)})`;
}

function clearUploadQueue() {
    if (confirm('Are you sure you want to clear all selected files?')) {
        resetUploadState();
    }
}

// OPTIMIZED UPLOAD PROCESS FOR BETTER SPEED
async function startBatchUpload() {
    if (selectedFiles.length === 0) return;
    
    isUploading = true;
    uploadCompleted = false;
    uploadStartTime = Date.now();
    totalUploads = selectedFiles.length;
    completedUploads = 0;
    
    // Show progress container
    document.getElementById('upload-progress-container').classList.remove('hidden');
    document.getElementById('clear-queue-btn').classList.remove('hidden');
    document.getElementById('start-upload-btn').disabled = true;
    
    // Separate TAR files from regular files
    const tarFiles = selectedFiles.filter(file => file.uploadType === 'tar');
    const regularFiles = selectedFiles.filter(file => file.uploadType !== 'tar');
    
    // Create progress items for each file
    const progressList = document.getElementById('file-progress-list');
    progressList.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const progressItem = createFileProgressItem(file, index);
        progressList.appendChild(progressItem);
    });
    
    // Process TAR files first with extraction tracking
    for (const tarFile of tarFiles) {
        await uploadTarFile(tarFile, selectedFiles.indexOf(tarFile));
    }
    
    // OPTIMIZED: Upload regular files with larger batches for better speed
    // NOTE: Batch size must respect PHP max_file_uploads limit (default 20)
    // Setting batch size to 15 ensures we stay below the limit with margin for error
    // If PHP max_file_uploads is increased in php.ini, these values can be adjusted upward
    if (regularFiles.length > 0) {
        // Determine optimal batch size based on average file size
        const avgFileSize = regularFiles.reduce((sum, file) => sum + file.size, 0) / regularFiles.length;
        let batchSize;

        if (avgFileSize < 1024 * 1024) { // < 1MB files
            batchSize = 15; // Safe batch size respecting PHP max_file_uploads limit (default 20)
        } else if (avgFileSize < 10 * 1024 * 1024) { // < 10MB files
            batchSize = 15; // Safe batch size for medium files
        } else if (avgFileSize < 50 * 1024 * 1024) { // < 50MB files
            batchSize = 10; // Medium batch size
        } else {
            batchSize = 5; // Fewer concurrent uploads for very large files
        }

        const batches = [];
        for (let i = 0; i < regularFiles.length; i += batchSize) {
            batches.push(regularFiles.slice(i, i + batchSize));
        }

        // OPTIMIZED: Upload entire batch in single request for better performance
        for (const batch of batches) {
            await uploadBatchOptimized(batch);
        }
    }
    
    // All uploads completed - finalize to generate single manifest/patch
    uploadCompleted = true;
    isUploading = false;
    
    // Call finalize endpoint to generate manifest/patch once
    try {
        const response = await fetch('/admin/cache/finalize-upload', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await response.json();
        console.log('Upload finalized:', data);
    } catch (error) {
        console.error('Failed to finalize upload:', error);
    }
    
    setTimeout(() => {
        hideUploadModal();
        location.reload(); // Refresh to show new files
    }, 2000);
}

function createFileProgressItem(file, index) {
    const div = document.createElement('div');
    div.id = `file-progress-${index}`;
    div.className = 'bg-dragon-black/30 rounded-lg p-4';
    
    const isTar = file.uploadType === 'tar';
    const iconClass = isTar ? 'fas fa-file-archive text-purple-400' : 'fas fa-file text-blue-400';
    
    div.innerHTML = `
        <div class="flex justify-between items-center mb-2">
            <span class="text-dragon-silver font-medium truncate flex-1 mr-4">
                <i class="${iconClass} mr-2"></i>${file.name}
            </span>
            <span class="text-dragon-silver-dark text-sm">${formatBytes(file.size)}</span>
        </div>
        <div class="flex items-center mb-1">
            <div class="flex-1 bg-gray-700 rounded-full h-2 mr-3">
                <div class="bg-gradient-to-r ${isTar ? 'from-purple-600 to-purple-400' : 'from-dragon-red to-dragon-red-bright'} h-2 rounded-full transition-all duration-300" 
                     style="width: 0%" id="progress-bar-${index}"></div>
            </div>
            <span class="text-dragon-silver-dark text-sm min-w-[50px]" id="progress-text-${index}">0%</span>
        </div>
        <div class="flex justify-between text-xs text-dragon-silver-dark">
            <span id="status-${index}">Pending...</span>
            <span id="speed-${index}">--</span>
        </div>
        ${file.webkitRelativePath ? `<div class="text-xs text-blue-400 mt-1">Path: ${file.webkitRelativePath}</div>` : ''}
        ${isTar ? '<div class="text-xs text-purple-400 mt-1">TAR Archive - Will be extracted</div>' : ''}
    `;
    return div;
}

async function uploadTarFile(file, index) {
    return new Promise((resolve) => {
        const formData = new FormData();
        formData.append('tar_file', file);
        formData.append('preserve_structure', document.getElementById('preserve-structure').checked ? '1' : '0');
        formData.append('current_path', currentNavigationPath);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        const xhr = new XMLHttpRequest();
        
        // Track upload progress
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                updateFileProgress(index, percent, e.loaded, e.total);
                updateFileStatus(index, 'Uploading TAR...', 'text-purple-400');
                updateOverallProgress();
            }
        });
        
        // Handle completion
        xhr.addEventListener('load', () => {
            if (xhr.status >= 200 && xhr.status < 400) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        updateFileStatus(index, 'TAR Uploaded - Extracting...', 'text-purple-400');
                        // Start polling for extraction progress
                        pollExtractionProgress(response.extraction_id, index, resolve);
                    } else {
                        updateFileStatus(index, 'TAR Upload Failed', 'text-red-400');
                        completedUploads++;
                        updateOverallProgress();
                        resolve();
                    }
                } catch (e) {
                    updateFileStatus(index, 'TAR Response Error', 'text-red-400');
                    completedUploads++;
                    updateOverallProgress();
                    resolve();
                }
            } else {
                updateFileStatus(index, 'TAR Upload Failed', 'text-red-400');
                completedUploads++;
                updateOverallProgress();
                resolve();
            }
        });

        xhr.addEventListener('error', () => {
            updateFileStatus(index, 'TAR Network Error', 'text-red-400');
            completedUploads++;
            updateOverallProgress();
            resolve();
        });

        updateFileStatus(index, 'Uploading TAR...', 'text-purple-400');
        
        xhr.open('POST', '/admin/cache/store-tar');
        xhr.send(formData);
    });
}

async function pollExtractionProgress(extractionId, fileIndex, resolve) {
    const pollInterval = setInterval(async () => {
        try {
            // Fixed URL construction
            const response = await fetch('/admin/cache/extraction-progress?id=' + extractionId);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.status === 'completed') {
                clearInterval(pollInterval);
                updateFileProgress(fileIndex, 100, 1, 1);
                updateFileStatus(fileIndex, `Extracted ${data.files_count} files`, 'text-green-400');
                completedUploads++;
                updateOverallProgress();
                resolve();
            } else if (data.status === 'failed') {
                clearInterval(pollInterval);
                updateFileStatus(fileIndex, 'Extraction Failed', 'text-red-400');
                completedUploads++;
                updateOverallProgress();
                resolve();
            } else {
                // Update extraction progress
                const percent = data.total > 0 ? Math.round((data.processed / data.total) * 100) : 0;
                updateFileProgress(fileIndex, percent, data.processed, data.total);
                updateFileStatus(fileIndex, `Extracting... ${data.processed}/${data.total} files`, 'text-purple-400');
            }
        } catch (error) {
            console.error('Extraction polling error:', error);
            clearInterval(pollInterval);
            updateFileStatus(fileIndex, 'Extraction Polling Error', 'text-red-400');
            completedUploads++;
            updateOverallProgress();
            resolve();
        }
    }, 1000);
}

// OPTIMIZED: Upload entire batch in single HTTP request for maximum speed
async function uploadBatchOptimized(batch) {
    return new Promise((resolve) => {
        const formData = new FormData();
        const batchIndices = [];

        // Add all files to single FormData
        batch.forEach(file => {
            const index = selectedFiles.indexOf(file);
            batchIndices.push(index);
            formData.append('files[]', file);

            // Add relative path for folder uploads
            if (file.webkitRelativePath) {
                formData.append('relative_paths[]', file.webkitRelativePath);
            } else {
                formData.append('relative_paths[]', '');
            }
        });

        formData.append('preserve_structure', document.getElementById('preserve-structure').checked ? '1' : '0');
        formData.append('current_path', currentNavigationPath);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        const xhr = new XMLHttpRequest();
        xhr.timeout = 300000; // 5 minutes timeout

        let batchStartTime = Date.now();
        let lastLoaded = 0;

        // Track progress for entire batch
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);

                // Calculate speed
                const now = Date.now();
                const elapsed = (now - batchStartTime) / 1000;
                const speed = e.loaded / elapsed;

                // Update progress for each file in batch proportionally
                batchIndices.forEach(index => {
                    updateFileProgress(index, percent, e.loaded / batch.length, e.total / batch.length);
                    const speedElement = document.getElementById(`speed-${index}`);
                    if (speedElement) speedElement.textContent = formatSpeed(speed);
                });

                updateOverallProgress();
            }
        });

        // Handle completion
        xhr.addEventListener('load', () => {
            if (xhr.status >= 200 && xhr.status < 400) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    batchIndices.forEach(index => {
                        if (response.skipped_count > 0) {
                            updateFileStatus(index, 'Completed (some skipped)', 'text-yellow-400');
                        } else {
                            updateFileStatus(index, 'Completed', 'text-green-400');
                        }
                    });
                } catch (e) {
                    batchIndices.forEach(index => {
                        updateFileStatus(index, 'Completed', 'text-green-400');
                    });
                }
            } else {
                batchIndices.forEach(index => {
                    updateFileStatus(index, 'Failed', 'text-red-400');
                });
            }
            completedUploads += batch.length;
            updateOverallProgress();
            resolve();
        });

        xhr.addEventListener('error', () => {
            batchIndices.forEach(index => {
                updateFileStatus(index, 'Network Error', 'text-red-400');
            });
            completedUploads += batch.length;
            updateOverallProgress();
            resolve();
        });

        xhr.addEventListener('timeout', () => {
            batchIndices.forEach(index => {
                updateFileStatus(index, 'Upload Timeout', 'text-red-400');
            });
            completedUploads += batch.length;
            updateOverallProgress();
            resolve();
        });

        batchIndices.forEach(index => {
            updateFileStatus(index, 'Uploading...', 'text-blue-400');
        });

        xhr.open('POST', '/admin/cache');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    });
}

// OPTIMIZED UPLOAD FUNCTION WITH BETTER SPEED HANDLING
async function uploadSingleFileOptimized(file, index) {
    return new Promise((resolve) => {
        const formData = new FormData();
        formData.append('files[]', file);
        formData.append('preserve_structure', document.getElementById('preserve-structure').checked ? '1' : '0');
        formData.append('current_path', currentNavigationPath);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // Add relative path for folder uploads - PRESERVE FULL DIRECTORY STRUCTURE
        if (file.webkitRelativePath) {
            formData.append('relative_paths[]', file.webkitRelativePath);
        }

        const xhr = new XMLHttpRequest();
        
        // OPTIMIZED: Set timeout and configure for better performance
        xhr.timeout = 300000; // 5 minutes timeout
        
        // Track progress with optimized updates
        let lastProgressUpdate = 0;
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const now = Date.now();
                // Throttle progress updates to every 100ms for better performance
                if (now - lastProgressUpdate > 100) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    updateFileProgress(index, percent, e.loaded, e.total);
                    updateOverallProgress();
                    lastProgressUpdate = now;
                }
            }
        });

        // Handle completion
        xhr.addEventListener('load', () => {
            if (xhr.status >= 200 && xhr.status < 400) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.skipped_count > 0) {
                        updateFileStatus(index, 'Skipped - File already exists', 'text-yellow-400');
                    } else {
                        updateFileStatus(index, 'Completed', 'text-green-400');
                    }
                } catch (e) {
                    updateFileStatus(index, 'Completed', 'text-green-400');
                }
            } else {
                updateFileStatus(index, 'Failed', 'text-red-400');
            }
            completedUploads++;
            updateOverallProgress();
            resolve();
        });

        xhr.addEventListener('error', () => {
            updateFileStatus(index, 'Network Error', 'text-red-400');
            completedUploads++;
            updateOverallProgress();
            resolve();
        });

        xhr.addEventListener('timeout', () => {
            updateFileStatus(index, 'Upload Timeout', 'text-red-400');
            completedUploads++;
            updateOverallProgress();
            resolve();
        });

        updateFileStatus(index, 'Uploading...', 'text-blue-400');
        
        xhr.open('POST', '/admin/cache');
        
        // OPTIMIZED: Set headers for better performance
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.send(formData);
    });
}

function updateFileProgress(index, percent, loaded, total) {
    const progressBar = document.getElementById(`progress-bar-${index}`);
    const progressText = document.getElementById(`progress-text-${index}`);
    const speedElement = document.getElementById(`speed-${index}`);
    
    if (progressBar) progressBar.style.width = percent + '%';
    if (progressText) progressText.textContent = percent + '%';
    
    if (loaded > 0) {
        const elapsed = (Date.now() - uploadStartTime) / 1000;
        const speed = loaded / elapsed;
        if (speedElement) speedElement.textContent = formatSpeed(speed);
    }
}

function updateFileStatus(index, status, className = '') {
    const statusEl = document.getElementById(`status-${index}`);
    if (statusEl) {
        statusEl.textContent = status;
        statusEl.className = `text-xs ${className}`;
    }
}

function updateOverallProgress() {
    const overallPercent = Math.round((completedUploads / totalUploads) * 100);
    document.getElementById('overall-progress-bar').style.width = overallPercent + '%';
    document.getElementById('overall-progress-text').textContent = overallPercent + '%';
    document.getElementById('upload-stats').textContent = `${completedUploads} / ${totalUploads} files`;
    
    if (completedUploads > 0) {
        const elapsed = (Date.now() - uploadStartTime) / 1000;
        const totalBytes = selectedFiles.reduce((sum, file) => sum + file.size, 0);
        const uploadedBytes = selectedFiles.slice(0, completedUploads).reduce((sum, file) => sum + file.size, 0);
        
        const speed = uploadedBytes / elapsed;
        document.getElementById('upload-speed').textContent = formatSpeed(speed);
    }
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function formatSpeed(bytesPerSecond) {
    return formatBytes(bytesPerSecond) + '/s';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Only call updateBulkActions if the necessary elements exist
    if (document.getElementById('bulk-actions')) {
        updateBulkActions();
    }
    setupFileBrowserDragDrop();
    
    // Hide context menu when clicking elsewhere
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#context-menu')) {
            hideContextMenu();
        }
    });
    
    // Hide context menu on window resize to prevent positioning issues
    window.addEventListener('resize', hideContextMenu);
    
    // Confirmation modal event listeners
    document.getElementById('confirm-action-btn').addEventListener('click', executeConfirmation);
});

function toggleUploadMenu() {
    const menu = document.getElementById('upload-menu');
    menu.classList.toggle('hidden');
}

function hideUploadMenu() {
    document.getElementById('upload-menu').classList.add('hidden');
}

// Close upload menu when clicking outside
document.addEventListener('click', function(e) {
    const menuBtn = document.getElementById('upload-menu-btn');
    const menu = document.getElementById('upload-menu');
    if (!menuBtn.contains(e.target) && !menu.contains(e.target)) {
        hideUploadMenu();
    }
});

// Patch Management Functions
function downloadPatch(version) {
    window.location.href = `/admin/cache/patches/${version}/download`;
}

function deletePatch(version) {
    if (confirm(`Are you sure you want to delete patch v${version}?\n\nThis action cannot be undone.`)) {
        fetch(`/admin/cache/patches/${version}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Successfully deleted patch v${version}`);
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete patch'));
            }
        })
        .catch(error => {
            console.error('Delete patch error:', error);
            alert('Network error occurred while deleting patch');
        });
    }
}

function mergePatches() {
    if (confirm('Merge all incremental patches into a new base patch?\n\nThis will:\n• Create a new consolidated base patch\n• Delete old incremental patches\n• Optimize patch downloads\n\nProceed with merge?')) {
        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Merging...';
        
        fetch('/admin/cache/patches/merge', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Successfully merged patches!\n\nNew base version: v${data.new_version || 'unknown'}`);
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to merge patches'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-object-group mr-2"></i>Merge Patches';
            }
        })
        .catch(error => {
            console.error('Merge patches error:', error);
            alert('Network error occurred while merging patches');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-object-group mr-2"></i>Merge Patches';
        });
    }
}

// Patch Data Modal Functions
function viewPatchData(patch) {
    // Store current patch for insights
    currentPatchData = patch;
    
    // Populate patch info
    document.getElementById('patch-data-version').textContent = 'v' + patch.version;
    document.getElementById('patch-data-type').innerHTML = patch.is_base 
        ? '<span class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded text-xs">BASE PATCH</span>'
        : '<span class="px-2 py-1 bg-blue-500/20 text-blue-400 rounded text-xs">DELTA PATCH</span>';
    document.getElementById('patch-data-file-count').textContent = patch.file_count;
    document.getElementById('patch-data-size').textContent = formatBytes(patch.size);
    document.getElementById('patch-data-created').textContent = new Date(patch.created_at).toLocaleString();
    document.getElementById('patch-data-path').textContent = patch.path;
    document.getElementById('patch-data-path').title = patch.path;
    
    // Show/hide base version
    const baseVersionContainer = document.getElementById('patch-data-base-version-container');
    if (!patch.is_base && patch.base_version) {
        baseVersionContainer.classList.remove('hidden');
        document.getElementById('patch-data-base-version').textContent = 'v' + patch.base_version;
    } else {
        baseVersionContainer.classList.add('hidden');
    }
    
    // Build directory tree from file_manifest
    buildDirectoryTree(patch.file_manifest);
    
    // Show modal
    document.getElementById('patch-data-modal').classList.remove('hidden');
}

function hidePatchDataModal() {
    document.getElementById('patch-data-modal').classList.add('hidden');
}

function buildDirectoryTree(fileManifest) {
    const treeContainer = document.getElementById('patch-data-tree');
    
    if (!fileManifest || Object.keys(fileManifest).length === 0) {
        treeContainer.innerHTML = '<p class="text-dragon-silver-dark">No files in this patch.</p>';
        return;
    }
    
    // Build tree structure from flat file list
    const tree = {};
    const filePaths = Object.keys(fileManifest);
    
    filePaths.forEach(filePath => {
        const hash = fileManifest[filePath];
        const parts = filePath.split('/');
        let currentLevel = tree;
        
        parts.forEach((part, index) => {
            if (!currentLevel[part]) {
                currentLevel[part] = {
                    isFile: index === parts.length - 1,
                    hash: index === parts.length - 1 ? hash : null,
                    children: {}
                };
            }
            currentLevel = currentLevel[part].children;
        });
    });
    
    // Render tree
    treeContainer.innerHTML = renderTree(tree, '');
}

function renderTree(node, path = '', level = 0) {
    let html = '';
    const entries = Object.entries(node).sort((a, b) => {
        // Directories first, then files
        const aIsDir = !a[1].isFile;
        const bIsDir = !b[1].isFile;
        if (aIsDir && !bIsDir) return -1;
        if (!aIsDir && bIsDir) return 1;
        return a[0].localeCompare(b[0]);
    });
    
    entries.forEach(([name, data]) => {
        const currentPath = path ? `${path}/${name}` : name;
        const indent = '  '.repeat(level);
        
        if (data.isFile) {
            // Render file (clickable to view history)
            html += `<div class="text-dragon-silver hover:text-dragon-red transition-colors py-1 file-item cursor-pointer group" 
                     title="Hash: ${data.hash}\nClick to view file history" 
                     onclick="showFileHistory('${currentPath.replace(/'/g, "\\'")}')">
                ${indent}<i class="fas fa-file text-blue-400 mr-2"></i>${name}
                <span class="text-xs text-dragon-silver-dark ml-2">(${data.hash.substring(0, 8)}...)</span>
                <i class="fas fa-history text-dragon-silver-dark opacity-0 group-hover:opacity-100 transition-opacity ml-2 text-xs"></i>
            </div>`;
        } else {
            // Render directory
            const hasChildren = Object.keys(data.children).length > 0;
            const dirId = 'dir-' + currentPath.replace(/[^a-zA-Z0-9]/g, '-');
            
            html += `<div class="py-1">
                <div class="text-dragon-silver hover:text-dragon-red transition-colors cursor-pointer directory-toggle" 
                     onclick="toggleDirectory('${dirId}')" 
                     data-dir-id="${dirId}">
                    ${indent}<i class="fas fa-folder text-yellow-400 mr-2 folder-icon" id="icon-${dirId}"></i>${name}/
                </div>
                <div id="${dirId}" class="directory-content">
                    ${hasChildren ? renderTree(data.children, currentPath, level + 1) : ''}
                </div>
            </div>`;
        }
    });
    
    return html;
}

function toggleDirectory(dirId) {
    const dirContent = document.getElementById(dirId);
    const icon = document.getElementById('icon-' + dirId);
    
    if (dirContent.style.display === 'none') {
        dirContent.style.display = 'block';
        icon.classList.remove('fa-folder');
        icon.classList.add('fa-folder-open');
    } else {
        dirContent.style.display = 'none';
        icon.classList.remove('fa-folder-open');
        icon.classList.add('fa-folder');
    }
}

function expandAllDirectories() {
    document.querySelectorAll('.directory-content').forEach(dir => {
        dir.style.display = 'block';
    });
    document.querySelectorAll('.folder-icon').forEach(icon => {
        icon.classList.remove('fa-folder');
        icon.classList.add('fa-folder-open');
    });
}

function collapseAllDirectories() {
    document.querySelectorAll('.directory-content').forEach(dir => {
        dir.style.display = 'none';
    });
    document.querySelectorAll('.folder-icon').forEach(icon => {
        icon.classList.remove('fa-folder-open');
        icon.classList.add('fa-folder');
    });
}

// Store current patch for insights
let currentPatchData = null;

// Patch Comparison Functions
function comparePatchesAction() {
    const fromId = document.getElementById('compare-patch-from').value;
    const toId = document.getElementById('compare-patch-to').value;
    
    if (!fromId || !toId) {
        alert('Please select both patches to compare');
        return;
    }
    
    if (fromId === toId) {
        alert('Please select different patches to compare');
        return;
    }
    
    fetch(`/admin/cache/patches/compare?from=${fromId}&to=${toId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showPatchDiff(data);
            } else {
                alert('Error: ' + (data.message || 'Failed to compare patches'));
            }
        })
        .catch(error => {
            console.error('Comparison error:', error);
            alert('Error comparing patches: ' + error.message + '\n\nPlease check the browser console for details.');
        });
}

function showPatchDiff(data) {
    const { from_patch, to_patch, added, removed, modified } = data;
    
    document.getElementById('diff-patches-info').textContent = 
        `v${from_patch.version} → v${to_patch.version}`;
    document.getElementById('diff-added-count').textContent = added.length;
    document.getElementById('diff-modified-count').textContent = modified.length;
    document.getElementById('diff-removed-count').textContent = removed.length;
    document.getElementById('diff-total-count').textContent = added.length + removed.length + modified.length;
    
    const resultsContainer = document.getElementById('diff-results');
    let html = '';
    
    if (added.length > 0) {
        html += '<div class="mb-4"><h4 class="text-green-400 font-semibold mb-2"><i class="fas fa-plus-circle mr-2"></i>Added Files</h4>';
        added.forEach(file => {
            html += `<div class="bg-green-500/10 border border-green-500/30 rounded p-2 mb-1 text-dragon-silver font-mono text-sm">
                <i class="fas fa-file text-green-400 mr-2"></i>${file}
            </div>`;
        });
        html += '</div>';
    }
    
    if (removed.length > 0) {
        html += '<div class="mb-4"><h4 class="text-red-400 font-semibold mb-2"><i class="fas fa-minus-circle mr-2"></i>Removed Files</h4>';
        removed.forEach(file => {
            html += `<div class="bg-red-500/10 border border-red-500/30 rounded p-2 mb-1 text-dragon-silver font-mono text-sm">
                <i class="fas fa-file text-red-400 mr-2"></i>${file}
            </div>`;
        });
        html += '</div>';
    }
    
    if (modified.length > 0) {
        html += '<div class="mb-4"><h4 class="text-yellow-400 font-semibold mb-2"><i class="fas fa-edit mr-2"></i>Modified Files</h4>';
        modified.forEach(item => {
            html += `<div class="bg-yellow-500/10 border border-yellow-500/30 rounded p-2 mb-1 text-dragon-silver font-mono text-sm">
                <i class="fas fa-file text-yellow-400 mr-2"></i>${item.file}
                <div class="text-xs text-dragon-silver-dark mt-1 ml-6">
                    Hash: ${item.old_hash.substring(0, 12)}... → ${item.new_hash.substring(0, 12)}...
                </div>
            </div>`;
        });
        html += '</div>';
    }
    
    if (added.length === 0 && removed.length === 0 && modified.length === 0) {
        html = '<p class="text-dragon-silver-dark text-center py-8">No differences found between these patches.</p>';
    }
    
    resultsContainer.innerHTML = html;
    document.getElementById('patch-diff-modal').classList.remove('hidden');
}

function hidePatchDiffModal() {
    document.getElementById('patch-diff-modal').classList.add('hidden');
}

// Changelog Generation
function generateChangelog() {
    if (!currentPatchData) return;
    
    const patchId = currentPatchData.id;
    
    fetch(`/admin/cache/patches/${patchId}/changelog`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showChangelog(data);
            } else {
                alert('Error: ' + (data.message || 'Failed to generate changelog'));
            }
        })
        .catch(error => {
            console.error('Changelog error:', error);
            alert('Error generating changelog: ' + error.message + '\n\nPlease check the browser console for details.');
        });
}

function showChangelog(data) {
    document.getElementById('changelog-version').textContent = 'v' + data.version;
    
    const contentContainer = document.getElementById('changelog-content');
    let html = '';
    
    if (data.is_base) {
        html += `<div class="bg-purple-500/10 border border-purple-500/30 rounded-lg p-4 mb-4">
            <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-cube text-purple-400"></i>
                <h4 class="text-purple-400 font-semibold">Base Patch</h4>
            </div>
            <p class="text-dragon-silver-dark text-sm">This is a base patch containing ${data.file_count} files.</p>
        </div>`;
        
        html += '<div class="mb-3"><h5 class="text-dragon-silver font-medium mb-2">Files Included:</h5>';
        html += `<ul class="space-y-1">`;
        Object.keys(data.file_manifest).slice(0, 10).forEach(file => {
            html += `<li class="text-sm text-dragon-silver-dark font-mono"><i class="fas fa-file text-blue-400 mr-2"></i>${file}</li>`;
        });
        if (data.file_count > 10) {
            html += `<li class="text-sm text-dragon-silver-dark italic">...and ${data.file_count - 10} more files</li>`;
        }
        html += '</ul></div>';
    } else {
        html += `<div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 mb-4">
            <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-layer-group text-blue-400"></i>
                <h4 class="text-blue-400 font-semibold">Delta Patch</h4>
            </div>
            <p class="text-dragon-silver-dark text-sm">Based on v${data.base_version}</p>
        </div>`;
        
        html += `<div class="space-y-3">
            <div>
                <h5 class="text-green-400 font-medium mb-2"><i class="fas fa-plus-circle mr-2"></i>Changes (${data.file_count} files)</h5>
                <ul class="space-y-1">`;
        Object.keys(data.file_manifest).slice(0, 10).forEach(file => {
            html += `<li class="text-sm text-dragon-silver-dark font-mono"><i class="fas fa-file text-green-400 mr-2"></i>${file}</li>`;
        });
        if (data.file_count > 10) {
            html += `<li class="text-sm text-dragon-silver-dark italic">...and ${data.file_count - 10} more files</li>`;
        }
        html += '</ul></div></div>';
    }
    
    html += `<div class="mt-4 pt-4 border-t border-dragon-border">
        <p class="text-xs text-dragon-silver-dark">Created: ${new Date(data.created_at).toLocaleString()}</p>
        <p class="text-xs text-dragon-silver-dark">Size: ${formatBytes(data.size)}</p>
    </div>`;
    
    contentContainer.innerHTML = html;
    document.getElementById('changelog-modal').classList.remove('hidden');
}

function hideChangelogModal() {
    document.getElementById('changelog-modal').classList.add('hidden');
}

// File History Tracking
function showFileHistory(filePath) {
    fetch(`/admin/cache/patches/file-history?path=${encodeURIComponent(filePath)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayFileHistory(filePath, data.history);
            } else {
                alert('Error: ' + (data.message || 'Failed to load file history'));
            }
        })
        .catch(error => {
            console.error('File history error:', error);
            alert('Error loading file history: ' + error.message + '\n\nPlease check the browser console for details.');
        });
}

function displayFileHistory(filePath, history) {
    document.getElementById('file-history-name').textContent = filePath;
    
    const timeline = document.getElementById('file-history-timeline');
    let html = '';
    
    history.forEach((item, index) => {
        const isLast = index === history.length - 1;
        html += `<div class="flex gap-4">
            <div class="flex flex-col items-center">
                <div class="w-4 h-4 rounded-full ${item.status === 'added' ? 'bg-green-400' : item.status === 'modified' ? 'bg-yellow-400' : 'bg-blue-400'}"></div>
                ${!isLast ? '<div class="w-0.5 h-full bg-dragon-border"></div>' : ''}
            </div>
            <div class="flex-1 pb-6">
                <div class="glass-effect rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-dragon-silver font-medium">v${item.version}</span>
                        <span class="text-xs px-2 py-1 rounded ${item.status === 'added' ? 'bg-green-500/20 text-green-400' : item.status === 'modified' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-blue-500/20 text-blue-400'}">
                            ${item.status.toUpperCase()}
                        </span>
                    </div>
                    <p class="text-xs text-dragon-silver-dark mb-2">${new Date(item.created_at).toLocaleString()}</p>
                    <p class="text-xs text-dragon-silver-dark font-mono">Hash: ${item.hash}</p>
                </div>
            </div>
        </div>`;
    });
    
    timeline.innerHTML = html;
    document.getElementById('file-history-modal').classList.remove('hidden');
}

function hideFileHistoryModal() {
    document.getElementById('file-history-modal').classList.add('hidden');
}

// Integrity Verification
function verifyIntegrity() {
    if (!currentPatchData) return;
    
    const patchId = currentPatchData.id;
    document.getElementById('integrity-version').textContent = 'v' + currentPatchData.version;
    document.getElementById('integrity-modal').classList.remove('hidden');
    document.getElementById('integrity-progress').classList.remove('hidden');
    document.getElementById('integrity-results').innerHTML = '<p class="text-dragon-silver-dark">Starting verification...</p>';
    
    fetch(`/admin/cache/patches/${patchId}/verify`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('integrity-progress').classList.add('hidden');
            
            if (data.success) {
                showIntegrityResults(data);
            } else {
                document.getElementById('integrity-results').innerHTML = 
                    `<p class="text-red-400">Verification failed: ${data.message}</p>`;
            }
        })
        .catch(error => {
            console.error('Verification error:', error);
            document.getElementById('integrity-progress').classList.add('hidden');
            document.getElementById('integrity-results').innerHTML = 
                `<p class="text-red-400">Verification error: ${error.message}<br><small>Please check the browser console for details.</small></p>`;
        });
}

function showIntegrityResults(data) {
    const { valid, invalid, missing, total } = data;
    
    let html = `<div class="space-y-4">
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="text-center">
                <div class="text-2xl font-bold text-green-400">${valid}</div>
                <div class="text-xs text-dragon-silver-dark">Valid</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-red-400">${invalid}</div>
                <div class="text-xs text-dragon-silver-dark">Invalid</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-400">${missing}</div>
                <div class="text-xs text-dragon-silver-dark">Missing</div>
            </div>
        </div>`;
    
    if (invalid === 0 && missing === 0) {
        html += `<div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-center">
            <i class="fas fa-check-circle text-green-400 text-3xl mb-2"></i>
            <p class="text-green-400 font-semibold">All checksums verified successfully!</p>
            <p class="text-dragon-silver-dark text-sm mt-1">All ${total} files passed integrity check.</p>
        </div>`;
    } else {
        if (data.invalid_files && data.invalid_files.length > 0) {
            html += '<div class="mb-3"><h5 class="text-red-400 font-medium mb-2">Invalid Checksums:</h5><ul class="space-y-1">';
            data.invalid_files.forEach(file => {
                html += `<li class="text-sm text-red-400 font-mono">${file}</li>`;
            });
            html += '</ul></div>';
        }
        
        if (data.missing_files && data.missing_files.length > 0) {
            html += '<div><h5 class="text-yellow-400 font-medium mb-2">Missing Files:</h5><ul class="space-y-1">';
            data.missing_files.forEach(file => {
                html += `<li class="text-sm text-yellow-400 font-mono">${file}</li>`;
            });
            html += '</ul></div>';
        }
    }
    
    html += '</div>';
    document.getElementById('integrity-results').innerHTML = html;
}

function hideIntegrityModal() {
    document.getElementById('integrity-modal').classList.add('hidden');
}

// ===========================
// CHUNKED UPLOAD FUNCTIONS
// ===========================

// Chunked upload modal functions
function showChunkedUploadModal() {
    chunkedModalClosing = false;
    chunkedUploadCompleted = false;
    document.getElementById('chunked-upload-modal').classList.remove('hidden');
    resetChunkedUploadState();
    setupChunkedUploadHandlers();
}

function hideChunkedUploadModal() {
    if (chunkedIsUploading && !chunkedModalClosing && !chunkedUploadCompleted) {
        if (!confirm('Upload in progress. Are you sure you want to cancel?')) {
            return;
        }
    }
    chunkedModalClosing = true;
    document.getElementById('chunked-upload-modal').classList.add('hidden');
    resetChunkedUploadState();
}

function resetChunkedUploadState() {
    chunkedSelectedFiles = [];
    chunkedIsUploading = false;
    chunkedCompletedUploads = 0;
    chunkedTotalUploads = 0;
    updateChunkedSelectedFilesCount();
    document.getElementById('chunked-start-upload-btn').disabled = true;
    document.getElementById('chunked-upload-progress-container').classList.add('hidden');
    document.getElementById('chunked-clear-queue-btn').classList.add('hidden');
    document.getElementById('chunked-file-progress-list').innerHTML = '';
}

function setupChunkedUploadHandlers() {
    const filesInput = document.getElementById('chunked-files-input');
    const folderInput = document.getElementById('chunked-folder-input');
    const dropZone = document.getElementById('chunked-drop-zone');

    // Remove old listeners
    filesInput.replaceWith(filesInput.cloneNode(true));
    folderInput.replaceWith(folderInput.cloneNode(true));
    
    // Get fresh references
    const newFilesInput = document.getElementById('chunked-files-input');
    const newFolderInput = document.getElementById('chunked-folder-input');
    
    // Add event listeners
    newFilesInput.addEventListener('change', (e) => {
        handleChunkedFileSelection(Array.from(e.target.files));
    });
    
    newFolderInput.addEventListener('change', (e) => {
        handleChunkedFileSelection(Array.from(e.target.files));
    });
    
    // Drag and drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-yellow-400', 'bg-yellow-400/10');
    });
    
    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-yellow-400', 'bg-yellow-400/10');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-yellow-400', 'bg-yellow-400/10');
        
        const files = Array.from(e.dataTransfer.files);
        if (files.length > 0) {
            handleChunkedFileSelection(files);
        }
    });
}

function handleChunkedFileSelection(files) {
    files.forEach(file => {
        chunkedSelectedFiles.push(file);
    });
    
    updateChunkedSelectedFilesCount();
    document.getElementById('chunked-start-upload-btn').disabled = chunkedSelectedFiles.length === 0;
}

function updateChunkedSelectedFilesCount() {
    const count = chunkedSelectedFiles.length;
    const totalSize = chunkedSelectedFiles.reduce((sum, file) => sum + file.size, 0);
    document.getElementById('chunked-selected-files-count').textContent = 
        `${count} files selected (${formatBytes(totalSize)})`;
}

function clearChunkedUploadQueue() {
    if (confirm('Are you sure you want to clear all selected files?')) {
        resetChunkedUploadState();
    }
}

// Start chunked batch upload with dynamic strategy
async function startChunkedBatchUpload() {
    if (chunkedSelectedFiles.length === 0) return;
    
    chunkedIsUploading = true;
    chunkedUploadCompleted = false;
    chunkedUploadStartTime = Date.now();
    chunkedTotalUploads = chunkedSelectedFiles.length;
    chunkedCompletedUploads = 0;
    
    // Show progress container
    document.getElementById('chunked-upload-progress-container').classList.remove('hidden');
    document.getElementById('chunked-clear-queue-btn').classList.remove('hidden');
    document.getElementById('chunked-start-upload-btn').disabled = true;
    
    // Create progress items for each file
    const progressList = document.getElementById('chunked-file-progress-list');
    progressList.innerHTML = '';
    
    chunkedSelectedFiles.forEach((file, index) => {
        const progressItem = createChunkedFileProgressItem(file, index);
        progressList.appendChild(progressItem);
    });
    
    // Categorize files by size for optimal upload strategy
    const LARGE_FILE_THRESHOLD = 5 * 1024 * 1024; // 5MB
    const SMALL_FILE_THRESHOLD = 1 * 1024 * 1024; // 1MB
    const CONCURRENT_SMALL_FILES = 20; // Upload 20 small files at once
    const CONCURRENT_MEDIUM_FILES = 2; // Upload 2 medium files at once
    
    const largeFiles = [];
    const mediumFiles = [];
    const smallFiles = [];
    
    chunkedSelectedFiles.forEach((file, index) => {
        const fileWithIndex = { file, index };
        if (file.size > LARGE_FILE_THRESHOLD) {
            largeFiles.push(fileWithIndex);
        } else if (file.size > SMALL_FILE_THRESHOLD) {
            mediumFiles.push(fileWithIndex);
        } else {
            smallFiles.push(fileWithIndex);
        }
    });
    
    console.log(`Upload strategy: ${smallFiles.length} small, ${mediumFiles.length} medium, ${largeFiles.length} large files`);
    
    // Upload small files in parallel batches (5 at a time)
    for (let i = 0; i < smallFiles.length; i += CONCURRENT_SMALL_FILES) {
        const batch = smallFiles.slice(i, i + CONCURRENT_SMALL_FILES);
        await Promise.all(batch.map(({file, index}) => uploadFileDirectly(file, index)));
    }
    
    // Upload medium files in parallel batches (2 at a time)
    for (let i = 0; i < mediumFiles.length; i += CONCURRENT_MEDIUM_FILES) {
        const batch = mediumFiles.slice(i, i + CONCURRENT_MEDIUM_FILES);
        await Promise.all(batch.map(({file, index}) => uploadFileDirectly(file, index)));
    }
    
    // Upload large files one by one with chunking
    for (const {file, index} of largeFiles) {
        await uploadFileInChunks(file, index);
    }
    
    // All uploads completed - finalize to generate manifest/patch
    chunkedUploadCompleted = true;
    chunkedIsUploading = false;
    
    // Call finalize endpoint
    try {
        const response = await fetch('/admin/cache/finalize-upload', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await response.json();
        console.log('Chunked upload finalized:', data);
    } catch (error) {
        console.error('Failed to finalize chunked upload:', error);
    }
    
    setTimeout(() => {
        hideChunkedUploadModal();
        location.reload();
    }, 2000);
}

function createChunkedFileProgressItem(file, index) {
    const div = document.createElement('div');
    div.id = `chunked-file-progress-${index}`;
    div.className = 'bg-dragon-black/30 rounded-lg p-4';
    
    div.innerHTML = `
        <div class="flex justify-between items-center mb-2">
            <span class="text-dragon-silver font-medium truncate flex-1 mr-4">
                <i class="fas fa-file text-yellow-400 mr-2"></i>${file.name}
            </span>
            <span class="text-dragon-silver-dark text-sm">${formatBytes(file.size)}</span>
        </div>
        <div class="flex items-center mb-1">
            <div class="flex-1 bg-gray-700 rounded-full h-2 mr-3">
                <div class="bg-gradient-to-r from-yellow-600 to-yellow-400 h-2 rounded-full transition-all duration-300" 
                     style="width: 0%" id="chunked-progress-bar-${index}"></div>
            </div>
            <span class="text-dragon-silver-dark text-sm min-w-[50px]" id="chunked-progress-text-${index}">0%</span>
        </div>
        <div class="flex justify-between text-xs text-dragon-silver-dark">
            <span id="chunked-status-${index}">Pending...</span>
            <span id="chunked-speed-${index}">--</span>
        </div>
        ${file.webkitRelativePath ? `<div class="text-xs text-blue-400 mt-1">Path: ${file.webkitRelativePath}</div>` : ''}
    `;
    return div;
}

// Upload file in chunks with progress tracking (for large files)
async function uploadFileInChunks(file, index) {
    const CHUNK_SIZE = 5 * 1024 * 1024; // 5MB chunks for large files
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
    const relativePath = file.webkitRelativePath || '';
    
    updateChunkedFileStatus(index, 'Initializing...', 'text-yellow-400');
    
    try {
        // Step 1: Initialize upload
        const initResponse = await fetch('/admin/cache/chunked-init', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                filename: file.name,
                total_size: file.size,
                total_chunks: totalChunks,
                relative_path: relativePath
            })
        });
        
        const initData = await initResponse.json();
        if (!initData.success) {
            throw new Error(initData.message || 'Failed to initialize upload');
        }
        
        const uploadId = initData.upload_id;
        updateChunkedFileStatus(index, 'Uploading chunks...', 'text-yellow-400');
        
        // Step 2: Upload chunks
        for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
            const start = chunkIndex * CHUNK_SIZE;
            const end = Math.min(start + CHUNK_SIZE, file.size);
            const chunk = file.slice(start, end);
            
            const formData = new FormData();
            formData.append('upload_id', uploadId);
            formData.append('chunk_index', chunkIndex);
            formData.append('chunk', chunk);
            
            const chunkResponse = await fetch('/admin/cache/chunked-upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const chunkData = await chunkResponse.json();
            if (!chunkData.success) {
                throw new Error(chunkData.message || 'Failed to upload chunk');
            }
            
            // Update progress
            const percent = Math.round(((chunkIndex + 1) / totalChunks) * 100);
            updateChunkedFileProgress(index, percent);
            updateChunkedFileStatus(index, `Uploading chunk ${chunkIndex + 1}/${totalChunks}`, 'text-yellow-400');
            updateChunkedOverallProgress();
        }
        
        // Step 3: Complete upload
        updateChunkedFileStatus(index, 'Assembling file...', 'text-yellow-400');
        
        const completeResponse = await fetch('/admin/cache/chunked-complete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                upload_id: uploadId,
                preserve_structure: document.getElementById('chunked-preserve-structure').checked ? 1 : 0,
                current_path: currentNavigationPath
            })
        });
        
        const completeData = await completeResponse.json();
        if (completeData.success) {
            updateChunkedFileProgress(index, 100);
            updateChunkedFileStatus(index, 'Completed', 'text-green-400');
        } else {
            throw new Error(completeData.message || 'Failed to complete upload');
        }
        
    } catch (error) {
        console.error('Chunked upload error:', error);
        updateChunkedFileStatus(index, 'Failed: ' + error.message, 'text-red-400');
    } finally {
        chunkedCompletedUploads++;
        updateChunkedOverallProgress();
    }
}

// Upload small/medium files directly without chunking (faster for small files)
async function uploadFileDirectly(file, index) {
    const relativePath = file.webkitRelativePath || '';
    
    updateChunkedFileStatus(index, 'Uploading...', 'text-yellow-400');
    
    try {
        const formData = new FormData();
        formData.append('files[]', file);
        formData.append('preserve_structure', document.getElementById('chunked-preserve-structure').checked ? '1' : '0');
        formData.append('current_path', currentNavigationPath);
        formData.append('is_chunked_session', '1'); // Flag to prevent manifest generation
        
        if (relativePath) {
            formData.append('relative_paths[]', relativePath);
        }
        
        const xhr = new XMLHttpRequest();
        
        // Track upload progress
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                updateChunkedFileProgress(index, percent);
                updateChunkedFileStatus(index, `Uploading ${percent}%`, 'text-yellow-400');
            }
        });
        
        // Handle completion
        const uploadPromise = new Promise((resolve, reject) => {
            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        updateChunkedFileProgress(index, 100);
                        updateChunkedFileStatus(index, 'Completed', 'text-green-400');
                        resolve(response);
                    } else {
                        reject(new Error(response.message || 'Upload failed'));
                    }
                } else {
                    reject(new Error(`HTTP ${xhr.status}: ${xhr.statusText}`));
                }
            });
            
            xhr.addEventListener('error', () => {
                reject(new Error('Network error'));
            });
            
            xhr.addEventListener('abort', () => {
                reject(new Error('Upload cancelled'));
            });
        });
        
        xhr.open('POST', '/admin/cache');
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
        
        await uploadPromise;
        
    } catch (error) {
        console.error('Direct upload error:', error);
        updateChunkedFileStatus(index, 'Failed: ' + error.message, 'text-red-400');
    } finally {
        chunkedCompletedUploads++;
        updateChunkedOverallProgress();
    }
}

function updateChunkedFileProgress(index, percent) {
    const progressBar = document.getElementById(`chunked-progress-bar-${index}`);
    const progressText = document.getElementById(`chunked-progress-text-${index}`);
    
    if (progressBar) progressBar.style.width = percent + '%';
    if (progressText) progressText.textContent = percent + '%';
}

function updateChunkedFileStatus(index, status, className = '') {
    const statusEl = document.getElementById(`chunked-status-${index}`);
    if (statusEl) {
        statusEl.textContent = status;
        statusEl.className = `text-xs ${className}`;
    }
}

function updateChunkedOverallProgress() {
    const overallPercent = Math.round((chunkedCompletedUploads / chunkedTotalUploads) * 100);
    document.getElementById('chunked-overall-progress-bar').style.width = overallPercent + '%';
    document.getElementById('chunked-overall-progress-text').textContent = overallPercent + '%';
    document.getElementById('chunked-upload-stats').textContent = `${chunkedCompletedUploads} / ${chunkedTotalUploads} files`;
    
    if (chunkedCompletedUploads > 0) {
        const elapsed = (Date.now() - chunkedUploadStartTime) / 1000;
        const totalBytes = chunkedSelectedFiles.reduce((sum, file) => sum + file.size, 0);
        const uploadedBytes = chunkedSelectedFiles.slice(0, chunkedCompletedUploads).reduce((sum, file) => sum + file.size, 0);
        
        const speed = uploadedBytes / elapsed;
        document.getElementById('chunked-upload-speed').textContent = formatSpeed(speed);
    }
}

// ===========================
// ZIP → PATCH FUNCTIONS
// ===========================

function showZipPatchModal() {
    zipSelectedFile = null;
    zipIsProcessing = false;
    document.getElementById('zip-patch-modal').classList.remove('hidden');
    resetZipPatchState();
    setupZipPatchHandlers();
}

function hideZipPatchModal() {
    if (zipIsProcessing) {
        if (!confirm('Processing in progress. Are you sure you want to cancel?')) {
            return;
        }
    }
    document.getElementById('zip-patch-modal').classList.add('hidden');
    resetZipPatchState();
}

function resetZipPatchState() {
    zipSelectedFile = null;
    zipIsProcessing = false;
    document.getElementById('zip-file-selected').classList.add('hidden');
    document.getElementById('zip-patch-progress-container').classList.add('hidden');
    document.getElementById('zip-start-btn').disabled = true;
    document.getElementById('zip-file-input').value = '';
}

function setupZipPatchHandlers() {
    const fileInput = document.getElementById('zip-file-input');
    const dropZone = document.getElementById('zip-drop-zone');
    
    // Remove old listeners
    fileInput.replaceWith(fileInput.cloneNode(true));
    
    // Get fresh reference
    const newFileInput = document.getElementById('zip-file-input');
    
    // File input change
    newFileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleZipFileSelection(e.target.files[0]);
        }
    });
    
    // Drag and drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-purple-400', 'bg-purple-400/10');
    });
    
    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-purple-400', 'bg-purple-400/10');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-purple-400', 'bg-purple-400/10');
        
        const files = Array.from(e.dataTransfer.files);
        const zipFile = files.find(f => f.name.toLowerCase().endsWith('.zip'));
        
        if (zipFile) {
            handleZipFileSelection(zipFile);
        } else {
            alert('Please select a .zip file');
        }
    });
}

function handleZipFileSelection(file) {
    if (!file.name.toLowerCase().endsWith('.zip')) {
        alert('Only .zip files are supported for this method');
        return;
    }
    
    zipSelectedFile = file;
    document.getElementById('zip-file-name').textContent = file.name;
    document.getElementById('zip-file-size').textContent = formatBytes(file.size);
    document.getElementById('zip-file-selected').classList.remove('hidden');
    document.getElementById('zip-start-btn').disabled = false;
}

function clearZipFile() {
    resetZipPatchState();
}

async function startZipPatchUpload() {
    if (!zipSelectedFile) return;
    
    zipIsProcessing = true;
    document.getElementById('zip-start-btn').disabled = true;
    document.getElementById('zip-cancel-btn').disabled = true;
    document.getElementById('zip-patch-progress-container').classList.remove('hidden');
    
    const CHUNK_SIZE = 5 * 1024 * 1024; // 5MB chunks
    const totalChunks = Math.ceil(zipSelectedFile.size / CHUNK_SIZE);
    
    try {
        // Step 1: Initialize chunked upload
        updateZipStatus('upload', 'processing', 'Initializing upload...');
        
        const initResponse = await fetch('/admin/cache/chunked-init', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                filename: zipSelectedFile.name,
                total_size: zipSelectedFile.size,
                total_chunks: totalChunks,
                relative_path: ''
            })
        });
        
        const initData = await initResponse.json();
        if (!initData.success) {
            throw new Error(initData.message || 'Failed to initialize upload');
        }
        
        const uploadId = initData.upload_id;
        const uploadStartTime = Date.now();
        
        // Step 2: Upload chunks with progress tracking
        updateZipStatus('upload', 'processing', 'Uploading: 0%');
        
        for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
            const start = chunkIndex * CHUNK_SIZE;
            const end = Math.min(start + CHUNK_SIZE, zipSelectedFile.size);
            const chunk = zipSelectedFile.slice(start, end);
            
            const formData = new FormData();
            formData.append('upload_id', uploadId);
            formData.append('chunk_index', chunkIndex);
            formData.append('chunk', chunk);
            
            const chunkResponse = await fetch('/admin/cache/chunked-upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const chunkData = await chunkResponse.json();
            if (!chunkData.success) {
                throw new Error(chunkData.message || 'Failed to upload chunk');
            }
            
            // Update progress
            const uploadedBytes = (chunkIndex + 1) * CHUNK_SIZE;
            const progress = Math.round(((chunkIndex + 1) / totalChunks) * 100);
            const elapsed = (Date.now() - uploadStartTime) / 1000; // seconds
            const speed = uploadedBytes / elapsed; // bytes per second
            
            updateZipStatus('upload', 'processing', `Uploading: ${progress}% (${formatSpeed(speed)})`);
        }
        
        // Step 3: Complete chunked upload (reassemble file)
        updateZipStatus('upload', 'processing', 'Finalizing upload...');
        
        const completeResponse = await fetch('/admin/cache/chunked-complete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                upload_id: uploadId,
                preserve_structure: true,
                current_path: ''
            })
        });
        
        const completeData = await completeResponse.json();
        if (!completeData.success) {
            throw new Error(completeData.message || 'Failed to complete upload');
        }
        
        updateZipStatus('upload', 'complete', 'Upload complete!');
        
        // Step 4: Start extraction and patch generation
        updateZipStatus('extract', 'processing', 'Starting extraction...');
        
        const extractResponse = await fetch('/admin/cache/zip-extract-patch', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                upload_id: uploadId
            })
        });
        
        const extractData = await extractResponse.json();
        if (!extractData.success) {
            throw new Error(extractData.message || 'Failed to start extraction');
        }
        
        // Step 5: Process completed extraction result
        updateZipStatus('extract', 'complete', `Processed ${extractData.file_count || 0} files`);
        updateZipStatus('patch', 'complete', `Patch v${extractData.patch_version} created`);
        updateZipStatus('cleanup', 'complete', 'Cleanup complete!');
        
        // Show success result
        const resultDiv = document.getElementById('zip-patch-result');
        resultDiv.className = 'mt-6 p-4 rounded-lg bg-green-500/10 border border-green-500/30';
        resultDiv.innerHTML = `
            <div class="flex items-center mb-2">
                <i class="fas fa-check-circle text-green-400 text-2xl mr-3"></i>
                <div>
                    <p class="text-green-400 font-medium">Success!</p>
                    <p class="text-dragon-silver-dark text-sm">Patch v${extractData.patch_version} created with ${extractData.file_count || 0} files</p>
                </div>
            </div>
            <button onclick="location.reload()" class="mt-3 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors w-full">
                <i class="fas fa-sync-alt mr-2"></i>Refresh Page
            </button>
        `;
        resultDiv.classList.remove('hidden');
        zipIsProcessing = false;
        
    } catch (error) {
        console.error('ZIP → Patch error:', error);
        
        // Show error
        const resultDiv = document.getElementById('zip-patch-result');
        resultDiv.className = 'mt-6 p-4 rounded-lg bg-red-500/10 border border-red-500/30';
        resultDiv.innerHTML = `
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-circle text-red-400 text-2xl mr-3"></i>
                <div>
                    <p class="text-red-400 font-medium">Error</p>
                    <p class="text-dragon-silver-dark text-sm">${error.message}</p>
                </div>
            </div>
            <button onclick="hideZipPatchModal()" class="mt-3 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors w-full">
                Close
            </button>
        `;
        resultDiv.classList.remove('hidden');
        
        updateZipStatus('upload', 'error', 'Failed');
        document.getElementById('zip-cancel-btn').disabled = false;
        zipIsProcessing = false;
    }
}


function updateZipStatus(step, status, message) {
    const statusMap = {
        'upload': 'zip-upload-status',
        'extract': 'zip-extract-status',
        'patch': 'zip-patch-status',
        'cleanup': 'zip-cleanup-status'
    };
    
    const el = document.getElementById(statusMap[step]);
    if (!el) return;
    
    const icon = el.querySelector('i');
    const text = el.querySelector('span');
    
    if (status === 'processing') {
        icon.className = 'fas fa-circle-notch fa-spin text-purple-400 mr-3';
        el.className = 'flex items-center';
        text.textContent = message;
    } else if (status === 'complete') {
        icon.className = 'fas fa-check-circle text-green-400 mr-3';
        el.className = 'flex items-center text-green-400';
        text.textContent = message;
    } else if (status === 'error') {
        icon.className = 'fas fa-times-circle text-red-400 mr-3';
        el.className = 'flex items-center text-red-400';
        text.textContent = message;
    }
}

</script>
@endpush

@endsection