@extends('admin.layout')

@section('title', 'Events Management - Aragon RSPS Admin')

@section('header', 'Events Management')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-dragon-silver">All Events</h2>
    <a href="{{ route('admin.events.create') }}" class="px-4 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg transition-colors">
        <i class="fas fa-plus mr-2"></i> Create Event
    </a>
</div>

@if(session('success'))
    <div class="mb-6 p-4 bg-green-600 text-green-100 rounded-lg flex items-center justify-between">
        <div>
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    </div>
@endif

<div class="bg-dragon-surface border border-dragon-border rounded-lg shadow-lg overflow-hidden">
    @if($events->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dragon-black border-b border-dragon-border">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Start Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">End Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dragon-red uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dragon-border">
                    @foreach($events as $event)
                        <tr class="hover:bg-dragon-black transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-dragon-silver">{{ $event->id }}</td>
                            <td class="px-6 py-4 text-sm text-dragon-silver">{{ $event->title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 bg-gray-600 text-gray-200 rounded text-xs font-semibold">{{ $event->type }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($event->status == 'active')
                                    <span class="px-2 py-1 bg-green-600 text-white rounded text-xs font-semibold">Active</span>
                                @elseif($event->status == 'upcoming')
                                    <span class="px-2 py-1 bg-yellow-600 text-white rounded text-xs font-semibold">Upcoming</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-600 text-gray-200 rounded text-xs font-semibold">Ended</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-dragon-silver-dark">
                                {{ $event->start_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-dragon-silver-dark">
                                {{ $event->end_at ? $event->end_at->format('M d, Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.events.edit', $event) }}" 
                                       class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.events.destroy', $event) }}" 
                                          method="POST" 
                                          class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this event?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded transition-colors"
                                                title="Delete">
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

        @if($events->hasPages())
            <div class="px-6 py-4 border-t border-dragon-border">
                {{ $events->links() }}
            </div>
        @endif
    @else
        <div class="p-12 text-center">
            <i class="fas fa-calendar-star text-6xl text-dragon-border mb-4"></i>
            <p class="text-dragon-silver-dark text-lg">No events created yet.</p>
            <a href="{{ route('admin.events.create') }}" class="mt-4 inline-block px-6 py-2 bg-dragon-red hover:bg-dragon-red-bright text-white rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i> Create Your First Event
            </a>
        </div>
    @endif
</div>
@endsection
