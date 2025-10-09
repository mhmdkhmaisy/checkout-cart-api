@extends('admin.layout')

@section('title', 'Client Management')
@section('header', 'Client Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-dragon-silver">Game Client Management</h2>
            <p class="text-dragon-silver-dark text-sm">Manage downloadable game clients for different platforms</p>
        </div>
        <a href="{{ route('admin.clients.create') }}" class="px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i>Upload New Client
        </a>
    </div>

    <!-- Latest Clients Overview -->
    <div class="glass-effect rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-dragon-silver">Current Active Clients</h3>
            <a href="{{ route('admin.clients.manifest') }}" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm transition-colors" target="_blank">
                <i class="fas fa-code mr-1"></i>View Manifest
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach(App\Models\Client::OS_TYPES as $os => $displayName)
                @php
                    $client = $latestClients->get($os);
                @endphp
                <div class="bg-dragon-black rounded-lg p-4 border {{ $client && $client->enabled ? 'border-green-500' : 'border-dragon-border' }}">
                    <div class="text-center">
                        <i class="fas fa-{{ $os === 'windows' ? 'windows' : ($os === 'macos' ? 'apple' : ($os === 'linux' ? 'linux' : 'coffee')) }} text-3xl mb-3 {{ $client && $client->enabled ? 'text-green-500' : 'text-dragon-silver-dark' }}"></i>
                        <h4 class="font-medium text-dragon-silver mb-2">{{ $displayName }}</h4>
                        @if($client)
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-dragon-silver">v{{ $client->version }}</p>
                                <p class="text-xs text-dragon-silver-dark">{{ $client->formatted_size }}</p>
                                <span class="inline-block px-2 py-1 text-xs rounded {{ $client->enabled ? 'bg-green-600 text-green-100' : 'bg-gray-600 text-gray-100' }}">
                                    {{ $client->enabled ? 'Active' : 'Disabled' }}
                                </span>
                            </div>
                            @if($client->enabled)
                                <a href="{{ $client->download_url }}" class="mt-3 inline-block px-3 py-1 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded text-sm transition-colors">
                                    <i class="fas fa-download mr-1"></i>Download
                                </a>
                            @endif
                        @else
                            <p class="text-dragon-silver-dark text-sm">No client uploaded</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- All Clients Table -->
    <div class="glass-effect rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-dragon-border">
            <h3 class="text-lg font-medium text-dragon-silver">All Client Versions</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dragon-black">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Platform</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Version</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">File Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Uploaded</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-silver-dark uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dragon-border">
                    @forelse($clients as $client)
                        <tr class="hover:bg-dragon-black/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-{{ $client->os === 'windows' ? 'windows' : ($client->os === 'macos' ? 'apple' : ($client->os === 'linux' ? 'linux' : 'coffee')) }} mr-2 text-dragon-silver-dark"></i>
                                    <span class="text-dragon-silver">{{ $client->os_display }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="font-medium text-dragon-silver">{{ $client->version }}</span>
                                    @if($client->enabled)
                                        <span class="ml-2 px-2 py-1 text-xs bg-green-600 text-green-100 rounded">Current</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-dragon-silver-dark">{{ $client->formatted_size }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer toggle-client" 
                                           data-client-id="{{ $client->id }}"
                                           {{ $client->enabled ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-dragon-red"></div>
                                </label>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-dragon-silver text-sm">{{ $client->created_at->format('M j, Y') }}</div>
                                <div class="text-dragon-silver-dark text-xs">{{ $client->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.clients.show', $client) }}" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm transition-colors">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.clients.edit', $client) }}" class="px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white rounded text-sm transition-colors">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($client->enabled)
                                        <a href="{{ $client->download_url }}" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-sm transition-colors">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-sm transition-colors" 
                                                onclick="return confirm('Are you sure you want to delete this client?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-dragon-silver-dark">
                                No clients uploaded yet. <a href="{{ route('admin.clients.create') }}" class="text-dragon-red hover:text-dragon-red-bright">Upload your first client</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-dragon-border">
            {{ $clients->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.toggle-client').change(function() {
        const clientId = $(this).data('client-id');
        const isChecked = $(this).is(':checked');
        
        $.ajax({
            url: `/admin/clients/${clientId}/toggle`,
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    const alertClass = isChecked ? 'bg-green-600' : 'bg-yellow-600';
                    const alertHtml = `
                        <div class="${alertClass} text-white p-4 rounded-lg mb-6">
                            <i class="fas fa-check-circle mr-2"></i>
                            ${response.message}
                        </div>
                    `;
                    $('.space-y-6').prepend(alertHtml);
                    
                    // Auto-hide alert after 3 seconds
                    setTimeout(function() {
                        $('.space-y-6 > div:first-child').fadeOut();
                    }, 3000);
                    
                    // Reload page to update the current clients section
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function() {
                // Revert the toggle
                $(this).prop('checked', !isChecked);
                alert('Error updating client status');
            }
        });
    });
});
</script>
@endpush
@endsection