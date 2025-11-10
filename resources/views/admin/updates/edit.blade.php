@extends('admin.layout')

@section('title', 'Edit Update - Aragon RSPS Admin')

@section('header', 'Edit Update')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.updates.index') }}" class="inline-flex items-center px-4 py-2 bg-dragon-surface border border-dragon-border text-dragon-silver rounded-lg hover:bg-dragon-red hover:text-white transition-colors">
        <i class="fas fa-arrow-left mr-2"></i> Back to Updates
    </a>
</div>

<div class="bg-dragon-surface border border-dragon-border rounded-lg shadow-lg">
    <div class="p-6">
        <form id="updateForm" action="{{ route('admin.updates.update', $update) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="title" class="block text-dragon-silver font-semibold mb-2">
                    Title <span class="text-dragon-red">*</span>
                </label>
                <input type="text" 
                       class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('title') border-red-500 @enderror" 
                       id="title" 
                       name="title" 
                       value="{{ old('title', $update->title) }}" 
                       required>
                <p class="text-sm text-dragon-silver-dark mt-1">Slug: <code class="text-dragon-red">{{ $update->slug }}</code> (auto-generated)</p>
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
                    <!-- Blocks will be loaded here -->
                </div>

                <!-- Add Block Buttons -->
                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button" onclick="addBlock('header')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-heading mr-1"></i> Add Header
                    </button>
                    <button type="button" onclick="addBlock('paragraph')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-paragraph mr-1"></i> Add Paragraph
                    </button>
                    <button type="button" onclick="addBlock('list')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-list mr-1"></i> Add List
                    </button>
                    <button type="button" onclick="addBlock('code')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-code mr-1"></i> Add Code
                    </button>
                    <button type="button" onclick="addBlock('alert')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Add Alert
                    </button>
                    <button type="button" onclick="addBlock('image')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-image mr-1"></i> Add Image
                    </button>
                    <button type="button" onclick="addBlock('callout')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-lightbulb mr-1"></i> Add Callout
                    </button>
                    <button type="button" onclick="addBlock('table')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-table mr-1"></i> Add Table
                    </button>
                    <button type="button" onclick="addBlock('separator')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-minus mr-1"></i> Add Separator
                    </button>
                    <button type="button" onclick="addBlock('osrs_header')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-font mr-1"></i> Add OSRS Header
                    </button>
                    <button type="button" onclick="addBlock('patch_notes_section')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-wrench mr-1"></i> Add Patch Notes Section
                    </button>
                    <button type="button" onclick="addBlock('custom_section')" class="px-3 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg text-sm transition-colors">
                        <i class="fas fa-folder-open mr-1"></i> Add Custom Section
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
                              maxlength="500">{{ old('excerpt', $update->excerpt) }}</textarea>
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
                              maxlength="160">{{ old('meta_description', $update->meta_description) }}</textarea>
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
                           value="{{ old('featured_image', $update->featured_image) }}">
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
                           value="{{ old('category', $update->category) }}"
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
                           value="{{ old('author', $update->author) }}"
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
                        @foreach(\App\Models\Update::where('id', '!=', $update->id)->whereNull('attached_to_update_id')->orderBy('created_at', 'desc')->take(50)->get() as $availableUpdate)
                            <option value="{{ $availableUpdate->id }}" {{ old('attached_to_update_id', $update->attached_to_update_id) == $availableUpdate->id ? 'selected' : '' }}>
                                {{ $availableUpdate->title }}
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
                                   {{ old('is_published', $update->is_published) ? 'checked' : '' }}>
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
                                   {{ old('client_update', $update->client_update) ? 'checked' : '' }}>
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
                                   {{ old('is_featured', $update->is_featured) ? 'checked' : '' }}>
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
                                   {{ old('is_pinned', $update->is_pinned) ? 'checked' : '' }}>
                            <span class="ml-2 text-dragon-silver">Pin to top</span>
                        </label>
                        <p class="text-sm text-dragon-silver-dark mt-1 ml-7">Always show first in list</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i> Update
                </button>
                <a href="{{ route('admin.updates.index') }}" class="px-6 py-2 bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg hover:bg-dragon-surface transition-colors">
                    Cancel
                </a>
                <a href="{{ route('updates.show', $update->slug) }}" target="_blank" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-eye mr-2"></i> Preview
                </a>
            </div>
        </form>
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
</style>

<script>
let blockCounter = 0;
let blocks = [];

// Load existing blocks
const existingContent = {!! json_encode(json_decode($update->content)) !!};

