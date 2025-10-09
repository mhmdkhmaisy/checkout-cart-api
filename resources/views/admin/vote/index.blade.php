@extends('admin.layout')

@section('title', 'Vote Sites Management - Aragon RSPS Admin')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="glass-effect rounded-xl p-6 border border-dragon-border">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-dragon-red rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-globe text-white"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-dragon-silver">{{ $stats['total_sites'] }}</div>
                    <div class="text-dragon-silver-dark">Total Sites</div>
                </div>
            </div>
        </div>

        <div class="glass-effect rounded-xl p-6 border border-dragon-border">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-white"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-dragon-silver">{{ $stats['active_sites'] }}</div>
                    <div class="text-dragon-silver-dark">Active Sites</div>
                </div>
            </div>
        </div>

        <div class="glass-effect rounded-xl p-6 border border-dragon-border">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-vote-yea text-white"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-dragon-silver">{{ number_format($stats['total_votes']) }}</div>
                    <div class="text-dragon-silver-dark">Total Votes</div>
                </div>
            </div>
        </div>

        <div class="glass-effect rounded-xl p-6 border border-dragon-border">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-600 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-clock text-white"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold text-dragon-silver">{{ $stats['today_votes'] ?? 0 }}</div>
                    <div class="text-dragon-silver-dark">Today's Votes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-between items-center">
        <h3 class="text-xl font-bold text-dragon-silver">Vote Sites Management</h3>
        <a href="{{ route('admin.vote.sites.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Add New Site
        </a>
    </div>

    <!-- Sites Table -->
    <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-dragon-surface border-b border-dragon-border">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Site</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Site ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Total Votes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Completed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dragon-border">
                    @forelse($sites as $site)
                        <tr class="hover:bg-dragon-surface transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-dragon-red rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-vote-yea text-white text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-dragon-silver">{{ $site->title }}</div>
                                        <div class="text-sm text-dragon-silver-dark">Created {{ $site->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-dragon-silver font-mono">{{ $site->site_id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-dragon-silver">{{ number_format($site->votes_count) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-green-400">{{ number_format($site->completed_votes_count) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($site->active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-600 text-green-100">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-600 text-gray-100">
                                        <i class="fas fa-pause-circle mr-1"></i>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.vote.sites.show', $site) }}" 
                                       class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-md transition-colors">
                                        <i class="fas fa-eye mr-1"></i>
                                        View
                                    </a>
                                    <a href="{{ route('admin.vote.sites.edit', $site) }}" 
                                       class="inline-flex items-center px-3 py-1 bg-dragon-red hover:bg-dragon-red-bright text-white text-xs rounded-md transition-colors">
                                        <i class="fas fa-edit mr-1"></i>
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.vote.sites.toggle', $site) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1 {{ $site->active ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700' }} text-white text-xs rounded-md transition-colors">
                                            <i class="fas fa-{{ $site->active ? 'pause' : 'play' }} mr-1"></i>
                                            {{ $site->active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-vote-yea text-dragon-border text-4xl mb-4"></i>
                                    <h3 class="text-lg font-medium text-dragon-silver mb-2">No Vote Sites</h3>
                                    <p class="text-dragon-silver-dark mb-4">Get started by adding your first vote site.</p>
                                    <a href="{{ route('admin.vote.sites.create') }}" 
                                       class="inline-flex items-center px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg transition-colors">
                                        <i class="fas fa-plus mr-2"></i>
                                        Add Vote Site
                                    </a>
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