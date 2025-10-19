import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Rate, Trend } from 'k6/metrics';
import { config } from '../config.js';

const errorRate = new Rate('errors');
const cacheBundlesDuration = new Trend('cache_bundles_duration');
const patchDownloadDuration = new Trend('patch_download_duration');

export let options = {
    vus: 5,
    duration: '5m',
    thresholds: {
        'errors': ['rate<0.15'],
        'http_req_duration': ['p(95)<3000'],
        'cache_bundles_duration': ['p(95)<1000'],
        'patch_download_duration': ['p(95)<2000']
    },
    discardResponseBodies: true
};

export default function () {
    const baseUrl = config.baseUrl;
    const params = {
        headers: {
            'Accept': 'application/json'
        },
        timeout: '60s'
    };

    group('Cache Download Flow', function () {
        let res;

        group('View Cache Bundles', function () {
            const start = Date.now();
            res = http.get(`${baseUrl}/admin/cache`, params);
            cacheBundlesDuration.add(Date.now() - start);
            
            const passed = check(res, {
                'cache page loaded': (r) => r.status === 200
            });
            errorRate.add(!passed);
            sleep(1);
        });

        group('Get Latest Patch Version', function () {
            res = http.get(`${baseUrl}/admin/cache/patches/latest`, params);
            
            const passed = check(res, {
                'latest version retrieved': (r) => r.status === 200 || r.status === 404
            });
            errorRate.add(!passed);
            sleep(0.5);
        });

        group('Download Combined Patches', function () {
            const start = Date.now();
            res = http.post(`${baseUrl}/admin/cache/patches/download-combined`, 
                `from_version=1.0.0&to_version=latest`,
                {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/json'
                    },
                    responseType: 'none',
                    timeout: '120s'
                }
            );
            patchDownloadDuration.add(Date.now() - start);
            
            const passed = check(res, {
                'patch download initiated': (r) => r.status === 200 || r.status === 302 || r.status === 404 || r.status === 422
            });
            errorRate.add(!passed);
            sleep(3);
        });

        group('Download Manifest', function () {
            res = http.get(`${baseUrl}/admin/cache/download-manifest`, {
                ...params,
                timeout: '30s',
                redirects: 0
            });
            
            const passed = check(res, {
                'manifest downloaded': (r) => r.status === 200 || r.status === 302 || r.status === 404
            });
            errorRate.add(!passed);
            sleep(1);
        });

        group('Check for Updates', function () {
            res = http.post(`${baseUrl}/admin/cache/patches/check-updates`,
                `current_version=1.0.0`,
                {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/json'
                    }
                }
            );
            
            const passed = check(res, {
                'update check completed': (r) => r.status === 200 || r.status === 404 || r.status === 422
            });
            errorRate.add(!passed);
            sleep(2);
        });
    });

    sleep(Math.random() * 3 + 1);
}

export function handleSummary(data) {
    return {
        'results/cache-downloads-summary.json': JSON.stringify(data),
        'stdout': textSummary(data)
    };
}

function textSummary(data) {
    return `
================================================================================
CACHE DOWNLOADS LOAD TEST SUMMARY
================================================================================
Duration: ${data.state.testRunDurationMs / 1000}s
VUs: ${data.metrics.vus.values.value}

HTTP Metrics:
  Requests: ${data.metrics.http_reqs.values.count}
  Failed: ${data.metrics.http_req_failed.values.rate * 100}%
  Duration (avg): ${data.metrics.http_req_duration.values.avg.toFixed(2)}ms
  Duration (p95): ${data.metrics.http_req_duration.values['p(95)'].toFixed(2)}ms

Custom Metrics:
  Cache Bundles (p95): ${data.metrics.cache_bundles_duration?.values['p(95)']?.toFixed(2) || 'N/A'}ms
  Patch Download (p95): ${data.metrics.patch_download_duration?.values['p(95)']?.toFixed(2) || 'N/A'}ms
  Error Rate: ${data.metrics.errors?.values.rate * 100 || 0}%

Note: 404/422 responses are expected if no patches/cache files exist yet.
================================================================================
    `;
}
