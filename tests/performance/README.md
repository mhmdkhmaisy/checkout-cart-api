# 🚀 Performance Testing Suite

Complete load testing framework for Aragon RSPS application.

## Quick Start

```bash
# 1. Install k6 (optional but recommended)
sudo ./tests/performance/install-k6.sh

# 2. Run the test menu
./tests/performance/run-tests.sh

# 3. View results
./tests/performance/utils/analyze-results.sh
```

## Available Tests

| Test | Duration | Users | Purpose |
|------|----------|-------|---------|
| **Baseline** | 30s | 1 | Quick health check |
| **Store Flow** | 12min | 10→50 | E-commerce simulation |
| **Vote Rush** | 4min | 10→100 (spike) | Vote campaign stress |
| **Admin Panel** | 12min | 10→50 | Admin operations |
| **Simple** | Variable | Custom | Lightweight curl-based |

## Test Structure

```
tests/performance/
├── config.js                    # Global configuration
├── scenarios/                   # k6 test scripts
│   ├── baseline.js             # Quick health check
│   ├── store-flow.js           # Store load test
│   ├── vote-rush.js            # Vote spike test
│   └── admin-panel.js          # Admin stress test
├── utils/                       # Helper scripts
│   ├── simple-load-test.sh     # Curl-based testing
│   └── analyze-results.sh      # Results analyzer
├── results/                     # Test output (auto-created)
├── run-tests.sh                # Main test runner
├── install-k6.sh               # k6 installer
├── SETUP.md                    # Detailed setup guide
└── README.md                   # This file
```

## Running Tests

### Interactive Menu

```bash
./tests/performance/run-tests.sh
```

Choose from:
1. Baseline Test - Quick performance check
2. Store Flow - Full shopping simulation
3. Vote Rush - Spike testing
4. Admin Panel - Backend stress test
5. Simple Test - No k6 required
6. Run ALL - Complete suite
7. Analyze Results

### Direct Execution

```bash
# With k6
k6 run tests/performance/scenarios/baseline.js
k6 run tests/performance/scenarios/store-flow.js

# Without k6 (simple curl-based)
./tests/performance/utils/simple-load-test.sh
```

### Custom Parameters

```bash
# Set custom base URL
export BASE_URL="https://your-app.replit.dev"

# For simple test, set concurrency
export CONCURRENT_USERS=25
export REQUESTS_PER_USER=100

./tests/performance/utils/simple-load-test.sh
```

## What Each Test Does

### 📊 Baseline Test
- **1 virtual user**
- Tests all major routes once
- Establishes performance baseline
- Quick sanity check

**Routes tested:**
- Homepage, Store, Vote, Events, Updates
- Admin: Dashboard, Performance, Cache, Orders

### 🛒 Store Flow Test
- **10-50 concurrent users**
- Simulates realistic shopping journey
- Measures cart operations

**User flow:**
1. Browse homepage
2. View store
3. Add items to cart (random products)
4. View cart
5. Update quantities

### 🗳️ Vote Rush Test
- **Spike: 10 → 100 → 10 users**
- Simulates vote campaign traffic surge
- Tests system recovery

**User flow:**
1. Load vote page
2. Set username
3. Check vote status
4. View statistics

### ⚙️ Admin Panel Test
- **10-50 concurrent users**
- Stress tests backend operations
- Database-heavy operations

**User flow:**
1. Load dashboard
2. Check performance monitor
3. Fetch live metrics
4. View route performance
5. Browse cache manager
6. Check orders

## Performance Targets

| Metric | Good | Warning | Critical |
|--------|------|---------|----------|
| Error Rate | <1% | 1-5% | >5% |
| Avg Response | <300ms | 300-800ms | >800ms |
| P95 Response | <500ms | 500-1000ms | >1000ms |
| Admin P95 | <800ms | 800-2000ms | >2000ms |

## Monitoring During Tests

### 1. Performance Monitor Dashboard
Open in browser: `https://your-app/admin/performance`

Watch real-time:
- Route performance
- Response times
- Error rates
- System resources

### 2. System Resources

```bash
# CPU/Memory
htop

# Network
nethogs

# Disk I/O
iostat -x 1
```

### 3. Application Logs

```bash
tail -f storage/logs/laravel.log
```

## Analyzing Results

### Quick Analysis

