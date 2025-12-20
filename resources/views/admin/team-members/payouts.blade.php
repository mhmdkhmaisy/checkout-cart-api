@extends('admin.layout')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">
                Payout History
            </h2>
            <p class="text-dragon-silver-dark text-sm mt-1">View all revenue share payouts</p>
        </div>
        <a href="{{ route('admin.team-members.index') }}" 
           class="px-4 py-2 bg-dragon-surface hover:bg-dragon-surface-light text-dragon-silver rounded-lg transition-colors border border-dragon-border">
            <i class="fas fa-arrow-left mr-2"></i>Back to Team Members
        </a>
    </div>

    <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-dragon-black border-b border-dragon-border">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Order</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Team Member</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Net Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Percentage</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Payout</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dragon-border">
                    @forelse($payouts as $payout)
                    <tr class="hover:bg-dragon-surface transition-colors">
                        <td class="px-6 py-4 text-dragon-silver">{{ $payout->id }}</td>
                        <td class="px-6 py-4">
                            @if($payout->order)
                                <a href="{{ route('admin.orders.show', $payout->order) }}" class="text-dragon-red hover:text-dragon-red-bright">
                                    #{{ $payout->order_id }}
                                </a>
                            @else
                                <span class="text-dragon-silver-dark">#{{ $payout->order_id }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-dragon-silver">{{ $payout->teamMember->name ?? 'Unknown' }}</span>
                                <span class="text-dragon-silver-dark text-xs">{{ $payout->paypal_email }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-dragon-silver">${{ number_format($payout->net_amount, 2) }}</td>
                        <td class="px-6 py-4 text-dragon-gold">{{ number_format($payout->percentage, 2) }}%</td>
                        <td class="px-6 py-4 text-green-400 font-bold">${{ number_format($payout->payout_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-600 text-yellow-100',
                                    'processing' => 'bg-blue-600 text-blue-100',
                                    'completed' => 'bg-green-600 text-green-100',
                                    'failed' => 'bg-red-600 text-red-100',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs rounded {{ $statusColors[$payout->status] ?? 'bg-gray-600 text-gray-100' }}">
                                {{ ucfirst($payout->status) }}
                            </span>
                            @if($payout->error_message)
                                <div class="text-red-400 text-xs mt-1" title="{{ $payout->error_message }}">
                                    <i class="fas fa-exclamation-circle"></i> Error
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-dragon-silver-dark text-sm">
                            {{ $payout->created_at->format('M d, Y H:i') }}
                        </td>
                    </tr>
                    <tr class="bg-dragon-black/50">
                        <td colspan="8" class="px-6 py-3">
                            <p class="text-dragon-silver-dark text-xs">
                                <i class="fas fa-info-circle mr-2"></i>You have received {{ number_format($payout->percentage, 2) }}% of ${{ number_format($payout->net_amount, 2) }} which amounts to ${{ number_format($payout->payout_amount, 2) }}
                            </p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="text-dragon-silver-dark">
                                No payouts recorded yet. Payouts will appear here after completed PayPal transactions.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($payouts->hasPages())
    <div class="mt-4">
        {{ $payouts->links() }}
    </div>
    @endif
</div>
@endsection
