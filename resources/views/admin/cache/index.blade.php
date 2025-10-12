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

    <!-- Search and Filter Bar -->
    <div class="glass-effect rounded-lg p-6">
        <form method="GET" action="{{ route('admin.cache.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Input -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-dragon-silver mb-2">Search Files</label>
                    <div class="relative">
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by filename, path, or type..."
                               class="w-full px-4 py-2 bg-dragon-black/50 border border-dragon-border rounded-lg text-dragon-silver placeholder-dragon-silver-dark focus:border-dragon-red focus:outline-none">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <i class="fas fa-search text-dragon-silver-dark"></i>
                        </div>
                    </div>
                </div>

                <!-- Type Filter -->
                <div>
                    <label for="type_filter" class="block text-sm font-medium text-dragon-silver mb-2">Filter by Type</label>
                    <select id="type_filter" 
                            name="type_filter" 
                            class="w-full px-4 py-2 bg-dragon-black/50 border border-dragon-border rounded-lg text-dragon-silver focus:border-dragon-red focus:outline-none">
                        <option value="">All Types</option>
                        <option value="files" {{ request('type_filter') === 'files' ? 'selected' : '' }}>Files Only</option>
                        <option value="directories" {{ request('type_filter') === 'directories' ? 'selected' : '' }}>Directories Only</option>
                        @foreach($fileExtensions as $ext)
                            <option value="{{ $ext }}" {{ request('type_filter') === $ext ? 'selected' : '' }}>
                                .{{ $ext }} files
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort Options -->
                <div>
                    <label for="sort" class="block text-sm font-medium text-dragon-silver mb-2">Sort By</label>
                    <div class="flex gap-2">
                        <select id="sort" 
                                name="sort" 
                                class="flex-1 px-4 py-2 bg-dragon-black/50 border border-dragon-border rounded-lg text-dragon-silver focus:border-dragon-red focus:outline-none">
                            <option value="filename" {{ request('sort') === 'filename' ? 'selected' : '' }}>Name</option>
                            <option value="size" {{ request('sort') === 'size' ? 'selected' : '' }}>Size</option>
                            <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Date</option>
                            <option value="file_type" {{ request('sort') === 'file_type' ? 'selected' : '' }}>Type</option>
                        </select>
                        <select name="direction" class="px-3 py-2 bg-dragon-black/50 border border-dragon-border rounded-lg text-dragon-silver focus:border-dragon-red focus:outline-none">
                            <option value="asc" {{ request('direction') === 'asc' ? 'selected' : '' }}>↑</option>
                            <option value="desc" {{ request('direction') === 'desc' ? 'selected' : '' }}>↓</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-search mr-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('admin.cache.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
                <div class="text-sm text-dragon-silver-dark">
                    Showing {{ $files->count() }} of {{ $files->total() }} files
                </div>
            </div>
        </form>
    </div>

    <!-- cPanel-like File Manager -->
    <div class="glass-effect rounded-lg overflow-hidden">
        <!-- Toolbar -->
        <div class="px-6 py-4 border-b border-dragon-border bg-dragon-black/30">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <h3 class="text-lg font-semibold text-dragon-silver">File Manager</h3>
                    <div class="flex gap-2">
                        <button onclick="toggleView('grid')" id="grid-view-btn" class="p-2 rounded bg-dragon-red/20 text-dragon-red">
                            <i class="fas fa-th"></i>
                        </button>
                        <button onclick="toggleView('list')" id="list-view-btn" class="p-2 rounded hover:bg-dragon-red/20 text-dragon-silver-dark">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="relative">
                        <button onclick="showUploadModal()" class="px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                            <i class="fas fa-upload mr-2"></i>Upload
                        </button>
                    </div>
                    <button onclick="createFolder()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-folder-plus mr-2"></i>New Folder
                    </button>
                    <button onclick="showDeleteAllModal()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-trash mr-2"></i>Delete All
                    </button>
                    <button onclick="refreshFiles()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- File Browser Area -->
        <div id="file-browser" class="min-h-[400px]">
            <!-- Breadcrumb Navigation -->
            <div class="px-6 py-3 border-b border-dragon-border bg-dragon-black/10">
                <nav class="flex items-center space-x-2 text-sm">
                    <button onclick="navigateTo('/')" class="text-dragon-red hover:text-dragon-red-bright">
                        <i class="fas fa-home mr-1"></i>Root
                    </button>
                    <span class="text-dragon-silver-dark">/</span>
                    <span id="current-path" class="text-dragon-silver">cache</span>
                </nav>
            </div>

            <!-- Drop Zone for Drag & Drop -->
            <div id="drop-zone" class="relative">
                <div id="drop-overlay" class="absolute inset-0 bg-dragon-red/20 border-2 border-dashed border-dragon-red rounded-lg flex items-center justify-center z-10 hidden">
                    <div class="text-center">
                        <i class="fas fa-upload text-4xl text-dragon-red mb-4"></i>
                        <p class="text-xl text-dragon-silver">Drop files here to upload</p>
                        <p class="text-dragon-silver-dark">Supports all file types and folders</p>
                    </div>
                </div>

                <!-- Grid View -->
                <div id="grid-view" class="p-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                    @if($files->count() > 0)
                        @foreach($files as $file)
                            <div class="file-item group relative bg-dragon-black/20 rounded-lg p-4 hover:bg-dragon-black/40 transition-colors cursor-pointer" 
                                 data-file-id="{{ $file->id }}" 
                                 data-file-name="{{ $file->filename }}" 
                                 data-file-type="{{ $file->file_type }}"
                                 data-file-extension="{{ strtolower(pathinfo($file->filename, PATHINFO_EXTENSION)) }}"
                                 onclick="selectFile(this)"
                                 oncontextmenu="showContextMenu(event, this)"
                                 ondblclick="openFile(this)">
                                
                                <!-- File Icon -->
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 flex items-center justify-center rounded-lg mb-2 {{ $file->file_type === 'directory' ? 'bg-blue-500/20' : 'bg-dragon-red/20' }}">
                                        @if($file->file_type === 'directory')
                                            <i class="fas fa-folder text-2xl text-blue-400"></i>
                                        @else
                                            @php
                                                $extension = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
                                                $iconClass = match($extension) {
                                                    'dat', 'idx', 'mid' => 'fas fa-file-archive text-dragon-red',
                                                    'zip', 'tar', 'gz', 'rar', '7z' => 'fas fa-file-archive text-purple-400',
                                                    'txt', 'log' => 'fas fa-file-alt text-green-400',
                                                    'jpg', 'png', 'gif', 'bmp' => 'fas fa-file-image text-blue-400',
                                                    'mp3', 'wav', 'ogg' => 'fas fa-file-audio text-yellow-400',
                                                    'mp4', 'avi', 'mov' => 'fas fa-file-video text-red-400',
                                                    'pdf' => 'fas fa-file-pdf text-red-500',
                                                    'doc', 'docx' => 'fas fa-file-word text-blue-600',
                                                    'xls', 'xlsx' => 'fas fa-file-excel text-green-600',
                                                    'js' => 'fab fa-js-square text-yellow-500',
                                                    'html', 'htm' => 'fab fa-html5 text-orange-500',
                                                    'css' => 'fab fa-css3-alt text-blue-500',
                                                    'php' => 'fab fa-php text-purple-500',
                                                    'java' => 'fab fa-java text-red-600',
                                                    'py' => 'fab fa-python text-blue-500',
                                                    default => 'fas fa-file text-dragon-silver-dark'
                                                };
                                            @endphp
                                            <i class="{{ $iconClass }} text-2xl"></i>
                                        @endif
                                    </div>
                                    
                                    <!-- File Name -->
                                    <p class="text-xs text-center text-dragon-silver truncate w-full" title="{{ $file->filename }}">
                                        {{ Str::limit($file->filename, 12) }}
                                    </p>
                                    
                                    <!-- File Size -->
                                    @if($file->file_type === 'file')
                                        <p class="text-xs text-dragon-silver-dark">{{ $file->formatted_size }}</p>
                                    @endif
                                </div>
                                
                                <!-- Selection Checkbox -->
                                <div class="absolute top-2 left-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <input type="checkbox" class="file-checkbox" value="{{ $file->id }}" onclick="event.stopPropagation(); updateBulkActions();">
                                </div>
                                
                                <!-- Status Indicator -->
                                <div class="absolute top-2 right-2">
                                    @if($file->existsOnDisk())
                                        <i class="fas fa-check-circle text-green-400 text-xs"></i>
                                    @else
                                        <i class="fas fa-exclamation-triangle text-red-400 text-xs"></i>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-span-full text-center py-12">
                            <i class="fas fa-folder-open text-6xl text-dragon-silver-dark mb-4"></i>
                            <h3 class="text-xl font-medium text-dragon-silver mb-2">No files found</h3>
                            <p class="text-dragon-silver-dark mb-6">This directory is empty or no files match your filters.</p>
                            <button onclick="showUploadModal()" class="px-6 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                                <i class="fas fa-upload mr-2"></i>Upload Files
                            </button>
                        </div>
                    @endif
                </div>

                <!-- List View (Hidden by default) -->
                <div id="list-view" class="hidden">
                    <table class="w-full">
                        <thead class="bg-dragon-black/50">
                            <tr>
                                <th class="px-6 py-3 text-left w-8">
                                    <input type="checkbox" id="select-all" onchange="toggleSelectAll()">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Size</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Modified</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dragon-border">
                            @foreach($files as $file)
                                <tr class="hover:bg-dragon-black/30 cursor-pointer" 
                                    data-file-id="{{ $file->id }}"
                                    data-file-name="{{ $file->filename }}" 
                                    data-file-type="{{ $file->file_type }}"
                                    data-file-extension="{{ strtolower(pathinfo($file->filename, PATHINFO_EXTENSION)) }}"
                                    onclick="selectFile(this)"
                                    oncontextmenu="showContextMenu(event, this)"
                                    ondblclick="openFile(this)">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="file-checkbox" value="{{ $file->id }}" onclick="event.stopPropagation(); updateBulkActions();">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full {{ $file->file_type === 'directory' ? 'bg-blue-500/20' : 'bg-dragon-red/20' }} flex items-center justify-center">
                                                    @if($file->file_type === 'directory')
                                                        <i class="fas fa-folder text-blue-400 text-sm"></i>
                                                    @else
                                                        <i class="fas fa-file text-dragon-silver-dark text-sm"></i>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-dragon-silver">{{ $file->filename }}</div>
                                                <div class="text-sm text-dragon-silver-dark">
                                                    @if($file->existsOnDisk())
                                                        <span class="text-green-400"><i class="fas fa-check-circle mr-1"></i>Available</span>
                                                    @else
                                                        <span class="text-red-400"><i class="fas fa-exclamation-triangle mr-1"></i>Missing</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-dragon-silver">
                                        {{ $file->file_type === 'file' ? $file->formatted_size : '--' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-dragon-silver-dark">
                                        {{ ucfirst($file->file_type) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-dragon-silver-dark">
                                        {{ $file->created_at->format('M j, Y g:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            @if($file->existsOnDisk() && $file->file_type === 'file')
                                                <button onclick="downloadFile('{{ $file->id }}')" class="text-blue-400 hover:text-blue-300" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            @endif
                                            <button onclick="showDeleteModal('{{ $file->id }}', '{{ $file->filename }}')" class="text-red-400 hover:text-red-300" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        @if($files->hasPages())
            <div class="px-6 py-4 border-t border-dragon-border">
                {{ $files->links() }}
            </div>
        @endif
    </div>

    <!-- Selected Items Actions -->
    <div id="bulk-actions" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-dragon-black border border-dragon-border rounded-lg p-4 shadow-lg hidden">
        <div class="flex items-center gap-4">
            <span class="text-dragon-silver">
                <span id="selected-count">0</span> items selected
            </span>
            <div class="flex gap-2">
                <button onclick="downloadSelected()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-download mr-2"></i>Download
                </button>
                <button onclick="showDeleteSelectedModal()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-trash mr-2"></i>Delete
                </button>
                <button onclick="clearSelection()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-times mr-2"></i>Clear
                </button>
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

        <!-- Upload Options -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Files Upload -->
            <div class="glass-effect rounded-lg p-6">
                <h4 class="text-lg font-medium text-dragon-silver mb-4">
                    <i class="fas fa-file mr-2"></i>Upload Individual Files
                </h4>
                <div id="files-drop-zone" class="border-2 border-dashed border-dragon-border rounded-lg p-6 text-center transition-colors hover:border-dragon-red">
                    <i class="fas fa-file-upload text-3xl text-dragon-silver-dark mb-3"></i>
                    <p class="text-dragon-silver mb-2">Drop files here or</p>
                    <button type="button" onclick="document.getElementById('files-input').click()" 
                            class="px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                        Browse Files
                    </button>
                    <input type="file" id="files-input" multiple class="hidden">
                    <p class="text-xs text-dragon-silver-dark mt-2">
                        All file types supported • Max 1GB per file
                    </p>
                </div>
            </div>

            <!-- Folder Upload -->
            <div class="glass-effect rounded-lg p-6">
                <h4 class="text-lg font-medium text-dragon-silver mb-4">
                    <i class="fas fa-folder mr-2"></i>Upload Folders
                </h4>
                <div id="folder-drop-zone" class="border-2 border-dashed border-dragon-border rounded-lg p-6 text-center transition-colors hover:border-dragon-red">
                    <i class="fas fa-folder-open text-3xl text-dragon-silver-dark mb-3"></i>
                    <p class="text-dragon-silver mb-2">Drop folders here or</p>
                    <button type="button" onclick="document.getElementById('folder-input').click()" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        Browse Folders
                    </button>
                    <input type="file" id="folder-input" webkitdirectory directory multiple class="hidden">
                    <p class="text-xs text-dragon-silver-dark mt-2">
                        Preserves directory structure • All file types
                    </p>
                </div>
            </div>

            <!-- TAR Upload -->
            <div class="glass-effect rounded-lg p-6">
                <h4 class="text-lg font-medium text-dragon-silver mb-4">
                    <i class="fas fa-file-archive mr-2"></i>Upload TAR Archives
                </h4>
                <div id="tar-drop-zone" class="border-2 border-dashed border-dragon-border rounded-lg p-6 text-center transition-colors hover:border-dragon-red">
                    <i class="fas fa-archive text-3xl text-dragon-silver-dark mb-3"></i>
                    <p class="text-dragon-silver mb-2">Drop .tar files here or</p>
                    <button type="button" onclick="document.getElementById('tar-input').click()" 
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                        Browse TAR Files
                    </button>
                    <input type="file" id="tar-input" accept=".tar,.tar.gz,.tgz" multiple class="hidden">
                    <p class="text-xs text-dragon-silver-dark mt-2">
                        Auto-extracts with real-time progress
                    </p>
                </div>
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
    const totalFiles = {{ $totalFiles }};
    showConfirmationModal(
        'Delete All Files',
        `Are you sure you want to delete ALL ${totalFiles} files? This action cannot be undone and will permanently remove all cache files.`,
        () => deleteAllFiles(),
        'Delete All'
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

// Delete All Files Function
async function deleteAllFiles() {
    try {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        const response = await fetch('{{ route("admin.cache.delete-all") }}', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showConfirmationModal(
                'Success',
                `Successfully deleted ${result.deleted_count} files.`,
                () => location.reload(),
                'OK'
            );
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
            'Network error occurred while deleting all files.',
            () => {},
            'OK'
        );
        console.error('Delete all error:', error);
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
    const fileName = element.getAttribute('data-file-name');
    
    if (fileType === 'directory') {
        // Navigate to directory
        navigateTo(fileName);
    } else {
        // Download file
        const fileId = element.getAttribute('data-file-id');
        downloadFile(fileId);
    }
}

function openSelectedFile() {
    if (contextMenuTarget) {
        openFile(contextMenuTarget);
    }
    hideContextMenu();
}

function downloadFile(fileId) {
    // Find the file by ID and download it
    const file = @json($files->items()).find(f => f.id == fileId);
    if (file && file.file_type === 'file') {
        window.open(`{{ url('/api/cache/file') }}/${file.filename}`, '_blank');
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
        
        const response = await fetch('{{ route("admin.cache.extract-file") }}', {
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
    form.action = `{{ route('admin.cache.destroy', '') }}/${fileId}`;
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
    // Update current path display
    document.getElementById('current-path').textContent = path === '/' ? 'cache' : `cache/${path}`;
    
    // In a real implementation, this would reload the page with the new path
    // For now, we'll just update the URL
    const url = new URL(window.location);
    url.searchParams.set('path', path);
    window.history.pushState({}, '', url);
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
        
        const response = await fetch('{{ route("admin.cache.bulk-delete") }}', {
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
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropOverlay.classList.add('hidden');
        
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

// Drag & Drop functionality for files
const filesDropZone = document.getElementById('files-drop-zone');
const filesInput = document.getElementById('files-input');

setupDropZone(filesDropZone, (files) => {
    handleFileSelection(Array.from(files), 'files');
});

filesInput.addEventListener('change', (e) => {
    handleFileSelection(Array.from(e.target.files), 'files');
});

// Drag & Drop functionality for folders
const folderDropZone = document.getElementById('folder-drop-zone');
const folderInput = document.getElementById('folder-input');

setupDropZone(folderDropZone, (files) => {
    handleFileSelection(Array.from(files), 'folders');
});

folderInput.addEventListener('change', (e) => {
    handleFileSelection(Array.from(e.target.files), 'folders');
});

// Drag & Drop functionality for TAR files
const tarDropZone = document.getElementById('tar-drop-zone');
const tarInput = document.getElementById('tar-input');

setupDropZone(tarDropZone, (files) => {
    const tarFiles = Array.from(files).filter(file => 
        file.name.toLowerCase().endsWith('.tar') || 
        file.name.toLowerCase().endsWith('.tar.gz') || 
        file.name.toLowerCase().endsWith('.tgz')
    );
    if (tarFiles.length > 0) {
        handleFileSelection(tarFiles, 'tar');
    } else {
        showConfirmationModal(
            'Invalid File Type',
            'Please select valid TAR files (.tar, .tar.gz, .tgz)',
            () => {},
            'OK'
        );
    }
});

tarInput.addEventListener('change', (e) => {
    handleFileSelection(Array.from(e.target.files), 'tar');
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
    
    // OPTIMIZED: Upload regular files with dynamic concurrent batches based on file size
    if (regularFiles.length > 0) {
        // Determine optimal batch size based on average file size
        const avgFileSize = regularFiles.reduce((sum, file) => sum + file.size, 0) / regularFiles.length;
        let batchSize;
        
        if (avgFileSize < 1024 * 1024) { // < 1MB files
            batchSize = 10; // More concurrent uploads for small files
        } else if (avgFileSize < 10 * 1024 * 1024) { // < 10MB files
            batchSize = 5; // Medium batch size
        } else {
            batchSize = 2; // Fewer concurrent uploads for large files
        }
        
        const batches = [];
        for (let i = 0; i < regularFiles.length; i += batchSize) {
            batches.push(regularFiles.slice(i, i + batchSize));
        }
        
        // Process batches with optimized concurrency
        for (const batch of batches) {
            await Promise.all(batch.map((file) => 
                uploadSingleFileOptimized(file, selectedFiles.indexOf(file))
            ));
        }
    }
    
    // All uploads completed
    uploadCompleted = true;
    isUploading = false;
    
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
        
        xhr.open('POST', '{{ route("admin.cache.store-tar") }}');
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

// OPTIMIZED UPLOAD FUNCTION WITH BETTER SPEED HANDLING
async function uploadSingleFileOptimized(file, index) {
    return new Promise((resolve) => {
        const formData = new FormData();
        formData.append('files[]', file);
        formData.append('preserve_structure', document.getElementById('preserve-structure').checked ? '1' : '0');
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
        
        xhr.open('POST', '{{ route("admin.cache.store") }}');
        
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
    updateBulkActions();
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
</script>
@endpush
@endsection