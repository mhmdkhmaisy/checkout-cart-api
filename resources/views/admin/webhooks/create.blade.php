@extends('admin.layout')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-3xl font-bold text-dragon-red dragon-text-glow">
            Create New Webhook
        </h2>
        <a href="{{ route('admin.webhooks.index') }}" 
           class="px-6 py-3 bg-dragon-border hover:bg-dragon-silver-dark text-dragon-silver rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back to Webhooks
        </a>
    </div>

    <form action="{{ route('admin.webhooks.store') }}" method="POST" class="max-w-2xl">
        @csrf

        <div class="glass-effect rounded-xl p-6 border border-dragon-border space-y-6">
            <h3 class="text-xl font-bold text-dragon-red mb-4">
                <i class="fas fa-bell mr-2"></i>
                Webhook Configuration
            </h3>
            
            <div>
                <label for="name" class="block text-sm font-medium text-dragon-red mb-2">
                    Webhook Name <span class="text-red-400">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       placeholder="e.g., Main Discord Server"
                       class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all @error('name') border-red-500 @enderror"
                       required>
                @error('name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="url" class="block text-sm font-medium text-dragon-red mb-2">
                    Discord Webhook URL <span class="text-red-400">*</span>
                </label>
                <input type="url" 
                       id="url" 
                       name="url" 
                       value="{{ old('url') }}"
                       placeholder="https://discord.com/api/webhooks/..."
                       class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all @error('url') border-red-500 @enderror"
                       required>
                @error('url')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dragon-silver-dark text-xs mt-2">
                    <i class="fas fa-info-circle"></i> Get this URL from your Discord server settings under Integrations > Webhooks
                </p>
            </div>

            <div>
                <label for="event_type" class="block text-sm font-medium text-dragon-red mb-2">
                    Event Type <span class="text-red-400">*</span>
                </label>
                <select id="event_type" 
                        name="event_type"
                        class="w-full px-4 py-3 bg-dragon-black border border-dragon-border rounded-lg text-dragon-silver focus:outline-none focus:ring-2 focus:ring-dragon-red focus:border-transparent transition-all @error('event_type') border-red-500 @enderror"
                        required>
                    <option value="">Select an event type...</option>
                    <option value="promotion.created" {{ old('event_type') == 'promotion.created' ? 'selected' : '' }}>
                        Promotion Created
                    </option>
                    <option value="promotion.claimed" {{ old('event_type') == 'promotion.claimed' ? 'selected' : '' }}>
                        Promotion Claimed
                    </option>
                    <option value="update.published" {{ old('event_type') == 'update.published' ? 'selected' : '' }}>
                        Update Published
                    </option>
                </select>
                @error('event_type')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-5 h-5 text-dragon-red bg-dragon-black border-dragon-border rounded focus:ring-dragon-red focus:ring-2">
                    <span class="ml-3 text-dragon-silver">
                        Active (webhook will receive notifications)
                    </span>
                </label>
            </div>

            <div class="flex justify-end space-x-4 pt-4 border-t border-dragon-border">
                <a href="{{ route('admin.webhooks.index') }}" 
                   class="px-6 py-3 bg-dragon-border hover:bg-dragon-silver-dark text-dragon-silver rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                    <i class="fas fa-check mr-2"></i>Create Webhook
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
