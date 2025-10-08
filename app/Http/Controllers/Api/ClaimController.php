<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ClaimController extends Controller
{
    public function claim(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string|max:100',
            'server_id' => 'nullable|string|max:100'
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Find all paid, unclaimed orders for this user
                $orders = Order::paid()
                    ->unclaimed()
                    ->forUser($request->username, $request->server_id)
                    ->with(['orderItems.product'])
                    ->get();

                if ($orders->isEmpty()) {
                    return response()->json([
                        'success' => true,
                        'items' => [],
                        'message' => 'No items to claim'
                    ]);
                }

                $claimableItems = [];
                $orderItemsToUpdate = [];

                foreach ($orders as $order) {
                    foreach ($order->orderItems as $orderItem) {
                        if (!$orderItem->claimed && $orderItem->product) {
                            $claimableItems[] = [
                                'item_id' => $orderItem->product->item_id,
                                'qty' => $orderItem->total_qty
                            ];
                            $orderItemsToUpdate[] = $orderItem->id;
                        }
                    }
                }

                if (empty($claimableItems)) {
                    return response()->json([
                        'success' => true,
                        'items' => [],
                        'message' => 'No items to claim'
                    ]);
                }

                // Mark order items as claimed
                OrderItem::whereIn('id', $orderItemsToUpdate)
                    ->update(['claimed' => true]);

                // Update orders claim state if all items are claimed
                foreach ($orders as $order) {
                    $unclaimedCount = $order->orderItems()
                        ->where('claimed', false)
                        ->count();
                    
                    if ($unclaimedCount === 0) {
                        $order->update(['claim_state' => 'claimed']);
                    }
                }

                return response()->json([
                    'success' => true,
                    'items' => $claimableItems,
                    'message' => 'Items claimed successfully'
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to claim items'
            ], 500);
        }
    }
}