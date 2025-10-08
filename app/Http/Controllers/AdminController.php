<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderLog;
use App\Models\OrderEvent;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display order logs with pagination and filters
     */
    public function orderLogs(Request $request)
    {
        $query = OrderLog::with('order')
            ->orderBy('updated_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        if ($request->filled('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        if ($request->filled('event_type')) {
            $query->where('last_event', 'like', '%' . $request->event_type . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('updated_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('updated_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $logs,
            'filters' => [
                'statuses' => ['pending', 'paid', 'failed', 'refunded', 'claimed', 'reserved'],
                'current_filters' => $request->only(['status', 'username', 'order_id', 'event_type', 'date_from', 'date_to'])
            ]
        ]);
    }

    /**
     * Get full event history for a specific order
     */
    public function orderEvents($orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $events = OrderEvent::where('order_id', $orderId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $order,
                'events' => $events
            ]
        ]);
    }

    /**
     * Get order statistics
     */
    public function orderStats()
    {
        $stats = [
            'total_orders' => Order::count(),
            'by_status' => Order::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'recent_activity' => OrderEvent::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get(),
            'top_events' => OrderEvent::selectRaw('event_type, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('event_type')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Manual order status update (admin action)
     */
    public function updateOrderStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,failed,refunded,claimed,reserved',
            'reason' => 'required|string|max:255'
        ]);

        $order = Order::findOrFail($orderId);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return response()->json([
                'success' => false,
                'error' => 'Order is already in the requested status'
            ], 400);
        }

        // Update order
        $order->update(['status' => $newStatus]);

        // Log the manual update
        $webhookService = app(\App\Services\WebhookService::class);
        $webhookService->logWebhookEvent(
            $order->id,
            'admin.status_update',
            $newStatus,
            [
                'admin_reason' => $request->reason,
                'old_status' => $oldStatus,
                'updated_by' => auth()->user()->id ?? 'system'
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]
        ]);
    }
}