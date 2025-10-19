<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceSummary extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'timeframe',
        'avg_request_time',
        'max_memory_usage',
        'avg_cpu_load',
        'slowest_route',
        'slowest_route_time',
        'total_requests',
        'failed_requests',
        'slow_queries_count',
        'avg_query_time',
        'failed_jobs',
    ];

    protected $casts = [
        'avg_request_time' => 'float',
        'max_memory_usage' => 'integer',
        'avg_cpu_load' => 'float',
        'slowest_route_time' => 'float',
        'total_requests' => 'integer',
        'failed_requests' => 'integer',
        'slow_queries_count' => 'integer',
        'avg_query_time' => 'float',
        'failed_jobs' => 'integer',
        'created_at' => 'datetime',
    ];

    public static function getLatestSummary(string $timeframe = '1m')
    {
        return self::where('timeframe', $timeframe)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public static function getSummariesByTimeframe(string $timeframe, int $limit = 24)
    {
        return self::where('timeframe', $timeframe)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function cleanupOldSummaries(int $days = null): int
    {
        $days = $days ?? config('performance.history_retention', 7);
        
        return self::where('created_at', '<', now()->subDays($days))->delete();
    }
}
