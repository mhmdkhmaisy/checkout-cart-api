import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Rate, Trend } from 'k6/metrics';
import { config } from '../config.js';

const errorRate = new Rate('errors');
const dashboardDuration = new Trend('dashboard_duration');
const performanceDuration = new Trend('performance_duration');
const cacheDuration = new Trend('cache_duration');

export let options = {
    stages: config.rampUp.slow,
    thresholds: {
        'errors': ['rate<0.02'],
        'http_req_duration': ['p(95)<1000'],
        'dashboard_duration': ['p(95)<800'],
        'performance_duration': ['p(95)<600'],
        'cache_duration': ['p(95)<700']
    }
};

export default function () {
    const baseUrl = config.baseUrl;
    const params = {
        headers: {
            'Accept': 'application/json'
        }
    };

    group('Admin Panel Operations', function () {
        let res;

        group('Dashboard', function () {
            const start = Date.now();
            res = http.get(`${baseUrl}/admin`, params);
            dashboardDuration.add(Date.now() - start);
            
            const passed = check(res, {
                'dashboard loaded': (r) => r.status === 200
            });
            errorRate.add(!passed);
            sleep(2);
        });

        group('Performance Monitor', function () {
            const start = Date.now();
            res = http.get(`${baseUrl}/admin/performance`, params);
            performanceDuration.add(Date.now() - start);
            
            const passed = check(res, {
                'performance page loaded': (r) => r.status === 200
            });
            errorRate.add(!passed);
            sleep(1);
        });

        group('Performance Live Data', function () {
            res = http.get(`${baseUrl}/admin/performance/live`, params);
            
            const passed = check(res, {
                'live data retrieved': (r) => r.status === 200,
                'has metrics': (r) => {
                    try {
                        const json = JSON.parse(r.body);
                        return json.success === true;
                    } catch (e) {
                        return false;
                    }
                }
            });
            errorRate.add(!passed);
            sleep(0.5);
        });

        group('Route Performance', function () {
            res = http.get(`${baseUrl}/admin/performance/routes`, params);
            
            const passed = check(res, {
                'route data retrieved': (r) => r.status === 200
            });
            errorRate.add(!passed);
            sleep(1);
        });

        group('Cache Manager', function () {
            const start = Date.now();
            res = http.get(`${baseUrl}/admin/cache`, params);
            cacheDuration.add(Date.now() - start);
            
            const passed = check(res, {
                'cache page loaded': (r) => r.status === 200
            });
            errorRate.add(!passed);
            sleep(2);
        });

        group('Orders', function () {
            res = http.get(`${baseUrl}/admin/orders`, params);
            
            const passed = check(res, {
                'orders loaded': (r) => r.status === 200
            });
            errorRate.add(!passed);
            sleep(1);
        });
    });

    sleep(Math.random() * 3 + 2);
}

export function handleSummary(data) {
    return {
        'results/admin-panel-summary.json': JSON.stringify(data),
        'stdout': textSummary(data)
    };
}

function textSummary(data) {
    return `
================================================================================
ADMIN PANEL LOAD TEST SUMMARY
================================================================================
Duration: ${data.state.testRunDurationMs / 1000}s
VUs Max: ${data.metrics.vus_max.values.max}

HTTP Metrics:
  Requests: ${data.metrics.http_reqs.values.count}
  Failed: ${data.metrics.http_req_failed.values.rate * 100}%
  Duration (avg): ${data.metrics.http_req_duration.values.avg.toFixed(2)}ms
  Duration (p95): ${data.metrics.http_req_duration.values['p(95)'].toFixed(2)}ms

Custom Metrics:
  Dashboard (p95): ${data.metrics.dashboard_duration?.values['p(95)']?.toFixed(2) || 'N/A'}ms
  Performance (p95): ${data.metrics.performance_duration?.values['p(95)']?.toFixed(2) || 'N/A'}ms
  Cache (p95): ${data.metrics.cache_duration?.values['p(95)']?.toFixed(2) || 'N/A'}ms
  Error Rate: ${data.metrics.errors?.values.rate * 100 || 0}%
================================================================================
    `;
}
