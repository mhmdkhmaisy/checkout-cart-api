import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Rate, Trend } from 'k6/metrics';
import { config, testData } from '../config.js';

const errorRate = new Rate('errors');
const storePageDuration = new Trend('store_page_duration');
const addToCartDuration = new Trend('add_to_cart_duration');
const cartViewDuration = new Trend('cart_view_duration');

export let options = {
    stages: config.rampUp.slow,
    thresholds: {
        'errors': ['rate<0.01'],
        'http_req_duration': ['p(95)<800'],
        'store_page_duration': ['p(95)<500'],
        'add_to_cart_duration': ['p(95)<300'],
        'cart_view_duration': ['p(95)<400']
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

    group('Store Browse Flow', function () {
        let res;

        group('Load Homepage', function () {
            res = http.get(`${baseUrl}/`, params);
            const passed = check(res, {
                'homepage loaded': (r) => r.status === 200,
                'homepage has content': (r) => r.body.includes('Aragon')
            });
            errorRate.add(!passed);
            sleep(1);
        });

        group('Set Username', function () {
            const username = testData.usernames[Math.floor(Math.random() * testData.usernames.length)] 
                + '_' + Date.now() + '_' + __VU;
            
            res = http.post(`${baseUrl}/store/set-user`, 
                `username=${encodeURIComponent(username)}`,
                {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/json'
                    }
                }
            );
            
            const passed = check(res, {
                'username set': (r) => r.status === 200 || r.status === 302,
                'response has success': (r) => {
                    try {
                        const json = JSON.parse(r.body);
                        return json.success === true;
                    } catch (e) {
                        return r.status === 302;
                    }
                }
            });
            errorRate.add(!passed);
            sleep(0.5);
        });

        group('Browse Store', function () {
            const start = Date.now();
            res = http.get(`${baseUrl}/store`, params);
            storePageDuration.add(Date.now() - start);
            
            const passed = check(res, {
                'store page loaded': (r) => r.status === 200,
                'store has products': (r) => r.body.length > 1000
            });
            errorRate.add(!passed);
            sleep(2);
        });

        group('Add to Cart', function () {
            const productId = testData.products[Math.floor(Math.random() * testData.products.length)];
            const start = Date.now();
            
            res = http.post(`${baseUrl}/store/add-to-cart`, 
                JSON.stringify({
                    product_id: productId,
                    quantity: Math.floor(Math.random() * 3) + 1
                }), 
                params
            );
            
            addToCartDuration.add(Date.now() - start);
            
            const passed = check(res, {
                'item added': (r) => r.status === 200 || r.status === 302
            });
            errorRate.add(!passed);
            sleep(1);
        });

        group('View Cart', function () {
            const start = Date.now();
            res = http.get(`${baseUrl}/store/cart`, params);
            cartViewDuration.add(Date.now() - start);
            
            const passed = check(res, {
                'cart loaded': (r) => r.status === 200
            });
            errorRate.add(!passed);
            sleep(1);
        });

        group('Update Cart', function () {
            const productId = testData.products[Math.floor(Math.random() * testData.products.length)];
            res = http.post(`${baseUrl}/store/update-cart`, 
                JSON.stringify({
                    product_id: productId,
                    quantity: Math.floor(Math.random() * 5) + 1
                }), 
                params
            );
            
            const passed = check(res, {
                'cart updated': (r) => r.status === 200 || r.status === 302
            });
            errorRate.add(!passed);
            sleep(2);
        });
    });

    sleep(Math.random() * 3 + 1);
}

export function handleSummary(data) {
    return {
        'results/store-flow-summary.json': JSON.stringify(data),
        'stdout': textSummary(data, { indent: ' ', enableColors: true })
    };
}

function textSummary(data, options) {
    return `
================================================================================
STORE FLOW LOAD TEST SUMMARY
================================================================================
Duration: ${data.state.testRunDurationMs / 1000}s
VUs Max: ${data.metrics.vus_max.values.max}

HTTP Metrics:
  Requests: ${data.metrics.http_reqs.values.count}
  Failed: ${data.metrics.http_req_failed.values.rate * 100}%
  Duration (avg): ${data.metrics.http_req_duration.values.avg.toFixed(2)}ms
  Duration (p95): ${data.metrics.http_req_duration.values['p(95)'].toFixed(2)}ms
  Duration (max): ${data.metrics.http_req_duration.values.max.toFixed(2)}ms

Custom Metrics:
  Store Page (p95): ${data.metrics.store_page_duration?.values['p(95)']?.toFixed(2) || 'N/A'}ms
  Add to Cart (p95): ${data.metrics.add_to_cart_duration?.values['p(95)']?.toFixed(2) || 'N/A'}ms
  Cart View (p95): ${data.metrics.cart_view_duration?.values['p(95)']?.toFixed(2) || 'N/A'}ms
  Error Rate: ${data.metrics.errors?.values.rate * 100 || 0}%
================================================================================
    `;
}
