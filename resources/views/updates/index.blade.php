@extends('layouts.public')

@section('title', 'Updates - Aragon RSPS')
@section('description', 'Stay up to date with the latest news and updates from Aragon RSPS')

@section('content')
<div class="fade-in-up">
    <div class="text-center mb-5">
        <h1 class="text-primary" style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem;">
            <i class="fas fa-newspaper"></i> Updates
        </h1>
        <p class="text-muted" style="font-size: 1.25rem;">
            Stay informed with the latest news and updates
        </p>
    </div>

    @if($updates->count() > 0)
        <div style="max-width: 900px; margin: 0 auto;">
            @foreach ($updates as $update)
                <div class="mb-4">
                    <x-update-card :update="$update" />
                </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            {{ $updates->links() }}
        </div>
    @else
        <div class="glass-card text-center" style="padding: 3rem; max-width: 600px; margin: 0 auto;">
            <i class="fas fa-file-alt text-muted" style="font-size: 4rem; margin-bottom: 1rem;"></i>
            <h3 class="text-muted" style="font-size: 1.5rem;">No updates yet</h3>
            <p class="text-muted">Check back later for news and updates!</p>
        </div>
    @endif
</div>
@endsection
