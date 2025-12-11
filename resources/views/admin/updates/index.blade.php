@extends('admin.layout')

@section('title', 'Updates Management - Aragon RSPS Admin')

@section('header', 'Updates Management')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-dragon-silver">All Updates</h2>
    <div class="flex gap-3">
        <button onclick="openNotifyModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
            <i class="fab fa-discord mr-2"></i> Notify Client Update
        </button>
        <a href="{{ route('admin.updates.create') }}" class="px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i> Create Update
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 p-4 bg-green-600 text-green-100 rounded-lg flex items-center justify-between">
        <div>
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 p-4 bg-red-600 text-red-100 rounded-lg flex items-center justify-between">
        <div>
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    </div>
@endif

<div class="bg-dragon-surface border border-dragon-border rounded-lg shadow-lg overflow-hidden">
    @if($updates->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dragon-black border-b border-dragon-border">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Published</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dragon-border">
                    @foreach($updates as $update)
                        <tr class="hover:bg-dragon-black transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-dragon-silver">{{ $update->id }}</td>
                            <td class="px-6 py-4 text-sm text-dragon-silver">
                                {{ $update->title }}
                                @if($update->attached_to_update_id)
                                    <br><span class="text-xs text-dragon-silver-dark">
                                        <i class="fas fa-link"></i> Attached to: {{ $update->attachedToUpdate->title ?? 'Unknown' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex flex-wrap gap-1">
                                    @if($update->is_pinned)
                                        <span class="px-2 py-1 bg-red-600 text-white rounded text-xs font-semibold">
                                            <i class="fas fa-thumbtack"></i> Pinned
                                        </span>
                                    @endif
                                    @if($update->is_featured)
                                        <span class="px-2 py-1 bg-yellow-600 text-white rounded text-xs font-semibold">
                                            <i class="fas fa-star"></i> Featured
                                        </span>
                                    @endif
                                    @if(!$update->is_published)
                                        <span class="px-2 py-1 bg-gray-600 text-white rounded text-xs font-semibold">
                                            <i class="fas fa-file"></i> Draft
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($update->attached_to_update_id)
                                    <span class="px-2 py-1 bg-purple-600 text-white rounded text-xs font-semibold">Hotfix</span>
                                @elseif($update->client_update)
                                    <span class="px-2 py-1 bg-blue-600 text-white rounded text-xs font-semibold">Client Update</span>
                                @else
                                    <span class="px-2 py-1 bg-green-600 text-white rounded text-xs font-semibold">Regular</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-dragon-silver-dark">
                                {{ $update->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('updates.show', $update->slug) }}" 
                                       target="_blank"
                                       class="px-3 py-1 bg-dragon-black border border-dragon-border text-dragon-silver hover:bg-dragon-red hover:text-white rounded transition-colors"
                                       title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.updates.edit', $update) }}" 
                                       class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.updates.send-to-discord', $update) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Send this update to Discord as a screenshot?');">
                                        @csrf
                                        <button type="submit" 
                                                class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded transition-colors"
                                                title="Send to Discord">
                                            <i class="fab fa-discord"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.updates.destroy', $update) }}" 
                                          method="POST" 
                                          class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this update?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded transition-colors"
                                                title="Delete">
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

        @if($updates->hasPages())
            <div class="px-6 py-4 border-t border-dragon-border">
                {{ $updates->links() }}
            </div>
        @endif
    @else
        <div class="p-12 text-center">
            <i class="fas fa-newspaper text-6xl text-dragon-border mb-4"></i>
            <p class="text-dragon-silver-dark text-lg">No updates created yet.</p>
            <a href="{{ route('admin.updates.create') }}" class="mt-4 inline-block px-6 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i> Create Your First Update
            </a>
        </div>
    @endif
</div>

<div id="notifyModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-dragon-surface border border-dragon-border rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-dragon-border">
            <h3 class="text-xl font-bold text-dragon-silver">
                <i class="fab fa-discord text-indigo-400 mr-2"></i>
                Notify Client Update
            </h3>
        </div>
        <form id="notifyForm" method="POST" action="">
            @csrf
            <div class="px-6 py-4">
                <p class="text-dragon-silver-dark mb-4">
                    Send a Discord notification to remind players to update their client or refresh their launcher.
                </p>
                <div class="mb-4">
                    <label for="role_id" class="block text-sm font-medium text-dragon-silver mb-2">
                        Discord Role ID (Optional)
                    </label>
                    <input type="text" 
                           name="role_id" 
                           id="role_id" 
                           class="w-full px-4 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:border-dragon-red focus:outline-none"
                           placeholder="e.g., 123456789012345678">
                    <p class="text-xs text-dragon-silver-dark mt-1">
                        Enter a role ID to mention that role. Leave empty for no ping.
                    </p>
                </div>
                <div class="p-3 bg-dragon-black rounded-lg border border-dragon-border">
                    <p class="text-sm text-dragon-silver-dark mb-2">Preview:</p>
                    <p class="text-sm text-dragon-silver">
                        <strong>Client Update Available!</strong><br>
                        Please make sure to download the new client or refresh your launcher to be able to see the latest content added to Aragon.
                        <span id="rolePreview" class="text-indigo-400"></span>
                    </p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-dragon-border flex justify-end gap-3">
                <button type="button" onclick="closeNotifyModal()" class="px-4 py-2 bg-dragon-black border border-dragon-border text-dragon-silver hover:bg-dragon-border rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                    <i class="fab fa-discord mr-2"></i> Send Notification
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openNotifyModal() {
    @if($updates->count() > 0)
        const latestUpdate = @json($updates->first());
        document.getElementById('notifyForm').action = '{{ url("admin/updates") }}/' + latestUpdate.id + '/notify-client-update';
    @else
        alert('No updates available to notify about.');
        return;
    @endif
    document.getElementById('notifyModal').classList.remove('hidden');
}

function closeNotifyModal() {
    document.getElementById('notifyModal').classList.add('hidden');
    document.getElementById('role_id').value = '';
    document.getElementById('rolePreview').textContent = '';
}

document.getElementById('role_id').addEventListener('input', function() {
    const roleId = this.value.trim();
    const preview = document.getElementById('rolePreview');
    if (roleId) {
        preview.textContent = ' @Role';
    } else {
        preview.textContent = '';
    }
});

document.getElementById('notifyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeNotifyModal();
    }
});
</script>
@endpush
