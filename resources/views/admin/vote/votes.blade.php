@extends('admin.layout')

@section('title', 'Vote History - Aragon RSPS Admin')
@section('page-title', 'Vote History')
@section('page-description', 'Complete voting history and management')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="glass-effect rounded-xl p-6 border border-dragon-border">
        <h3 class="text-lg font-semibold text-dragon-red mb-4">Filter Votes</h3>
        <form method="GET" action="{{ route('admin.vote.votes') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-dragon-silver-dark mb-2">Username</label>
                <input type="text" name="username" value="{{ request('username') }}" 
                       class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-md text-dragon-silver focus:ring-2 focus:ring-dragon-red focus:border-transparent"
                       placeholder="Enter username">
            </div>
            <div>
                <label class="block text-sm font-medium text-dragon-silver-dark mb-2">Vote Site</label>
                <select name="site_id" class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-md text-dragon-silver focus:ring-2 focus:ring-dragon-red focus:border-transparent">
                    <option value="">All Sites</option>
                    @foreach(\App\Models\VoteSite::all() as $site)
                        <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                            {{ $site->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-dragon-silver-dark mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-md text-dragon-silver focus:ring-2 focus:ring-dragon-red focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="claimed" {{ request('status') === 'claimed' ? 'selected' : '' }}>Claimed</option>
                    <option value="unclaimed" {{ request('status') === 'unclaimed' ? 'selected' : '' }}>Unclaimed</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-md transition-colors">
                    <i class="fas fa-search mr-2"></i>
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Votes Table -->
    <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
        <div class="px-6 py-4 border-b border-dragon-border">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-dragon-red">All Votes</h3>
                <div class="text-sm text-dragon-silver-dark">
                    Showing {{ $votes->firstItem() ?? 0 }} to {{ $votes->lastItem() ?? 0 }} of {{ $votes->total() }} votes
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-dragon-surface border-b border-dragon-border">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Vote</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Site</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Started</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Completed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dragon-red uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dragon-border">
                    @forelse($votes as $vote)
                        <tr class="hover:bg-dragon-surface transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-dragon-red rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-dragon-silver text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-dragon-silver">{{ $vote->username }}</div>
                                        <div class="text-sm text-dragon-silver-dark">ID: {{ $vote->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-dragon-silver">{{ $vote->site->title }}</div>
                                <div class="text-sm text-dragon-silver-dark">{{ $vote->site->site_id }}</div>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    @if($vote->callback_date && !$vote->claimed)
                                        <form action="{{ route('admin.vote.votes.claim', $vote) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded-md transition-colors">
                                                <i class="fas fa-gift mr-1"></i>
                                                Mark Claimed
                                            </button>
                                        </form>
                                    @endif
                                    <button onclick="showVoteDetails({{ $vote->id }}, '{{ $vote->uid }}')" 
                                            class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-md transition-colors">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Details
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-vote-yea text-dragon-border text-4xl mb-4"></i>
                                    <h3 class="text-lg font-medium text-dragon-silver-dark mb-2">No votes found</h3>
                                    <p class="text-dragon-silver-dark">Try adjusting your search filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($votes->hasPages())
            <div class="px-6 py-4 border-t border-dragon-border">
                {{ $votes->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="glass-effect rounded-xl p-6 border border-dragon-border text-center">
            <div class="text-2xl font-bold text-dragon-red">{{ \App\Models\Vote::count() }}</div>
            <div class="text-sm text-dragon-silver-dark mt-1">Total Votes</div>
        </div>
        <div class="glass-effect rounded-xl p-6 border border-dragon-border text-center">
            <div class="text-2xl font-bold text-green-400">{{ \App\Models\Vote::completed()->count() }}</div>
            <div class="text-sm text-dragon-silver-dark mt-1">Completed</div>
        </div>
        <div class="glass-effect rounded-xl p-6 border border-dragon-border text-center">
            <div class="text-2xl font-bold text-yellow-400">{{ \App\Models\Vote::whereNull('callback_date')->count() }}</div>
            <div class="text-sm text-dragon-silver-dark mt-1">Pending</div>
        </div>
        <div class="glass-effect rounded-xl p-6 border border-dragon-border text-center">
            <div class="text-2xl font-bold text-blue-400">{{ \App\Models\Vote::where('claimed', true)->count() }}</div>
            <div class="text-sm text-dragon-silver-dark mt-1">Claimed</div>
        </div>
    </div>
</div>

<!-- Vote Details Modal -->
<div id="voteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="glass-effect rounded-xl p-6 border border-dragon-border max-w-md w-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-dragon-red">Vote Details</h3>
                <button onclick="hideVoteDetails()" class="text-dragon-silver-dark hover:text-dragon-silver">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="voteDetails" class="space-y-3">
                <!-- Details will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
function showVoteDetails(voteId, uid) {
    document.getElementById('voteDetails').innerHTML = `
        <div class="text-sm">
            <div class="flex justify-between py-2 border-b border-dragon-border">
                <span class="text-dragon-silver-dark">Vote ID:</span>
                <span class="text-dragon-silver">${voteId}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-dragon-border">
                <span class="text-dragon-silver-dark">Unique ID:</span>
                <span class="text-dragon-silver font-mono text-xs">${uid}</span>
            </div>
            <div class="mt-4">
                <button onclick="copyToClipboard('${uid}')" 
                        class="w-full px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg transition-colors">
                    <i class="fas fa-copy mr-2"></i>
                    Copy UID
                </button>
            </div>
        </div>
    `;
    document.getElementById('voteModal').classList.remove('hidden');
}

function hideVoteDetails() {
    document.getElementById('voteModal').classList.add('hidden');
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success feedback
        alert('UID copied to clipboard!');
    });
}

// Close modal when clicking outside
document.getElementById('voteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideVoteDetails();
    }
});
</script>
@endsection