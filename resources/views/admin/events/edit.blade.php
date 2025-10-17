@extends('admin.layout')

@section('title', 'Edit Event - Aragon RSPS Admin')

@section('header', 'Edit Event')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.events.index') }}" class="inline-flex items-center px-4 py-2 bg-dragon-surface border border-dragon-border text-dragon-silver rounded-lg hover:bg-dragon-red hover:text-white transition-colors">
        <i class="fas fa-arrow-left mr-2"></i> Back to Events
    </a>
</div>

<div class="bg-dragon-surface border border-dragon-border rounded-lg shadow-lg">
    <div class="p-6">
        <h3 class="text-xl font-semibold text-dragon-silver mb-6">{{ $event->title }}</h3>
        
        <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="title" class="block text-dragon-silver font-semibold mb-2">
                    Event Title <span class="text-dragon-red">*</span>
                </label>
                <input type="text" 
                       class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('title') border-red-500 @enderror" 
                       id="title" 
                       name="title" 
                       value="{{ old('title', $event->title) }}" 
                       required>
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="type" class="block text-dragon-silver font-semibold mb-2">
                    Event Type <span class="text-dragon-red">*</span>
                </label>
                <select class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('type') border-red-500 @enderror" 
                        id="type" 
                        name="type" 
                        required>
                    <option value="">Select Type</option>
                    <option value="PvP" {{ old('type', $event->type) == 'PvP' ? 'selected' : '' }}>PvP</option>
                    <option value="Giveaway" {{ old('type', $event->type) == 'Giveaway' ? 'selected' : '' }}>Giveaway</option>
                    <option value="Double XP" {{ old('type', $event->type) == 'Double XP' ? 'selected' : '' }}>Double XP</option>
                    <option value="Boss Event" {{ old('type', $event->type) == 'Boss Event' ? 'selected' : '' }}>Boss Event</option>
                    <option value="Other" {{ old('type', $event->type) == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-dragon-silver font-semibold mb-2">
                    Description <span class="text-dragon-red">*</span>
                </label>
                <textarea class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('description') border-red-500 @enderror" 
                          id="description" 
                          name="description" 
                          rows="5" 
                          required>{{ old('description', $event->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="rewards" class="block text-dragon-silver font-semibold mb-2">
                    Rewards (one per line) <span class="text-dragon-red">*</span>
                </label>
                <textarea class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('rewards') border-red-500 @enderror" 
                          id="rewards" 
                          name="rewards" 
                          rows="4" 
                          required>{{ old('rewards', implode("\n", $event->rewards_array)) }}</textarea>
                <p class="text-sm text-dragon-silver-dark mt-1">Enter each reward on a new line</p>
                @error('rewards')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="start_at" class="block text-dragon-silver font-semibold mb-2">
                        Start Date/Time <span class="text-dragon-red">*</span>
                    </label>
                    <input type="datetime-local" 
                           class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('start_at') border-red-500 @enderror" 
                           id="start_at" 
                           name="start_at" 
                           value="{{ old('start_at', $event->start_at->format('Y-m-d\TH:i')) }}" 
                           required>
                    @error('start_at')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_at" class="block text-dragon-silver font-semibold mb-2">
                        End Date/Time
                    </label>
                    <input type="datetime-local" 
                           class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('end_at') border-red-500 @enderror" 
                           id="end_at" 
                           name="end_at" 
                           value="{{ old('end_at', $event->end_at ? $event->end_at->format('Y-m-d\TH:i') : '') }}">
                    <p class="text-sm text-dragon-silver-dark mt-1">Leave empty for no end date</p>
                    @error('end_at')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @if($event->image)
                <div class="mb-6">
                    <label class="block text-dragon-silver font-semibold mb-2">Current Image</label>
                    <div class="bg-dragon-black p-4 rounded-lg inline-block">
                        <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" class="max-w-sm h-auto rounded-lg border-2 border-dragon-border">
                    </div>
                </div>
            @endif

            <div class="mb-6">
                <label for="image" class="block text-dragon-silver font-semibold mb-2">
                    {{ $event->image ? 'Replace Image' : 'Event Image' }}
                </label>
                <input type="file" 
                       class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-4 py-2 focus:border-dragon-red focus:ring-1 focus:ring-dragon-red @error('image') border-red-500 @enderror" 
                       id="image" 
                       name="image" 
                       accept="image/*">
                <p class="text-sm text-dragon-silver-dark mt-1">Recommended size: 800x400px (PNG, JPG, max 2MB)</p>
                @error('image')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i> Update Event
                </button>
                <a href="{{ route('admin.events.index') }}" class="px-6 py-2 bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg hover:bg-dragon-surface transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
