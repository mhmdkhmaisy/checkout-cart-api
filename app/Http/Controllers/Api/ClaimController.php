<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ClaimController extends Controller
{
    public function claim(string $username, Request $request): JsonResponse
    {
        // Decode URL-encoded username (handles %20 and + for spaces)
        $username = urldecode($username);
        
        $request->validate([
            'server_id' => 'nullable|string|max:100'
        ]);

        try {
            return DB::transaction(function () use ($username, $request) {
                // Find all paid, unclaimed orders for this user
                $orders = Order::paid()
                    ->unclaimed()
                    ->forUser($username, $request->server_id)
                    ->with(['orderItems.product.bundleItems'])
                    ->get();

                if ($orders->isEmpty()) {
                    return response()->json([
                        'success' => true,
                        'items' => [],
                        'total_gross' => 0,
                        'message' => 'No items to claim'
                    ]);
                }

                $claimableItems = [];
                $orderItemsToUpdate = [];
                $totalGross = 0;

                foreach ($orders as $order) {
                    $totalGross += (float) $order->amount;
                    foreach ($order->orderItems as $orderItem) {
                        if (!$orderItem->claimed && $orderItem->product) {
                            $expandedItems = $this->expandOrderItem($orderItem);
                            $claimableItems = array_merge($claimableItems, $expandedItems);
                            $orderItemsToUpdate[] = $orderItem->id;
                        }
                    }
                }

                if (empty($claimableItems)) {
                    return response()->json([
                        'success' => true,
                        'items' => [],
                        'total_gross' => $totalGross,
                        'message' => 'No items to claim'
                    ]);
                }

                // Mark order items as claimed
                OrderItem::whereIn('id', $orderItemsToUpdate)
                    ->update(['claimed' => true]);

                return response()->json([
                    'success' => true,
                    'items' => $claimableItems,
                    'total_gross' => $totalGross,
                    'message' => 'Items claimed successfully'
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Error claiming items for user ' . $username . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to claim items'
            ], 500);
        }
    }

    /**
     * Expand an order item into claimable items.
     * If the product is a bundle, expand it into individual items.
     * Otherwise, return the single product as an item.
     */
    private function expandOrderItem(OrderItem $orderItem): array
    {
        $product = $orderItem->product;
        $items = [];

        // Check if product is a bundle
        if ($product->bundleItems->isNotEmpty()) {
            // Expand bundle items
            foreach ($product->bundleItems as $bundleItem) {
                $items[] = [
                    'item_id' => $bundleItem->item_id,
                    'qty' => $bundleItem->qty_unit * $orderItem->qty_units
                ];
            }
        } else {
            // Single product item
            $items[] = [
                'item_id' => $product->item_id,
                'qty' => $orderItem->total_qty
            ];
        }

        return $items;
    }

    public function claimVote(string $playerName): JsonResponse
    {
        // Decode URL-encoded username (handles %20 and + for spaces)
        $playerName = urldecode($playerName);
        
        try {
            return DB::transaction(function () use ($playerName) {
                $unclaimedVotes = Vote::where('username', $playerName)
                    ->whereNotNull('callback_date')
                    ->where('claimed', false)
                    ->with('site')
                    ->get();

                if ($unclaimedVotes->isEmpty()) {
                    return response()->json([
                        'success' => true,
                        'votes' => [],
                        'message' => 'No unclaimed votes found'
                    ]);
                }

                $voteIds = $unclaimedVotes->pluck('id')->toArray();

                Vote::whereIn('id', $voteIds)->update(['claimed' => true]);

                $votes = $unclaimedVotes->map(function ($vote) {
                    return [
                        'id' => $vote->id,
                        'site_name' => $vote->site ? $vote->site->title : 'Unknown',
                        'site_id' => $vote->site_id,
                        'voted_at' => $vote->callback_date->toIso8601String(),
                    ];
                });

                return response()->json([
                    'success' => true,
                    'votes' => $votes,
                    'count' => count($votes),
                    'message' => count($votes) . ' vote(s) claimed successfully'
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Error claiming votes for player ' . $playerName . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to claim votes'
            ], 500);
        }
    }
}