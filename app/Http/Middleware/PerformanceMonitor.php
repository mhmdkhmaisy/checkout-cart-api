<?php

namespace App\Http\Middleware;

use App\Models\PerformanceLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('performance.enabled')) {
            return $next($request);
        }

        $excludedRoutes = config('performance.excluded_routes', []);
        $currentPath = $request->path();
        
        foreach ($excludedRoutes as $excluded) {
            if (str_starts_with($currentPath, $excluded)) {
                return $next($request);
            }
        }

        if (config('performance.sampling.enabled')) {
            $samplingRate = config('performance.sampling.rate', 0.1);
            if (mt_rand() / mt_getrandmax() > $samplingRate) {
                return $next($request);
            }
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000;
        $memoryPeak = memory_get_peak_usage(true);
        $memoryUsed = $memoryPeak - $startMemory;

        $route = $request->route() ? $request->route()->getName() ?? $request->path() : $request->path();
        
        try {
            PerformanceLog::recordMetric('request', $route, $duration, [
                'method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'memory_used' => $memoryUsed,
                'memory_peak' => $memoryPeak,
                'url' => $request->fullUrl(),
            ]);

            $threshold = config('performance.thresholds.request_time', 1000);
            if ($duration > $threshold) {
                PerformanceLog::recordMetric('slow_request', $route, $duration, [
                    'method' => $request->method(),
                    'threshold' => $threshold,
                    'url' => $request->fullUrl(),
                ]);
            }
        } catch (\Exception $e) {
        }

        return $response;
    }
}
