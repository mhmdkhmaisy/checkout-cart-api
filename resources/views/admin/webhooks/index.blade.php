@extends('admin.layout')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">
                Webhook Management
            </h2>
            <p class="text-dragon-silver-dark text-sm mt-1">Manage Discord webhooks for notifications</p>
        </div>
        <a href="{{ route('admin.webhooks.create') }}" 
           class="px-6 py-3 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i>Create Webhook
        </a>
    </div>

    <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-dragon-black border-b border-dragon-border">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Event Type</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">URL</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dragon-border">
                    @forelse($webhooks as $webhook)
                    <tr class="hover:bg-dragon-surface transition-colors">
                        <td class="px-6 py-4 text-dragon-silver">{{ $webhook->id }}</td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-dragon-silver">{{ $webhook->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 text-xs rounded 
                                {{ $webhook->event_type == 'promotion.created' ? 'bg-green-600 text-green-100' : '' }}
                                {{ $webhook->event_type == 'promotion.claimed' ? 'bg-blue-600 text-blue-100' : '' }}
                                {{ $webhook->event_type == 'update.published' ? 'bg-purple-600 text-purple-100' : '' }}">
                                {{ str_replace('.', ' - ', ucwords($webhook->event_type, '.')) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-dragon-silver-dark text-sm">
                            <span class="truncate max-w-xs block" title="{{ $webhook->url }}">
                                {{ substr($webhook->url, 0, 50) }}{{ strlen($webhook->url) > 50 ? '...' : '' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.webhooks.toggle-active', $webhook) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="px-3 py-1 text-xs rounded transition-colors {{ $webhook->is_active ? 'bg-green-600 hover:bg-green-700 text-green-100' : 'bg-gray-600 hover:bg-gray-700 text-gray-100' }}">
                                    {{ $webhook->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-dragon-silver-dark text-sm">
                            {{ $webhook->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.webhooks.edit', $webhook) }}" 
                                   class="px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white rounded text-sm transition-colors">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.webhooks.destroy', $webhook) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this webhook?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-sm transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-dragon-silver-dark">
                                No webhooks found. <a href="{{ route('admin.webhooks.create') }}" class="text-dragon-red hover:text-dragon-red-bright transition-colors">Create your first webhook</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