document.addEventListener('DOMContentLoaded', function() {
    if (existingContent && existingContent.blocks) {
        existingContent.blocks.forEach(block => {
            addBlock(block.type, block.data);
        });
    }
});

function addBlock(type, data = null) {
    const id = `block-${blockCounter++}`;
    const blockEditor = document.getElementById('blockEditor');
    
    const blockDiv = document.createElement('div');
    blockDiv.className = 'block-item bg-dragon-black border border-dragon-border rounded-lg p-4';
    blockDiv.draggable = true;
    blockDiv.dataset.id = id;
    blockDiv.dataset.type = type;
    
    let content = `
        <div class="flex items-start gap-3">
            <div class="drag-handle text-dragon-silver-dark pt-2">
                <i class="fas fa-grip-vertical"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-dragon-red font-semibold uppercase text-sm">${type}</span>
                    <button type="button" onclick="removeBlock('${id}')" class="text-dragon-silver-dark hover:text-red-500 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
    `;
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    switch(type) {
        case 'header':
            content += `
                <select id="${id}-level" class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                    <option value="2" ${data?.level === 2 ? 'selected' : ''}>H2</option>
                    <option value="3" ${data?.level === 3 ? 'selected' : ''}>H3</option>
                    <option value="4" ${data?.level === 4 ? 'selected' : ''}>H4</option>
                </select>
                <input type="text" id="${id}-text" placeholder="Header text" value="${escapeHtml(data?.text || '')}" 
                       class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">
            `;
            break;
        case 'paragraph':
            content += `
                <textarea id="${id}-text" placeholder="Paragraph text" rows="3" 
                          class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">${escapeHtml(data?.text || '')}</textarea>
            `;
            break;
        case 'list':
            const items = data?.items || [''];
            content += `
                <select id="${id}-style" class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                    <option value="unordered" ${data?.style === 'unordered' ? 'selected' : ''}>Bullet List</option>
                    <option value="ordered" ${data?.style === 'ordered' ? 'selected' : ''}>Numbered List</option>
                </select>
                <div id="${id}-items" class="space-y-2">
                    ${items.map((item, idx) => `
                        <div class="flex gap-2">
                            <input type="text" value="${escapeHtml(item)}" placeholder="List item" 
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
            break;
        case 'code':
            content += `
                <textarea id="${id}-code" placeholder="Code snippet" rows="4" 
                          class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 font-mono text-sm">${escapeHtml(data?.code || '')}</textarea>
            `;
            break;
        case 'alert':
            content += `
                <select id="${id}-alertType" class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                    <option value="info" ${data?.type === 'info' ? 'selected' : ''}>Info</option>
                    <option value="warning" ${data?.type === 'warning' ? 'selected' : ''}>Warning</option>
                    <option value="success" ${data?.type === 'success' ? 'selected' : ''}>Success</option>
                    <option value="danger" ${data?.type === 'danger' ? 'selected' : ''}>Danger</option>
                </select>
                <textarea id="${id}-message" placeholder="Alert message" rows="2" 
                          class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">${escapeHtml(data?.message || '')}</textarea>
            `;
            break;
        case 'image':
            content += `
                <div class="mb-2">
                    <label class="text-dragon-silver-dark text-sm mb-1 block">Image URL or Upload</label>
                    <input type="text" id="${id}-url" placeholder="Image URL" value="${escapeHtml(data?.url || '')}" 
                           class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">
                </div>
                <div class="mb-2 flex items-center gap-2">
                    <span class="text-dragon-silver-dark text-sm">OR</span>
                    <input type="file" id="${id}-file" accept="image/*" 
                           class="flex-1 bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 text-sm"
                           onchange="handleImageUpload('${id}', this)">
                </div>
                <div id="${id}-preview" class="mb-2 ${data?.url ? '' : 'hidden'}">
                    <img src="${escapeHtml(data?.url || '')}" class="max-w-full h-auto max-h-48 rounded border border-dragon-border">
                </div>
                <input type="text" id="${id}-caption" placeholder="Caption (optional)" value="${escapeHtml(data?.caption || '')}" 
                       class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">
            `;
            break;
        case 'callout':
            content += `
                <select id="${id}-calloutType" class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                    <option value="info" ${data?.type === 'info' ? 'selected' : ''}>Info (Blue)</option>
                    <option value="tip" ${data?.type === 'tip' ? 'selected' : ''}>Tip (Green)</option>
                    <option value="warning" ${data?.type === 'warning' ? 'selected' : ''}>Warning (Yellow)</option>
                    <option value="important" ${data?.type === 'important' ? 'selected' : ''}>Important (Red)</option>
                    <option value="new" ${data?.type === 'new' ? 'selected' : ''}>New Feature (Purple)</option>
                </select>
                <input type="text" id="${id}-title" placeholder="Callout title (e.g., 'New Feature')" value="${escapeHtml(data?.title || '')}" 
                       class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                <textarea id="${id}-message" placeholder="Callout message" rows="3" 
                          class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">${escapeHtml(data?.message || '')}</textarea>
            `;
            break;
        case 'table':
            const tableData = data?.data || [['Header 1', 'Header 2'], ['Row 1 Col 1', 'Row 1 Col 2']];
            content += `
                <div class="mb-2">
                    <label class="text-dragon-silver-dark text-sm mb-1 block">Table Content</label>
                    <div id="${id}-table-container" class="space-y-2">
                        ${tableData.map((row, rowIdx) => `
                            <div class="flex gap-2">
                                ${row.map((cell, cellIdx) => `
                                    <input type="text" value="${escapeHtml(cell)}" placeholder="${rowIdx === 0 ? 'Header' : 'Cell'}" 
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
            break;
        case 'separator':
            content += `
                <div class="text-center text-dragon-silver-dark py-4">
                    <i class="fas fa-minus"></i> Horizontal separator line
                </div>
            `;
            break;
        case 'osrs_header':
            content += `
                <div class="mb-2">
                    <label class="text-dragon-silver-dark text-sm mb-1 block">Main Header</label>
                    <input type="text" id="${id}-header" placeholder="Main header text" value="${escapeHtml(data?.header || '')}" 
                           class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                </div>
                <div class="mb-2">
                    <label class="text-dragon-silver-dark text-sm mb-1 block">Subheader (optional)</label>
                    <input type="text" id="${id}-subheader" placeholder="Subheader text" value="${escapeHtml(data?.subheader || '')}" 
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
            break;
        case 'patch_notes_section':
            content += `
                <div class="mb-2">
                    <label class="text-dragon-silver-dark text-sm mb-1 block">Patch Notes Content (JSON format)</label>
                    <textarea id="${id}-children" placeholder='[{"type":"paragraph","data":{"text":"Fixed a bug"}},{"type":"list","data":{"style":"unordered","items":["Item 1","Item 2"]}}]' rows="6" 
                              class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 font-mono text-sm">${data?.children ? escapeHtml(JSON.stringify(data.children, null, 2)) : ''}</textarea>
                    <p class="text-xs text-dragon-silver-dark mt-1">Add child blocks as JSON array. Supports: paragraph, list, table, image, separator, etc.</p>
                </div>
            `;
            break;
        case 'custom_section':
            content += `
                <div class="mb-2">
                    <label class="text-dragon-silver-dark text-sm mb-1 block">Section Title</label>
                    <input type="text" id="${id}-title" placeholder="Section title" value="${escapeHtml(data?.title || '')}" 
                           class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                </div>
                <div class="mb-2">
                    <label class="text-dragon-silver-dark text-sm mb-1 block">Color Scheme</label>
                    <select id="${id}-color" class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                        <option value="primary" ${data?.color === 'primary' ? 'selected' : ''}>Primary (Red)</option>
                        <option value="gold" ${data?.color === 'gold' ? 'selected' : ''}>Gold</option>
                        <option value="blue" ${data?.color === 'blue' ? 'selected' : ''}>Blue</option>
                        <option value="green" ${data?.color === 'green' ? 'selected' : ''}>Green</option>
                        <option value="purple" ${data?.color === 'purple' ? 'selected' : ''}>Purple</option>
                        <option value="orange" ${data?.color === 'orange' ? 'selected' : ''}>Orange</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="text-dragon-silver-dark text-sm mb-1 block">Section Content (JSON format)</label>
                    <textarea id="${id}-children" placeholder='[{"type":"paragraph","data":{"text":"Content here"}},{"type":"image","data":{"url":"...","caption":"..."}}]' rows="6" 
                              class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 font-mono text-sm">${data?.children ? escapeHtml(JSON.stringify(data.children, null, 2)) : ''}</textarea>
                    <p class="text-xs text-dragon-silver-dark mt-1">Add child blocks as JSON array. Supports: paragraph, list, table, image, separator, etc.</p>
                </div>
            `;
            break;
    }
    
    content += `
            </div>
        </div>
    `;
    
    blockDiv.innerHTML = content;
    blockEditor.appendChild(blockDiv);
    
    // Drag and drop events
    blockDiv.addEventListener('dragstart', handleDragStart);
    blockDiv.addEventListener('dragover', handleDragOver);
    blockDiv.addEventListener('drop', handleDrop);
    blockDiv.addEventListener('dragend', handleDragEnd);
}

function removeBlock(id) {
    const block = document.querySelector(`[data-id="${id}"]`);
    if (block) {
        block.remove();
    }
}

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

let draggedElement = null;

function handleDragStart(e) {
    draggedElement = this;
    this.classList.add('dragging');
}

function handleDragOver(e) {
    e.preventDefault();
    const afterElement = getDragAfterElement(e.clientY);
    const blockEditor = document.getElementById('blockEditor');
    if (afterElement == null) {
        blockEditor.appendChild(draggedElement);
    } else {
        blockEditor.insertBefore(draggedElement, afterElement);
    }
}

function handleDrop(e) {
    e.preventDefault();
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
}

function getDragAfterElement(y) {
    const blockEditor = document.getElementById('blockEditor');
    const draggableElements = [...blockEditor.querySelectorAll('.block-item:not(.dragging)')];
    
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
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

// Generate JSON before submit
document.getElementById('updateForm').addEventListener('submit', function(e) {
    const blocks = [];
    const blockElements = document.querySelectorAll('.block-item');
    
    blockElements.forEach(block => {
        const type = block.dataset.type;
        const id = block.dataset.id;
        const blockData = { type: type, data: {} };
        
        switch(type) {
            case 'header':
                blockData.data.level = parseInt(document.getElementById(`${id}-level`).value);
                blockData.data.text = document.getElementById(`${id}-text`).value;
                break;
            case 'paragraph':
                blockData.data.text = document.getElementById(`${id}-text`).value;
                break;
            case 'list':
                blockData.data.style = document.getElementById(`${id}-style`).value;
                const items = document.querySelectorAll(`#${id}-items input`);
                blockData.data.items = Array.from(items).map(input => input.value).filter(v => v.trim());
                break;
            case 'code':
                blockData.data.code = document.getElementById(`${id}-code`).value;
                break;
            case 'alert':
                blockData.data.type = document.getElementById(`${id}-alertType`).value;
                blockData.data.message = document.getElementById(`${id}-message`).value;
                break;
            case 'image':
                blockData.data.url = document.getElementById(`${id}-url`).value;
                blockData.data.caption = document.getElementById(`${id}-caption`).value;
                break;
            case 'callout':
                blockData.data.type = document.getElementById(`${id}-calloutType`).value;
                blockData.data.title = document.getElementById(`${id}-title`).value;
                blockData.data.message = document.getElementById(`${id}-message`).value;
                break;
            case 'table':
                const tableContainer = document.getElementById(`${id}-table-container`);
                const tableRows = tableContainer.querySelectorAll('div');
                const tableArray = [];
                tableRows.forEach(row => {
                    const cells = row.querySelectorAll('input');
                    const rowData = Array.from(cells).map(cell => cell.value);
                    tableArray.push(rowData);
                });
                blockData.data.data = tableArray;
                break;
            case 'separator':
                break;
            case 'osrs_header':
                blockData.data.header = document.getElementById(`${id}-header`).value;
                blockData.data.subheader = document.getElementById(`${id}-subheader`).value;
                blockData.data.color = document.getElementById(`${id}-color`).value;
                break;
            case 'patch_notes_section':
                try {
                    const childrenText = document.getElementById(`${id}-children`).value.trim();
                    blockData.data.children = childrenText ? JSON.parse(childrenText) : [];
                } catch (e) {
                    alert(`Invalid JSON in patch notes section ${id}`);
                    blockData.data.children = [];
                }
                break;
            case 'custom_section':
                blockData.data.title = document.getElementById(`${id}-title`).value;
                blockData.data.color = document.getElementById(`${id}-color`).value;
                try {
                    const childrenText = document.getElementById(`${id}-children`).value.trim();
                    blockData.data.children = childrenText ? JSON.parse(childrenText) : [];
                } catch (e) {
                    alert(`Invalid JSON in custom section ${id}`);
                    blockData.data.children = [];
                }
                break;
        }
        
        blocks.push(blockData);
    });
    
    const content = { blocks: blocks };
    document.getElementById('contentJson').value = JSON.stringify(content, null, 2);
});
</script>
@endsection
