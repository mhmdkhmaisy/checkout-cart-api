<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::orderBy('start_at', 'desc')->paginate(20);
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string',
            'rewards' => 'required|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after:start_at',
            'image' => 'nullable|image|max:2048',
        ]);

        $rewards = array_filter(array_map('trim', explode("\n", $validated['rewards'])));
        $validated['rewards'] = json_encode($rewards);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event = Event::create($validated);
        $event->updateStatus();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully.');
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string',
            'rewards' => 'required|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after:start_at',
            'image' => 'nullable|image|max:2048',
        ]);

        $rewards = array_filter(array_map('trim', explode("\n", $validated['rewards'])));
        $validated['rewards'] = json_encode($rewards);

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);
        $event->updateStatus();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted successfully.');
    }
}
