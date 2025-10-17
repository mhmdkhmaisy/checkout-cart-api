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
                </div>

                <!-- Hidden textarea for JSON content -->
                <textarea name="content" id="contentJson" class="hidden"></textarea>
                @error('content')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" 
                           class="w-5 h-5 text-dragon-red bg-dragon-black border-dragon-border rounded focus:ring-dragon-red" 
                           id="client_update" 
                           name="client_update" 
                           value="1" 
                           {{ old('client_update') ? 'checked' : '' }}>
                    <span class="ml-2 text-dragon-silver">This update requires a client update</span>
                </label>
                <p class="text-sm text-dragon-silver-dark mt-1 ml-7">Check this if users need to download a new client version</p>
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

// Initialize with one header block
document.addEventListener('DOMContentLoaded', function() {
    addBlock('header', {text: 'Update Title', level: 2});
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
    
    switch(type) {
        case 'header':
            content += `
                <select id="${id}-level" class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                    <option value="2" ${data?.level === 2 ? 'selected' : ''}>H2</option>
                    <option value="3" ${data?.level === 3 ? 'selected' : ''}>H3</option>
                    <option value="4" ${data?.level === 4 ? 'selected' : ''}>H4</option>
                </select>
                <input type="text" id="${id}-text" placeholder="Header text" value="${data?.text || ''}" 
                       class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">
            `;
            break;
        case 'paragraph':
            content += `
                <textarea id="${id}-text" placeholder="Paragraph text" rows="3" 
                          class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">${data?.text || ''}</textarea>
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
            break;
        case 'code':
            content += `
                <textarea id="${id}-code" placeholder="Code snippet" rows="4" 
                          class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 font-mono text-sm">${data?.code || ''}</textarea>
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
                          class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">${data?.message || ''}</textarea>
            `;
            break;
        case 'image':
            content += `
                <input type="text" id="${id}-url" placeholder="Image URL" value="${data?.url || ''}" 
                       class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2 mb-2">
                <input type="text" id="${id}-caption" placeholder="Caption (optional)" value="${data?.caption || ''}" 
                       class="w-full bg-dragon-surface border border-dragon-border text-dragon-silver rounded px-3 py-2">
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
        }
        
        blocks.push(blockData);
    });
    
    const content = { blocks: blocks };
    document.getElementById('contentJson').value = JSON.stringify(content, null, 2);
});
</script>
@endsection
