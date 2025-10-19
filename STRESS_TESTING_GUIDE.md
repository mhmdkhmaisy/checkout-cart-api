# Performance Stress Testing Guide

## Overview
This guide outlines strategies and methodologies for stress testing the Aragon RSPS application to identify performance bottlenecks, resource limits, and scalability issues.

---

## 1. Testing Objectives

### Primary Goals
- **Identify Breaking Points**: Determine the maximum load the application can handle
- **Measure Response Times**: Track how response times degrade under load
- **Resource Monitoring**: Monitor CPU, memory, and database performance
- **Bottleneck Detection**: Identify slow routes, queries, and operations
- **Concurrent User Simulation**: Test realistic multi-user scenarios

### Key Metrics to Track
- **Response Time**: Average, median, 95th percentile, max
- **Throughput**: Requests per second (RPS)
- **Error Rate**: Percentage of failed requests
- **Resource Usage**: CPU, Memory, Database connections
- **Database Performance**: Query times, connection pool saturation

---

## 2. Testing Methodology

### Phase 1: Baseline Testing
Establish performance baselines under normal load conditions.

**Target Routes for Baseline:**
- `GET /store` - Store page load
- `GET /admin/dashboard` - Admin dashboard
- `GET /admin/performance` - Performance monitor
- `GET /admin/cache` - Cache manager
- `POST /store/add-to-cart` - Cart operations
- `GET /vote` - Vote page
- `GET /events` - Events listing

**Baseline Metrics:**
- Single user response times
- Database query counts per route
- Memory usage per request
- Cache hit/miss ratios

### Phase 2: Load Testing
Gradually increase concurrent users to identify performance degradation.

**Load Profiles:**
```
Light:     10 concurrent users  (5 min duration)
Moderate:  50 concurrent users  (10 min duration)
Heavy:     100 concurrent users (10 min duration)
Peak:      250 concurrent users (5 min duration)
Extreme:   500 concurrent users (3 min duration)
```

### Phase 3: Spike Testing
Sudden traffic spikes to test auto-scaling and recovery.

**Spike Scenarios:**
- Normal load → 10x spike → return to normal
- Simulate viral event (product launch, vote campaign)
- Flash sale simulation (high POST /add-to-cart volume)

### Phase 4: Endurance Testing
Sustained load over extended periods to detect memory leaks and resource exhaustion.

**Configuration:**
- Duration: 2-4 hours minimum
- Load: 50-75% of maximum capacity
- Monitor: Memory trends, database connection leaks, session storage

### Phase 5: Stress Testing
Push the system beyond normal capacity to find breaking points.

**Approach:**
- Gradually increase users until errors exceed 5%
- Monitor for cascade failures
- Test graceful degradation
- Verify error handling and logging

---

## 3. Tools & Technologies

### Recommended Load Testing Tools

#### Option 1: Apache JMeter
**Pros:**
- GUI for test plan creation
- Detailed reporting and graphs
- Database testing capabilities
- Distributed testing support

**Use Case:** Comprehensive testing with detailed analysis

#### Option 2: k6 (Grafana)
**Pros:**
- Modern, JavaScript-based scripting
- Cloud testing capabilities
- Excellent metrics and visualization
- CI/CD integration

**Use Case:** Developer-friendly scripting, cloud-native testing

#### Option 3: Locust
**Pros:**
- Python-based, easy to script
- Real-time web UI
- Distributed testing
- Custom user behavior modeling

**Use Case:** Complex user journey simulation

#### Option 4: Artillery
**Pros:**
- YAML configuration
- WebSocket support
- Built-in reporting
- Easy CLI usage

**Use Case:** Quick API testing, CI/CD pipelines

#### Option 5: wrk (Lightweight)
**Pros:**
- Extremely lightweight
- High performance
- Lua scripting
- Minimal resource overhead

**Use Case:** Raw HTTP benchmarking, baseline testing

### Monitoring Stack

**Application Performance:**
- Performance Monitor (built-in at `/admin/performance`)
- Laravel Telescope (optional, development only)
- New Relic / Datadog (production APM)

**System Resources:**
- `htop` / `top` - Real-time process monitoring
- `vmstat` - Virtual memory statistics
- `iostat` - I/O statistics
- `netstat` - Network statistics

