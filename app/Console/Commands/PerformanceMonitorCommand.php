<?php

namespace App\Console\Commands;

use App\Services\PerformanceCollector;
use Illuminate\Console\Command;

class PerformanceMonitorCommand extends Command
{
    protected $signature = 'performance:monitor
                          {--interval=60 : Aggregation interval in seconds}
                          {--once : Run aggregation once and exit}';

    protected $description = 'Monitor and aggregate performance metrics';

    protected PerformanceCollector $collector;

    public function __construct(PerformanceCollector $collector)
    {
        parent::__construct();
        $this->collector = $collector;
    }

    public function handle(): int
    {
        if (!config('performance.enabled')) {
            $this->error('Performance monitoring is disabled in config.');
            return 1;
        }

        $interval = (int) $this->option('interval');
        $once = $this->option('once');

        $this->info('Performance monitoring daemon started...');

        do {
            $this->runAggregation();

            if (!$once) {
                $this->info("Sleeping for {$interval} seconds...");
                sleep($interval);
            }
        } while (!$once);

        $this->info('Performance monitoring daemon stopped.');
        return 0;
    }

    protected function runAggregation(): void
    {
        try {
            $this->line('Collecting system metrics...');
            $systemMetrics = $this->collector->collectSystemMetrics();

            $this->line('Aggregating performance data...');
            $aggregatedMetrics = $this->collector->aggregateMetrics(60);

            $this->line('Creating performance summary...');
            $summary = $this->collector->createSummary('1h', $aggregatedMetrics);

            $this->line('Checking thresholds...');
            $alerts = $this->collector->checkThresholds();

            if (!empty($alerts)) {
                $this->warn('⚠ Alerts detected:');
                foreach ($alerts as $alert) {
                    $this->warn("  [{$alert['severity']}] {$alert['message']}");
                }
            }

            $this->info('✓ Aggregation completed successfully.');
        } catch (\Exception $e) {
            $this->error('Error during aggregation: ' . $e->getMessage());
        }
    }
}
