@extends('layouts.admin')

@section('title', 'Edit Event')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Events
        </a>
    </div>

    <h1 class="h3 mb-4">Edit Event: {{ $event->title }}</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="title" class="form-label">Event Title *</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                           id="title" name="title" value="{{ old('title', $event->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="type" class="form-label">Event Type *</label>
                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="">Select Type</option>
                        <option value="PvP" {{ old('type', $event->type) == 'PvP' ? 'selected' : '' }}>PvP</option>
                        <option value="Giveaway" {{ old('type', $event->type) == 'Giveaway' ? 'selected' : '' }}>Giveaway</option>
                        <option value="Double XP" {{ old('type', $event->type) == 'Double XP' ? 'selected' : '' }}>Double XP</option>
                        <option value="Boss Event" {{ old('type', $event->type) == 'Boss Event' ? 'selected' : '' }}>Boss Event</option>
                        <option value="Other" {{ old('type', $event->type) == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description *</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="5" required>{{ old('description', $event->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="rewards" class="form-label">Rewards (one per line) *</label>
                    <textarea class="form-control @error('rewards') is-invalid @enderror" 
                              id="rewards" name="rewards" rows="4" required>{{ old('rewards', implode("\n", $event->rewards_array)) }}</textarea>
                    <small class="form-text text-muted">Enter each reward on a new line</small>
                    @error('rewards')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_at" class="form-label">Start Date/Time *</label>
                        <input type="datetime-local" class="form-control @error('start_at') is-invalid @enderror" 
                               id="start_at" name="start_at" value="{{ old('start_at', $event->start_at->format('Y-m-d\TH:i')) }}" required>
                        @error('start_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="end_at" class="form-label">End Date/Time</label>
                        <input type="datetime-local" class="form-control @error('end_at') is-invalid @enderror" 
                               id="end_at" name="end_at" value="{{ old('end_at', $event->end_at ? $event->end_at->format('Y-m-d\TH:i') : '') }}">
                        <small class="form-text text-muted">Leave empty for no end date</small>
                        @error('end_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @if($event->image)
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <div>
                            <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" style="max-width: 300px; height: auto; border-radius: 8px;">
                        </div>
                    </div>
                @endif

                <div class="mb-3">
                    <label for="image" class="form-label">{{ $event->image ? 'Replace Image' : 'Event Image' }}</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                           id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">Recommended size: 800x400px (PNG, JPG, max 2MB)</small>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Event
                    </button>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
