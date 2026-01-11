@extends('admin.layout')

@section('title', 'Store Alerts - Admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">Store Alerts</h2>
        <button onclick="openCreateModal()" class="px-4 py-2 bg-dragon-red text-dragon-silver rounded-lg hover:bg-dragon-red-bright transition-colors">
            Create New Alert
        </button>
    </div>

    <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-dragon-surface border-b border-dragon-border text-dragon-silver-dark uppercase text-xs">
                    <th class="px-6 py-4">Reorder</th>
                    <th class="px-6 py-4">Type</th>
                    <th class="px-6 py-4">Text</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="alerts-table-body">
                @forelse($alerts as $alert)
                    <tr class="border-b border-dragon-border bg-dragon-surface/50 hover:bg-dragon-border/20 transition-colors" data-id="{{ $alert->id }}">
                        <td class="px-6 py-4 cursor-move handle">
                            <svg class="w-5 h-5 text-dragon-silver-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                            </svg>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs uppercase
                                @if($alert->type === 'info') bg-blue-900 text-blue-100
                                @elseif($alert->type === 'success') bg-green-900 text-green-100
                                @elseif($alert->type === 'warning') bg-yellow-900 text-yellow-100
                                @elseif($alert->type === 'danger') bg-red-900 text-red-100
                                @endif">
                                {{ $alert->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-dragon-silver">
                            {{ $alert->text }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="{{ $alert->is_active ? 'text-green-400' : 'text-red-400' }}">
                                {{ $alert->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <button onclick="openEditModal({{ json_encode($alert) }})" class="text-blue-400 hover:text-blue-300">Edit</button>
                            <form action="{{ route('admin.store-alerts.destroy', $alert) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300" onclick="return confirm('Delete this alert?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-dragon-silver-dark">No alerts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="alertModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50">
    <div class="glass-effect rounded-xl border border-dragon-border p-6 w-full max-w-md">
        <h3 id="modalTitle" class="text-xl font-bold text-dragon-red mb-4">Create Alert</h3>
        <form id="alertForm" method="POST">
            @csrf
            <div id="methodField"></div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-dragon-silver-dark mb-1">Type</label>
                    <select name="type" id="alertType" class="w-full bg-dragon-surface border border-dragon-border rounded-lg px-4 py-2 text-dragon-silver focus:ring-1 focus:ring-dragon-red focus:border-dragon-red outline-none">
                        <option value="info">Info (Blue)</option>
                        <option value="success">Success (Green)</option>
                        <option value="warning">Warning (Yellow)</option>
                        <option value="danger">Danger (Red)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-dragon-silver-dark mb-1">Text</label>
                    <textarea name="text" id="alertText" rows="3" class="w-full bg-dragon-surface border border-dragon-border rounded-lg px-4 py-2 text-dragon-silver focus:ring-1 focus:ring-dragon-red focus:border-dragon-red outline-none" required></textarea>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="alertActive" value="1" checked class="rounded border-dragon-border bg-dragon-surface text-dragon-red focus:ring-dragon-red">
                    <label class="ml-2 text-sm text-dragon-silver">Active</label>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-dragon-silver hover:text-white transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-dragon-red text-dragon-silver rounded-lg hover:bg-dragon-red-bright transition-colors">Save Alert</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    const modal = document.getElementById('alertModal');
    const form = document.getElementById('alertForm');
    const title = document.getElementById('modalTitle');
    const methodField = document.getElementById('methodField');

    function openCreateModal() {
        title.innerText = 'Create Alert';
        form.action = "{{ route('admin.store-alerts.store') }}";
        methodField.innerHTML = '';
        document.getElementById('alertType').value = 'info';
        document.getElementById('alertText').value = '';
        document.getElementById('alertActive').checked = true;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function openEditModal(alert) {
        title.innerText = 'Edit Alert';
        form.action = `/admin/store-alerts/${alert.id}`;
        methodField.innerHTML = '@method("PUT")';
        document.getElementById('alertType').value = alert.type;
        document.getElementById('alertText').value = alert.text;
        document.getElementById('alertActive').checked = alert.is_active;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    new Sortable(document.getElementById('alerts-table-body'), {
        handle: '.handle',
        animation: 150,
        onEnd: function() {
            const orders = Array.from(document.querySelectorAll('#alerts-table-body tr')).map((tr, index) => ({
                id: tr.dataset.id,
                sort_order: index + 1
            }));

            fetch("{{ route('admin.store-alerts.update-order') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ orders })
            });
        }
    });
</script>
@endsection
