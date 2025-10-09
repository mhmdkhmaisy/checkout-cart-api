@extends('layouts.public')

@section('title', 'Vote Statistics - Aragon RSPS')

@section('content')
<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stats-card {
    background: rgba(26, 26, 26, 0.8);
    backdrop-filter: blur(20px);
    border: 1px solid #333333;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
}

.stats-card:hover {
    border-color: #d40000;
    transform: translateY(-5px);
}

.site-performance {
    background: rgba(26, 26, 26, 0.8);
    backdrop-filter: blur(20px);
    border: 1px solid #333333;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.site-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background: rgba(10, 10, 10, 0.8);
    border-radius: 8px;
    border: 1px solid #333333;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.site-item:hover {
    border-color: #d40000;
}

.site-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.site-stats {
    display: flex;
    gap: 2rem;
    align-items: center;
}

.stat-item {
    text-align: center;
}

.two-column {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.voter-item, .vote-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    background: rgba(10, 10, 10, 0.8);
    border-radius: 8px;
    border: 1px solid #333333;
    margin-bottom: 0.75rem;
    transition: all 0.3s ease;
}

.voter-item:hover, .vote-item:hover {
    border-color: #d40000;
}

.voter-info, .vote-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .two-column {
        grid-template-columns: 1fr;
    }
    
    .site-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .site-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        width: 100%;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
    <!-- Header -->
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; font-weight: bold; color: #d40000; text-shadow: 0 0 10px rgba(212, 0, 0, 0.5); margin-bottom: 1rem;">
            <i class="fas fa-chart-bar" style="margin-right: 0.75rem;"></i>Vote Statistics
        </h1>
        <p style="font-size: 1.25rem; color: #c0c0c0;">
            Track voting performance and community engagement
        </p>
    </div>

    <!-- Overall Stats -->
    <div class="stats-grid">
        <div class="stats-card">
            <div style="font-size: 2.5rem; font-weight: bold; color: #d40000; margin-bottom: 0.5rem;">
                {{ number_format($stats['total_votes']) }}
            </div>
            <div style="color: #c0c0c0;">
                <i class="fas fa-vote-yea" style="margin-right: 0.5rem;"></i>Total Votes
            </div>
        </div>
        <div class="stats-card">
            <div style="font-size: 2.5rem; font-weight: bold; color: #22c55e; margin-bottom: 0.5rem;">
                {{ number_format($stats['today_votes']) }}
            </div>
            <div style="color: #c0c0c0;">
                <i class="fas fa-calendar-day" style="margin-right: 0.5rem;"></i>Today's Votes
            </div>
        </div>
        <div class="stats-card">
            <div style="font-size: 2.5rem; font-weight: bold; color: #3b82f6; margin-bottom: 0.5rem;">
                {{ number_format($stats['week_votes']) }}
            </div>
            <div style="color: #c0c0c0;">
                <i class="fas fa-calendar-week" style="margin-right: 0.5rem;"></i>This Week
            </div>
        </div>
        <div class="stats-card">
            <div style="font-size: 2.5rem; font-weight: bold; color: #8b5cf6; margin-bottom: 0.5rem;">
                {{ number_format($stats['active_sites']) }}
            </div>
            <div style="color: #c0c0c0;">
                <i class="fas fa-globe" style="margin-right: 0.5rem;"></i>Active Sites
            </div>
        </div>
    </div>

    <!-- Site Performance -->
    <div class="site-performance">
        <h3 style="font-size: 1.5rem; font-weight: bold; color: #d40000; margin-bottom: 1.5rem;">
            <i class="fas fa-trophy" style="margin-right: 0.75rem;"></i>Site Performance
        </h3>
        <div>
            @foreach($siteStats as $site)
                @php
                    $successRate = $site->total_votes > 0 ? round(($site->completed_votes / $site->total_votes) * 100, 1) : 0;
                @endphp
                <div class="site-item">
                    <div class="site-info">
                        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #d40000, #ff0000); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-vote-yea" style="color: white;"></i>
                        </div>
                        <div>
                            <h4 style="font-weight: 600; color: #e8e8e8; margin: 0;">{{ $site->title }}</h4>
                            <p style="font-size: 0.875rem; color: #c0c0c0; margin: 0;">
                                <i class="fas fa-circle" style="font-size: 0.5rem; margin-right: 0.25rem; color: {{ $site->active ? '#22c55e' : '#ef4444' }};"></i>
                                {{ $site->active ? 'Active' : 'Inactive' }}
                            </p>
                        </div>
                    </div>
                    <div class="site-stats">
                        <div class="stat-item">
                            <div style="font-size: 1.125rem; font-weight: bold; color: #e8e8e8;">{{ number_format($site->total_votes) }}</div>
                            <div style="font-size: 0.75rem; color: #c0c0c0;">Total</div>
                        </div>
                        <div class="stat-item">
                            <div style="font-size: 1.125rem; font-weight: bold; color: #22c55e;">{{ number_format($site->completed_votes) }}</div>
                            <div style="font-size: 0.75rem; color: #c0c0c0;">Completed</div>
                        </div>
                        <div class="stat-item">
                            <div style="font-size: 1.125rem; font-weight: bold; color: #3b82f6;">{{ number_format($site->today_votes) }}</div>
                            <div style="font-size: 0.75rem; color: #c0c0c0;">Today</div>
                        </div>
                        <div class="stat-item">
                            <div style="font-size: 1.125rem; font-weight: bold; color: #d40000;">{{ $successRate }}%</div>
                            <div style="font-size: 0.75rem; color: #c0c0c0;">Success</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Top Voters and Recent Activity -->
    <div class="two-column">
        <!-- Top Voters -->
        <div class="stats-card">
            <h3 style="font-size: 1.5rem; font-weight: bold; color: #d40000; margin-bottom: 1.5rem;">
                <i class="fas fa-crown" style="margin-right: 0.75rem;"></i>Top Voters This Month
            </h3>
            @if($topVoters->count() > 0)
                <div>
                    @foreach($topVoters as $index => $voter)
                        <div class="voter-item">
                            <div class="voter-info">
                                <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: {{ $index === 0 ? '#eab308' : ($index === 1 ? '#9ca3af' : ($index === 2 ? '#d97706' : '#d40000')) }};">
                                    @if($index < 3)
                                        <i class="fas fa-trophy" style="color: white; font-size: 0.875rem;"></i>
                                    @else
                                        <span style="color: white; font-weight: bold; font-size: 0.875rem;">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #e8e8e8;">{{ $voter->username }}</div>
                                    <div style="font-size: 0.75rem; color: #c0c0c0;">Rank #{{ $index + 1 }}</div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.125rem; font-weight: bold; color: #d40000;">{{ number_format($voter->vote_count) }}</div>
                                <div style="font-size: 0.75rem; color: #c0c0c0;">votes</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 2rem 0;">
                    <i class="fas fa-users" style="font-size: 3rem; color: rgba(212, 0, 0, 0.2); margin-bottom: 1rem;"></i>
                    <p style="color: #c0c0c0;">No voters this month yet</p>
                </div>
            @endif
        </div>

        <!-- Recent Activity -->
        <div class="stats-card">
            <h3 style="font-size: 1.5rem; font-weight: bold; color: #d40000; margin-bottom: 1.5rem;">
                <i class="fas fa-clock" style="margin-right: 0.75rem;"></i>Recent Votes
            </h3>
            @if($recentVotes->count() > 0)
                <div>
                    @foreach($recentVotes as $vote)
                        <div class="vote-item">
                            <div class="vote-info">
                                <div style="width: 32px; height: 32px; background: #d40000; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user" style="color: white; font-size: 0.75rem;"></i>
                                </div>
                                <div style="min-width: 0; flex: 1;">
                                    <div style="font-weight: 600; color: #e8e8e8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $vote->username }}</div>
                                    <div style="font-size: 0.75rem; color: #c0c0c0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $vote->site->title }}</div>
                                </div>
                            </div>
                            <div style="text-align: right; flex-shrink: 0;">
                                <div style="font-size: 0.875rem; color: #e8e8e8;">{{ $vote->callback_date->diffForHumans() }}</div>
                                <div style="font-size: 0.75rem; color: #22c55e;">
                                    <i class="fas fa-check-circle" style="margin-right: 0.25rem;"></i>Completed
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 2rem 0;">
                    <i class="fas fa-vote-yea" style="font-size: 3rem; color: rgba(212, 0, 0, 0.2); margin-bottom: 1rem;"></i>
                    <p style="color: #c0c0c0;">No recent votes</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Back to Voting -->
    <div style="text-align: center;">
        <a href="{{ route('vote.index') }}" class="btn btn-primary" style="font-size: 1.125rem; padding: 0.75rem 2rem;">
            <i class="fas fa-vote-yea" style="margin-right: 0.5rem;"></i>Start Voting Now
        </a>
    </div>
</div>
@endsection