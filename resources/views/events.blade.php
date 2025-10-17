@extends('layouts.public')

@section('title', 'Events - Aragon RSPS')
@section('description', 'View all ongoing and upcoming events on Aragon RSPS')

@section('content')
<div class="fade-in-up">
    <div class="text-center mb-5">
        <h1 class="text-primary" style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem;">
            <i class="fas fa-calendar-star"></i> Events
        </h1>
        <p class="text-muted" style="font-size: 1.25rem;">
            Join our exciting in-game events and earn amazing rewards!
        </p>
    </div>

    @if($events->count() > 0)
        <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            @foreach ($events as $event)
                <x-event-card :event="$event" />
            @endforeach
        </div>

        <div class="text-center">
            {{ $events->links() }}
        </div>
    @else
        <div class="glass-card text-center" style="padding: 3rem;">
            <i class="fas fa-calendar-times text-muted" style="font-size: 4rem; margin-bottom: 1rem;"></i>
            <h3 class="text-muted" style="font-size: 1.5rem;">No events available at the moment</h3>
            <p class="text-muted">Check back later for exciting new events!</p>
        </div>
    @endif
</div>
@endsection
