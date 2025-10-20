@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Promotions
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Edit Promotion: {{ $promotion->title }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.promotions.update', $promotion) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Promotion Title *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title', $promotion->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" required>{{ old('description', $promotion->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reward Items *</label>
                            <div id="reward-items-container">
                                @foreach(old('reward_items', $promotion->reward_items) as $index => $item)
                                <div class="reward-item-row mb-2">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="number" class="form-control" name="reward_items[{{ $index }}][item_id]" 
                                                   placeholder="Item ID" value="{{ $item['item_id'] }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" class="form-control" name="reward_items[{{ $index }}][item_amount]" 
                                                   placeholder="Amount" value="{{ $item['item_amount'] }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" name="reward_items[{{ $index }}][item_name]" 
                                                   placeholder="Item Name" value="{{ $item['item_name'] }}" required>
                                        </div>
                                        @if($index > 0)
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger btn-sm remove-reward-item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary" id="add-reward-item">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="min_amount" class="form-label">Minimum Amount ($) *</label>
                            <input type="number" step="0.01" class="form-control @error('min_amount') is-invalid @enderror" 
                                   id="min_amount" name="min_amount" value="{{ old('min_amount', $promotion->min_amount) }}" required>
                            @error('min_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bonus_type" class="form-label">Bonus Type *</label>
                            <select class="form-select @error('bonus_type') is-invalid @enderror" 
                                    id="bonus_type" name="bonus_type" required>
                                <option value="single" {{ old('bonus_type', $promotion->bonus_type) == 'single' ? 'selected' : '' }}>
                                    Single (Claim once per user)
                                </option>
                                <option value="recurrent" {{ old('bonus_type', $promotion->bonus_type) == 'recurrent' ? 'selected' : '' }}>
                                    Recurrent (Claim multiple times)
                                </option>
                            </select>
                            @error('bonus_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="claim_limit_per_user" class="form-label">Claims Per User Limit</label>
                            <input type="number" class="form-control @error('claim_limit_per_user') is-invalid @enderror" 
                                   id="claim_limit_per_user" name="claim_limit_per_user" 
                                   value="{{ old('claim_limit_per_user', $promotion->claim_limit_per_user) }}" placeholder="Unlimited">
                            <small class="text-muted">Leave empty for unlimited</small>
                            @error('claim_limit_per_user')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="global_claim_limit" class="form-label">Global Claim Limit</label>
                            <input type="number" class="form-control @error('global_claim_limit') is-invalid @enderror" 
                                   id="global_claim_limit" name="global_claim_limit" 
                                   value="{{ old('global_claim_limit', $promotion->global_claim_limit) }}" placeholder="Unlimited">
                            <small class="text-muted">Total claims allowed across all users</small>
                            @error('global_claim_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="start_at" class="form-label">Start Date *</label>
                            <input type="datetime-local" class="form-control @error('start_at') is-invalid @enderror" 
                                   id="start_at" name="start_at" value="{{ old('start_at', $promotion->start_at->format('Y-m-d\TH:i')) }}" required>
                            @error('start_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="end_at" class="form-label">End Date *</label>
                            <input type="datetime-local" class="form-control @error('end_at') is-invalid @enderror" 
                                   id="end_at" name="end_at" value="{{ old('end_at', $promotion->end_at->format('Y-m-d\TH:i')) }}" required>
                            @error('end_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1" {{ old('is_active', $promotion->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Promotion</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let rewardItemIndex = {{ count($promotion->reward_items) }};

document.getElementById('add-reward-item').addEventListener('click', function() {
    const container = document.getElementById('reward-items-container');
    const newRow = document.createElement('div');
    newRow.className = 'reward-item-row mb-2';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <input type="number" class="form-control" name="reward_items[${rewardItemIndex}][item_id]" 
                       placeholder="Item ID" required>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control" name="reward_items[${rewardItemIndex}][item_amount]" 
                       placeholder="Amount" required>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="reward_items[${rewardItemIndex}][item_name]" 
                       placeholder="Item Name" required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm remove-reward-item">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
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