**Database:**
- MySQL Slow Query Log
- Performance Schema
- `EXPLAIN ANALYZE` for query profiling

---

## 4. Test Scenarios

### Scenario A: Store Browse & Purchase Flow
Simulates typical customer journey.

**User Journey:**
1. Load homepage (`GET /`)
2. Browse store (`GET /store`)
3. View product details (3-5 products)
4. Add items to cart (`POST /store/add-to-cart`)
5. Update cart quantities (`POST /store/update-cart`)
6. Proceed to checkout (`POST /store/checkout`)
7. Complete payment flow

**Expected Behavior:**
- < 500ms response for product pages
- < 200ms for cart operations
- Payment processing < 2s

### Scenario B: Admin Panel Operations
Tests administrative workload.

**User Journey:**
1. Login to admin panel
2. Load dashboard (`GET /admin/dashboard`)
3. Browse cache files (`GET /admin/cache`)
4. Upload cache file (`POST /admin/cache`)
5. Verify patch (`GET /admin/cache/patches/{id}/verify`)
6. View performance monitor (`GET /admin/performance`)
7. Check order logs (`GET /admin/orders`)

**Expected Behavior:**
- Dashboard load < 1s
- File uploads process within timeout
- Performance monitor < 500ms
- Order queries < 300ms

### Scenario C: Vote Campaign Rush
Simulates vote site traffic surge.

**User Journey:**
1. Load vote page (`GET /vote`)
2. Set username (`POST /vote/set-username`)
3. Check vote status (`GET /vote/status`)
4. View stats (`GET /vote/stats`)
5. Admin claims votes (`PATCH /admin/vote/votes/{id}/claim`)

**Expected Behavior:**
- Vote page load < 400ms
- Status checks < 100ms
- Admin operations < 300ms

### Scenario D: Cache Download Burst
Simulates game client updates (high bandwidth).

**User Journey:**
1. Check manifest (`GET /manifest.json`)
2. Download client (`GET /download/{os}/{version}`)
3. Download cache files (parallel requests)
4. Verify checksums

**Expected Behavior:**
- Manifest < 100ms
- Download initiation < 500ms
- Sustained download throughput
- No connection timeouts

---

## 5. Critical Routes to Stress Test

### High Priority Routes (Test First)

```
POST /store/add-to-cart          - Cart operations
POST /vote/set-username           - Vote system
GET  /admin/cache                 - File manager
GET  /admin/performance/routes    - Performance data
POST /admin/cache/finalize-upload - File uploads
GET  /store                       - Store page
GET  /download/{os}/{version}     - Client downloads
```

### Database-Intensive Routes

```
GET  /admin/dashboard             - Multiple aggregations
GET  /admin/orders                - Large dataset queries
GET  /admin/performance           - Performance log queries
GET  /vote/stats                  - Vote aggregations
```

### File I/O Routes

```
POST /admin/cache                 - File uploads
GET  /admin/cache/download-manifest
POST /admin/cache/extract-file    - TAR extraction
```

---

## 6. Expected Performance Targets

### Response Time Targets (95th Percentile)

| Route Type          | Target  | Warning | Critical |
|---------------------|---------|---------|----------|
| Static Pages        | < 200ms | 500ms   | 1000ms   |
| Dynamic Pages       | < 500ms | 1000ms  | 2000ms   |
| API Endpoints       | < 300ms | 800ms   | 1500ms   |
| Database Queries    | < 200ms | 500ms   | 1000ms   |
| File Operations     | < 1000ms| 3000ms  | 5000ms   |
| Admin Operations    | < 800ms | 2000ms  | 4000ms   |

### Throughput Targets

```
Light Load:    10-50 RPS
Normal Load:   50-150 RPS
Peak Load:     150-300 RPS
Maximum:       300-500 RPS (before degradation)
```

### Error Rate Thresholds

```
Normal:     < 0.1% error rate
Warning:    0.1% - 1% error rate
Critical:   > 1% error rate
Failure:    > 5% error rate
```

---

## 7. Resource Limits & Capacity Planning

### Server Resources (Baseline)

**Web Server (PHP-FPM):**
- Max workers: 50-100 (adjust based on memory)
- Request timeout: 60s (120s for file ops)
- Memory per worker: ~50-100MB

