<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreAlert;
use Illuminate\Http\Request;

class StoreAlertController extends Controller
{
    public function index()
    {
        $alerts = StoreAlert::orderBy('sort_order', 'asc')->get();
        return view('admin.store-alerts.index', compact('alerts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'text' => 'required|string',
            'is_active' => 'boolean'
        ]);

        $maxOrder = StoreAlert::max('sort_order') ?? 0;
        
        StoreAlert::create([
            'type' => $request->type,
            'text' => $request->text,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $maxOrder + 1
        ]);

        return back()->with('success', 'Alert created successfully');
    }

    public function update(Request $request, StoreAlert $storeAlert)
    {
        $request->validate([
            'type' => 'required|string',
            'text' => 'required|string',
            'is_active' => 'boolean'
        ]);

        $storeAlert->update([
            'type' => $request->type,
            'text' => $request->text,
            'is_active' => $request->boolean('is_active')
        ]);

        return back()->with('success', 'Alert updated successfully');
    }

    public function destroy(StoreAlert $storeAlert)
    {
        $storeAlert->delete();
        return back()->with('success', 'Alert deleted successfully');
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:store_alerts,id',
            'orders.*.sort_order' => 'required|integer'
        ]);

        foreach ($request->orders as $order) {
            StoreAlert::where('id', $order['id'])->update(['sort_order' => $order['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
