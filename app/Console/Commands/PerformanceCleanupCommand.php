<?php

namespace App\Console\Commands;

use App\Models\PerformanceLog;
use App\Models\PerformanceSummary;
use Illuminate\Console\Command;

class PerformanceCleanupCommand extends Command
{
    protected $signature = 'performance:cleanup
                          {--days= : Number of days to retain (defaults to config)}';

    protected $description = 'Cleanup old performance logs and summaries';

    public function handle(): int
    {
        $days = $this->option('days');
        
        if ($days !== null) {
            $days = (int) $days;
        }

        $this->info('Cleaning up old performance data...');

        try {
            $logsDeleted = PerformanceLog::cleanupOldLogs($days);
            $this->info("✓ Deleted {$logsDeleted} old performance logs.");

            $summariesDeleted = PerformanceSummary::cleanupOldSummaries($days);
            $this->info("✓ Deleted {$summariesDeleted} old performance summaries.");

            $this->info('Cleanup completed successfully.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error during cleanup: ' . $e->getMessage());
            return 1;
        }
    }
}
