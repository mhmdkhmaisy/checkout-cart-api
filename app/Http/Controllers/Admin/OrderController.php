<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with('orderItems.product');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by claim state (derived from items)
        if ($request->filled('claim_state')) {
            $claimState = $request->claim_state;
            if ($claimState === 'unclaimed') {
                $query->whereHas('orderItems', function($q) {
                    $q->where('claimed', false);
                })->whereDoesntHave('orderItems', function($q) {
                    $q->where('claimed', true);
                });
            } elseif ($claimState === 'fully_claimed') {
                $query->whereHas('orderItems')
                      ->whereDoesntHave('orderItems', function($q) {
                          $q->where('claimed', false);
                      });
            } elseif ($claimState === 'partially_claimed') {
                $query->whereHas('orderItems', function($q) {
                    $q->where('claimed', true);
                })->whereHas('orderItems', function($q) {
                    $q->where('claimed', false);
                });
            }
        }

        // Filter by username
        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load('orderItems.product');
        return view('admin.orders.show', compact('order'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $order = Order::create([
            'username' => $request->username,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'status' => 'paid',
            'currency' => 'USD',
            'payment_id' => 'MANUAL-' . strtoupper(bin2hex(random_bytes(4))),
        ]);

        foreach ($request->products as $item) {
            $product = \App\Models\Product::find($item['id']);
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'price' => $product->price,
                'quantity' => $item['quantity'],
                'total_qty' => $product->qty_unit * $item['quantity'],
                'claimed' => false,
            ]);
        }

        return redirect()->route('admin.orders.index')->with('success', 'Order created successfully.');
    }
}