**Database (MySQL/MariaDB):**
- Max connections: 150-200
- Connection pool size: 10-20 per worker
- Query cache: 64-128MB
- InnoDB buffer pool: 70% of RAM

**Memory Allocation:**
```
Total RAM: 2GB minimum (4GB recommended)
  - OS:            500MB
  - PHP-FPM:       1GB (50 workers × 20MB)
  - MySQL:         1.5GB
  - Redis/Cache:   512MB
  - Buffer:        500MB
```

### Scaling Indicators

**Scale Up When:**
- CPU consistently > 70%
- Memory usage > 80%
- Response times exceed targets
- Database connections near limit
- Queue backlog growing

**Optimize Before Scaling:**
- Implement query caching
- Add database indexes
- Enable OpCache
- Use CDN for static assets
- Implement Redis for sessions

---

## 8. Database Optimization Checklist

### Pre-Test Database Preparation

**Indexes:**
```sql
-- Performance logs (already should exist)
CREATE INDEX idx_perf_type_created ON performance_logs(metric_type, created_at);
CREATE INDEX idx_perf_identifier ON performance_logs(identifier);

-- Cache files
CREATE INDEX idx_cache_hash ON cache_files(file_hash);
CREATE INDEX idx_cache_created ON cache_files(created_at);

-- Orders
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created ON orders(created_at);

-- Votes
CREATE INDEX idx_votes_username ON votes(username);
CREATE INDEX idx_votes_site_created ON votes(vote_site_id, created_at);
```

**Query Optimization:**
- Use `EXPLAIN ANALYZE` on slow queries
- Avoid N+1 query problems (use eager loading)
- Implement query result caching
- Consider read replicas for heavy queries

**Connection Pooling:**
- Configure persistent connections
- Set appropriate pool size
- Monitor connection leaks

---

## 9. Monitoring During Tests

### Real-Time Monitoring Commands

**System Resources:**
```bash
# CPU and Memory
htop

# Disk I/O
iostat -x 1

# Network
nethogs

# Database connections
mysql -e "SHOW PROCESSLIST;"

# PHP-FPM status
curl http://localhost/fpm-status
```

**Application Metrics:**
```bash
# Watch logs
tail -f storage/logs/laravel.log

# Monitor slow queries
tail -f /var/log/mysql/slow-query.log

# Check queue status
php artisan queue:stats
```

### Key Metrics Dashboard

Monitor these in real-time during tests:
- **Performance Monitor**: `/admin/performance`
- **Route Performance**: Track avg response times
- **Error Logs**: Watch for spikes
- **Database Connections**: Monitor active/max
- **Memory Usage**: Watch for leaks
- **Request Queue**: Check for backlog

---

## 10. Post-Test Analysis

### Data Collection

**Collect from Load Testing Tool:**
- Response time distribution graphs
- Error rate timeline
- Throughput (RPS) over time
- Success/failure breakdown by endpoint

**Collect from Performance Monitor:**
- Route performance rankings
- Slow query logs
- Resource usage peaks
- Alert history

**Collect from System:**
- CPU/Memory graphs
- Disk I/O patterns
- Network saturation points
- Database slow query log

### Analysis Questions

1. **Which routes degraded first under load?**
2. **What was the breaking point (RPS or concurrent users)?**
3. **Did memory usage grow linearly or exponentially?**
4. **Were there any cascade failures?**
5. **Did the application recover after load reduction?**
6. **Which database queries became bottlenecks?**
7. **Did file operations impact overall performance?**
8. **Were there any connection pool exhaustion issues?**

### Optimization Priorities

**High Priority (Fix Immediately):**
- Routes with > 2s response time under normal load
- Database queries > 500ms
- Memory leaks (growing usage over time)
- Error rates > 1%

**Medium Priority (Plan for Next Sprint):**
- Routes with 500ms-2s response times
- Queries 200-500ms
- Inefficient caching strategies

**Low Priority (Monitor):**
- Routes meeting targets but could be optimized
- Proactive scaling preparations

---

## 11. Implementation Plan

### Phase 1: Tool Setup (Week 1)
- [ ] Install chosen load testing tool(s)
- [ ] Configure monitoring stack
- [ ] Set up test data generators
- [ ] Create baseline test scripts

