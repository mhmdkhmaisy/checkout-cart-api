<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\OrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Admin routes (in production, add authentication middleware)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('products', ProductController::class);
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Fixed order logs and events routes
    Route::get('order-logs', function () {
        $logs = \App\Models\OrderLog::with('order')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.orders.logs', compact('logs'));
    })->name('orders.logs');
    
    Route::get('orders/{order}/events', function ($order) {
        $order = \App\Models\Order::findOrFail($order);
        $events = $order->events()->orderBy('created_at', 'desc')->get();
        return view('admin.orders.events', compact('order', 'events'));
    })->name('orders.events');
    
    Route::get('api-docs', function () {
        return view('admin.api-docs');
    })->name('api-docs');
});

// Payment completion pages (NEW ROUTES)
Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/success', [PaymentController::class, 'success'])->name('success');
    Route::get('/cancel', [PaymentController::class, 'cancel'])->name('cancel');
    Route::get('/receipt/{orderId}/download', [PaymentController::class, 'downloadPdf'])->name('download-pdf');
});
