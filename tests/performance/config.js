export const config = {
    baseUrl: __ENV.BASE_URL || 'https://a45e440a-81dc-4a5e-b209-5c0b2f078577-00-35b253as32cbt.pike.replit.dev',
    
    thresholds: {
        light: {
            vus: 10,
            duration: '5m',
            errorRate: 0.01,
            p95: 500
        },
        moderate: {
            vus: 50,
            duration: '10m',
            errorRate: 0.01,
            p95: 800
        },
        heavy: {
            vus: 100,
            duration: '10m',
            errorRate: 0.05,
            p95: 1000
        },
        peak: {
            vus: 250,
            duration: '5m',
            errorRate: 0.1,
            p95: 2000
        },
        extreme: {
            vus: 500,
            duration: '3m',
            errorRate: 0.15,
            p95: 3000
        }
    },
    
    rampUp: {
        slow: [
            { duration: '2m', target: 10 },
            { duration: '3m', target: 50 },
            { duration: '5m', target: 50 },
            { duration: '2m', target: 0 }
        ],
        moderate: [
            { duration: '1m', target: 25 },
            { duration: '2m', target: 100 },
            { duration: '5m', target: 100 },
            { duration: '2m', target: 0 }
        ],
        aggressive: [
            { duration: '30s', target: 50 },
            { duration: '1m', target: 200 },
            { duration: '3m', target: 200 },
            { duration: '1m', target: 0 }
        ],
        spike: [
            { duration: '30s', target: 10 },
            { duration: '10s', target: 100 },
            { duration: '2m', target: 100 },
            { duration: '10s', target: 10 },
            { duration: '30s', target: 10 }
        ]
    }
};

export const testData = {
    products: [1, 2, 3, 4, 5],
    usernames: [
        'testuser1', 'testuser2', 'testuser3', 'testuser4', 'testuser5',
        'stresstest1', 'stresstest2', 'stresstest3', 'loadtest1', 'loadtest2'
    ],
    voteSites: [1, 2, 3],
    os: ['windows', 'mac', 'linux'],
    versions: ['1.0.0', '1.1.0', '1.2.0']
};
