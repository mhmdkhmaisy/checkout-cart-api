<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\VoteAdminController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CacheFileController;
use App\Http\Controllers\Admin\CacheBundleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\UpdateController;
use App\Http\Controllers\Admin\PerformanceController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\PromotionUserController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Admin routes (in production, add authentication middleware)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class)->except(['show', 'create']);
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
        Route::post('/finalize-upload', [CacheFileController::class, 'finalizeUpload'])->name('finalize-upload');
        Route::post('/check-duplicates', [CacheFileController::class, 'checkDuplicates'])->name('check-duplicates');
        Route::post('/store-tar', [CacheFileController::class, 'storeTar'])->name('store-tar');
        
        // Chunked upload routes
        Route::post('/chunked-init', [CacheFileController::class, 'chunkedUploadInit'])->name('chunked-init');
        Route::post('/chunked-upload', [CacheFileController::class, 'chunkedUpload'])->name('chunked-upload');
        Route::post('/chunked-complete', [CacheFileController::class, 'chunkedUploadComplete'])->name('chunked-complete');
        Route::post('/extract-file', [CacheFileController::class, 'extractFile'])->name('extract-file');
        Route::post('/zip-extract-patch', [CacheFileController::class, 'zipExtractPatch'])->name('zip-extract-patch');
        Route::get('/extraction-progress', [CacheFileController::class, 'extractionProgress'])->name('extraction-progress');
        Route::get('/zip-extraction-progress', [CacheFileController::class, 'zipExtractionProgress'])->name('zip-extraction-progress');
        Route::post('/bulk-delete', [CacheFileController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/delete-all', [CacheFileController::class, 'deleteAll'])->name('delete-all');
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
            
            // Patch insights & analysis routes
            // SECURITY NOTE: These routes expose patch metadata, file hashes, and system info
            // Ensure authentication middleware is added before production deployment
            Route::get('/compare', [CacheFileController::class, 'comparePatches'])->name('compare');
            Route::get('/{patch}/changelog', [CacheFileController::class, 'generateChangelog'])->name('changelog');
            Route::get('/file-history', [CacheFileController::class, 'getFileHistory'])->name('file-history');
            Route::get('/{patch}/verify', [CacheFileController::class, 'verifyIntegrity'])->name('verify');
        });
        
        // Wildcard delete route must be LAST to avoid catching specific routes
        Route::delete('/{cacheFile}', [CacheFileController::class, 'destroy'])->name('destroy');
    });

    // Event management
    Route::resource('events', EventController::class);

    // Update management
    Route::resource('updates', UpdateController::class);

    // Performance monitoring
    Route::prefix('performance')->name('performance.')->group(function () {
        Route::get('/', [PerformanceController::class, 'index'])->name('index');
        Route::get('/metrics', [PerformanceController::class, 'metrics'])->name('metrics');
        Route::get('/live', [PerformanceController::class, 'live'])->name('live');
        Route::get('/history', [PerformanceController::class, 'history'])->name('history');
        Route::get('/routes', [PerformanceController::class, 'routes'])->name('routes');
        Route::get('/slow-queries', [PerformanceController::class, 'slowQueries'])->name('slow-queries');
        Route::get('/alerts', [PerformanceController::class, 'alerts'])->name('alerts');
        Route::get('/summaries', [PerformanceController::class, 'summaries'])->name('summaries');
        Route::get('/queue-stats', [PerformanceController::class, 'queueStats'])->name('queue-stats');
        Route::delete('/clear-all', [PerformanceController::class, 'clearAll'])->name('clear-all');
    });

    // Promotions management
    Route::resource('promotions', PromotionController::class);
    Route::patch('promotions/{promotion}/toggle-active', [PromotionController::class, 'toggleActive'])->name('promotions.toggle-active');

});

// Payment completion pages (NEW ROUTES)
Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/success', [PaymentController::class, 'success'])->name('success');
    Route::get('/cancel', [PaymentController::class, 'cancel'])->name('cancel');
    Route::get('/receipt/{orderId}/download', [PaymentController::class, 'downloadPdf'])->name('download-pdf');
});

// Public client download routes
Route::get('/download/{os}/{version}', [ClientController::class, 'download'])->name('client.download');
Route::get('/manifest.json', [ClientController::class, 'manifest'])->name('client.manifest');

// Public patch download route (secured with signed URLs)
Route::get('/cache/patches/{filename}', [CacheFileController::class, 'downloadPublicPatch'])->name('patches.download.public');

// Public play page
Route::get('/play', function () {
    $clients = \App\Models\Client::getLatestClients();
    return view('play', compact('clients'));
})->name('play');

// Public store routes
Route::prefix('store')->name('store.')->group(function () {
    Route::get('/', [App\Http\Controllers\StoreController::class, 'index'])->name('index');
    Route::get('/terms', [App\Http\Controllers\StoreController::class, 'terms'])->name('terms');
    Route::post('/set-user', [App\Http\Controllers\StoreController::class, 'setUser'])->name('set-user');
    Route::post('/clear-user', [App\Http\Controllers\StoreController::class, 'clearUser'])->name('clear-user');
    Route::post('/add-to-cart', [App\Http\Controllers\StoreController::class, 'addToCart'])->name('add-to-cart');
    Route::post('/update-cart', [App\Http\Controllers\StoreController::class, 'updateCart'])->name('update-cart');
    Route::delete('/remove-from-cart/{productId}', [App\Http\Controllers\StoreController::class, 'removeFromCart'])->name('remove-from-cart');
    Route::get('/cart', [App\Http\Controllers\StoreController::class, 'getCart'])->name('get-cart');
    Route::post('/clear-cart', [App\Http\Controllers\StoreController::class, 'clearCart'])->name('clear-cart');
});

// Public events and updates routes
Route::get('/events', [HomeController::class, 'events'])->name('events');
Route::get('/updates', [HomeController::class, 'updates'])->name('updates');
Route::get('/updates/{slug}', [HomeController::class, 'showUpdate'])->name('updates.show');

// Public vote routes (if not already defined)
Route::prefix('vote')->name('vote.')->group(function () {
    Route::get('/', [VoteController::class, 'index'])->name('index');
    Route::post('/set-username', [VoteController::class, 'setUsername'])->name('set-username');
    Route::get('/status', [VoteController::class, 'getStatus'])->name('status');
    Route::get('/stats', [VoteController::class, 'stats'])->name('stats');
});

// Public promotion routes
Route::prefix('promotions')->name('promotions.')->group(function () {
    Route::get('/active', [PromotionUserController::class, 'getActive'])->name('active');
    Route::get('/progress/{username}', [PromotionUserController::class, 'getUserProgress'])->name('progress');
    Route::post('/{promotion}/claim', [PromotionUserController::class, 'claim'])->name('claim');
});

// Banner generation route
Route::get('/banner/generate', [App\Http\Controllers\BannerController::class, 'generate'])->name('banner.generate');
Route::get('/banner/demo', function () {
    return view('banner-demo');
})->name('banner.demo');