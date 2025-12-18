@extends('admin.layout')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">
                Team Members & Payouts
            </h2>
            <p class="text-dragon-silver-dark text-sm mt-1">Manage revenue share team members and view payout history</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.team-members.payouts') }}" 
               class="px-6 py-3 bg-dragon-surface hover:bg-dragon-surface-light text-dragon-silver rounded-lg transition-colors border border-dragon-border">
                <i class="fas fa-history mr-2"></i>Payout History
            </a>
            <a href="{{ route('admin.team-members.create') }}" 
               class="px-6 py-3 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>Add Team Member
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="glass-effect rounded-xl border border-dragon-border p-4">
            <div class="text-dragon-silver-dark text-sm">Total Percentage</div>
            <div class="text-2xl font-bold {{ $totalPercentage > 100 ? 'text-red-500' : 'text-dragon-gold' }}">
                {{ number_format($totalPercentage, 2) }}%
            </div>
            @if($totalPercentage > 100)
                <div class="text-red-400 text-xs mt-1">Exceeds 100%!</div>
            @endif
        </div>
        <div class="glass-effect rounded-xl border border-dragon-border p-4">
            <div class="text-dragon-silver-dark text-sm">Total Payouts</div>
            <div class="text-2xl font-bold text-dragon-silver">{{ $payoutStats['total_payouts'] }}</div>
        </div>
        <div class="glass-effect rounded-xl border border-dragon-border p-4">
            <div class="text-dragon-silver-dark text-sm">Completed Payouts</div>
            <div class="text-2xl font-bold text-green-400">{{ $payoutStats['completed_payouts'] }}</div>
        </div>
        <div class="glass-effect rounded-xl border border-dragon-border p-4">
            <div class="text-dragon-silver-dark text-sm">Total Paid Out</div>
            <div class="text-2xl font-bold text-dragon-gold">${{ number_format($payoutStats['total_paid_out'], 2) }}</div>
        </div>
    </div>

    <div class="glass-effect rounded-xl border border-dragon-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-dragon-black border-b border-dragon-border">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">PayPal Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Percentage</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Total Paid</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Payouts</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dragon-border">
                    @forelse($teamMembers as $member)
                    <tr class="hover:bg-dragon-surface transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-semibold text-dragon-silver">{{ $member->name }}</span>
                        </td>
                        <td class="px-6 py-4 text-dragon-silver-dark">{{ $member->paypal_email }}</td>
                        <td class="px-6 py-4">
                            <span class="text-dragon-gold font-bold">{{ number_format($member->percentage, 2) }}%</span>
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.team-members.toggle-active', $member) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="px-3 py-1 text-xs rounded transition-colors {{ $member->is_active ? 'bg-green-600 hover:bg-green-700 text-green-100' : 'bg-gray-600 hover:bg-gray-700 text-gray-100' }}">
                                    {{ $member->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-dragon-silver">${{ number_format($member->total_paid ?? 0, 2) }}</td>
                        <td class="px-6 py-4 text-dragon-silver">{{ $member->payouts_count }}</td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.team-members.edit', $member) }}" 
                                   class="px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white rounded text-sm transition-colors">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.team-members.destroy', $member) }}" method="POST" onsubmit="return confirm('Are you sure?')">
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
                                No team members yet. <a href="{{ route('admin.team-members.create') }}" class="text-dragon-red hover:text-dragon-red-bright transition-colors">Add your first team member</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-effect rounded-xl border border-dragon-border p-4">
        <h3 class="text-dragon-red font-bold mb-2">How Auto Payouts Work</h3>
        <ul class="text-dragon-silver-dark text-sm space-y-1">
            <li>When a PayPal payment is captured (CAPTURE.COMPLETED), the system automatically sends payouts to active team members.</li>
            <li>Payouts are calculated from the <strong>net amount</strong> (after PayPal fees) so you don't eat the fees alone.</li>
            <li>Each transaction only triggers payouts once - duplicate webhooks are safely ignored.</li>
            <li>Make sure total active percentages don't exceed 100%.</li>
        </ul>
    </div>
</div>
@endsection
