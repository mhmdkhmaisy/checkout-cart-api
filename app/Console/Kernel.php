<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Clean up expired cache bundles every hour
        $schedule->command('cache:cleanup-bundles')->hourly();
        
        // Regenerate cache manifest daily at 3 AM
        $schedule->command('cache:generate-manifest')->dailyAt('03:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the commands to register.
     */
    protected $commands = [
        Commands\GenerateCacheManifest::class,
        Commands\CleanupCacheBundles::class,
    ];
}