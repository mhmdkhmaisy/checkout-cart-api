@extends('layouts.public')

@section('title', 'Welcome to Aragon RSPS')
@section('description', 'Join Aragon RSPS for the ultimate Old School RuneScape experience with custom content, active community, and regular updates.')

@section('content')
<div class="fade-in-up">
    <div class="text-center mb-5">
        <h1 class="text-primary" style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem;">
            Welcome to Aragon RSPS
        </h1>
        <p class="text-muted" style="font-size: 1.25rem; max-width: 800px; margin: 0 auto;">
            Experience the ultimate RuneScape private server with custom content,<br>
            active events, and an amazing community!
        </p>
    </div>

    @if($events->count() > 0)
    <section id="events" class="mb-5">
        <div style="display: flex; justify-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 class="text-primary" style="font-size: 2rem; font-weight: 700;">
                <i class="fas fa-calendar-star"></i> Ongoing & Upcoming Events
            </h2>
            <a href="{{ route('events') }}" class="btn btn-outline">
                <i class="fas fa-arrow-right"></i> View All Events
            </a>
        </div>
        
        <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            @foreach ($events as $event)
                <x-event-card :event="$event" />
            @endforeach
        </div>
    </section>
    @endif

    @if($updates->count() > 0)
    <section id="updates" class="mb-5">
        <div style="display: flex; justify-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 class="text-primary" style="font-size: 2rem; font-weight: 700;">
                <i class="fas fa-newspaper"></i> Recent Updates
            </h2>
            <a href="{{ route('updates') }}" class="btn btn-outline">
                <i class="fas fa-arrow-right"></i> View All Updates
            </a>
        </div>
        
        <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
            @foreach ($updates as $update)
                <x-update-card :update="$update" />
            @endforeach
        </div>
    </section>
    @endif

    <section id="top-voters" class="mb-5">
        <h2 class="text-primary" style="font-size: 2rem; font-weight: 700; margin-bottom: 1.5rem;">
            <i class="fas fa-trophy"></i> Top Voters
        </h2>
        <x-voter-tabs :weekly="$topVotersWeek" :monthly="$topVotersMonth" />
    </section>

    <section class="text-center mt-5">
        <div class="glass-card" style="padding: 3rem;">
            <h3 class="text-primary" style="font-size: 2rem; font-weight: 700; margin-bottom: 1rem;">
                Ready to Start Your Adventure?
            </h3>
            <p class="text-muted mb-4" style="font-size: 1.1rem;">
                Download our launcher and join thousands of players today!
            </p>
            <a href="{{ route('play') }}" class="btn btn-primary" style="font-size: 1.25rem; padding: 1rem 2rem;">
                <i class="fas fa-play"></i> Play Now
            </a>
        </div>
    </section>
</div>
@endsection
