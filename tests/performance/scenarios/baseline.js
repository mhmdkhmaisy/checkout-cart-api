import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { config } from '../config.js';

export let options = {
    vus: 1,
    iterations: 10,
    thresholds: {
        'http_req_duration': ['p(95)<500'],
        'http_req_failed': ['rate<0.01']
    }
};

export default function () {
    const baseUrl = config.baseUrl;
    
    const routes = [
        { name: 'Homepage', url: '/' },
        { name: 'Store', url: '/store' },
        { name: 'Vote', url: '/vote' },
        { name: 'Events', url: '/events' },
        { name: 'Updates', url: '/updates' },
        { name: 'Play', url: '/play' },
        { name: 'Admin Dashboard', url: '/admin' },
        { name: 'Admin Performance', url: '/admin/performance' },
        { name: 'Admin Cache', url: '/admin/cache' },
        { name: 'Admin Orders', url: '/admin/orders' }
    ];

    routes.forEach(route => {
        group(route.name, function () {
            const res = http.get(`${baseUrl}${route.url}`);
            check(res, {
                [`${route.name} - status 200`]: (r) => r.status === 200,
                [`${route.name} - response < 500ms`]: (r) => r.timings.duration < 500
            });
            sleep(0.5);
        });
    });
}

export function handleSummary(data) {
    const summary = {
        timestamp: new Date().toISOString(),
        duration: data.state.testRunDurationMs / 1000,
        metrics: {
            requests: data.metrics.http_reqs.values.count,
            failed: data.metrics.http_req_failed.values.rate * 100,
            duration_avg: data.metrics.http_req_duration.values.avg,
            duration_p95: data.metrics.http_req_duration.values['p(95)'],
            duration_max: data.metrics.http_req_duration.values.max
        }
    };

    return {
        'results/baseline-summary.json': JSON.stringify(summary, null, 2),
        'stdout': `
================================================================================
BASELINE PERFORMANCE TEST
================================================================================
Timestamp: ${summary.timestamp}
Duration: ${summary.duration}s

Metrics:
  Total Requests: ${summary.metrics.requests}
  Failed Requests: ${summary.metrics.failed.toFixed(2)}%
  Avg Response Time: ${summary.metrics.duration_avg.toFixed(2)}ms
  95th Percentile: ${summary.metrics.duration_p95.toFixed(2)}ms
  Max Response Time: ${summary.metrics.duration_max.toFixed(2)}ms

Status: ${summary.metrics.failed < 1 && summary.metrics.duration_p95 < 500 ? 'PASS ✓' : 'FAIL ✗'}
================================================================================
        `
    };
}
