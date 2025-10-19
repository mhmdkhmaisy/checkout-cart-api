<?php

return [

    'enabled' => env('PERFORMANCE_MONITORING_ENABLED', true),

    'store' => env('PERFORMANCE_STORE', 'database'),

    'thresholds' => [
        'request_time' => env('PERFORMANCE_THRESHOLD_REQUEST_TIME', 1000),
        'query_time' => env('PERFORMANCE_THRESHOLD_QUERY_TIME', 200),
        'cpu_load' => env('PERFORMANCE_THRESHOLD_CPU_LOAD', 0.85),
        'disk_free' => env('PERFORMANCE_THRESHOLD_DISK_FREE', 0.10),
        'memory_usage' => env('PERFORMANCE_THRESHOLD_MEMORY_MB', 512),
    ],

    'history_retention' => env('PERFORMANCE_HISTORY_RETENTION_DAYS', 7),

    'buffer_size' => env('PERFORMANCE_BUFFER_SIZE', 1000),

    'batch_interval' => env('PERFORMANCE_BATCH_INTERVAL_SECONDS', 30),

    'aggregation_interval' => env('PERFORMANCE_AGGREGATION_INTERVAL_SECONDS', 60),

    'sampling' => [
        'enabled' => env('PERFORMANCE_SAMPLING_ENABLED', false),
        'rate' => env('PERFORMANCE_SAMPLING_RATE', 0.1),
    ],

    'excluded_routes' => [
        'admin/performance/metrics',
        'admin/performance/live',
        'admin/performance/routes',
        'admin/performance/slow-queries',
        'admin/performance/history',
        'admin/performance/queue-stats',
        'admin/performance/alerts',
        'admin/performance',
    ],

];
