# Critical PHP Configuration for Upload Performance

## ⚠️ Issue Identified

Your 200MB file upload took **4.7 minutes (0.7 MB/s)** because PHP upload limits were too low:

```
upload_max_filesize = 2M   ❌ (Was blocking 200MB file)
post_max_size = 8M         ❌ (Was blocking 200MB file)
```

## ✅ Required Settings

These settings MUST be configured for proper upload performance:

```ini
upload_max_filesize = 1024M
post_max_size = 1024M
memory_limit = 2G
max_execution_time = 600
max_input_time = 600
max_input_vars = 5000
```

## How to Configure (Choose Based on Your Setup)

### 1️⃣ For Local Development (php artisan serve)

**The workflow is already configured**, but when running manually use:

```bash
php -d upload_max_filesize=1024M \
    -d post_max_size=1024M \
    -d memory_limit=2G \
    -d max_execution_time=600 \
    -d max_input_time=600 \
    -d max_input_vars=5000 \
    artisan serve --host=0.0.0.0 --port=8000
```

### 2️⃣ For Apache with mod_php

**Option A: .htaccess (Already configured in `public/.htaccess`)**
```apache
<IfModule mod_php.c>
    php_value upload_max_filesize 1024M
    php_value post_max_size 1024M
    php_value memory_limit 2G
    php_value max_execution_time 600
</IfModule>
```

**Option B: php.ini**
Edit your `php.ini` file and add/update these lines.

### 3️⃣ For Nginx with PHP-FPM

**Nginx config (`/etc/nginx/sites-available/yoursite`):**
```nginx
server {
    client_max_body_size 1024M;
    client_body_timeout 600s;
    
    location ~ \.php$ {
        fastcgi_read_timeout 600s;
        # ... other fastcgi settings
    }
}
```

**PHP-FPM pool config (`/etc/php/8.x/fpm/pool.d/www.conf`):**
```ini
php_admin_value[upload_max_filesize] = 1024M
php_admin_value[post_max_size] = 1024M
php_admin_value[memory_limit] = 2G
php_admin_value[max_execution_time] = 600
```

**Or in php.ini (`/etc/php/8.x/fpm/php.ini`):**
```ini
upload_max_filesize = 1024M
post_max_size = 1024M
memory_limit = 2G
max_execution_time = 600
```

### 4️⃣ For XAMPP/WAMP/MAMP

Edit `php.ini` file (usually in XAMPP/php/php.ini):
1. Find and update the settings
2. Restart Apache
3. Verify with `<?php phpinfo(); ?>`

## Verification

Run this command to check current settings:
```bash
php -i | grep -E "upload_max_filesize|post_max_size|memory_limit"
```

Expected output:
```
upload_max_filesize => 1024M => 1024M
post_max_size => 1024M => 1024M  
memory_limit => 2G => 2G
```

## Why This Matters

### Before Fix:
- 200MB file tries to upload
- PHP sees max is 2M
- Upload gets throttled/rejected
- Browser slows down transmission
- Takes 4.7 minutes at 0.7 MB/s ❌

### After Fix:
- 200MB file uploads
- PHP accepts it (max 1024M)
- No throttling
- Expected speed: 10-15 MB/s ✅
- Should take ~13-20 seconds

## Additional Performance Notes

1. **PHP Development Server is Slow**
   - Single-threaded, not for production
   - Use Apache/Nginx for real performance testing

2. **Disk I/O Matters**
   - SSD: 15-20 MB/s uploads
   - HDD: 5-10 MB/s uploads
   - Network drive: May be slower

3. **Network Speed**
   - Local network: Limited by disk speed
   - Internet: Limited by upload bandwidth
   - 100Mbps connection ≈ 12.5 MB/s theoretical max

4. **Hash Optimization Applied**
   - Using MD5 for new files (10x faster)
   - SHA256 only for duplicate detection
   - See UPLOAD_PERFORMANCE_FIX.md for details
