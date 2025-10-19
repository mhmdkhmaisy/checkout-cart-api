import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Rate, Trend } from 'k6/metrics';
import { config, testData } from '../config.js';

const errorRate = new Rate('errors');
const votePageDuration = new Trend('vote_page_duration');
const statusCheckDuration = new Trend('status_check_duration');

export let options = {
    stages: config.rampUp.spike,
    thresholds: {
        'errors': ['rate<0.05'],
        'http_req_duration': ['p(95)<600'],
        'vote_page_duration': ['p(95)<400'],
        'status_check_duration': ['p(95)<100']
    }
};

export default function () {
    const baseUrl = config.baseUrl;
    const params = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    };

    group('Vote Campaign Rush', function () {
        let res;

        group('Load Vote Page', function () {
            const start = Date.now();
            res = http.get(`${baseUrl}/vote`, params);
            votePageDuration.add(Date.now() - start);
            
            const passed = check(res, {
                'vote page loaded': (r) => r.status === 200
            });
            errorRate.add(!passed);
            sleep(1);
        });

        group('Set Username', function () {
            const username = testData.usernames[Math.floor(Math.random() * testData.usernames.length)] 
                + '_' + Date.now();
            
            res = http.post(`${baseUrl}/vote/set-username`, 
                JSON.stringify({ username: username }), 
                params
            );
            
            const passed = check(res, {
                'username set': (r) => r.status === 200 || r.status === 302
            });
            errorRate.add(!passed);
            sleep(0.5);
        });

        group('Check Vote Status', function () {
            const start = Date.now();
            res = http.get(`${baseUrl}/vote/status`, params);
            statusCheckDuration.add(Date.now() - start);
            
            const passed = check(res, {
                'status retrieved': (r) => r.status === 200
            });
            errorRate.add(!passed);
            sleep(0.5);
        });

        group('View Stats', function () {
            res = http.get(`${baseUrl}/vote/stats`, params);
            
            const passed = check(res, {
                'stats loaded': (r) => r.status === 200
            });
            errorRate.add(!passed);
            sleep(1);
        });
    });

    sleep(Math.random() * 2 + 0.5);
}

export function handleSummary(data) {
    return {
        'results/vote-rush-summary.json': JSON.stringify(data),
        'stdout': textSummary(data)
    };
}

function textSummary(data) {
    return `
================================================================================
VOTE RUSH LOAD TEST SUMMARY
================================================================================
Duration: ${data.state.testRunDurationMs / 1000}s
VUs Max: ${data.metrics.vus_max.values.max}

HTTP Metrics:
  Requests: ${data.metrics.http_reqs.values.count}
  Failed: ${data.metrics.http_req_failed.values.rate * 100}%
  Duration (avg): ${data.metrics.http_req_duration.values.avg.toFixed(2)}ms
  Duration (p95): ${data.metrics.http_req_duration.values['p(95)'].toFixed(2)}ms

Custom Metrics:
  Vote Page (p95): ${data.metrics.vote_page_duration?.values['p(95)']?.toFixed(2) || 'N/A'}ms
  Status Check (p95): ${data.metrics.status_check_duration?.values['p(95)']?.toFixed(2) || 'N/A'}ms
  Error Rate: ${data.metrics.errors?.values.rate * 100 || 0}%
================================================================================
    `;
}
