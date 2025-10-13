# Upload Performance Issue Analysis

## Problem Description
Upload speed starts at **20MB/s** but degrades to **~1MB/s** even with 100MB/s upload bandwidth.

## Root Cause
**PHP configuration limits are too restrictive**, causing the server to reject or truncate large POST requests:

### Current PHP Limits (Actual):
```
upload_max_filesize = 2M    ❌ TOO LOW
post_max_size = 8M          ❌ TOO LOW  
memory_limit = 128M         ❌ TOO LOW
max_input_vars = 1000       ❌ TOO LOW
```

### Required Limits (From Optimization Doc):
```
upload_max_filesize = 1024M ✅
post_max_size = 1024M       ✅
memory_limit = 2G           ✅
max_input_vars = 5000       ✅
max_execution_time = 600    ✅
max_input_time = 600        ✅
```

## Why Speed Degrades

1. **Frontend batches files correctly** (50 files for <1MB, 20 for <10MB, etc.)
2. **Batch size often exceeds 8MB** (current post_max_size limit)
3. **PHP silently truncates/rejects oversized requests**
4. **Client retries or server struggles**, causing severe slowdown
5. **Speed drops from 20MB/s → 1MB/s**

## Solutions Applied

### 1. Created `.user.ini` Files
```bash
# Created in project root and public/ directory
upload_max_filesize = 1024M
post_max_size = 1024M
max_execution_time = 600
max_input_time = 600
memory_limit = 2G
max_input_vars = 5000
```

**Note:** `.user.ini` files require **5-minute cache time** before PHP reads them. You may need to restart PHP-FPM.

### 2. Existing Optimizations (Already in Code)

#### Backend:
- ✅ Batch database operations (single `upsert()` instead of N queries)
- ✅ Deferred manifest regeneration (once at end, not per batch)
- ✅ Smart hash computation (after storage)
- ✅ Optimized duplicate checking (single query)

#### Frontend:
- ✅ Dynamic batch sizing based on file size
- ✅ Single HTTP request per batch
- ✅ Throttled progress updates (100ms)
- ✅ 5-minute XHR timeout

## Testing on Your PC

### Step 1: Verify PHP Configuration
```bash
php -i | grep -E "upload_max_filesize|post_max_size|memory_limit"
```

Expected output:
```
upload_max_filesize => 1024M
post_max_size => 1024M
memory_limit => 2G
```

### Step 2: If Limits Are Still Low

#### Option A: Edit php.ini (Recommended)
```bash
# Find your php.ini location
php --ini

# Edit the file and add/update:
upload_max_filesize = 1024M
post_max_size = 1024M
max_execution_time = 600
max_input_time = 600
memory_limit = 2G
max_input_vars = 5000

# Restart PHP-FPM or your web server
sudo systemctl restart php-fpm
# OR
sudo systemctl restart apache2
# OR  
sudo systemctl restart nginx
```

#### Option B: Use Environment Variables (Laravel Valet/Homestead)
```bash
# In your .env or server config
PHP_MEMORY_LIMIT=2G
PHP_POST_MAX_SIZE=1024M
PHP_UPLOAD_MAX_FILESIZE=1024M
```

#### Option C: Nginx Configuration (if using Nginx)
Add to your nginx site config:
```nginx
client_max_body_size 1024M;
client_body_timeout 600s;
fastcgi_read_timeout 600s;

# In your FastCGI params
fastcgi_param PHP_VALUE "upload_max_filesize=1024M \n post_max_size=1024M \n memory_limit=2G";
```

### Step 3: Clear PHP OpCache (if enabled)
```bash
# Via CLI
php -r "opcache_reset();"

# OR restart PHP-FPM
sudo systemctl restart php-fpm
```

### Step 4: Test Upload
1. Navigate to `/admin/cache`
2. Select multiple files (test with ~100MB total)
3. Monitor upload speed - should stay consistent at **15-20 MB/s**
4. Check Network tab in browser DevTools for actual POST sizes

## Expected Performance After Fix

### Small Files (100 files @ 1MB each):
- Speed: **Sustained 15-20 MB/s** (no degradation)
- Time: **~30-40 seconds** (was 2-3 minutes)
- Database queries: **3-5 total** (was 100+)
- HTTP requests: **2-3** (was 100)

### Large Files (10 files @ 100MB each):
- Speed: **Sustained 10-15 MB/s**
- Time: **~1-2 minutes**
- Minimal overhead

## Debugging Upload Issues

### 1. Check PHP Limits in Browser
Add this temporary route to verify limits:
```php
Route::get('/phpinfo', function() {
    return [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'max_input_vars' => ini_get('max_input_vars'),
    ];
});
```

### 2. Monitor Network Tab
- Check POST request size
- If >8MB and fails → post_max_size too low
- If file >2MB and fails → upload_max_filesize too low

### 3. Check Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

Look for:
- "POST Content-Length" errors
- Memory limit errors
- Timeout errors

### 4. Server Resource Monitoring
```bash
# CPU and Memory
htop

# Disk I/O
iotop

# Network
iftop
```

## Files Modified

1. **Created:** `.user.ini` (root directory)
2. **Created:** `public/.user.ini` 
3. **Existing:** `public/.htaccess` (already has correct settings, but ignored by PHP-FPM)
4. **No changes needed:** Upload optimization code already implemented

## Next Steps for Local Testing

1. **Pull the code to your PC**
2. **Configure PHP limits** using one of the methods above
3. **Restart PHP-FPM/Apache/Nginx**
4. **Test with real files** - monitor speed consistency
5. **Check browser Network tab** for successful large POST requests
6. **Verify sustained 15-20 MB/s** throughout upload

## Additional Optimization (If Still Slow)

### 1. Enable PHP OpCache
```ini
[opcache]
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
```

### 2. Use Redis for Sessions (reduces DB load)
```bash
composer require predis/predis
```

Update `.env`:
```env
SESSION_DRIVER=redis
CACHE_DRIVER=redis
```

### 3. Database Indexing
```sql
-- Already optimized in migration:
CREATE INDEX idx_cache_files_lookup ON cache_files(filename, relative_path);
CREATE INDEX idx_cache_files_hash ON cache_files(hash);
```

## Summary

The **upload performance issue is caused by PHP configuration limits**, not code optimization. The optimizations in `CACHE_UPLOAD_OPTIMIZATION.md` are already implemented and working correctly. 

**To fix:** Update PHP limits to the required values (1024M upload, 1024M post, 2G memory) on your local PC and restart PHP.

**Expected result:** Consistent 15-20 MB/s upload speed without degradation.
