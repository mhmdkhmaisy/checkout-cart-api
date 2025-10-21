@extends('admin.layout')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">
            {{ $promotion->title }}
        </h2>
        <a href="{{ route('admin.promotions.index') }}"
           class="px-6 py-3 bg-dragon-border hover:bg-dragon-silver-dark text-dragon-silver rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back to Promotions
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="glass-effect rounded-xl p-6 border border-dragon-border">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-dragon-red">
                        <i class="fas fa-info-circle mr-2"></i>
                        Promotion Details
                    </h3>
                    <div>
                        @if($promotion->isCurrentlyActive())
                            <span class="inline-block px-3 py-1 text-sm rounded bg-green-600 text-green-100">Active</span>
                        @elseif($promotion->isExpired())
                            <span class="inline-block px-3 py-1 text-sm rounded bg-gray-600 text-gray-100">Expired</span>
                        @else
                            <span class="inline-block px-3 py-1 text-sm rounded bg-blue-600 text-blue-100">Upcoming</span>
                        @endif
                    </div>
                </div>

                <p class="text-dragon-silver mb-4">{{ $promotion->description }}</p>

                <div class="mb-4">
                    <h4 class="text-dragon-red font-semibold mb-2">Reward Items:</h4>
                    <ul class="list-disc list-inside text-dragon-silver-dark space-y-1">
                        @foreach($promotion->reward_items as $item)
                            <li>{{ $item['item_name'] }} (ID: {{ $item['item_id'] }}) x{{ $item['item_amount'] }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-dragon-silver-dark text-sm">
                    <div class="space-y-2">
                        <p><span class="font-semibold text-dragon-red">Minimum Amount:</span> ${{ number_format($promotion->min_amount, 2) }}</p>
                        <p><span class="font-semibold text-dragon-red">Bonus Type:</span> {{ ucfirst($promotion->bonus_type) }}</p>
                        <p><span class="font-semibold text-dragon-red">Claim Limit Per User:</span> {{ $promotion->claim_limit_per_user ?? 'Unlimited' }}</p>
                        <p><span class="font-semibold text-dragon-red">Global Claim Limit:</span> {{ $promotion->global_claim_limit ?? 'Unlimited' }}</p>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-semibold text-dragon-red">Start Date:</span> {{ $promotion->start_at->format('M d, Y H:i') }}</p>
                        <p><span class="font-semibold text-dragon-red">End Date:</span> {{ $promotion->end_at->format('M d, Y H:i') }}</p>
                        <p><span class="font-semibold text-dragon-red">Status:</span> {{ $promotion->is_active ? 'Active' : 'Inactive' }}</p>
                        <p><span class="font-semibold text-dragon-red">Time Remaining:</span> {{ $promotion->time_remaining }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-effect rounded-xl p-6 border border-dragon-border">
                <h3 class="text-xl font-bold text-dragon-red mb-4">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Statistics
                </h3>

                <div class="space-y-3 text-dragon-silver-dark">
                    <div class="flex justify-between items-center">
                        <span>Total Claims:</span>
                        <span class="font-bold text-dragon-silver">{{ $stats['total_claims'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Unique Claimers:</span>
                        <span class="font-bold text-dragon-silver">{{ $stats['unique_claimers'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Eligible Users:</span>
                        <span class="font-bold text-dragon-silver">{{ $stats['eligible_users'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Total Spent:</span>
                        <span class="font-bold text-dragon-silver">${{ number_format($stats['total_spent'], 2) }}</span>
                    </div>
                    @if($promotion->global_claim_limit)
                    <div class="mt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span>Global Progress:</span>
                            <span class="font-bold text-dragon-silver">{{ $stats['global_claims'] }} / {{ $stats['global_limit'] }}</span>
                        </div>
                        <div class="w-full bg-dragon-black rounded-full h-4 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-dragon-red to-dragon-red-bright text-xs text-center text-white leading-4"
                                 style="width: {{ $promotion->progress_percent }}%">
                                {{ number_format($promotion->progress_percent, 1) }}%
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
        <div class="p-6 bg-dragon-black border-b border-dragon-border">
            <h3 class="text-xl font-bold text-dragon-red">
                <i class="fas fa-users mr-2"></i>
                User Progress & Claims
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-dragon-black border-b border-dragon-border">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Username</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Total Spent</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Claims</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Last Claimed</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Claimed In-Game</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dragon-border">
                    @forelse($claims as $claim)
                    <tr class="hover:bg-dragon-surface transition-colors">
                        <td class="px-6 py-4 text-dragon-silver font-semibold">{{ $claim->username }}</td>
                        <td class="px-6 py-4 text-dragon-silver">${{ number_format($claim->total_spent_during_promo, 2) }}</td>
                        <td class="px-6 py-4">
                            <div class="w-full bg-dragon-black rounded-full h-6 overflow-hidden" style="min-width: 100px;">
                                <div class="h-full {{ $claim->progress_percent >= 100 ? 'bg-green-600' : 'bg-blue-600' }} text-xs text-center text-white leading-6"
                                     style="width: {{ min(100, $claim->progress_percent) }}%">
                                    {{ number_format($claim->progress_percent, 0) }}%
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 text-xs rounded bg-blue-600 text-blue-100">{{ $claim->claim_count }}</span>
                        </td>
                        <td class="px-6 py-4 text-dragon-silver-dark text-sm">
                            @if($claim->last_claimed_at)
                                {{ $claim->last_claimed_at->format('M d, Y H:i') }}
                            @else
                                <span class="text-dragon-silver-dark">Never</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($claim->claimed_ingame)
                                <span class="inline-block px-2 py-1 text-xs rounded bg-green-600 text-green-100">Yes</span>
                            @else
                                <span class="inline-block px-2 py-1 text-xs rounded bg-yellow-600 text-yellow-100">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-dragon-silver-dark">
                            No user activity yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-dragon-black border-t border-dragon-border">
            {{ $claims->links() }}
        </div>
    </div>
</div>
@endsection
