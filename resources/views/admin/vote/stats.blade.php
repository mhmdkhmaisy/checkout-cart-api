@extends('admin.layout')

@section('title', 'Vote Statistics - Aragon RSPS Admin')
@section('page-title', 'Vote Statistics')
@section('page-description', 'Comprehensive voting analytics and performance metrics')

@section('content')
<div class="space-y-6">
    <!-- Overall Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="glass-effect rounded-xl p-6 border border-dragon-border text-center">
            <div class="text-3xl font-bold text-dragon-red mb-2">{{ number_format($sites->sum('total_votes_count')) }}</div>
            <div class="text-dragon-silver-dark">Total Votes</div>
        </div>
        <div class="glass-effect rounded-xl p-6 border border-dragon-border text-center">
            <div class="text-3xl font-bold text-green-400 mb-2">{{ number_format($sites->sum('today_votes_count')) }}</div>
            <div class="text-dragon-silver-dark">Today's Votes</div>
        </div>
        <div class="glass-effect rounded-xl p-6 border border-dragon-border text-center">
            <div class="text-3xl font-bold text-blue-400 mb-2">{{ number_format($sites->sum('week_votes_count')) }}</div>
            <div class="text-dragon-silver-dark">This Week</div>
        </div>
        <div class="glass-effect rounded-xl p-6 border border-dragon-border text-center">
            <div class="text-3xl font-bold text-purple-400 mb-2">{{ $sites->where('active', true)->count() }}</div>
            <div class="text-dragon-silver-dark">Active Sites</div>
        </div>
    </div>

    <!-- Site Performance Chart -->
    <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
        <div class="px-6 py-4 border-b border-dragon-border">
            <h3 class="text-xl font-bold text-dragon-red">Site Performance Overview</h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Sites Performance Table -->
                <div>
                    <h4 class="text-lg font-semibold text-dragon-silver mb-4">Vote Sites Performance</h4>
                    <div class="space-y-3">
                        @foreach($sites as $site)
                            @php
                                $totalVotes = $site->total_votes_count;
                                $completedVotes = $site->completed_votes_count;
                                $successRate = $totalVotes > 0 ? round(($completedVotes / $totalVotes) * 100, 1) : 0;
                            @endphp
                            <div class="bg-dragon-black rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-dragon-red rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-vote-yea text-white text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-dragon-silver">{{ $site->title }}</div>
                                            <div class="text-xs text-dragon-silver-dark">{{ $site->site_id }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold text-dragon-silver">{{ number_format($completedVotes) }}</div>
                                        <div class="text-xs text-dragon-silver-dark">{{ $successRate }}% success</div>
                                    </div>
                                </div>
                                <!-- Progress Bar -->
                                <div class="w-full bg-dragon-surface rounded-full h-2">
                                    <div class="bg-dragon-red h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ min($successRate, 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top Voters -->
                <div>
                    <h4 class="text-lg font-semibold text-dragon-silver mb-4">Top Voters This Month</h4>
                    <div class="space-y-3">
                        @foreach($topVoters as $index => $voter)
                            <div class="bg-dragon-black rounded-lg p-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3
                                        @if($index === 0) bg-yellow-500
                                        @elseif($index === 1) bg-gray-400
                                        @elseif($index === 2) bg-yellow-600
                                        @else bg-dragon-red
                                        @endif">
                                        @if($index < 3)
                                            <i class="fas fa-crown text-white text-xs"></i>
                                        @else
                                            <span class="text-white text-xs font-bold">{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-dragon-silver">{{ $voter->username }}</div>
                                        <div class="text-xs text-dragon-silver-dark">Rank #{{ $index + 1 }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-dragon-red">{{ $voter->vote_count }}</div>
                                    <div class="text-xs text-dragon-silver-dark">votes</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Vote Trends -->
    <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
        <div class="px-6 py-4 border-b border-dragon-border">
            <h3 class="text-xl font-bold text-dragon-red">Daily Vote Trends (Last 30 Days)</h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-7 gap-2">
                @foreach($dailyStats as $day)
                    @php
                        $maxVotes = $dailyStats->max('count');
                        $height = $maxVotes > 0 ? ($day->count / $maxVotes) * 100 : 0;
                    @endphp
                    <div class="text-center">
                        <div class="bg-dragon-black rounded-lg p-2 mb-2 h-32 flex items-end">
                            <div class="w-full bg-dragon-red rounded transition-all duration-300" 
                                 style="height: {{ $height }}%"
                                 title="{{ $day->count }} votes on {{ \Carbon\Carbon::parse($day->date)->format('M j') }}">
                            </div>
                        </div>
                        <div class="text-xs text-dragon-silver-dark">
                            {{ \Carbon\Carbon::parse($day->date)->format('M j') }}
                        </div>
                        <div class="text-xs text-dragon-silver font-medium">{{ $day->count }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Detailed Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Vote Completion Rates -->
        <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
            <div class="px-6 py-4 border-b border-dragon-border">
                <h3 class="text-lg font-bold text-dragon-red">Completion Rates by Site</h3>
            </div>
            <div class="p-6">
                @foreach($sites as $site)
                    @php
                        $totalVotes = $site->total_votes_count;
                        $completedVotes = $site->completed_votes_count;
                        $completionRate = $totalVotes > 0 ? ($completedVotes / $totalVotes) * 100 : 0;
                    @endphp
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-dragon-silver">{{ $site->title }}</span>
                            <span class="text-sm text-dragon-silver-dark">{{ number_format($completionRate, 1) }}%</span>
                        </div>
                        <div class="w-full bg-dragon-black rounded-full h-2">
                            <div class="h-2 rounded-full transition-all duration-300
                                @if($completionRate >= 80) bg-green-500
                                @elseif($completionRate >= 60) bg-yellow-500
                                @elseif($completionRate >= 40) bg-orange-500
                                @else bg-red-500
                                @endif" 
                                 style="width: {{ $completionRate }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-dragon-silver-dark mt-1">
                            <span>{{ number_format($completedVotes) }} completed</span>
                            <span>{{ number_format($totalVotes) }} total</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
            <div class="px-6 py-4 border-b border-dragon-border">
                <h3 class="text-lg font-bold text-dragon-red">Recent Vote Activity</h3>
            </div>
            <div class="p-6">
                @php
                    $recentVotes = \App\Models\Vote::with('site')
                        ->completed()
                        ->latest('callback_date')
                        ->take(10)
                        ->get();
                @endphp
                <div class="space-y-3">
                    @foreach($recentVotes as $vote)
                        <div class="flex items-center justify-between p-3 bg-dragon-black rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-dragon-red rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-white text-xs"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-dragon-silver">{{ $vote->username }}</div>
                                    <div class="text-xs text-dragon-silver-dark">{{ $vote->site->title }}</div>
                                </div>
                            </div>
                            <div class="text-xs text-dragon-silver-dark">
                                {{ $vote->callback_date->diffForHumans() }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection