@extends('admin.layout')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">
                Add Team Member
            </h2>
            <p class="text-dragon-silver-dark text-sm mt-1">Configure revenue share for a new team member</p>
        </div>
        <a href="{{ route('admin.team-members.index') }}" 
           class="px-4 py-2 bg-dragon-surface hover:bg-dragon-surface-light text-dragon-silver rounded-lg transition-colors border border-dragon-border">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>

    <div class="glass-effect rounded-xl border border-dragon-border p-6">
        <div class="mb-4 p-4 bg-dragon-surface rounded-lg border border-dragon-border">
            <div class="text-dragon-silver-dark text-sm">Current Total Percentage: <span class="text-dragon-gold font-bold">{{ number_format($currentTotal, 2) }}%</span></div>
            <div class="text-dragon-silver-dark text-sm">Maximum Allowed for New Member: <span class="text-green-400 font-bold">{{ number_format($maxAllowed, 2) }}%</span></div>
        </div>

        <form action="{{ route('admin.team-members.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-dragon-silver mb-2">Name</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       required
                       class="w-full px-4 py-3 bg-dragon-surface border border-dragon-border rounded-lg text-dragon-silver focus:border-dragon-red focus:ring-1 focus:ring-dragon-red"
                       placeholder="Team member name">
                @error('name')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="paypal_email" class="block text-sm font-medium text-dragon-silver mb-2">PayPal Email</label>
                <input type="email" 
                       id="paypal_email" 
                       name="paypal_email" 
                       value="{{ old('paypal_email') }}"
                       required
                       class="w-full px-4 py-3 bg-dragon-surface border border-dragon-border rounded-lg text-dragon-silver focus:border-dragon-red focus:ring-1 focus:ring-dragon-red"
                       placeholder="paypal@example.com">
                @error('paypal_email')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="percentage" class="block text-sm font-medium text-dragon-silver mb-2">Revenue Share Percentage</label>
                <div class="relative">
                    <input type="number" 
                           id="percentage" 
                           name="percentage" 
                           value="{{ old('percentage') }}"
                           required
                           step="0.01"
                           min="0.01"
                           max="{{ $maxAllowed }}"
                           class="w-full px-4 py-3 bg-dragon-surface border border-dragon-border rounded-lg text-dragon-silver focus:border-dragon-red focus:ring-1 focus:ring-dragon-red pr-10"
                           placeholder="0.00">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-dragon-silver-dark">%</span>
                </div>
                @error('percentage')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-dragon-silver-dark">Percentage of the net amount (after PayPal fees) to pay out</p>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.team-members.index') }}" 
                   class="px-6 py-3 bg-dragon-surface hover:bg-dragon-surface-light text-dragon-silver rounded-lg transition-colors border border-dragon-border">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i>Add Team Member
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
