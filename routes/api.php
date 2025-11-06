<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ClaimController;
use App\Http\Controllers\Api\CacheController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Webhook routes (no auth required) - Using the Api namespace controller
Route::post('/webhooks/paypal', [WebhookController::class, 'paypal']);
Route::post('/webhooks/coinbase', [WebhookController::class, 'coinbase']);

// Checkout routes
// Throttle checkout to 1 request per second per user to prevent spam clicking
Route::post('/checkout', [CheckoutController::class, 'checkout']);
    // ->middleware('throttle:1,1'); // 1 request per 1 second
Route::get('/checkout/paypal/success', [CheckoutController::class, 'paypalSuccess']);
Route::get('/checkout/paypal/cancel', [CheckoutController::class, 'paypalCancel']);
Route::get('/checkout/coinbase/success', [CheckoutController::class, 'coinbaseSuccess']);
Route::get('/checkout/coinbase/cancel', [CheckoutController::class, 'coinbaseCancel']);

// Claim routes
Route::get('/claim/{username}', [ClaimController::class, 'claim']);

// Product routes (for testing)
Route::get('/products', [ProductController::class, 'index']);

// Admin routes (add auth middleware as needed)
Route::prefix('admin')->group(function () {
    Route::get('/orders/logs', [AdminController::class, 'orderLogs']);
    Route::get('/orders/{orderId}/events', [AdminController::class, 'orderEvents']);
    Route::get('/orders/stats', [AdminController::class, 'orderStats']);
    Route::patch('/orders/{orderId}/status', [AdminController::class, 'updateOrderStatus']);
});

// Enhanced Cache API routes (public access for launcher/client)
Route::prefix('cache')->name('api.cache.')->group(function () {
    // Core manifest and download endpoints
    Route::get('/manifest', [CacheController::class, 'manifest'])->name('manifest');
    Route::get('/download', [CacheController::class, 'download'])->name('download');
    Route::get('/file/{filename}', [CacheController::class, 'downloadFile'])->name('file');
    
    // Directory structure and navigation
    Route::get('/directory-tree', [CacheController::class, 'directoryTree'])->name('directory-tree');
    Route::get('/search', [CacheController::class, 'search'])->name('search');
    
    // Statistics and monitoring
    Route::get('/stats', [CacheController::class, 'stats'])->name('stats');
    
    // Patch download endpoints (for launcher/client)
    Route::prefix('patches')->name('patches.')->group(function () {
        Route::get('/{patch}/download', [\App\Http\Controllers\Admin\CacheFileController::class, 'downloadPatch'])->name('download');
        Route::post('/download-combined', [\App\Http\Controllers\Admin\CacheFileController::class, 'downloadCombinedPatches'])->name('download-combined');
        Route::get('/latest', [CacheFileController::class, 'getLatestVersion'])->name('latest');
        Route::post('/check-updates', [CacheFileController::class, 'checkForUpdates'])->name('check-updates');
 
    });
});

// Updates API routes (public access with rate limiting)
Route::prefix('updates')->name('api.updates.')->middleware('throttle:60,1')->group(function () {
    Route::get('/latest', [\App\Http\Controllers\Api\UpdateApiController::class, 'latest'])->name('latest');
    Route::get('/', [\App\Http\Controllers\Api\UpdateApiController::class, 'index'])->name('index');
    Route::get('/{slug}', [\App\Http\Controllers\Api\UpdateApiController::class, 'show'])->name('show')->middleware('throttle:30,1');
});