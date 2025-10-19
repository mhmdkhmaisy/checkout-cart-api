<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceLog extends Model
{
    const UPDATED_AT = null;

    public static $disableLogging = false;

    protected $fillable = [
        'metric_type',
        'identifier',
        'value',
        'metadata',
    ];

    protected $casts = [
        'value' => 'float',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public static function recordMetric(string $type, string $identifier, float $value, array $metadata = []): void
    {
        if (!config('performance.enabled') || self::$disableLogging) {
            return;
        }

        self::$disableLogging = true;
        
        try {
            self::create([
                'metric_type' => $type,
                'identifier' => $identifier,
                'value' => $value,
                'metadata' => $metadata,
                'created_at' => now(),
            ]);
        } finally {
            self::$disableLogging = false;
        }
    }

    public static function getRecentMetrics(string $type, int $minutes = 5)
    {
        return self::where('metric_type', $type)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public static function getAverageValue(string $type, string $identifier = null, int $minutes = 60)
    {
        $query = self::where('metric_type', $type)
            ->where('created_at', '>=', now()->subMinutes($minutes));

        if ($identifier) {
            $query->where('identifier', $identifier);
        }

        return $query->avg('value');
    }

    public static function cleanupOldLogs(int $days = null): int
    {
        $days = $days ?? config('performance.history_retention', 7);
        
        return self::where('created_at', '<', now()->subDays($days))->delete();
    }
}
