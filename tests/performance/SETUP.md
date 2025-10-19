# Stress Testing Setup & Usage

## Quick Start

### 1. Install k6 (Recommended)

**Linux:**
```bash
sudo gpg -k
sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update
sudo apt-get install k6
```

**Mac:**
```bash
brew install k6
```

**Windows:**
```powershell
choco install k6
```

### 2. Make Scripts Executable

```bash
chmod +x tests/performance/run-tests.sh
chmod +x tests/performance/utils/*.sh
```

### 3. Run Tests

```bash
# Interactive menu
./tests/performance/run-tests.sh

# Or run specific tests directly
k6 run tests/performance/scenarios/baseline.js
k6 run tests/performance/scenarios/store-flow.js
k6 run tests/performance/scenarios/vote-rush.js
k6 run tests/performance/scenarios/admin-panel.js

# Or use simple curl-based test (no k6 required)
./tests/performance/utils/simple-load-test.sh
```

## Test Scenarios

### Baseline Test
- **Duration:** ~30 seconds
- **Users:** 1
- **Purpose:** Establish baseline performance metrics
- **Use when:** Starting testing, after optimizations

```bash
k6 run tests/performance/scenarios/baseline.js
```

### Store Flow Load Test
- **Duration:** ~12 minutes
- **Users:** Ramps 10 → 50 → 0
- **Purpose:** Simulate customer shopping journey
- **Includes:** Browse → Add to cart → View cart → Update cart

```bash
k6 run tests/performance/scenarios/store-flow.js
```

### Vote Rush Spike Test
- **Duration:** ~4 minutes
- **Users:** Spike from 10 → 100 → 10
- **Purpose:** Test vote system under sudden traffic spike
- **Includes:** Vote page → Set username → Check status → View stats

```bash
k6 run tests/performance/scenarios/vote-rush.js
```

### Admin Panel Load Test
- **Duration:** ~12 minutes
- **Users:** Ramps 10 → 50 → 0
- **Purpose:** Test admin operations under load
- **Includes:** Dashboard → Performance monitor → Cache manager → Orders

```bash
k6 run tests/performance/scenarios/admin-panel.js
```

## Configuration

### Environment Variables

```bash
# Set custom base URL
export BASE_URL="https://your-app.replit.dev"

# For simple load test
export CONCURRENT_USERS=20
export REQUESTS_PER_USER=50

# Run test
./tests/performance/utils/simple-load-test.sh
```

### Custom Load Profiles

Edit `tests/performance/config.js` to customize:
- Number of virtual users
- Test duration
- Ramp-up patterns
- Performance thresholds

## Analyzing Results

### Quick Analysis

```bash
./tests/performance/utils/analyze-results.sh
```

### Detailed Metrics

Results are saved in `tests/performance/results/`:
- `*-summary.json` - k6 test results (JSON format)
- `simple-test-*.log` - Simple test raw data

### View in Performance Monitor

Navigate to `/admin/performance` in your browser to see real-time metrics captured during the test.

## Tips for Effective Testing

### 1. Clear Performance Data Before Testing

```bash
# Via browser: Click "Clear All Data" button in /admin/performance
# Or via curl:
curl -X DELETE https://your-app/admin/performance/clear-all
```

### 2. Monitor During Tests

Keep these open in separate terminals/tabs:
```bash
# Terminal 1: Run test
./tests/performance/run-tests.sh

# Terminal 2: Watch system resources
htop

# Terminal 3: Watch Laravel logs
tail -f storage/logs/laravel.log

# Browser: Performance monitor
open http://your-app/admin/performance
```

### 3. Test Progression

Recommended order:
1. **Baseline** - Establish single-user performance
2. **Light load** - 10-25 users
3. **Moderate load** - 50-100 users
4. **Spike test** - Sudden traffic bursts
5. **Endurance** - Long-running tests (hours)

### 4. Between Tests

- Wait 30-60 seconds between tests
- Check for error logs
- Verify system has returned to normal state
- Clear browser/app caches if needed

## Interpreting Results

### Good Performance
```
✓ Error rate < 1%
✓ P95 response time < 500ms (pages) or < 300ms (API)
✓ No memory leaks (stable usage)
✓ No database connection exhaustion
```

### Warning Signs
```
⚠ Error rate 1-5%
⚠ P95 response time 500-1000ms
⚠ Memory usage growing over time
⚠ Database connections near limit
```

### Critical Issues
```
✗ Error rate > 5%
✗ P95 response time > 2000ms
✗ Requests timing out
✗ Out of memory errors
✗ Database connection pool exhausted
```

## Troubleshooting

### k6 Not Found
```bash
# Verify installation
k6 version

# If not installed, use simple test instead
./tests/performance/utils/simple-load-test.sh
```

### Permission Denied
```bash
chmod +x tests/performance/run-tests.sh
chmod +x tests/performance/utils/*.sh
```

### Connection Refused
- Ensure the application is running
- Check the BASE_URL in config.js
- Verify the server is accessible

### High Error Rates
- Check Laravel logs: `storage/logs/laravel.log`
- Review performance monitor: `/admin/performance`
- Check database connections
- Verify server resources (CPU/Memory)

## Advanced Usage

### Custom Test Script

Create your own test in `tests/performance/scenarios/`:

```javascript
import http from 'k6/http';
import { check, sleep } from 'k6';
import { config } from '../config.js';

export let options = {
    vus: 10,
    duration: '5m'
};

export default function () {
    const res = http.get(config.baseUrl + '/your-route');
    check(res, { 'status 200': (r) => r.status === 200 });
    sleep(1);
}
```

Run it:
```bash
k6 run tests/performance/scenarios/your-test.js
```

### CI/CD Integration

Add to your workflow:

```yaml
- name: Performance Test
  run: |
    k6 run --quiet tests/performance/scenarios/baseline.js
```

## Next Steps

1. ✅ Run baseline test
2. ✅ Review results in performance monitor
3. ✅ Identify bottlenecks
4. ✅ Optimize slow routes/queries
5. ✅ Re-test to verify improvements
6. ✅ Scale up load tests progressively

## Resources

- [k6 Documentation](https://k6.io/docs/)
- [Performance Testing Guide](../STRESS_TESTING_GUIDE.md)
- [Laravel Optimization](https://laravel.com/docs/optimization)