```bash
./tests/performance/utils/analyze-results.sh
```

Shows:
- Total requests & success rate
- Response time statistics (avg, min, max, P95)
- Per-route breakdown
- Error summary

### Detailed Results

Results saved in `tests/performance/results/`:
- **JSON files** - Full k6 metrics
- **Log files** - Simple test raw data

### Performance Monitor

Check `/admin/performance` for:
- Route performance rankings
- Slow queries
- Resource usage trends
- Real-time metrics

## Best Practices

### Before Testing

```bash
# 1. Clear old performance data
# Click "Clear All Data" in /admin/performance

# 2. Ensure stable environment
# No other heavy processes running

# 3. Prepare monitoring
# Open performance monitor in browser
# Start htop in separate terminal
```

### During Testing

- ✅ Monitor system resources
- ✅ Watch error logs
- ✅ Note when degradation starts
- ✅ Check database connections
- ❌ Don't run multiple tests simultaneously

### After Testing

```bash
# 1. Analyze results
./tests/performance/utils/analyze-results.sh

# 2. Check performance monitor
# Review route rankings and slow queries

# 3. Document findings
# Note bottlenecks and errors

# 4. Wait before next test
# Let system return to baseline (30-60s)
```

## Progressive Testing Strategy

1. **Week 1: Baseline**
   - Run baseline test
   - Document current performance
   - Identify obvious issues

2. **Week 2: Light Load**
   - Store flow: 10 users
   - Vote rush: Light spike
   - Admin panel: 10 users

3. **Week 3: Moderate Load**
   - Store flow: 25-50 users
   - Sustained load for 10+ minutes
   - Monitor resource usage

4. **Week 4: Heavy Load**
   - Store flow: 100+ users
   - Find breaking point
   - Test recovery

5. **Week 5: Optimization**
   - Fix bottlenecks
   - Re-run tests
   - Verify improvements

## Common Issues & Solutions

### High Error Rates

**Check:**
- Laravel logs for exceptions
- Database connection pool
- Memory limits
- Request timeouts

**Fix:**
- Increase PHP-FPM workers
- Optimize slow queries
- Add database indexes
- Implement caching

### Slow Response Times

**Check:**
- Performance monitor → Route rankings
- Slow query logs
- Database connection count
- Memory usage

**Fix:**
- Add query caching
- Optimize N+1 queries
- Enable OpCache
- Add Redis for sessions

### Connection Timeouts

**Check:**
- Server resources (CPU/Memory)
- Network bandwidth
- Database max connections
- PHP-FPM queue

**Fix:**
- Scale server resources
- Implement connection pooling
- Increase timeout limits
- Add load balancing

## Files Generated

```
tests/performance/results/
├── baseline-summary.json       # Baseline test results
├── store-flow-summary.json     # Store flow results
├── vote-rush-summary.json      # Vote rush results
├── admin-panel-summary.json    # Admin panel results
└── simple-test-*.log           # Simple test logs
```

## Environment Variables

```bash
# Base URL for testing
export BASE_URL="https://your-app.replit.dev"

# Simple test configuration
export CONCURRENT_USERS=20      # Number of parallel users
export REQUESTS_PER_USER=50     # Requests each user makes

# k6 overrides (in config.js)
export K6_VUS=100               # Virtual users
export K6_DURATION="10m"        # Test duration
```

## Next Steps

After running tests:

1. **Review Results**
   - Check test summaries
   - Identify bottlenecks
   - Note error patterns

2. **Optimize**
   - Fix slow routes
   - Add database indexes
   - Implement caching
   - Optimize queries

3. **Verify**
   - Re-run tests
   - Compare before/after
   - Document improvements

4. **Scale**
   - Plan capacity needs
   - Set up monitoring
   - Prepare for production

## Resources

- 📖 [Detailed Setup Guide](SETUP.md)
- 📖 [Stress Testing Strategy](../../STRESS_TESTING_GUIDE.md)
- 🔗 [k6 Documentation](https://k6.io/docs/)
- 🔗 [Laravel Performance](https://laravel.com/docs/optimization)

## Support

**Questions or issues?**
- Check SETUP.md for detailed instructions
- Review STRESS_TESTING_GUIDE.md for methodology
- Check k6 documentation for script help

---

**Happy Testing! 🚀**
