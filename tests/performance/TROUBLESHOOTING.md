# Performance Testing Troubleshooting Guide

## Common Issues & Solutions

### High Error Rates in Tests

#### Cache Downloads Test - 40% Error Rate

**Problem:** Cache downloads test shows high failure rate (40%)

**Causes:**
1. **No manifest file exists** - `downloadManifest()` returns 302 redirect
2. **No patches created** - Returns 404 responses
3. **No cache files uploaded** - Empty system

**Expected Behavior:**
- 404/422 responses are **normal** if no cache files/patches exist yet
- 302 redirects are **normal** for missing manifest
- These are handled gracefully in the test

**Solution:**
Upload some cache files first:
1. Go to `/admin/cache`
2. Upload a cache `.tar` file
3. Generate patches if needed
4. Re-run the test

**Acceptable Error Rates:**
- With empty system: up to 40% (expected)
- With cache files: should be <10%

---

#### Store Flow Test - Username Set Failures

**Problem:** Cart operations fail with "username required" error

**Cause:** Username not set before cart operations

**Solution:** ✅ Fixed - Test now sets username before cart operations

**Route:** `POST /store/set-user`
**Format:** `application/x-www-form-urlencoded`

---

#### Vote Rush Test - Username Set Failures

**Problem:** Vote operations fail

**Cause:** Username not set before voting

**Solution:** ✅ Fixed - Test now sets username correctly

**Route:** `POST /vote/set-username`
**Format:** `application/x-www-form-urlencoded`

---

#### Client Downloads Test - 404 Responses

**Problem:** All downloads return 404

**Cause:** No client files uploaded

**Solution:**
1. Go to `/admin/clients`
2. Upload client files for Windows, Mac, Linux
3. Set versions to "latest"
4. Re-run test

**Expected:** 404 is OK if no clients exist

---

### Performance Issues

#### Slow Response Times (>2000ms)

**Check:**
```bash
# View slow queries
open http://your-app/admin/performance

# Check Laravel logs
tail -f storage/logs/laravel.log
```

**Common Causes:**
- Missing database indexes
- N+1 query problems
- Large file operations
- Insufficient server resources

**Solutions:**
- Add indexes to frequently queried columns
- Use eager loading for relationships
- Implement query caching
- Increase PHP memory limit

---

#### Memory Errors

**Symptoms:**
- Tests fail after running for a while
- "Allowed memory size exhausted" errors
- Server becomes unresponsive

**Solution:**
```bash
# Increase PHP memory in workflow
php -d memory_limit=2G artisan serve --host=0.0.0.0 --port=5000
```

Already configured in the Server workflow!

---

### Test-Specific Issues

#### Baseline Test Failures

**Expected:** Should have 0% error rate

**If failing:**
1. Check if server is running
2. Verify routes are accessible
3. Check for PHP errors in logs
4. Clear Laravel cache: `php artisan cache:clear`

---

#### Download Tests Timeout

**Cause:** Files are too large or server is slow

**Solution:**
```javascript
// Increase timeout in test file
timeout: '120s'  // Change from 60s
```

Or test with smaller files first.

---

## Understanding Test Results

### Success Rates

| Test | Good | Warning | Critical |
|------|------|---------|----------|
| **Baseline** | >99% | 95-99% | <95% |
| **Store Flow** | >95% | 90-95% | <90% |
| **Vote Rush** | >90% | 85-90% | <85% |
| **Admin Panel** | >95% | 90-95% | <90% |
| **Client Downloads** | >90% | 80-90% | <80% |
| **Cache Downloads** | >60% | 40-60% | <40% |

**Note:** Cache downloads has lower target because 404s are expected on empty systems.

---

### Response Time Targets

| Metric | Good | Warning | Critical |
|--------|------|---------|----------|
| **Average** | <300ms | 300-800ms | >800ms |
| **P95** | <500ms | 500-1000ms | >1000ms |
| **P99** | <1000ms | 1000-2000ms | >2000ms |
| **Max** | <2000ms | 2000-5000ms | >5000ms |

---

## HTTP Status Code Reference

### Expected Status Codes

| Code | Meaning | When It's OK |
|------|---------|--------------|
| **200** | Success | Always good |
| **302** | Redirect | OK for manifest download, auth redirects |
| **404** | Not Found | OK for missing cache/patches/clients |
| **422** | Validation Error | OK for invalid version numbers |

