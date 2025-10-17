@extends('layouts.admin')

@section('title', 'Events Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Events Management</h1>
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Event
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($events->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($events as $event)
                                <tr>
                                    <td>{{ $event->id }}</td>
                                    <td>{{ $event->title }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $event->type }}</span>
                                    </td>
                                    <td>
                                        @if($event->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($event->status == 'upcoming')
                                            <span class="badge bg-warning">Upcoming</span>
                                        @else
                                            <span class="badge bg-secondary">Ended</span>
                                        @endif
                                    </td>
                                    <td>{{ $event->start_at->format('M d, Y H:i') }}</td>
                                    <td>{{ $event->end_at ? $event->end_at->format('M d, Y H:i') : 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $events->links() }}
                </div>
            @else
                <p class="text-center text-muted py-4">No events created yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
