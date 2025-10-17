<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Update;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function index()
    {
        $updates = Update::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.updates.index', compact('updates'));
    }

    public function create()
    {
        return view('admin.updates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'client_update' => 'boolean',
        ]);

        $validated['client_update'] = $request->has('client_update');

        $update = Update::create($validated);

        return redirect()->route('admin.updates.index')
            ->with('success', 'Update created successfully.');
    }

    public function edit(Update $update)
    {
        return view('admin.updates.edit', compact('update'));
    }

    public function update(Request $request, Update $update)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'client_update' => 'boolean',
        ]);

        $validated['client_update'] = $request->has('client_update');

        $update->update($validated);

        return redirect()->route('admin.updates.index')
            ->with('success', 'Update updated successfully.');
    }

    public function destroy(Update $update)
    {
        $update->delete();

        return redirect()->route('admin.updates.index')
            ->with('success', 'Update deleted successfully.');
    }
}
