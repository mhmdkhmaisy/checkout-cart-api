import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Rate, Trend } from 'k6/metrics';
import { config, testData } from '../config.js';

const errorRate = new Rate('errors');
const manifestDuration = new Trend('manifest_duration');
const downloadInitDuration = new Trend('download_init_duration');

export let options = {
    stages: [
        { duration: '1m', target: 5 },
        { duration: '3m', target: 10 },
        { duration: '3m', target: 10 },
        { duration: '1m', target: 0 }
    ],
    thresholds: {
        'errors': ['rate<0.05'],
        'http_req_duration': ['p(95)<2000'],
        'manifest_duration': ['p(95)<200'],
        'download_init_duration': ['p(95)<1000']
    }
};

export default function () {
    const baseUrl = config.baseUrl;
    const params = {
        headers: {
            'Accept': 'application/json'
        },
        timeout: '30s'
    };

    group('Client Download Flow', function () {
        let res;

        group('Check Manifest', function () {
            const start = Date.now();
            res = http.get(`${baseUrl}/manifest.json`, params);
            manifestDuration.add(Date.now() - start);
            
            const passed = check(res, {
                'manifest retrieved': (r) => r.status === 200,
                'manifest is JSON': (r) => {
                    try {
                        JSON.parse(r.body);
                        return true;
                    } catch (e) {
                        return false;
                    }
                }
            });
            errorRate.add(!passed);
            sleep(0.5);
        });

        group('Download Windows Client', function () {
            const start = Date.now();
            res = http.get(`${baseUrl}/download/windows/latest`, {
                ...params,
                responseType: 'none',
                timeout: '60s'
            });
            downloadInitDuration.add(Date.now() - start);
            
            const passed = check(res, {
                'download initiated': (r) => r.status === 200 || r.status === 302 || r.status === 404
            });
            errorRate.add(!passed);
            sleep(2);
        });

        group('Download Mac Client', function () {
            const start = Date.now();
            res = http.get(`${baseUrl}/download/mac/latest`, {
                ...params,
                responseType: 'none',
                timeout: '60s'
            });
            downloadInitDuration.add(Date.now() - start);
            
            const passed = check(res, {
                'download initiated': (r) => r.status === 200 || r.status === 302 || r.status === 404
            });
            errorRate.add(!passed);
            sleep(2);
        });

        group('Download Linux Client', function () {
            const start = Date.now();
            res = http.get(`${baseUrl}/download/linux/latest`, {
                ...params,
                responseType: 'none',
                timeout: '60s'
            });
            downloadInitDuration.add(Date.now() - start);
            
            const passed = check(res, {
                'download initiated': (r) => r.status === 200 || r.status === 302 || r.status === 404
            });
            errorRate.add(!passed);
            sleep(2);
        });

        group('View Play Page', function () {
            res = http.get(`${baseUrl}/play`, params);
            
            const passed = check(res, {
                'play page loaded': (r) => r.status === 200
            });
            errorRate.add(!passed);
            sleep(1);
        });
    });

    sleep(Math.random() * 5 + 2);
}

export function handleSummary(data) {
    return {
        'results/client-downloads-summary.json': JSON.stringify(data),
        'stdout': textSummary(data)
    };
}

function textSummary(data) {
    return `
================================================================================
CLIENT DOWNLOADS LOAD TEST SUMMARY
================================================================================
Duration: ${data.state.testRunDurationMs / 1000}s
VUs Max: ${data.metrics.vus_max.values.max}

HTTP Metrics:
  Requests: ${data.metrics.http_reqs.values.count}
  Failed: ${data.metrics.http_req_failed.values.rate * 100}%
  Duration (avg): ${data.metrics.http_req_duration.values.avg.toFixed(2)}ms
  Duration (p95): ${data.metrics.http_req_duration.values['p(95)'].toFixed(2)}ms

Custom Metrics:
  Manifest (p95): ${data.metrics.manifest_duration?.values['p(95)']?.toFixed(2) || 'N/A'}ms
  Download Init (p95): ${data.metrics.download_init_duration?.values['p(95)']?.toFixed(2) || 'N/A'}ms
  Error Rate: ${data.metrics.errors?.values.rate * 100 || 0}%

Note: 404 responses are expected if no clients have been uploaded yet.
================================================================================
    `;
}
