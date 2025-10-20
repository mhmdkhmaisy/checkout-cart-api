@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Promotions
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">{{ $promotion->title }}</h3>
                    <div>
                        @if($promotion->isCurrentlyActive())
                            <span class="badge bg-success">Active</span>
                        @elseif($promotion->isExpired())
                            <span class="badge bg-secondary">Expired</span>
                        @else
                            <span class="badge bg-info">Upcoming</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <p class="mb-3">{{ $promotion->description }}</p>

                    <div class="mb-3">
                        <strong>Reward Items:</strong>
                        <ul>
                            @foreach($promotion->reward_items as $item)
                                <li>{{ $item['item_name'] }} (ID: {{ $item['item_id'] }}) x{{ $item['item_amount'] }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Minimum Amount:</strong> ${{ number_format($promotion->min_amount, 2) }}</p>
                            <p><strong>Bonus Type:</strong> {{ ucfirst($promotion->bonus_type) }}</p>
                            <p><strong>Claim Limit Per User:</strong> {{ $promotion->claim_limit_per_user ?? 'Unlimited' }}</p>
                            <p><strong>Global Claim Limit:</strong> {{ $promotion->global_claim_limit ?? 'Unlimited' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Start Date:</strong> {{ $promotion->start_at->format('M d, Y H:i') }}</p>
                            <p><strong>End Date:</strong> {{ $promotion->end_at->format('M d, Y H:i') }}</p>
                            <p><strong>Status:</strong> {{ $promotion->is_active ? 'Active' : 'Inactive' }}</p>
                            <p><strong>Time Remaining:</strong> {{ $promotion->time_remaining }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Claims:</span>
                            <strong>{{ $stats['total_claims'] }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Unique Claimers:</span>
                            <strong>{{ $stats['unique_claimers'] }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Eligible Users:</span>
                            <strong>{{ $stats['eligible_users'] }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Spent:</span>
                            <strong>${{ number_format($stats['total_spent'], 2) }}</strong>
                        </div>
                    </div>
                    @if($promotion->global_claim_limit)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Global Progress:</span>
                            <strong>{{ $stats['global_claims'] }} / {{ $stats['global_limit'] }}</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $promotion->progress_percent }}%" 
                                 aria-valuenow="{{ $promotion->progress_percent }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                                {{ number_format($promotion->progress_percent, 1) }}%
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">User Progress & Claims</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Total Spent</th>
                            <th>Progress</th>
                            <th>Claims</th>
                            <th>Last Claimed</th>
                            <th>Claimed In-Game</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claims as $claim)
                        <tr>
                            <td>{{ $claim->username }}</td>
                            <td>${{ number_format($claim->total_spent_during_promo, 2) }}</td>
                            <td>
                                <div class="progress" style="min-width: 100px;">
                                    <div class="progress-bar bg-{{ $claim->progress_percent >= 100 ? 'success' : 'primary' }}" 
                                         role="progressbar" 
                                         style="width: {{ min(100, $claim->progress_percent) }}%" 
                                         aria-valuenow="{{ $claim->progress_percent }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        {{ number_format($claim->progress_percent, 0) }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $claim->claim_count }}</span>
                            </td>
                            <td>
                                @if($claim->last_claimed_at)
                                    {{ $claim->last_claimed_at->format('M d, Y H:i') }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                @if($claim->claimed_ingame)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No user activity yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $claims->links() }}
        </div>
    </div>
</div>
@endsection
