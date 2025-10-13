<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Sales statistics
        $totalRevenue = Order::where('status', 'paid')->sum('amount');
        $totalOrders = Order::count();
        $paidOrders = Order::where('status', 'paid')->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $unclaimedOrders = OrderItem::where('claimed', false)
            ->count();

        // Recent orders
        $recentOrders = Order::with('orderItems.product')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top products
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'paid')
            ->select('products.product_name', DB::raw('SUM(order_items.qty_units) as total_sold'))
            ->groupBy('products.id', 'products.product_name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Monthly revenue chart data
        $monthlyRevenue = Order::where('status', 'paid')
            ->select(
                DB::raw("strftime('%Y', created_at) as year"),
                DB::raw("strftime('%m', created_at) as month"),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->reverse();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'paidOrders',
            'pendingOrders',
            'unclaimedOrders',
            'recentOrders',
            'topProducts',
            'monthlyRevenue'
        ));
    }
}