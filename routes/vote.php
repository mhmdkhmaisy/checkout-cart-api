<?php

use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

// Public vote routes
Route::prefix('vote')->name('vote.')->group(function () {
    Route::get('/', [VoteController::class, 'index'])->name('index');
    Route::post('/vote/{site}', [VoteController::class, 'vote'])->name('submit');
    Route::any('/callback', [VoteController::class, 'callback'])->name('callback');
    Route::get('/stats', [VoteController::class, 'stats'])->name('stats');
    Route::get('/user-votes', [VoteController::class, 'getUserVotes'])->name('user-votes');
});