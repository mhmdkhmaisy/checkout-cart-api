@extends('admin.layout')

@section('title', 'Create Update - Aragon RSPS Admin')

@section('header', 'Create New Update')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.updates.index') }}" class="inline-flex items-center px-4 py-2 bg-dragon-surface border border-dragon-border text-dragon-silver rounded-lg hover:bg-dragon-red hover:text-white transition-colors">
        <i class="fas fa-arrow-left mr-2"></i> Back to Updates
    </a>
</div>

<div class="bg-dragon-surface border border-dragon-border rounded-lg shadow-lg">
    <div class="p-6">
        <form id="updateForm" action="{{ route('admin.updates.store') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label for="title" class="block text-dragon-silver font-semibold mb-2">
                    Title <span class="text-dragon-red">*</span>
                </label>
                <input type="text" 
                       class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('title') border-red-500 @enderror" 
                       id="title" 
                       name="title" 
                       value="{{ old('title') }}" 
                       required>
                <p class="text-sm text-dragon-silver-dark mt-1">The slug will be auto-generated from the title</p>
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-dragon-silver font-semibold mb-2">
                    Content Blocks <span class="text-dragon-red">*</span>
                </label>
                
                <!-- Block Editor -->
                <div id="blockEditor" class="space-y-3">
                    <!-- Blocks will be added here -->
                </div>

                <!-- Add Block Buttons -->
                <div id="addBlockButtons" class="mt-4 flex flex-wrap gap-2">
                    <button type="button" onclick="rootEditor?.addBlock('header')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-heading mr-1"></i> Add Header
                    </button>
                    <button type="button" onclick="rootEditor?.addBlock('paragraph')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-paragraph mr-1"></i> Add Paragraph
                    </button>
                    <button type="button" onclick="rootEditor?.addBlock('list')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-list mr-1"></i> Add List
                    </button>
                    <button type="button" onclick="rootEditor?.addBlock('code')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-code mr-1"></i> Add Code
                    </button>
                    <button type="button" onclick="rootEditor?.addBlock('alert')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Add Alert
                    </button>
                    <button type="button" onclick="rootEditor?.addBlock('image')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-image mr-1"></i> Add Image
                    </button>
                    <button type="button" onclick="rootEditor?.addBlock('callout')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-lightbulb mr-1"></i> Add Callout
                    </button>
                    <button type="button" onclick="rootEditor?.addBlock('table')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-table mr-1"></i> Add Table
                    </button>
                    <button type="button" onclick="rootEditor?.addBlock('separator')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-minus mr-1"></i> Add Separator
                    </button>
                    <button type="button" onclick="rootEditor?.addBlock('osrs_header')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-font mr-1"></i> Add OSRS Header
                    </button>
                    <button type="button" onclick="rootEditor?.addBlock('patch_notes_section')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-wrench mr-1"></i> Add Patch Notes Section
                    </button>
                    <button type="button" onclick="rootEditor?.addBlock('custom_section')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-folder-open mr-1"></i> Add Custom Section
                    </button>
                    <button type="button" onclick="openAutoFillModal()" class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-magic mr-1"></i> Auto-Fill from Text
                    </button>
                </div>

                <!-- Hidden textarea for JSON content -->
                <textarea name="content" id="contentJson" class="hidden"></textarea>
                @error('content')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="excerpt" class="block text-dragon-silver font-semibold mb-2">
                        Excerpt (Optional)
                    </label>
                    <textarea class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('excerpt') border-red-500 @enderror" 
                              id="excerpt" 
                              name="excerpt" 
                              rows="3"
                              maxlength="500">{{ old('excerpt') }}</textarea>
                    <p class="text-sm text-dragon-silver-dark mt-1">Short summary (max 500 chars). Auto-generated if empty.</p>
                    @error('excerpt')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="meta_description" class="block text-dragon-silver font-semibold mb-2">
                        SEO Meta Description (Optional)
                    </label>
                    <textarea class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('meta_description') border-red-500 @enderror" 
                              id="meta_description" 
                              name="meta_description" 
                              rows="3"
                              maxlength="160">{{ old('meta_description') }}</textarea>
                    <p class="text-sm text-dragon-silver-dark mt-1">For search engines (max 160 chars)</p>
                    @error('meta_description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="featured_image" class="block text-dragon-silver font-semibold mb-2">
                        Featured Image URL (Optional)
                    </label>
                    <input type="text" 
                           class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('featured_image') border-red-500 @enderror" 
                           id="featured_image" 
                           name="featured_image" 
                           value="{{ old('featured_image') }}">
                    <p class="text-sm text-dragon-silver-dark mt-1">Image for preview cards</p>
                    @error('featured_image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category" class="block text-dragon-silver font-semibold mb-2">
                        Category (Optional)
                    </label>
                    <input type="text" 
                           class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('category') border-red-500 @enderror" 
                           id="category" 
                           name="category" 
                           value="{{ old('category') }}"
                           placeholder="e.g., Game Update, Bugfix, Event">
                    @error('category')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="author" class="block text-dragon-silver font-semibold mb-2">
                        Author (Optional)
                    </label>
                    <input type="text" 
                           class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('author') border-red-500 @enderror" 
                           id="author" 
                           name="author" 
                           value="{{ old('author') }}"
                           placeholder="e.g., Admin Team">
                    @error('author')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="border-t border-dragon-border pt-6 mb-6">
                <h3 class="text-lg font-semibold text-dragon-silver mb-4">Update Type</h3>
                <div class="mb-6">
                    <label for="attached_to_update_id" class="block text-dragon-silver font-semibold mb-2">
                        Hotfix - Attach to Existing Update (Optional)
                    </label>
                    <select class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('attached_to_update_id') border-red-500 @enderror" 
                            id="attached_to_update_id" 
                            name="attached_to_update_id">
                        <option value="">None - Regular Update</option>
                        @foreach(\App\Models\Update::whereNull('attached_to_update_id')->orderBy('created_at', 'desc')->take(50)->get() as $update)
                            <option value="{{ $update->id }}" {{ old('attached_to_update_id') == $update->id ? 'selected' : '' }}>
                                {{ $update->title }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-sm text-dragon-silver-dark mt-1">If this is a hotfix, select the update to attach it to. The hotfix will appear at the end of that update's page.</p>
                    @error('attached_to_update_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="border-t border-dragon-border pt-6 mb-6">
                <h3 class="text-lg font-semibold text-dragon-silver mb-4">Publishing Options</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-dragon-red bg-dragon-black border-dragon-border rounded focus:ring-dragon-red" 
                                   id="is_published" 
                                   name="is_published" 
                                   value="1" 
                                   checked
                                   {{ old('is_published', true) ? 'checked' : '' }}>
                            <span class="ml-2 text-dragon-silver font-semibold">Publish immediately</span>
                        </label>
                        <p class="text-sm text-dragon-silver-dark mt-1 ml-7">Uncheck to save as draft</p>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-dragon-red bg-dragon-black border-dragon-border rounded focus:ring-dragon-red" 
                                   id="client_update" 
                                   name="client_update" 
                                   value="1" 
                                   {{ old('client_update') ? 'checked' : '' }}>
                            <span class="ml-2 text-dragon-silver">Requires client update</span>
                        </label>
                        <p class="text-sm text-dragon-silver-dark mt-1 ml-7">Check if users need to download new client</p>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-dragon-red bg-dragon-black border-dragon-border rounded focus:ring-dragon-red" 
                                   id="is_featured" 
                                   name="is_featured" 
                                   value="1" 
                                   {{ old('is_featured') ? 'checked' : '' }}>
                            <span class="ml-2 text-dragon-silver">Featured update</span>
                        </label>
                        <p class="text-sm text-dragon-silver-dark mt-1 ml-7">Highlight in listings</p>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-dragon-red bg-dragon-black border-dragon-border rounded focus:ring-dragon-red" 
                                   id="is_pinned" 
                                   name="is_pinned" 
                                   value="1" 
                                   {{ old('is_pinned') ? 'checked' : '' }}>
                            <span class="ml-2 text-dragon-silver">Pin to top</span>
                        </label>
                        <p class="text-sm text-dragon-silver-dark mt-1 ml-7">Always show first in list</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i> Create Update
                </button>
                <a href="{{ route('admin.updates.index') }}" class="px-6 py-2 bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg hover:bg-dragon-surface transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Auto-Fill Modal -->
<div id="autoFillModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-surface border border-dragon-border rounded-lg shadow-2xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="p-6 border-b border-dragon-border">
            <h3 class="text-xl font-bold text-dragon-silver">Auto-Fill from Text</h3>
            <p class="text-sm text-dragon-silver-dark mt-1">Paste formatted text with headers and items to automatically create blocks</p>
        </div>
        
        <div class="p-6 overflow-y-auto flex-1">
            <label class="block text-dragon-silver font-semibold mb-2">Paste Your Text</label>
            <textarea id="autoFillInput" 
                      class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-3 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red font-mono text-sm" 
                      rows="15"
                      placeholder="**Yippe:**&#10;* Bonereaper Helm -> 100000&#10;* Bonereaper Body -> 100000&#10;&#10;---&#10;&#10;**Emerald Crab:**&#10;* Corrupt Helmet -> 110000&#10;* Corrupt Platebody -> 110000"></textarea>
            
            <div id="autoFillError" class="hidden mt-3 p-3 bg-red-900 bg-opacity-30 border border-red-700 rounded text-red-400 text-sm"></div>
            
            <div class="mt-3 p-3 bg-blue-900 bg-opacity-30 border border-blue-700 rounded text-blue-300 text-sm">
                <strong>Format Guide:</strong><br>
                • Headers: Start with <code>**</code> (e.g., <code>**Boss Name:**</code>)<br>
                • Items: Start with <code>*</code> or <code>-</code> (e.g., <code>* Item Name -> Price</code>)<br>
                • Separators: Use <code>---</code> between sections<br>
                • Price format: <code>Item Name -> 12345</code> (optional)
            </div>
        </div>
        
        <div class="p-6 border-t border-dragon-border flex gap-3">
            <button type="button" onclick="processAutoFill()" class="px-6 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg transition-colors">
                <i class="fas fa-check mr-2"></i> Create Blocks
            </button>
            <button type="button" onclick="closeAutoFillModal()" class="px-6 py-2 bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg hover:bg-dragon-surface transition-colors">
                Cancel
            </button>
        </div>
    </div>
</div>

<style>
.block-item {
    transition: all 0.2s ease;
}
.block-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(212, 0, 0, 0.3);
}
.block-item.dragging {
    opacity: 0.5;
}
.drag-handle {
    cursor: move;
}
.drag-handle:hover {
    color: #d40000;
}
.osrs-tag {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    letter-spacing: 1px;
    image-rendering: pixelated;
    font-size: 11px;
    background: #1a1a1a;
    border: 2px solid #8b7355;
    padding: 2px 6px;
    color: #ff9040;
    text-shadow: 1px 1px 0px #000;
}
</style>

<script>
// Store nested editors globally
const nestedEditors = new Map();

// BlockEditor class for reusable block editing (root and nested)
class BlockEditor {
    constructor(containerId, contextId = 'root', allowSections = true) {
        this.containerId = containerId;
        this.contextId = contextId;
        this.allowSections = allowSections; // Don't allow sections inside sections
        this.blockCounter = 0;
        this.container = document.getElementById(containerId);
        
        if (contextId !== 'root') {
            nestedEditors.set(contextId, this);
        }
    }
    
    getBlockId() {
        return `${this.contextId}__block-${this.blockCounter++}`;
    }
    
    addBlock(type, data = null) {
        // Don't allow nested sections to prevent infinite nesting
        if (!this.allowSections && (type === 'patch_notes_section' || type === 'custom_section')) {
            alert('Sections cannot be nested inside other sections');
            return;
        }
        
        const id = this.getBlockId();
        const blockDiv = document.createElement('div');
        blockDiv.className = 'block-item bg-dragon-black border border-dragon-border rounded-lg p-4';
        blockDiv.draggable = true;
        blockDiv.dataset.id = id;
        blockDiv.dataset.type = type;
        blockDiv.dataset.context = this.contextId;
        
        blockDiv.innerHTML = this.generateBlockHtml(id, type, data);
        this.container.appendChild(blockDiv);
        
        // Setup drag and drop
        this.setupDragDrop(blockDiv);
        
        // For sections, create nested editor
        if (type === 'patch_notes_section' || type === 'custom_section') {
            this.initializeNestedEditor(id, data?.children || []);
        }
        
        return id;
    }
    
    generateBlockHtml(id, type, data) {
        const contextId = this.contextId;
        let content = `
            <div class="flex items-start gap-3">
                <div class="drag-handle text-dragon-silver-dark pt-2">
                    <i class="fas fa-grip-vertical"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-dragon-red font-semibold uppercase text-sm">${type.replace(/_/g, ' ')}</span>
                        <button type="button" onclick="(nestedEditors.get('${contextId}') || rootEditor)?.removeBlock('${id}')" class="text-dragon-silver-dark hover:text-red-500 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
        `;
        
        content += this.getBlockFields(id, type, data);
        content += `</div></div>`;
        return content;
    }
    
    getBlockFields(id, type, data) {
        switch(type) {
            case 'header':
                return `
                    <select id="${id}-level" class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                        <option value="2" ${data?.level === 2 ? 'selected' : ''}>H2</option>
                        <option value="3" ${data?.level === 3 ? 'selected' : ''}>H3</option>
                        <option value="4" ${data?.level === 4 ? 'selected' : ''}>H4</option>
                    </select>
                    <input type="text" id="${id}-text" placeholder="Header text" value="${data?.text || ''}" 
                           class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">
                `;
            case 'paragraph':
                return `
                    <textarea id="${id}-text" placeholder="Paragraph text" rows="3" 
                              class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">${data?.text || ''}</textarea>
                `;
            case 'list':
                const items = data?.items || [''];
                return `
                    <select id="${id}-style" class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                        <option value="unordered" ${data?.style === 'unordered' ? 'selected' : ''}>Bullet List</option>
                        <option value="ordered" ${data?.style === 'ordered' ? 'selected' : ''}>Numbered List</option>
                    </select>
                    <div id="${id}-items" class="space-y-2">
                        ${items.map((item, idx) => `
                            <div class="flex gap-2">
                                <input type="text" value="${item}" placeholder="List item" 
                                       class="flex-1 bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">
                                <button type="button" onclick="removeListItem(this)" class="text-red-500 hover:text-red-400">
                                    <i class="fas fa-minus-circle"></i>
                                </button>
                            </div>
                        `).join('')}
                    </div>
                    <button type="button" onclick="addListItem('${id}')" class="mt-2 text-dragon-red hover:text-dragon-red-bright text-sm">
                        <i class="fas fa-plus-circle mr-1"></i> Add Item
                    </button>
                `;
            case 'code':
                return `
                    <textarea id="${id}-code" placeholder="Code snippet" rows="4" 
                              class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 font-mono text-sm">${data?.code || ''}</textarea>
                `;
            case 'alert':
                return `
                    <select id="${id}-alertType" class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                        <option value="info" ${data?.type === 'info' ? 'selected' : ''}>Info</option>
                        <option value="warning" ${data?.type === 'warning' ? 'selected' : ''}>Warning</option>
                        <option value="success" ${data?.type === 'success' ? 'selected' : ''}>Success</option>
                        <option value="danger" ${data?.type === 'danger' ? 'selected' : ''}>Danger</option>
                    </select>
                    <textarea id="${id}-message" placeholder="Alert message" rows="2" 
                              class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">${data?.message || ''}</textarea>
                `;
            case 'image':
                return `
                    <div class="mb-2">
                        <label class="text-dragon-silver-dark text-sm mb-1 block">Image URL or Upload</label>
                        <input type="text" id="${id}-url" placeholder="Image URL" value="${data?.url || ''}" 
                               class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">
                    </div>
                    <div class="mb-2 flex items-center gap-2">
                        <span class="text-dragon-silver-dark text-sm">OR</span>
                        <input type="file" id="${id}-file" accept="image/*" 
                               class="flex-1 bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 text-sm"
                               onchange="handleImageUpload('${id}', this)">
                    </div>
                    <div id="${id}-preview" class="mb-2 hidden">
                        <img src="" class="max-w-full h-auto max-h-48 rounded border border-dragon-border">
                    </div>
                    <input type="text" id="${id}-caption" placeholder="Caption (optional)" value="${data?.caption || ''}" 
                           class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">
                `;
            case 'callout':
                return `
                    <select id="${id}-calloutType" class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                        <option value="info" ${data?.type === 'info' ? 'selected' : ''}>Info (Blue)</option>
                        <option value="tip" ${data?.type === 'tip' ? 'selected' : ''}>Tip (Green)</option>
                        <option value="warning" ${data?.type === 'warning' ? 'selected' : ''}>Warning (Yellow)</option>
                        <option value="important" ${data?.type === 'important' ? 'selected' : ''}>Important (Red)</option>
                        <option value="new" ${data?.type === 'new' ? 'selected' : ''}>New Feature (Purple)</option>
                    </select>
                    <input type="text" id="${id}-title" placeholder="Callout title (e.g., 'New Feature')" value="${data?.title || ''}" 
                           class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                    <textarea id="${id}-message" placeholder="Callout message" rows="3" 
                              class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">${data?.message || ''}</textarea>
                `;
            case 'table':
                const tableData = data?.data || [['Header 1', 'Header 2'], ['Row 1 Col 1', 'Row 1 Col 2']];
                return `
                    <div class="mb-2">
                        <label class="text-dragon-silver-dark text-sm mb-1 block">Table Content</label>
                        <div id="${id}-table-container" class="space-y-2">
                            ${tableData.map((row, rowIdx) => `
                                <div class="flex gap-2">
                                    ${row.map((cell, cellIdx) => `
                                        <input type="text" value="${cell}" placeholder="${rowIdx === 0 ? 'Header' : 'Cell'}" 
                                               class="flex-1 bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 text-sm ${rowIdx === 0 ? 'font-semibold' : ''}"
                                               data-row="${rowIdx}" data-col="${cellIdx}">
                                    `).join('')}
                                    <button type="button" onclick="removeTableRow('${id}', ${rowIdx})" class="text-red-500 hover:text-red-400 ${rowIdx === 0 ? 'invisible' : ''}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `).join('')}
                        </div>
                        <div class="mt-2 flex gap-2">
                            <button type="button" onclick="addTableRow('${id}')" class="text-dragon-red hover:text-dragon-red-bright text-sm">
                                <i class="fas fa-plus mr-1"></i> Add Row
                            </button>
                            <button type="button" onclick="addTableColumn('${id}')" class="text-dragon-red hover:text-dragon-red-bright text-sm">
                                <i class="fas fa-plus mr-1"></i> Add Column
                            </button>
                        </div>
                    </div>
                `;
            case 'separator':
                return `
                    <div class="text-center text-dragon-silver-dark py-4">
                        <i class="fas fa-minus"></i> Horizontal separator line
                    </div>
                `;
            case 'osrs_header':
                return `
                    <div class="mb-2">
                        <label class="text-dragon-silver-dark text-sm mb-1 block">Main Header</label>
                        <input type="text" id="${id}-header" placeholder="Main header text" value="${data?.header || ''}" 
                               class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                    </div>
                    <div class="mb-2">
                        <label class="text-dragon-silver-dark text-sm mb-1 block">Subheader (optional)</label>
                        <input type="text" id="${id}-subheader" placeholder="Subheader text" value="${data?.subheader || ''}" 
                               class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">
                    </div>
                    <div class="mb-2">
                        <label class="text-dragon-silver-dark text-sm mb-1 block">Color Scheme</label>
                        <select id="${id}-color" class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">
                            <option value="gold" ${data?.color === 'gold' ? 'selected' : ''}>Gold (Classic OSRS)</option>
                            <option value="red" ${data?.color === 'red' ? 'selected' : ''}>Red (Dragon Theme)</option>
                            <option value="cyan" ${data?.color === 'cyan' ? 'selected' : ''}>Cyan (Quest)</option>
                            <option value="green" ${data?.color === 'green' ? 'selected' : ''}>Green (Success)</option>
                            <option value="white" ${data?.color === 'white' ? 'selected' : ''}>White (Standard)</option>
                        </select>
                    </div>
                `;
            case 'patch_notes_section':
                const patchChildrenId = id + '-children';
                return `
                    <div class="bg-dragon-surface rounded-lg p-4 border-2 border-red-900">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-red-500 font-semibold flex items-center gap-2">
                                <i class="fas fa-wrench"></i> Patch Notes Content
                            </h4>
                        </div>
                        <div id="${patchChildrenId}" class="space-y-3 mb-3 min-h-[100px] bg-dragon-black/30 rounded p-3">
                            <p class="text-dragon-silver-dark text-sm text-center py-4">No blocks yet. Add blocks below.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('header')" class="px-2 py-1 bg-red-900 hover:bg-red-800 text-white rounded text-xs">
                                <i class="fas fa-heading"></i> Header
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('paragraph')" class="px-2 py-1 bg-red-900 hover:bg-red-800 text-white rounded text-xs">
                                <i class="fas fa-paragraph"></i> Paragraph
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('list')" class="px-2 py-1 bg-red-900 hover:bg-red-800 text-white rounded text-xs">
                                <i class="fas fa-list"></i> List
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('code')" class="px-2 py-1 bg-red-900 hover:bg-red-800 text-white rounded text-xs">
                                <i class="fas fa-code"></i> Code
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('image')" class="px-2 py-1 bg-red-900 hover:bg-red-800 text-white rounded text-xs">
                                <i class="fas fa-image"></i> Image
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('callout')" class="px-2 py-1 bg-red-900 hover:bg-red-800 text-white rounded text-xs">
                                <i class="fas fa-lightbulb"></i> Callout
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('alert')" class="px-2 py-1 bg-red-900 hover:bg-red-800 text-white rounded text-xs">
                                <i class="fas fa-exclamation-triangle"></i> Alert
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('separator')" class="px-2 py-1 bg-red-900 hover:bg-red-800 text-white rounded text-xs">
                                <i class="fas fa-minus"></i> Separator
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('osrs_header')" class="px-2 py-1 bg-red-900 hover:bg-red-800 text-white rounded text-xs">
                                <i class="fas fa-font"></i> OSRS Header
                            </button>
                            <button type="button" onclick="openAutoFillModal('${id}')" class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs">
                                <i class="fas fa-magic"></i> Auto-Fill
                            </button>
                        </div>
                    </div>
                `;
            case 'custom_section':
                const customTitleId = id + '-title';
                const customTagId = id + '-tag';
                const customColorId = id + '-color';
                const customChildrenId = id + '-children';
                return `
                    <div class="mb-2">
                        <label class="text-dragon-silver-dark text-sm mb-1 block">Section Title</label>
                        <input type="text" id="${customTitleId}" placeholder="Section title" value="${data?.title || ''}" 
                               class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                    </div>
                    <div class="mb-2">
                        <label class="text-dragon-silver-dark text-sm mb-1 block">Section Tag</label>
                        <input type="text" id="${customTagId}" placeholder="Tag text (e.g., SECTION, UPDATE, INFO)" value="${data?.tag || 'SECTION'}" 
                               class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                    </div>
                    <div class="mb-2">
                        <label class="text-dragon-silver-dark text-sm mb-1 block">Color Scheme</label>
                        <select id="${customColorId}" class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                            <option value="primary" ${data?.color === 'primary' ? 'selected' : ''}>Primary (Red)</option>
                            <option value="gold" ${data?.color === 'gold' ? 'selected' : ''}>Gold</option>
                            <option value="blue" ${data?.color === 'blue' ? 'selected' : ''}>Blue</option>
                            <option value="green" ${data?.color === 'green' ? 'selected' : ''}>Green</option>
                            <option value="purple" ${data?.color === 'purple' ? 'selected' : ''}>Purple</option>
                            <option value="orange" ${data?.color === 'orange' ? 'selected' : ''}>Orange</option>
                        </select>
                    </div>
                    <div class="bg-dragon-surface rounded-lg p-4 border-2 border-dragon-border">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-dragon-silver font-semibold flex items-center gap-2">
                                <span class="osrs-tag">${data?.tag || 'SECTION'}</span> Section Content
                            </h4>
                        </div>
                        <div id="${customChildrenId}" class="space-y-3 mb-3 min-h-[100px] bg-dragon-black/30 rounded p-3">
                            <p class="text-dragon-silver-dark text-sm text-center py-4">No blocks yet. Add blocks below.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('header')" class="px-2 py-1 bg-dragon-red hover:bg-dragon-red-bright text-white rounded text-xs">
                                <i class="fas fa-heading"></i> Header
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('paragraph')" class="px-2 py-1 bg-dragon-red hover:bg-dragon-red-bright text-white rounded text-xs">
                                <i class="fas fa-paragraph"></i> Paragraph
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('list')" class="px-2 py-1 bg-dragon-red hover:bg-dragon-red-bright text-white rounded text-xs">
                                <i class="fas fa-list"></i> List
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('image')" class="px-2 py-1 bg-dragon-red hover:bg-dragon-red-bright text-white rounded text-xs">
                                <i class="fas fa-image"></i> Image
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('table')" class="px-2 py-1 bg-dragon-red hover:bg-dragon-red-bright text-white rounded text-xs">
                                <i class="fas fa-table"></i> Table
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('code')" class="px-2 py-1 bg-dragon-red hover:bg-dragon-red-bright text-white rounded text-xs">
                                <i class="fas fa-code"></i> Code
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('callout')" class="px-2 py-1 bg-dragon-red hover:bg-dragon-red-bright text-white rounded text-xs">
                                <i class="fas fa-lightbulb"></i> Callout
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('alert')" class="px-2 py-1 bg-dragon-red hover:bg-dragon-red-bright text-white rounded text-xs">
                                <i class="fas fa-exclamation-triangle"></i> Alert
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('separator')" class="px-2 py-1 bg-dragon-red hover:bg-dragon-red-bright text-white rounded text-xs">
                                <i class="fas fa-minus"></i> Separator
                            </button>
                            <button type="button" onclick="nestedEditors.get('${id}')?.addBlock('osrs_header')" class="px-2 py-1 bg-dragon-red hover:bg-dragon-red-bright text-white rounded text-xs">
                                <i class="fas fa-font"></i> OSRS Header
                            </button>
                            <button type="button" onclick="openAutoFillModal('${id}')" class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs">
                                <i class="fas fa-magic"></i> Auto-Fill
                            </button>
                        </div>
                    </div>
                `;
            default:
                return '';
        }
    }
    
    initializeNestedEditor(sectionId, children = []) {
        // Create nested editor for this section
        const nestedEditor = new BlockEditor(sectionId + '-children', sectionId, false);
        
        // Load children blocks if provided
        if (children && children.length > 0) {
            children.forEach(childBlock => {
                nestedEditor.addBlock(childBlock.type, childBlock.data);
            });
        }
    }
    
    removeBlock(blockId) {
        const block = this.container.querySelector(`[data-id="${blockId}"]`);
        if (block) {
            // If it's a section, cleanup nested editor
            const type = block.dataset.type;
            if (type === 'patch_notes_section' || type === 'custom_section') {
                nestedEditors.delete(blockId);
            }
            block.remove();
        }
    }
    
    setupDragDrop(blockDiv) {
        blockDiv.addEventListener('dragstart', (e) => {
            blockDiv.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', blockDiv.innerHTML);
        });
        
        blockDiv.addEventListener('dragend', () => {
            blockDiv.classList.remove('dragging');
        });
        
        blockDiv.addEventListener('dragover', (e) => {
            e.preventDefault();
            const afterElement = this.getDragAfterElement(e.clientY);
            const dragging = this.container.querySelector('.dragging');
            if (dragging && dragging.dataset.context === this.contextId) {
                if (afterElement == null) {
                    this.container.appendChild(dragging);
                } else {
                    this.container.insertBefore(dragging, afterElement);
                }
            }
        });
    }
    
    getDragAfterElement(y) {
        const draggableElements = [...this.container.querySelectorAll('.block-item:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            if (child.dataset.context !== this.contextId) return closest;
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
    
    serialize() {
        const blocks = [];
        const blockElements = this.container.querySelectorAll(`.block-item[data-context="${this.contextId}"]`);
        
        blockElements.forEach(block => {
            const type = block.dataset.type;
            const id = block.dataset.id;
            const blockData = { type: type, data: {} };
            
            this.serializeBlockData(blockData, id, type);
            blocks.push(blockData);
        });
        
        return blocks;
    }
    
    serializeBlockData(blockData, id, type) {
        switch(type) {
            case 'header':
                blockData.data.level = parseInt(document.getElementById(`${id}-level`)?.value || 2);
                blockData.data.text = document.getElementById(`${id}-text`)?.value || '';
                break;
            case 'paragraph':
                blockData.data.text = document.getElementById(`${id}-text`)?.value || '';
                break;
            case 'list':
                blockData.data.style = document.getElementById(`${id}-style`)?.value || 'unordered';
                const items = document.querySelectorAll(`#${id}-items input`);
                blockData.data.items = Array.from(items).map(input => input.value).filter(v => v.trim());
                break;
            case 'code':
                blockData.data.code = document.getElementById(`${id}-code`)?.value || '';
                break;
            case 'alert':
                blockData.data.type = document.getElementById(`${id}-alertType`)?.value || 'info';
                blockData.data.message = document.getElementById(`${id}-message`)?.value || '';
                break;
            case 'image':
                blockData.data.url = document.getElementById(`${id}-url`)?.value || '';
                blockData.data.caption = document.getElementById(`${id}-caption`)?.value || '';
                break;
            case 'callout':
                blockData.data.type = document.getElementById(`${id}-calloutType`)?.value || 'info';
                blockData.data.title = document.getElementById(`${id}-title`)?.value || '';
                blockData.data.message = document.getElementById(`${id}-message`)?.value || '';
                break;
            case 'table':
                const tableContainer = document.getElementById(`${id}-table-container`);
                if (tableContainer) {
                    const tableRows = tableContainer.querySelectorAll('div');
                    const tableArray = [];
                    tableRows.forEach(row => {
                        const cells = row.querySelectorAll('input');
                        const rowData = Array.from(cells).map(cell => cell.value);
                        tableArray.push(rowData);
                    });
                    blockData.data.data = tableArray;
                }
                break;
            case 'separator':
                break;
            case 'osrs_header':
                blockData.data.header = document.getElementById(`${id}-header`)?.value || '';
                blockData.data.subheader = document.getElementById(`${id}-subheader`)?.value || '';
                blockData.data.color = document.getElementById(`${id}-color`)?.value || 'gold';
                break;
            case 'patch_notes_section':
                // Recursively serialize nested blocks
                const patchEditor = nestedEditors.get(id);
                blockData.data.children = patchEditor ? patchEditor.serialize() : [];
                break;
            case 'custom_section':
                blockData.data.title = document.getElementById(`${id}-title`)?.value || '';
                blockData.data.tag = document.getElementById(`${id}-tag`)?.value || 'SECTION';
                blockData.data.color = document.getElementById(`${id}-color`)?.value || 'primary';
                // Recursively serialize nested blocks
                const customEditor = nestedEditors.get(id);
                blockData.data.children = customEditor ? customEditor.serialize() : [];
                break;
        }
    }
}

// Initialize root editor
let rootEditor;
document.addEventListener('DOMContentLoaded', function() {
    rootEditor = new BlockEditor('blockEditor', 'root', true);
    rootEditor.addBlock('header', {text: 'Update Title', level: 2});
});

// Global wrapper for backward compatibility with button onclick
function addBlock(type, data = null) {
    if (rootEditor) {
        rootEditor.addBlock(type, data);
    }
}

// Global removeBlock dispatcher
function removeBlock(blockId) {
    // Try root editor first
    if (rootEditor) {
        rootEditor.removeBlock(blockId);
    }
    // Try nested editors
    nestedEditors.forEach(editor => {
        editor.removeBlock(blockId);
    });
}

// Helper functions for list items
function addListItem(blockId) {
    const itemsContainer = document.getElementById(`${blockId}-items`);
    const newItem = document.createElement('div');
    newItem.className = 'flex gap-2';
    newItem.innerHTML = `
        <input type="text" placeholder="List item" 
               class="flex-1 bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">
        <button type="button" onclick="removeListItem(this)" class="text-red-500 hover:text-red-400">
            <i class="fas fa-minus-circle"></i>
        </button>
    `;
    itemsContainer.appendChild(newItem);
}

function removeListItem(btn) {
    btn.parentElement.remove();
}

// Helper functions for tables
function addTableRow(blockId) {
    const container = document.getElementById(`${blockId}-table-container`);
    const firstRow = container.querySelector('div');
    const colCount = firstRow.querySelectorAll('input').length;
    const rowCount = container.querySelectorAll('div').length;
    
    const newRow = document.createElement('div');
    newRow.className = 'flex gap-2';
    let rowHTML = '';
    for (let i = 0; i < colCount; i++) {
        rowHTML += `
            <input type="text" placeholder="Cell" 
                   class="flex-1 bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 text-sm"
                   data-row="${rowCount}" data-col="${i}">
        `;
    }
    rowHTML += `
        <button type="button" onclick="removeTableRow('${blockId}', ${rowCount})" class="text-red-500 hover:text-red-400">
            <i class="fas fa-times"></i>
        </button>
    `;
    newRow.innerHTML = rowHTML;
    container.appendChild(newRow);
}

function addTableColumn(blockId) {
    const container = document.getElementById(`${blockId}-table-container`);
    const rows = container.querySelectorAll('div');
    
    rows.forEach((row, rowIdx) => {
        const inputs = row.querySelectorAll('input');
        const colCount = inputs.length;
        const removeBtn = row.querySelector('button');
        
        const newInput = document.createElement('input');
        newInput.type = 'text';
        newInput.placeholder = rowIdx === 0 ? 'Header' : 'Cell';
        newInput.className = `flex-1 bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 text-sm ${rowIdx === 0 ? 'font-semibold' : ''}`;
        newInput.dataset.row = rowIdx;
        newInput.dataset.col = colCount;
        
        row.insertBefore(newInput, removeBtn);
    });
}

function removeTableRow(blockId, rowIdx) {
    const container = document.getElementById(`${blockId}-table-container`);
    const rows = container.querySelectorAll('div');
    if (rows.length > 2) {
        rows[rowIdx].remove();
    }
}

// Handle image upload
async function handleImageUpload(blockId, input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    const formData = new FormData();
    formData.append('image', file);
    
    const urlInput = document.getElementById(`${blockId}-url`);
    const preview = document.getElementById(`${blockId}-preview`);
    const previewImg = preview.querySelector('img');
    
    try {
        urlInput.value = 'Uploading...';
        urlInput.disabled = true;
        
        const response = await fetch('{{ route('admin.updates.upload-image') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            urlInput.value = data.url;
            previewImg.src = data.url;
            preview.classList.remove('hidden');
        } else {
            alert('Upload failed. Please try again.');
            urlInput.value = '';
        }
    } catch (error) {
        console.error('Upload error:', error);
        alert('Upload failed. Please try again.');
        urlInput.value = '';
    } finally {
        urlInput.disabled = false;
    }
}

// Auto-Fill Modal Functions
let currentAutoFillContext = 'root';

function openAutoFillModal(contextId = 'root') {
    currentAutoFillContext = contextId;
    document.getElementById('autoFillModal').classList.remove('hidden');
    document.getElementById('autoFillInput').value = '';
    document.getElementById('autoFillError').classList.add('hidden');
}

function closeAutoFillModal() {
    document.getElementById('autoFillModal').classList.add('hidden');
    currentAutoFillContext = 'root';
}

function parseAutoFillText(text) {
    const lines = text.split('\n').map(line => line.trim());
    const sections = [];
    let currentSection = null;
    
    for (let line of lines) {
        if (!line || line === '---') {
            continue;
        }
        
        const isHeader = line.startsWith('**') || (line.endsWith(':') && !line.startsWith('*') && !line.startsWith('-'));
        const isItem = line.startsWith('*') || line.startsWith('-');
        
        if (isHeader) {
            if (currentSection && currentSection.items.length > 0) {
                sections.push(currentSection);
            }
            
            let headerText = line;
            if (headerText.startsWith('**')) {
                headerText = headerText.replace(/^\*\*\s*/, '').replace(/\*\*$/, '');
            }
            headerText = headerText.trim();
            
            currentSection = {
                header: headerText,
                items: []
            };
        } else if (isItem) {
            let itemText = line.replace(/^[*\-]\s*/, '').trim();
            
            if (!currentSection) {
                currentSection = {
                    header: 'Items',
                    items: []
                };
            }
            
            if (itemText) {
                currentSection.items.push(itemText);
            }
        }
    }
    
    if (currentSection && currentSection.items.length > 0) {
        sections.push(currentSection);
    }
    
    return sections;
}

function createBlocksFromSections(sections, contextId = 'root') {
    const editor = contextId === 'root' ? rootEditor : nestedEditors.get(contextId);
    
    if (!editor) {
        throw new Error('Block editor not initialized for context: ' + contextId);
    }
    
    let blocksCreated = 0;
    
    for (const section of sections) {
        editor.addBlock('header', {
            level: 3,
            text: section.header
        });
        blocksCreated++;
        
        editor.addBlock('list', {
            style: 'unordered',
            items: section.items
        });
        blocksCreated++;
    }
    
    return blocksCreated;
}

function processAutoFill() {
    const input = document.getElementById('autoFillInput').value;
    const errorDiv = document.getElementById('autoFillError');
    
    errorDiv.classList.add('hidden');
    
    if (!input.trim()) {
        errorDiv.textContent = 'Please paste some text to parse.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    try {
        const sections = parseAutoFillText(input);
        
        if (sections.length === 0) {
            errorDiv.textContent = 'No valid sections found. Make sure you have headers (starting with **) and items (starting with * or -).';
            errorDiv.classList.remove('hidden');
            return;
        }
        
        const blocksCreated = createBlocksFromSections(sections, currentAutoFillContext);
        
        closeAutoFillModal();
        
        const contextName = currentAutoFillContext === 'root' ? 'main content' : 'section';
        alert(`Successfully created ${blocksCreated} blocks from ${sections.length} section(s) in ${contextName}!`);
        
    } catch (error) {
        console.error('Auto-fill error:', error);
        errorDiv.textContent = `Error: ${error.message}`;
        errorDiv.classList.remove('hidden');
    }
}

// Form submission - serialize using BlockEditor
document.getElementById('updateForm').addEventListener('submit', function(e) {
    const blocks = rootEditor.serialize();
    const content = { blocks: blocks };
    document.getElementById('contentJson').value = JSON.stringify(content, null, 2);
});
</script>
@endsection