### Concerning Status Codes

| Code | Meaning | Action Required |
|------|---------|-----------------|
| **500** | Server Error | Check logs immediately |
| **503** | Service Unavailable | Server overloaded or down |
| **419** | CSRF Token Expired | Session/cache issue |
| **429** | Too Many Requests | Rate limiting triggered |

---

## Debugging Workflow

### 1. Identify the Problem

```bash
# Run test
./tests/performance/run-tests.sh

# Analyze results
./tests/performance/utils/analyze-results.sh
```

### 2. Check Logs

```bash
# Laravel application logs
tail -f storage/logs/laravel.log

# Server workflow logs
# Check via Replit console

# Performance monitor
open http://your-app/admin/performance
```

### 3. Isolate the Issue

Run tests individually:
```bash
k6 run tests/performance/scenarios/baseline.js
k6 run tests/performance/scenarios/store-flow.js
k6 run tests/performance/scenarios/vote-rush.js
```

### 4. Fix and Verify

```bash
# After fixing, clear performance data
# Go to /admin/performance → Click "Clear All Data"

# Re-run test
./tests/performance/run-tests.sh
```

---

## Common Error Messages

### "username is required"
**Test:** Store Flow, Vote Rush  
**Fix:** ✅ Already fixed - username is now set before operations

### "Manifest file not found"
**Test:** Cache Downloads  
**Fix:** Upload cache files or accept 302 redirect as OK

### "No patches available"
**Test:** Cache Downloads  
**Fix:** Normal if no cache changes - 404 is expected

### "Client not found"
**Test:** Client Downloads  
**Fix:** Upload client files or accept 404 as OK

### "CSRF token mismatch"
**All Tests**  
**Fix:** Tests should not encounter this (API/JSON routes)  
**If occurs:** Check that routes don't require CSRF

---

## Performance Optimization Checklist

After identifying slow routes:

- [ ] Check database queries (N+1 problems)
- [ ] Add missing indexes
- [ ] Enable query caching
- [ ] Implement Redis for sessions
- [ ] Enable OPcache
- [ ] Optimize large file operations
- [ ] Add CDN for static assets
- [ ] Implement response caching
- [ ] Review slow query logs
- [ ] Monitor memory usage

---

## Getting Help

### Before Asking for Help

Gather this information:

1. **Test Results:**
   ```bash
   ./tests/performance/utils/analyze-results.sh > test-results.txt
   ```

2. **Error Logs:**
   ```bash
   tail -200 storage/logs/laravel.log > errors.txt
   ```

3. **Performance Data:**
   - Screenshot of `/admin/performance`
   - Slowest routes
   - Error rates

4. **System Info:**
   - PHP version: `php -v`
   - Memory limit: `php -i | grep memory_limit`
   - Laravel version: `php artisan --version`

### Include in Report

```
Test: [which test failed]
Error Rate: [percentage]
Average Response Time: [ms]
Error Messages: [from logs]
System State: [empty/has data]
```

---

## Quick Fixes

### Clear Everything and Start Fresh

```bash
# Clear performance data
# Go to /admin/performance → "Clear All Data"

# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart server
# Stop and restart the Server workflow

# Re-run tests
./tests/performance/run-tests.sh
```

### Reset Test Results

```bash
rm -rf tests/performance/results/*
```

---

## FAQ

**Q: Why does cache download test have 40% errors?**  
A: Normal if no cache files exist. 404/302 responses are expected.

**Q: Should I worry about 404 responses?**  
A: Not for cache/patch/client downloads on empty systems.

**Q: What's a good baseline for response times?**  
A: Under 300ms average, under 500ms P95.

**Q: How often should I run tests?**  
A: After major changes, before deployment, weekly for monitoring.

**Q: Can I run tests in production?**  
A: **NO!** Only run on development/staging environments.

---

## Next Steps

1. ✅ Fix any critical errors (>50% failure rate)
2. ✅ Optimize slow routes (>1000ms P95)
3. ✅ Add missing data (cache files, clients)
4. ✅ Re-run tests to verify improvements
5. ✅ Document performance baseline
6. ✅ Set up automated testing (optional)

---

**Last Updated:** October 19, 2025
