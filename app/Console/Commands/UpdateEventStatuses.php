<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use Carbon\Carbon;

class UpdateEventStatuses extends Command
{
    protected $signature = 'events:update-statuses';
    protected $description = 'Update event statuses based on current time';

    public function handle()
    {
        $now = Carbon::now();
        $updatedCount = 0;

        $activeUpdates = Event::where('start_at', '<=', $now)
            ->where(function($query) use ($now) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>', $now);
            })
            ->where('status', '!=', 'active')
            ->update(['status' => 'active']);

        $endedUpdates = Event::whereNotNull('end_at')
            ->where('end_at', '<', $now)
            ->where('status', '!=', 'ended')
            ->update(['status' => 'ended']);

        $updatedCount = $activeUpdates + $endedUpdates;

        $this->info("Updated {$updatedCount} event(s) status.");
        
        return 0;
    }
}
