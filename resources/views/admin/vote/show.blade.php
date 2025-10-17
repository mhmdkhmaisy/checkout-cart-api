@extends('admin.layout')

@section('title', 'Vote Site Details - Aragon RSPS Admin')
@section('page-title', $voteSite->title)
@section('page-description', 'Detailed view of vote site performance and configuration')

@section('content')
<div class="space-y-6">
    <!-- Site Info Card -->
    <div class="glass-effect rounded-xl p-6 border border-dragon-border">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-dragon-red rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-vote-yea text-white"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-dragon-silver">{{ $voteSite->title }}</h3>
                    <p class="text-dragon-silver-dark">Site ID: {{ $voteSite->site_id }}</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.vote.sites.edit', $voteSite) }}" 
                   class="px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Site
                </a>
                <form action="{{ route('admin.vote.sites.toggle', $voteSite) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" 
                            class="px-4 py-2 {{ $voteSite->active ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg transition-colors">
                        <i class="fas fa-{{ $voteSite->active ? 'pause' : 'play' }} mr-2"></i>
                        {{ $voteSite->active ? 'Disable' : 'Enable' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Site Configuration -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-medium text-dragon-red mb-3">Configuration</h4>
                <div class="space-y-3">
                    <div>
                        <span class="text-dragon-silver-dark text-sm">Status:</span>
                        @if($voteSite->active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-600 text-green-100 ml-2">
                                <i class="fas fa-check-circle mr-1"></i>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-600 text-gray-100 ml-2">
                                <i class="fas fa-pause-circle mr-1"></i>
                                Inactive
                            </span>
                        @endif
                    </div>
                    <div>
                        <span class="text-dragon-silver-dark text-sm">Vote URL:</span>
                        <p class="text-dragon-silver text-sm font-mono bg-dragon-black p-2 rounded mt-1 break-all">{{ $voteSite->url }}</p>
                    </div>
                    <div>
                        <span class="text-dragon-silver-dark text-sm">Created:</span>
                        <span class="text-dragon-silver text-sm ml-2">{{ $voteSite->created_at->format('M j, Y g:i A') }}</span>
                    </div>
                    <div>
                        <span class="text-dragon-silver-dark text-sm">Last Updated:</span>
                        <span class="text-dragon-silver text-sm ml-2">{{ $voteSite->updated_at->format('M j, Y g:i A') }}</span>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div>
                <h4 class="text-sm font-medium text-dragon-red mb-3">Statistics</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-dragon-black rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-dragon-silver">{{ number_format($stats['total_votes']) }}</div>
                        <div class="text-dragon-silver-dark text-sm">Total Votes</div>
                    </div>
                    <div class="bg-dragon-black rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-green-400">{{ number_format($stats['today_votes']) }}</div>
                        <div class="text-dragon-silver-dark text-sm">Today</div>
                    </div>
                    <div class="bg-dragon-black rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-blue-400">{{ number_format($stats['month_votes']) }}</div>
                        <div class="text-dragon-silver-dark text-sm">This Month</div>
                    </div>
                    <div class="bg-dragon-black rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-400">{{ number_format($stats['pending_votes']) }}</div>
                        <div class="text-dragon-silver-dark text-sm">Pending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Votes -->
    <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
        <div class="px-6 py-4 border-b border-dragon-border">
            <h3 class="text-xl font-bold text-dragon-red">Recent Votes (Last 50)</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-dragon-surface border-b border-dragon-border">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Started</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Completed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dragon-border">
                    @forelse($voteSite->votes as $vote)
                        <tr class="hover:bg-dragon-surface transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-dragon-red rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-white text-xs"></i>
                                    </div>
                                    <span class="text-sm font-medium text-dragon-silver">{{ $vote->username }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-dragon-silver">{{ $vote->started->format('M j, Y') }}</div>
                                <div class="text-sm text-dragon-silver-dark">{{ $vote->started->format('g:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($vote->callback_date)
                                    <div class="text-sm text-dragon-silver">{{ $vote->callback_date->format('M j, Y') }}</div>
                                    <div class="text-sm text-dragon-silver-dark">{{ $vote->callback_date->format('g:i A') }}</div>
                                @else
                                    <span class="text-dragon-silver-dark text-sm">Not completed</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-dragon-silver-dark font-mono">{{ $vote->ip_address }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($vote->callback_date)
                                    @if($vote->claimed)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-600 text-blue-100">
                                            <i class="fas fa-gift mr-1"></i>
                                            Claimed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-600 text-green-100">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Completed
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-600 text-yellow-100">
                                        <i class="fas fa-clock mr-1"></i>
                                        Pending
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-vote-yea text-dragon-border text-4xl mb-4"></i>
                                    <h3 class="text-lg font-medium text-dragon-silver-dark mb-2">No Votes Yet</h3>
                                    <p class="text-dragon-silver-dark">This site hasn't received any votes yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Back Button -->
    <div class="text-center">
        <a href="{{ route('admin.vote.index') }}" 
           class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Vote Sites
        </a>
    </div>
</div>
@endsection