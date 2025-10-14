<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\VoteAdminController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CacheFileController;
use App\Http\Controllers\Admin\CacheBundleController;
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

        // Vote management
    Route::prefix('vote')->name('vote.')->group(function () {
        Route::get('/', [VoteAdminController::class, 'index'])->name('index');
        Route::get('/sites', [VoteAdminController::class, 'index'])->name('sites');
        Route::get('/sites/create', [VoteAdminController::class, 'create'])->name('sites.create');
        Route::post('/sites', [VoteAdminController::class, 'store'])->name('sites.store');
        Route::get('/sites/{voteSite}', [VoteAdminController::class, 'show'])->name('sites.show');
        Route::get('/sites/{voteSite}/edit', [VoteAdminController::class, 'edit'])->name('sites.edit');
        Route::put('/sites/{voteSite}', [VoteAdminController::class, 'update'])->name('sites.update');
        Route::patch('/sites/{voteSite}/toggle', [VoteAdminController::class, 'toggleActive'])->name('sites.toggle');
        Route::delete('/sites/{voteSite}', [VoteAdminController::class, 'destroy'])->name('sites.destroy');
        Route::get('/votes', [VoteAdminController::class, 'votes'])->name('votes');
        Route::patch('/votes/{vote}/claim', [VoteAdminController::class, 'claimVote'])->name('votes.claim');
        Route::get('/stats', [VoteAdminController::class, 'stats'])->name('stats');
    });

    // Client management
    Route::resource('clients', ClientController::class);
    Route::patch('clients/{client}/toggle', [ClientController::class, 'toggle'])->name('clients.toggle');
    Route::get('clients-manifest', [ClientController::class, 'manifest'])->name('clients.manifest');

        // Cache management routes
    Route::prefix('cache')->name('cache.')->group(function () {
        Route::get('/', [CacheFileController::class, 'index'])->name('index');
        Route::get('/create', [CacheFileController::class, 'create'])->name('create');
        Route::post('/', [CacheFileController::class, 'store'])->name('store');
        Route::post('/check-duplicates', [CacheFileController::class, 'checkDuplicates'])->name('check-duplicates');
        Route::post('/store-tar', [CacheFileController::class, 'storeTar'])->name('store-tar');
        Route::post('/extract-file', [CacheFileController::class, 'extractFile'])->name('extract-file');
        Route::get('/extraction-progress', [CacheFileController::class, 'extractionProgress'])->name('extraction-progress');
        Route::post('/bulk-delete', [CacheFileController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/delete-all', [CacheFileController::class, 'deleteAll'])->name('delete-all');
        Route::delete('/{cacheFile}', [CacheFileController::class, 'destroy'])->name('destroy');
        Route::post('/regenerate-manifest', [CacheFileController::class, 'regenerateManifest'])->name('regenerate-manifest');
        Route::get('/download-manifest', [CacheFileController::class, 'downloadManifest'])->name('download-manifest');
        Route::get('/upload-progress', [CacheFileController::class, 'uploadProgress'])->name('upload-progress');
        
        // Patch management routes (integrated with file manager)
        Route::prefix('patches')->name('patches.')->group(function () {
            Route::get('/latest', [CacheFileController::class, 'getLatestVersion'])->name('latest');
            Route::post('/check-updates', [CacheFileController::class, 'checkForUpdates'])->name('check-updates');
            Route::get('/{patch}/download', [CacheFileController::class, 'downloadPatch'])->name('download');
            Route::post('/download-combined', [CacheFileController::class, 'downloadCombinedPatches'])->name('download-combined');
            Route::post('/merge', [CacheFileController::class, 'mergePatches'])->name('merge');
            Route::delete('/{patch}', [CacheFileController::class, 'deletePatch'])->name('delete');
            Route::post('/clear-all', [CacheFileController::class, 'clearAllPatches'])->name('clear-all');
        });
    });


});

// Payment completion pages (NEW ROUTES)
Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/success', [PaymentController::class, 'success'])->name('success');
    Route::get('/cancel', [PaymentController::class, 'cancel'])->name('cancel');
    Route::get('/receipt/{orderId}/download', [PaymentController::class, 'downloadPdf'])->name('download-pdf');
});

// Public vote routes
Route::prefix('vote')->name('vote.')->group(function () {
    Route::get('/', [VoteController::class, 'index'])->name('index');
    Route::post('/set-username', [VoteController::class, 'setUsername'])->name('set-username');
    Route::post('/{site}', [VoteController::class, 'vote'])->name('submit');
    Route::any('/callback', [VoteController::class, 'callback'])->name('callback');
    Route::get('/stats', [VoteController::class, 'stats'])->name('stats');
    Route::get('/user-votes', [VoteController::class, 'getUserVotes'])->name('user-votes');
});

// Public client download routes
Route::get('/download/{os}/{version}', [ClientController::class, 'download'])->name('client.download');
Route::get('/manifest.json', [ClientController::class, 'manifest'])->name('client.manifest');

// Public play page
Route::get('/play', function () {
    $clients = \App\Models\Client::getLatestClients();
    return view('play', compact('clients'));
})->name('play');