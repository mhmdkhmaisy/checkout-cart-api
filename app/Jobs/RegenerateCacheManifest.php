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
        
        $lock = Cache::lock($lockKey, 600);

        if ($lock->get()) {
            try {
                Artisan::call('cache:generate-manifest');
            } finally {
                $lock->release();
            }
        }
    }
}
