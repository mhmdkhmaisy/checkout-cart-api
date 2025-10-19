<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerformanceLog;
use App\Models\PerformanceSummary;
use App\Services\PerformanceCollector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    protected PerformanceCollector $collector;

    public function __construct(PerformanceCollector $collector)
    {
        $this->collector = $collector;
    }

    public function index(): View
    {
        return view('admin.performance.index');
    }

    public function metrics(): JsonResponse
    {
        try {
            $liveMetrics = $this->collector->getLiveMetrics();
            $latestSummary = PerformanceSummary::getLatestSummary('1h');
            $alerts = $this->collector->checkThresholds();

            return response()->json([
                'success' => true,
                'data' => [
                    'live' => $liveMetrics,
                    'summary' => $latestSummary,
                    'alerts' => $alerts,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function live(): JsonResponse
    {
        try {
            $metrics = $this->collector->getLiveMetrics();
            
            return response()->json([
                'success' => true,
                'data' => $metrics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function history(Request $request): JsonResponse
    {
        try {
            $metricType = $request->get('type', 'request');
            $minutes = (int) $request->get('minutes', 60);

            $logs = PerformanceLog::where('metric_type', $metricType)
                ->where('created_at', '>=', now()->subMinutes($minutes))
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function routes(): JsonResponse
    {
        try {
            $minutes = 60;
            $since = now()->subMinutes($minutes);

            $routeStats = PerformanceLog::where('metric_type', 'request')
                ->where('created_at', '>=', $since)
                ->selectRaw('
                    identifier as route,
                    COUNT(*) as request_count,
                    AVG(value) as avg_time,
                    MAX(value) as max_time,
                    MIN(value) as min_time
                ')
                ->groupBy('identifier')
                ->orderByDesc('avg_time')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $routeStats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function slowQueries(): JsonResponse
    {
        try {
            $minutes = 60;
            
            $queries = PerformanceLog::where('metric_type', 'slow_query')
                ->where('created_at', '>=', now()->subMinutes($minutes))
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $queries,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function alerts(): JsonResponse
    {
        try {
            $alerts = $this->collector->checkThresholds();

            return response()->json([
                'success' => true,
                'data' => $alerts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function summaries(Request $request): JsonResponse
    {
        try {
            $timeframe = $request->get('timeframe', '1h');
            $limit = (int) $request->get('limit', 24);

            $summaries = PerformanceSummary::getSummariesByTimeframe($timeframe, $limit);

            return response()->json([
                'success' => true,
                'data' => $summaries,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function queueStats(): JsonResponse
    {
        try {
            $minutes = 60;
            $since = now()->subMinutes($minutes);

            $successfulJobs = PerformanceLog::where('metric_type', 'queue_job')
                ->where('created_at', '>=', $since)
                ->count();

            $failedJobs = PerformanceLog::where('metric_type', 'queue_job_failed')
                ->where('created_at', '>=', $since)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'successful_jobs' => $successfulJobs,
                    'failed_jobs' => $failedJobs->count(),
                    'recent_failures' => $failedJobs,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function clearAll(): JsonResponse
    {
        try {
            $logsDeleted = PerformanceLog::count();
            PerformanceLog::truncate();
            
            $summariesDeleted = PerformanceSummary::count();
            PerformanceSummary::truncate();

            return response()->json([
                'success' => true,
                'message' => 'All performance data cleared successfully',
                'data' => [
                    'logs_deleted' => $logsDeleted,
                    'summaries_deleted' => $summariesDeleted,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