### Phase 2: Baseline & Load Testing (Week 2)
- [ ] Run baseline tests on all critical routes
- [ ] Document current performance metrics
- [ ] Execute progressive load tests
- [ ] Identify initial bottlenecks

### Phase 3: Optimization (Week 3-4)
- [ ] Implement database query optimizations
- [ ] Add strategic caching
- [ ] Optimize slow routes
- [ ] Re-test to verify improvements

### Phase 4: Stress & Endurance (Week 5)
- [ ] Execute spike tests
- [ ] Run endurance tests
- [ ] Test extreme load scenarios
- [ ] Document breaking points

### Phase 5: Reporting & Planning (Week 6)
- [ ] Compile comprehensive test report
- [ ] Create capacity planning document
- [ ] Develop scaling strategy
- [ ] Set up production monitoring

---

## 12. Automated Testing Integration

### CI/CD Pipeline Integration

**Pre-Deployment Tests:**
```yaml
# .github/workflows/performance-test.yml
name: Performance Tests

on: [pull_request]

jobs:
  performance:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v2
      
      - name: Setup environment
        run: |
          composer install
          php artisan migrate --seed
      
      - name: Run k6 load test
        run: |
          k6 run --vus 10 --duration 30s tests/performance/baseline.js
      
      - name: Check performance thresholds
        run: |
          # Fail if avg response > 500ms or error rate > 1%
          k6 run --vus 10 --duration 30s \
            --threshold http_req_duration=avg<500 \
            --threshold http_req_failed=rate<0.01 \
            tests/performance/baseline.js
```

### Scheduled Testing

**Weekly Stress Tests:**
```bash
# crontab entry
0 2 * * 0 /path/to/run-stress-tests.sh
```

---

## 13. Sample Test Scripts

### k6 Basic Load Test Script

```javascript
// tests/performance/store-load-test.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  stages: [
    { duration: '2m', target: 10 },  // Ramp up to 10 users
    { duration: '5m', target: 50 },  // Ramp up to 50 users
    { duration: '5m', target: 50 },  // Stay at 50 users
    { duration: '2m', target: 0 },   // Ramp down to 0 users
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'], // 95% of requests under 500ms
    http_req_failed: ['rate<0.01'],   // Error rate under 1%
  },
};

export default function () {
  // Homepage
  let res = http.get('http://localhost/store');
  check(res, { 'store page loaded': (r) => r.status === 200 });
  sleep(1);

  // Add to cart
  res = http.post('http://localhost/store/add-to-cart', {
    product_id: 1,
    quantity: 1,
  });
  check(res, { 'item added to cart': (r) => r.status === 200 });
  sleep(2);

  // View cart
  res = http.get('http://localhost/store/cart');
  check(res, { 'cart loaded': (r) => r.status === 200 });
  sleep(1);
}
```

---

## 14. Quick Reference Checklist

### Before Testing
- [ ] Database properly indexed
- [ ] Test data populated (realistic volumes)
- [ ] Monitoring tools configured
- [ ] Backups created
- [ ] Test environment isolated from production
- [ ] Baseline metrics documented

### During Testing
- [ ] Monitor system resources in real-time
- [ ] Watch error logs for anomalies
- [ ] Track database connection usage
- [ ] Note when degradation begins
- [ ] Document any crashes or failures

### After Testing
- [ ] Export all metrics and graphs
- [ ] Analyze bottlenecks
- [ ] Prioritize optimizations
- [ ] Update capacity planning
- [ ] Share results with team
- [ ] Plan follow-up tests

---

## 15. Contact & Resources

### Internal Resources
- Performance Monitor: `/admin/performance`
- API Documentation: `/admin/api-docs`
- Slow Query Log: `storage/logs/slow-queries.log`

### External Tools
- k6: https://k6.io/docs/
- Locust: https://docs.locust.io/
- Apache JMeter: https://jmeter.apache.org/
- Artillery: https://artillery.io/docs/

### Performance Best Practices
- Laravel Performance: https://laravel.com/docs/optimization
- MySQL Performance: https://dev.mysql.com/doc/refman/8.0/en/optimization.html
- PHP-FPM Tuning: https://www.php.net/manual/en/install.fpm.configuration.php

---

**Document Version:** 1.0  
**Last Updated:** October 19, 2025  
**Author:** Development Team  
**Next Review:** After first stress test cycle
