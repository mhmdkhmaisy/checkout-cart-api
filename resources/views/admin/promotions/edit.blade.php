@extends('admin.layout')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">
            Edit Promotion: {{ $promotion->title }}
        </h2>
        <a href="{{ route('admin.promotions.index') }}" 
           class="px-6 py-3 bg-dragon-border hover:bg-dragon-silver-dark text-dragon-silver rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back to Promotions
        </a>
    </div>

    <form action="{{ route('admin.promotions.update', $promotion) }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        @method('PUT')

        <div class="lg:col-span-2 space-y-6">
            <div class="glass-effect rounded-xl p-6 border border-dragon-border">
                <h3 class="text-xl font-bold text-dragon-red mb-4">
                    <i class="fas fa-info-circle mr-2"></i>
                    Promotion Details
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-dragon-red mb-2">
                            Promotion Title <span class="text-red-400">*</span>
                        </label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               value="{{ old('title', $promotion->title) }}"
                               class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all @error('title') border-red-500 @enderror"
                               required>
                        @error('title')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-dragon-red mb-2">
                            Description <span class="text-red-400">*</span>
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all @error('description') border-red-500 @enderror"
                                  required>{{ old('description', $promotion->description) }}</textarea>
                        @error('description')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-dragon-red mb-2">
                            Reward Items <span class="text-red-400">*</span>
                        </label>
                        <div id="reward-items-container" class="space-y-2">
                            @foreach(old('reward_items', $promotion->reward_items) as $index => $item)
                            <div class="reward-item-row grid grid-cols-12 gap-2">
                                <div class="col-span-4">
                                    <input type="number" 
                                           class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red"
                                           name="reward_items[{{ $index }}][item_id]" 
                                           placeholder="Item ID" 
                                           value="{{ $item['item_id'] }}"
                                           required>
                                </div>
                                <div class="col-span-3">
                                    <input type="number" 
                                           class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red"
                                           name="reward_items[{{ $index }}][item_amount]" 
                                           placeholder="Amount" 
                                           value="{{ $item['item_amount'] }}"
                                           required>
                                </div>
                                <div class="col-span-{{ $index > 0 ? '4' : '5' }}">
                                    <input type="text" 
                                           class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red"
                                           name="reward_items[{{ $index }}][item_name]" 
                                           placeholder="Item Name" 
                                           value="{{ $item['item_name'] }}"
                                           required>
                                </div>
                                @if($index > 0)
                                <div class="col-span-1">
                                    <button type="button" class="w-full px-2 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors remove-reward-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <button type="button" 
                                id="add-reward-item"
                                class="mt-2 px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors text-sm">
                            <i class="fas fa-plus mr-1"></i> Add Item
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-effect rounded-xl p-6 border border-dragon-border">
                <h3 class="text-xl font-bold text-dragon-red mb-4">
                    <i class="fas fa-cog mr-2"></i>
                    Settings
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="min_amount" class="block text-sm font-medium text-dragon-red mb-2">
                            Minimum Amount ($) <span class="text-red-400">*</span>
                        </label>
                        <input type="number" 
                               step="0.01" 
                               id="min_amount" 
                               name="min_amount" 
                               value="{{ old('min_amount', $promotion->min_amount) }}"
                               class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all @error('min_amount') border-red-500 @enderror"
                               required>
                        @error('min_amount')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bonus_type" class="block text-sm font-medium text-dragon-red mb-2">
                            Bonus Type <span class="text-red-400">*</span>
                        </label>
                        <select id="bonus_type" 
                                name="bonus_type"
                                class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all @error('bonus_type') border-red-500 @enderror"
                                required>
                            <option value="single" {{ old('bonus_type', $promotion->bonus_type) == 'single' ? 'selected' : '' }}>Single (Claim once per user)</option>
                            <option value="recurrent" {{ old('bonus_type', $promotion->bonus_type) == 'recurrent' ? 'selected' : '' }}>Recurrent (Claim multiple times)</option>
                        </select>
                        @error('bonus_type')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="claim_limit_per_user" class="block text-sm font-medium text-dragon-red mb-2">
                            Claims Per User Limit
                        </label>
                        <input type="number" 
                               id="claim_limit_per_user" 
                               name="claim_limit_per_user" 
                               value="{{ old('claim_limit_per_user', $promotion->claim_limit_per_user) }}"
                               placeholder="Unlimited"
                               class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all @error('claim_limit_per_user') border-red-500 @enderror">
                        <p class="text-dragon-silver-dark text-sm mt-1">Leave empty for unlimited</p>
                        @error('claim_limit_per_user')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="global_claim_limit" class="block text-sm font-medium text-dragon-red mb-2">
                            Global Claim Limit
                        </label>
                        <input type="number" 
                               id="global_claim_limit" 
                               name="global_claim_limit" 
                               value="{{ old('global_claim_limit', $promotion->global_claim_limit) }}"
                               placeholder="Unlimited"
                               class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all @error('global_claim_limit') border-red-500 @enderror">
                        <p class="text-dragon-silver-dark text-sm mt-1">Total claims allowed across all users</p>
                        @error('global_claim_limit')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="start_at" class="block text-sm font-medium text-dragon-red mb-2">
                            Start Date <span class="text-red-400">*</span>
                        </label>
                        <input type="datetime-local" 
                               id="start_at" 
                               name="start_at" 
                               value="{{ old('start_at', $promotion->start_at->format('Y-m-d\TH:i')) }}"
                               class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all @error('start_at') border-red-500 @enderror"
                               required>
                        @error('start_at')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_at" class="block text-sm font-medium text-dragon-red mb-2">
                            End Date <span class="text-red-400">*</span>
                        </label>
                        <input type="datetime-local" 
                               id="end_at" 
                               name="end_at" 
                               value="{{ old('end_at', $promotion->end_at->format('Y-m-d\TH:i')) }}"
                               class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all @error('end_at') border-red-500 @enderror"
                               required>
                        @error('end_at')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', $promotion->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-dragon-red bg-dragon-black border-dragon-border rounded focus:ring-dragon-red focus:ring-2">
                        <label for="is_active" class="ml-2 text-sm text-dragon-silver">
                            Active
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.promotions.index') }}" 
                   class="px-6 py-3 bg-dragon-border hover:bg-dragon-silver-dark text-dragon-silver rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                    Update Promotion
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let rewardItemIndex = {{ count($promotion->reward_items) }};

document.getElementById('add-reward-item').addEventListener('click', function() {
    const container = document.getElementById('reward-items-container');
    const newRow = document.createElement('div');
    newRow.className = 'reward-item-row grid grid-cols-12 gap-2';
    newRow.innerHTML = `
        <div class="col-span-4">
            <input type="number" 
                   class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red"
                   name="reward_items[${rewardItemIndex}][item_id]" 
                   placeholder="Item ID" 
                   required>
        </div>
        <div class="col-span-3">
            <input type="number" 
                   class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red"
                   name="reward_items[${rewardItemIndex}][item_amount]" 
                   placeholder="Amount" 
                   required>
        </div>
        <div class="col-span-4">
            <input type="text" 
                   class="w-full px-3 py-2 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red"
                   name="reward_items[${rewardItemIndex}][item_name]" 
                   placeholder="Item Name" 
                   required>
        </div>
        <div class="col-span-1">
            <button type="button" class="w-full px-2 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors remove-reward-item">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(newRow);
    rewardItemIndex++;
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-reward-item') || e.target.parentElement.classList.contains('remove-reward-item')) {
        e.target.closest('.reward-item-row').remove();
    }
});
</script>
@endsection
