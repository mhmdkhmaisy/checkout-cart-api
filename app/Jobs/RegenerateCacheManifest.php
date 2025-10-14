<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class RegenerateCacheManifest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 3;

    public function handle(): void
    {
        $lockKey = 'manifest_regeneration_lock';
        $debounceKey = 'manifest_last_regenerated';
        
        // Debounce: Check if manifest was regenerated recently (within last 3 seconds)
        $lastRegenerated = Cache::get($debounceKey);
        if ($lastRegenerated && (time() - $lastRegenerated) < 3) {
            // Skip regeneration if it happened less than 3 seconds ago
            return;
        }
        
        $lock = Cache::lock($lockKey, 600);

        if ($lock->get()) {
            try {
                // Double check after acquiring lock
                $lastRegenerated = Cache::get($debounceKey);
                if ($lastRegenerated && (time() - $lastRegenerated) < 3) {
                    return;
                }
                
                Artisan::call('cache:generate-manifest');
                
                // Mark the time of this regeneration (expires after 10 seconds)
                Cache::put($debounceKey, time(), 10);
            } finally {
                $lock->release();
            }
        }
    }
}
