<?php

namespace App\Services;

use App\Models\PerformanceLog;
use App\Models\PerformanceSummary;
use Illuminate\Support\Facades\DB;

class PerformanceCollector
{
    public function collectSystemMetrics(): array
    {
        $metrics = [];

        $metrics['cpu_load'] = $this->getCpuLoad();
        $metrics['memory_usage'] = memory_get_usage(true);
        $metrics['memory_peak'] = memory_get_peak_usage(true);
        $metrics['disk_free'] = $this->getDiskFreeSpace();
        $metrics['disk_total'] = disk_total_space('/');

        try {
            PerformanceLog::recordMetric('system_cpu', 'server', $metrics['cpu_load']);
            PerformanceLog::recordMetric('system_memory', 'server', $metrics['memory_usage']);
            PerformanceLog::recordMetric('system_disk', 'server', $metrics['disk_free']);
        } catch (\Exception $e) {
        }

        return $metrics;
    }

    protected function getCpuLoad(): float
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0] ?? 0.0;
        }
        return 0.0;
    }

    protected function getDiskFreeSpace(): int
    {
        $free = disk_free_space('/');
        return $free !== false ? $free : 0;
    }

    public function aggregateMetrics(int $minutes = 60): array
    {
        $since = now()->subMinutes($minutes);
        
        $requestMetrics = $this->aggregateRequestMetrics($since);
        $dbMetrics = $this->aggregateDatabaseMetrics($since);
        $queueMetrics = $this->aggregateQueueMetrics($since);
        $systemMetrics = $this->aggregateSystemMetrics($since);

        return array_merge($requestMetrics, $dbMetrics, $queueMetrics, $systemMetrics);
    }

    protected function aggregateRequestMetrics($since): array
    {
        $requests = PerformanceLog::where('metric_type', 'request')
            ->where('created_at', '>=', $since)
            ->get();

        $totalRequests = $requests->count();
        $avgRequestTime = $requests->avg('value');
        $failedRequests = $requests->filter(function ($log) {
            $metadata = $log->metadata;
            return isset($metadata['status_code']) && $metadata['status_code'] >= 400;
        })->count();

        $slowestRoute = null;
        $slowestRouteTime = 0;
        $routeTimes = [];

        foreach ($requests as $request) {
            $route = $request->identifier;
            if (!isset($routeTimes[$route])) {
                $routeTimes[$route] = [];
            }
            $routeTimes[$route][] = $request->value;

            if ($request->value > $slowestRouteTime) {
                $slowestRouteTime = $request->value;
                $slowestRoute = $route;
            }
        }

        return [
            'total_requests' => $totalRequests,
            'avg_request_time' => $avgRequestTime,
            'failed_requests' => $failedRequests,
            'slowest_route' => $slowestRoute,
            'slowest_route_time' => $slowestRouteTime,
            'route_times' => $routeTimes,
        ];
    }

    protected function aggregateDatabaseMetrics($since): array
    {
        $queries = PerformanceLog::where('metric_type', 'db_query')
            ->where('created_at', '>=', $since)
            ->get();

        $slowQueries = PerformanceLog::where('metric_type', 'slow_query')
            ->where('created_at', '>=', $since)
            ->count();

        return [
            'avg_query_time' => $queries->avg('value'),
            'slow_queries_count' => $slowQueries,
        ];
    }

    protected function aggregateQueueMetrics($since): array
    {
        $failedJobs = PerformanceLog::where('metric_type', 'queue_job_failed')
            ->where('created_at', '>=', $since)
            ->count();

        return [
            'failed_jobs' => $failedJobs,
        ];
    }

    protected function aggregateSystemMetrics($since): array
    {
        $cpuLogs = PerformanceLog::where('metric_type', 'system_cpu')
            ->where('created_at', '>=', $since)
            ->get();

        $memoryLogs = PerformanceLog::where('metric_type', 'system_memory')
            ->where('created_at', '>=', $since)
            ->get();

        return [
            'avg_cpu_load' => $cpuLogs->avg('value'),
            'max_memory_usage' => $memoryLogs->max('value'),
        ];
    }

    public function createSummary(string $timeframe, array $metrics): PerformanceSummary
    {
        PerformanceSummary::$disableLogging = true;
        
        try {
            return PerformanceSummary::create([
                'timeframe' => $timeframe,
                'avg_request_time' => $metrics['avg_request_time'] ?? null,
                'max_memory_usage' => $metrics['max_memory_usage'] ?? null,
                'avg_cpu_load' => $metrics['avg_cpu_load'] ?? null,
                'slowest_route' => $metrics['slowest_route'] ?? null,
                'slowest_route_time' => $metrics['slowest_route_time'] ?? null,
                'total_requests' => $metrics['total_requests'] ?? 0,
                'failed_requests' => $metrics['failed_requests'] ?? 0,
                'slow_queries_count' => $metrics['slow_queries_count'] ?? 0,
                'avg_query_time' => $metrics['avg_query_time'] ?? null,
                'failed_jobs' => $metrics['failed_jobs'] ?? 0,
                'created_at' => now(),
            ]);
        } finally {
            PerformanceSummary::$disableLogging = false;
        }
    }

    public function getLiveMetrics(): array
    {
        $recentRequests = PerformanceLog::getRecentMetrics('request', 5);
        $recentSlowQueries = PerformanceLog::getRecentMetrics('slow_query', 5);
        
        return [
            'current_cpu' => $this->getCpuLoad(),
            'current_memory' => memory_get_usage(true),
            'current_memory_peak' => memory_get_peak_usage(true),
            'disk_free' => $this->getDiskFreeSpace(),
            'disk_total' => disk_total_space('/'),
            'recent_requests_count' => $recentRequests->count(),
            'recent_avg_time' => $recentRequests->avg('value'),
            'recent_slow_queries' => $recentSlowQueries->count(),
        ];
    }

    public function checkThresholds(): array
    {
        $alerts = [];
        $thresholds = config('performance.thresholds');

        $cpuLoad = $this->getCpuLoad();
        if ($cpuLoad > $thresholds['cpu_load']) {
            $alerts[] = [
                'type' => 'cpu',
                'severity' => 'warning',
                'message' => "CPU load is high: {$cpuLoad}",
                'threshold' => $thresholds['cpu_load'],
                'current' => $cpuLoad,
            ];
        }

        $diskFree = $this->getDiskFreeSpace();
        $diskTotal = disk_total_space('/');
        $diskFreePercent = $diskTotal > 0 ? ($diskFree / $diskTotal) : 1;
        
        if ($diskFreePercent < $thresholds['disk_free']) {
            $alerts[] = [
                'type' => 'disk',
                'severity' => 'critical',
                'message' => "Low disk space: " . round($diskFreePercent * 100, 2) . "% free",
                'threshold' => $thresholds['disk_free'] * 100,
                'current' => $diskFreePercent * 100,
            ];
        }

        $memoryUsage = memory_get_usage(true) / 1024 / 1024;
        if ($memoryUsage > $thresholds['memory_usage']) {
            $alerts[] = [
                'type' => 'memory',
                'severity' => 'warning',
                'message' => "High memory usage: {$memoryUsage} MB",
                'threshold' => $thresholds['memory_usage'],
                'current' => $memoryUsage,
            ];
        }

        $slowRequests = PerformanceLog::getRecentMetrics('slow_request', 5);
        if ($slowRequests->count() > 10) {
            $alerts[] = [
                'type' => 'slow_requests',
                'severity' => 'warning',
                'message' => "Multiple slow requests detected in last 5 minutes",
                'count' => $slowRequests->count(),
            ];
        }

        return $alerts;
    }
}
