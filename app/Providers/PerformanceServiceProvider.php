<?php

namespace App\Providers;

use App\Models\PerformanceLog;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;

class PerformanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (!config('performance.enabled')) {
            return;
        }

        $this->registerDatabaseQueryListener();
        $this->registerQueueListeners();
    }

    protected function registerDatabaseQueryListener(): void
    {
        DB::listen(function ($query) {
            try {
                $time = $query->time;
                
                if ($time > config('performance.thresholds.query_time', 200)) {
                    PerformanceLog::recordMetric('slow_query', 'database', $time, [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'connection' => $query->connectionName,
                    ]);
                }

                PerformanceLog::recordMetric('db_query', $query->connectionName, $time, [
                    'query_hash' => md5($query->sql),
                ]);
            } catch (\Exception $e) {
            }
        });
    }

    protected function registerQueueListeners(): void
    {
        Queue::after(function (JobProcessed $event) {
            try {
                $jobName = $event->job->resolveName();
                
                PerformanceLog::recordMetric('queue_job', $jobName, 0, [
                    'status' => 'success',
                    'connection' => $event->connectionName,
                    'queue' => $event->job->getQueue(),
                ]);
            } catch (\Exception $e) {
            }
        });

        Queue::failing(function (JobFailed $event) {
            try {
                $jobName = $event->job->resolveName();
                
                PerformanceLog::recordMetric('queue_job_failed', $jobName, 1, [
                    'exception' => $event->exception->getMessage(),
                    'connection' => $event->connectionName,
                    'queue' => $event->job->getQueue(),
                ]);
            } catch (\Exception $e) {
            }
        });
    }
}
